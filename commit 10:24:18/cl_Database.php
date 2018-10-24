<?php
// database calls.  All functions have an extra parameter
// called $type which is optional and defaults to "mysql".  This adds a layer of abstraction
// making it easier to change DBs.  Methods included in this class, in order are:
//
// - [constructor method] DB($server, $server_username, $server_password, $database, $type='mysql')
// - query($query, $type='mysql')
// - fetch_array($result, $type='mysql')
// - fetch_assoc($result, $type='mysql')
// - num_rows($result, $type='mysql')
// - insert_id($link=0, $type='mysql');
// - update($data, $primary_key=DEFAULT_KEY, $table=DEFAULT_TABLE)
// - insert($data, $primary_key=DEFAULT_KEY, $table=DEFAULT_TABLE)
// - list_fields($table, $name=DB_NAME, $type=DB_TYPE)
// - field_name($field_list, $index, $type=DB_TYPE)
// - field_type($field_list, $index, $type=DB_TYPE)
// - field_len($resource, $offset)
// - get_field_lengths($table_name, $name-DB_NAME, $type=DB_TYPE)
// - num_fields($field_list, $type=DB_TYPE)
// - get_field_types($table, $type=DB_TYPE)
// - make_filter($criteria, $type=DB_TYPE)
// - save_user_query($query)

class DB {

  var $link_identifier = 0;
  var $lengths = array();

function DB($server=DB_SERVER, $username=DB_USERNAME, $pass=DB_PASS, $name=DB_NAME, $type=DB_TYPE) {
   if(DEBUG) {
       if (!$this->link_identifier = mysql_pconnect($server, $username, $pass)) {
         $_SESSION['application_error'] .= "<p><strong>DATABASE ERROR</strong> (method: DB->DB): unable to connect to database server</p>";
       }
       if ($this->link_identifier) {
           if (!mysql_select_db($name)) {
            $_SESSION['application_error'] .= "<p><strong>DATABASE ERROR</strong> (method: DB->DB): unable to select database</p>";
           }
       }
   } else {
       $this->link_identifier = mysqli_connect($server, $username, $pass, $name);
      // if ($this->link_identifier) {mysqli_select_db($name);}
   }
   mysqli_set_charset($this->link_identifier, 'latin1');
   //$charset = mysql_client_encoding($this->link_identifier);
   //echo "The current character set is: $charset\n";
   return $this->link_identifier;
}

function query($query, $type=DB_TYPE) {
  if (DEBUG){
      if(!$result = mysqli_query($result, $query)){
         $_SESSION['application_error'] .= "<p><strong>DATABASE ERROR</strong> (method: DB->query):"
                                        ."\n - Error number " . mysqli_errno() . " . :  " . mysqli_error()
                                        . "\n - Failed query: " . $query;
      }
  } else {
      $result = mysqli_query($result, $query);
  }
  return $result;
}

function fetch_array($result, $type=DB_TYPE) {
    return mysqli_fetch_array($result);
}

function fetch_assoc($result, $type=DB_TYPE) {
    return mysqli_fetch_assoc($result);
}

function num_rows($result, $type=DB_TYPE) {
    return mysqli_num_rows($result);
}

function insert_id($link=0, $type=DB_TYPE){
  if (!$link && $this->link_identifier) {$link = $this->link_identifier;}
  return mysqli_insert_id($link);
}

// returns a list of fields with integer index
function list_fields($table, $db_name=DB_NAME, $type=DB_TYPE) {
   return mysqli_query($result, 'SHOW COLUMNS FROM '.$table);
}

function field_name($field_list, $index, $type=DB_TYPE) {
  $fieldinfo=mysqli_fetch_field_direct($name);
  //return mysql_field_name($field_list, $index);
  return $fieldinfo->name;
}

function field_type($field_list, $index, $type=DB_TYPE) {
  return mysql_field_type($field_list, $index);
}

function field_len($resource, $offset)
{
  return mysql_field_len($resource, $offset);
}

function get_field_lengths($table_name, $db_name=DB_NAME, $type=DB_TYPE)
{
  $fields = $this->list_fields($table_name, $db_name);
  $num_fields = $this->num_fields($fields);
  for ($i=0; $i<$num_fields; $i++)
  {
      $this->lengths[$table_name][$this->field_name($fields, $i)] = $this->field_len($fields, $i);
  }
  return true;
}

function num_fields($resource, $type=DB_TYPE) {
  return mysql_num_fields($resource);
}

function get_field_name_array($table, $name=DB_NAME, $type=DB_TYPE) {
  $field_list = $this->list_fields($table);
  $num_fields = $this->num_fields($field_list);
  for ($i=0; $i<$num_fields; $i++) {
     $fields[$i] = $this->field_name($field_list, $i);
  }
  return $fields;
}

// not used unless switching from MySQL to other db
//
function get_field_types($table, $name=DB_NAME, $type=DB_TYPE) {
  $field_list = $this->list_fields($table);
  $num_fields = $this->num_fields($field_list);
  for ($i=0; $i<$num_fields; $i++) {
     $key = $this->field_name($field_list, $i);
     $value = $this->field_type($field_list, $i);
     $fields[$key] = $value;
  }
  return $fields;
}

/* function make_filter
// PARAMETERS: array criteria
//   INDEXES on criteria
//     tables[]: list of tables to call
//     keys[]: primary key for each table.
//     haystack[]: columns to search on.  Must have values in form table.col to work with multiple tables
//     needle: string to look for.
//     operator: join operator - AND, OR, etc.  Defaults to AND.  Does not yet support complex queries
// RETURNS
//    filter upon success
//    false upon failure
//
// NOTES
//
// 1. sizeof(tables[]) must = sizeof(keys[]).  See #4
// 2. sizeof(haystack[]) must = sizeof(needle[]).  See #4
// 3. You can't simply do sizeof to test actually size of array because
//    POST will declare all form elements, but some will be empty.  You must
//    test each tables/keys or haystack/needle pair.
// 4. POST passes all data as strings,  so in some DB systems there may be a
//    need to convert type if LIKE does not work with number cols etc.
//    If there is need to test field types in non-MySQL db, first get an
//    associative array in the form $array['fieldname'] = fieldtype, as
//        $field_types = $db->get_field_types
//    This will make it necessary to pass an object of class DB to make the database
//    functions available.  Then create an array of types to test against, such as
//        $string_types = array('string', 'blob');
//    Finally, in the appropriate case, test
//        if (in_array($field_types[$criteria['haystack'][$i]], $string_types)) {do stuff}
*/
function make_filter($criteria) {
  $col_string = "";
  $from = "";
  $where = "";
  $order_by = "";

// QUIT if problem
  if(sizeof($criteria['tables']) != sizeof($criteria['keys'])) {
     echo "<br />ERROR: each table must have exactly one key declared and that must be the primary key<br />";
     return FALSE;
  }

// get list of tables for this query.
  for($i=0; $i<sizeof($criteria['tables']); $i++) {
     if($from) {$from .= ", ";}
     $from .=$criteria['tables'][$i];
  }

// select primary keys in the form table.key for all tables needed
  for($i=0; $i<sizeof($criteria['keys']); $i++) {
     if($col_string) {$col_string .= ", ";}
     if ($criteria['keys'][$i] == PERSON_MODIFIED_DATE) {
        $colstring .= "date_format(".$criteria['tables'][$i] . "." . $criteria['keys'][$i].", '%m/%d/%Y') as mod_date";
     } else {
        $col_string .= $criteria['tables'][$i] . "." . $criteria['keys'][$i];
     }
  }

// select pk1, pk2 ... from table1, table2
  $query = "SELECT $col_string FROM $from WHERE " . PERSON_DELETE_REC . "='0' AND ";

// if user is in SQL Mode - try user query.  If valid, save and exit
  if (isset($criteria['search_sql'])) {
    $query .= StripSlashes($criteria['where_clause']);
    if($this->query($query)) {
       $this->save_user_query($query);
       return $query;
    } else {
       return FALSE;
    }
  }

// if in wizard mode, build a query.
  $i=0;
  while(isset($criteria['needle'][$i]) && !empty($criteria['needle'][$i])
     && isset($criteria['haystack'][$i]) && !empty($criteria['haystack'][$i])) {
     if (  $i>0
           && !empty($where)
           && isset($criteria['operator'][($i-1)])
           && !empty($criteria['operator'][($i-1)])) {
        $where .= " " . $criteria['operator'][($i-1)] . " ";
     } else if ($where) {
        $where .= " AND ";  // always default to AND
     }

     if ($criteria['haystack'][$i] == PERSON_MODIFIED_BY) {
        $firstname = "";
        $lastname = "";
        $username = "";
        $match = FALSE;
        $got_names = FALSE;
        $modified_by = explode(",", $criteria['needle'][$i]);
        if (sizeof($modified_by) > 1) {
           $lastname = trim($modified_by[0]);
           $firstname = trim($modified_by[1]);
           $got_names = TRUE;
        }
        if (!$got_names) {
           $modified_by = explode(" ", $criteria['needle'][$i]);
           if (sizeof($modified_by) > 1) {
              $firstname = array_shift($modified_by);
              $lastname = implode(" ", $modified_by);
              $got_names = TRUE;
           }
        }
        if ($got_names && $criteria['match_type'][$i] == 1) {
            $modified_by_query = "SELECT user_id FROM users WHERE (lastname='". $lastname
                                 ."' AND firstname='". $firstname . "')";
           if ($modified_by_result = $this->query($modified_by_query)) {
              if ($this->num_rows($modified_by_result)) {
                $match = TRUE;
                $modified_by = $this->fetch_assoc($modified_by_result);
                $where .= " modified_by=" . $modified_by['user_id'];
              }
           }
           if (!$match) { // maybe names are reversed
              $modified_by_query = "SELECT user_id FROM users WHERE (lastname='". $firstname
                                 ."' AND firstname='". $lastname . "')";
              if ($modified_by_result = $this->query($modified_by_query)) {
                 if ($this->num_rows($modified_by_result)) {
                   $match = TRUE;
                   $modified_by = $this->fetch_assoc($modified_by_result);
                   $where .= " modified_by=" . $modified_by['user_id'];
                 }
             }
           }
        }
        if (!$match) { // now we just try a fulltext search
           $modified_by_query = "SELECT user_id FROM users WHERE MATCH (firstname,lastname,username) AGAINST ('".$criteria['needle'][$i]."')";
           if ($modified_by_result = $this->query($modified_by_query)) {
              if ($this->num_rows($modified_by_result)) {
                $match = TRUE;
                $modified_by = $this->fetch_assoc($modified_by_result);
                $where .= " modified_by=" . $modified_by['user_id'];
              }
           }
        }
     } else {
        if ($criteria['haystack'][$i] == PERSON_MODIFIED_DATE) {
           $search_date = strtotime($criteria['needle'][$i]);
           if($search_date != -1) {
              $where .=  "date_format(".PERSON_MODIFIED_DATE.", '%m/%d/%Y')";
              $criteria['needle'][$i] = date('m/d/Y', $search_date);
           } else {
              $_SESSION['application_error'] = ERR_INVALID_DATE;
           }
        } else {
            $where.= $criteria['haystack'][$i];
        }
            switch ($criteria['match_type'][$i]) {
              case 0: //included in
                   $where .= " LIKE '%" . $criteria['needle'][$i] . "%'";
                   break;
              case 1: // exact match
                   $where .= " = '" . $criteria['needle'][$i] . "'";
                   break;
              case 2: //begins with
                   $where .= " LIKE '" . $criteria['needle'][$i] . "%'";
                   break; //only break if string, otherwise default to error
              case 3: //ends with
                   $where .= " LIKE '%" . $criteria['needle'][$i] . "'";
                   break;
              case 4:  // greater than
                   $where .= " < '" . $criteria['needle'][$i] . "'";
                   break;
              case 5:  // greater than
                   $where .= " < '" . $criteria['needle'][$i] . "'";
                   break;
              default: // treat as case 1 : included in
                  $where .= " LIKE '%" . $criteria['needle'][$i] . "%'";
           } // end match type switch
    } // end WHERE clause switch

    if (isset($criteria['order'][$i]) && $criteria['order'][$i]) {
       if($order_by) {
           $order_by .= ",";
       }
       $order_by .= " " . $criteria['order'][$i];
    }
    $i++;
  } // end while loop

  $query .= $where;
  if ($order_by) {
     $query .= " ORDER BY" . $order_by;
     if(!in_array(PERSON_ID, $criteria['order'])) {
        $query .= ", " . PERSON_ID;  // id added as tiebreaker
     }
  } else {
     $query .= " ORDER BY " . $criteria['tables'][0] . "." . $criteria['keys'][0];
  }

  if($this->query($query)) {
    $this->save_user_query($query);
  } else {
    $query = FALSE;
  }
  return $query;
} // end function make_filter


function save_user_query($query) {
  $is_new_query = "SELECT filter_id FROM filters WHERE user_id=". $_SESSION['user_id'] ." AND filter='" . addslashes($query) . "'";
  $is_new_result = $this->query($is_new_query);
  if(! $this->num_rows($is_new_result)) {  // this query isn't saved yet
      $user_query = "SELECT filter_id FROM filters WHERE user_id=". $_SESSION['user_id'] ." ORDER BY time_added";
      $user_result = $this->query($user_query);
      if ($this->num_rows($user_result) < 10) {
         $add_query = "INSERT INTO filters (user_id, filter, time_added) VALUES(" . $_SESSION['user_id'] . ", '" . addslashes($query) . "', now())";
      } else {
         $oldest_filter = $this->fetch_assoc($user_result);
         $add_query = "UPDATE filters SET user_id=". $_SESSION['user_id']. ", filter='" . addslashes($query) . "', time_added=now() WHERE filter_id=" . $oldest_filter['filter_id'];
      }
      $this->query($add_query);
  }
}  // end function save_user_query

} // end class DB
?>
