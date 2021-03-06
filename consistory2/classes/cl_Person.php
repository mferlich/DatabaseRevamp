<?php
// class Person gets information about a person.
// CONSTRUCTOR PARAMETERS
//    $db (required) - object of type DB which makes database functions available.
//    $id (optional) - id of person; if not supplied, assumes new record
//    $recordset_query (optional) - query that returned the last result set so we can know who's
//             previous and next; if not supplied but $id is supplied, get next
//             record based on id.
// METHODS
//  Person(&$db, $id=0) - constructor.  Queries db for all info about person.
//  exists(&$db, $id)  - does the requested record exist?
//  update($data, &$db);
//  insert($data, &$db);
//  delete($id, &$db);
//  has_changes(&$db, $data)
//  has_data (&$db, $data)
//  is_flagged(&$db, $id)
//  set_flag(&$db, $rec_id, user_id) - sets a flag on the current record.
//  get_simplename($name) - takes all variants in name field and picks the first one.
//  set_adjacent(&$db [, $id])- gets id of next/prev person, ordered by id


class Person
{
        var $id = 0;
        var $lastname = "";
        var $firstname = "";
        var $nickname = "";
        var $gender = "";
        var $occupation = "";
        var $origin = "";
        var $residence = "";
        var $birthdate = "";
        var $deathdate = "";
        var $spouse = "";
        var $parents = "";
        var $children = "";
        var $relations = "";
        var $annotation = "";
        var $modified_by = "";
        var $modified_date = NULL;
        var $flagged = 0;
        var $delete_rec = 0;
        var $next = 0;
        var $prev = 0;
        var $first = 0;
        var $last = 0;
        var $position = 0;
        var $total = 0;
        var $versions = array();

function Person(&$db, $id=0)
{
  $count = 0;
  if ($id) {
     $q = "SELECT * FROM " . PERSON_TABLE . " WHERE id=$id";
     $result = $db->query($q);
     if ($person = $db->fetch_assoc($result)){
        $this->id = $id;
        $this->lastname = $person['lastname'];
        $this->firstname = $person['firstname'];
        $this->nickname = $person['nickname'];
        $this->gender = $person['gender'];
        $this->occupation = $person['occupation'];
        $this->origin = $person['origin'];
        $this->residence = $person['residence'];
        $this->birthdate = $person['birthdate'];
        $this->deathdate = $person['deathdate'];
        $this->spouse = $person['spouse'];
        $this->parents = $person['parents'];
        $this->children = $person['children'];
        $this->relations = $person['relations'];
        $this->annotation = $person['annotation'];

        $modifier_query = "select user_id, lastname, firstname from users ORDER BY user_id";
        if ($modifier_result = $db->query($modifier_query)) {
           $modifiers = array();
           while ($modifier = $db->fetch_assoc($modifier_result)) {
               $count++;
               $modifiers[$modifier['user_id']] = $modifier['firstname'] . " " . $modifier['lastname'];
           }
        }
        $this->modified_by = (!empty($modifiers[$person['modified_by']])) ? $modifiers[$person['modified_by']] : "Not Recorded";

        $this->modified_date = $person['modified_date'];
        $this->delete_rec = $person['delete_rec'];

        // are there earlier versions of this record?
         $qv = "SELECT versionId, modified_date, modified_by FROM " . VERSION_TABLE . " WHERE recordId LIKE $id ORDER BY modified_date";
         if ($version_result = $db->query($qv)) {
           while ($version_info = $db->fetch_assoc($version_result)) {
             $modified_by = (!empty($modifiers[$version_info['modified_by']])) ? $modifiers[$version_info['modified_by']] : "Not Recorded";
             $this->versions[] = array('versionId' => $version_info['versionId'], 'modified_date' => $version_info['modified_date'], 'modified_by' => $modified_by);
           }
         }

        if (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
           $q = "SELECT rec_id AS id FROM flags WHERE rec_id='$id' AND user_id='". $_SESSION['user_id'] ."'";
           if ($rslt = $db->query($q) ) {
              if ($db->num_rows($rslt)) {
                 $this->flagged = 1;
              }
           }
        }
     }
   }

} // end constructor 'Person'


// check to see whether given record exists
function exists(&$db, $id) {
  if (!is_int($id)) {
     return false;
  }
  $q = "SELECT " . PERSON_TABLE . "." . PERSON_ID . " FROM " . PERSON_TABLE
        . " WHERE " . PERSON_ID . "='$id'"
        . " AND " . PERSON_DELETE_REC . "='0' LIMIT 1";
  if ($rslt = $db->query($q)) {
     if ($db->num_rows($rslt)) {
        return true;
     }
  }
  return false; // if we haven't found someone yet, doesn't exist.

} // end function 'exists'


// function UPDATE - update a record, usually with post data
function update($data, &$db) {
// if we don't have a primary key for our data, then don't try update
 if(!$data[PERSON_ID]) {
   return ERR_NO_PK;
 }
 if (isset($_SESSION['user_id']) ) {
   $q = "";
   if (  (isset($data[FLAG_REC]) && !$this->is_flagged($db, $data[PERSON_ID], $_SESSION['user_id']) )
       || (!isset($data[FLAG_REC]) && $this->is_flagged($db, $data[PERSON_ID], $_SESSION['user_id']) )  ) {
      $this->toggle_flag($db, $data[PERSON_ID], $_SESSION['user_id']);
   }

   if (!empty($q) && !$db->query($q)) {
     return ERR_QUERY_FAILED;
   }
 }

 if(!$this->has_changes($db, $data)) {
   return SUCCESS;
 }
 $last_update_result = $db->query("SELECT ". PERSON_MODIFIED_DATE ." FROM " . PERSON_TABLE . " WHERE " . PERSON_ID . " = '" . $data[PERSON_ID] . "'");
 $last_update = $db->fetch_assoc($last_update_result);
 if ($data['checked_out_time'] < strtotime($last_update[PERSON_MODIFIED_DATE])) {
   return ERR_UPDATE_CONFLICT;
 }
// build our query
 $query = "UPDATE ". PERSON_TABLE ." SET ";

 if (isset($data[PERSON_LASTNAME])) { $query .= PERSON_LASTNAME . " = '" . mysql_real_escape_string($data[PERSON_LASTNAME]) . "', ";}
 if (isset($data[PERSON_FIRSTNAME])) { $query .= PERSON_FIRSTNAME . " = '" . mysql_real_escape_string($data[PERSON_FIRSTNAME]) . "', ";}
 if (isset($data[PERSON_NICKNAME])) { $query .= PERSON_NICKNAME . " = '" . mysql_real_escape_string($data[PERSON_NICKNAME]) . "', ";}
 if (isset($data[PERSON_GENDER])) { $query .= PERSON_GENDER . " = '" . $data[PERSON_GENDER] . "', ";}
 if (isset($data[PERSON_OCCUPATION])) { $query .= PERSON_OCCUPATION . " = '" . mysql_real_escape_string($data[PERSON_OCCUPATION]) . "', ";}
 if (isset($data[PERSON_SPOUSE])) { $query .= PERSON_SPOUSE . " = '" . mysql_real_escape_string($data[PERSON_SPOUSE]) . "', ";}
 if (isset($data[PERSON_PARENTS])) { $query .= PERSON_PARENTS . " = '" . mysql_real_escape_string($data[PERSON_PARENTS]) . "', ";}
 if (isset($data[PERSON_CHILDREN])) { $query .= PERSON_CHILDREN . " = '" . mysql_real_escape_string($data[PERSON_CHILDREN]) . "', ";}
 if (isset($data[PERSON_RELATIONS])) { $query .= PERSON_RELATIONS . " = '" . mysql_real_escape_string($data[PERSON_RELATIONS]) . "', ";}
 if (isset($data[PERSON_BIRTHDATE])) { $query .= PERSON_BIRTHDATE . " = '" . $data[PERSON_BIRTHDATE] . "', ";}
 if (isset($data[PERSON_DEATHDATE])) { $query .= PERSON_DEATHDATE . " = '" . $data[PERSON_DEATHDATE] . "', ";}
 if (isset($data[PERSON_ORIGIN])) { $query .= PERSON_ORIGIN . " = '" . mysql_real_escape_string($data[PERSON_ORIGIN]) . "', ";}
 if (isset($data[PERSON_RESIDENCE])) { $query .= PERSON_RESIDENCE . " = '" . mysql_real_escape_string($data[PERSON_RESIDENCE]) . "', ";}
 if (isset($data[PERSON_ANNOTATION])) { $query .= PERSON_ANNOTATION . " = '" . mysql_real_escape_string($data[PERSON_ANNOTATION]) . "', ";}

 $query .= PERSON_MODIFIED_BY . " = '" . $_SESSION['user_id'] . "', ";
 $query .= PERSON_MODIFIED_DATE . " = now()" . " WHERE " . PERSON_ID . " = '" . $data[PERSON_ID] . "'";

#error_log($query);

// first we back this up with our versioning
 $this->version_backup($db);

// now we commit the changes to the DB
 if (!$db->query($query)) {
   error_log('MySQL error: ' . mysql_error());
   return ERR_QUERY_FAILED;
 }

return SUCCESS;
}  // end function UPDATE


// function INSERT - insert record usually based on post data
function insert($data, &$db, $table='') {

// Is this an insert into the main table, or the versions backup table?
 $table = (empty($table)) ? PERSON_TABLE : VERSION_TABLE;


// build our query
 $query = "INSERT INTO ". $table;
 $cols = " (";
 $vals = " VALUES (";

 if(!$this->has_data($db, $data)) {
   return NO_DATA;
 }

 if ($table == VERSION_TABLE) {
    foreach($data as $key=>$val) {
       if (is_string($val)) {
         $data[$key] = mysql_real_escape_string($val);
       }
    }
    $cols .= VERSION_ID  . ", " . RECORD_ID . ", ";
    $vals .= "NULL, '" . $data[PERSON_ID] . "', ";
 }

 if (    isset($data[PERSON_LASTNAME])
      && $data[PERSON_LASTNAME] != LASTNAME_PROMPT
      && !empty($data[PERSON_LASTNAME])) {
    $cols .= PERSON_LASTNAME . ", ";
    $vals .= "'" . $data[PERSON_LASTNAME] . "', ";
 } else {
    $cols .= PERSON_LASTNAME . ", ";
    $vals .= "'', ";
 }

 if (    isset($data[PERSON_FIRSTNAME])
      && $data[PERSON_FIRSTNAME] != FIRSTNAME_PROMPT
      && !empty($data[PERSON_FIRSTNAME])) {
    $cols .= PERSON_FIRSTNAME . ", ";
    $vals .= "'" . $data[PERSON_FIRSTNAME] . "', ";
 } else {
    $cols .= PERSON_FIRSTNAME . ", ";
    $vals .= "'', ";
 }

  if (    isset($data[PERSON_NICKNAME])
       && $data[PERSON_NICKNAME] != NICKNAME_PROMPT
       && !empty($data[PERSON_NICKNAME])) {
    $cols .= PERSON_NICKNAME . ", ";
    $vals .= "'" . $data[PERSON_NICKNAME] . "', ";
 } else {
    $cols .= PERSON_NICKNAME . ", ";
    $vals .= "'', ";
 }

 if (isset($data[PERSON_GENDER])) { $cols .= PERSON_GENDER . ", ";  $vals .= "'" . $data[PERSON_GENDER] . "', ";}
 if (isset($data[PERSON_OCCUPATION])) { $cols .= PERSON_OCCUPATION . ", ";  $vals .= "'" . $data[PERSON_OCCUPATION] . "', ";}
 if (isset($data[PERSON_SPOUSE])) { $cols .= PERSON_SPOUSE . ", ";  $vals .= "'" . $data[PERSON_SPOUSE] . "', ";}
 if (isset($data[PERSON_PARENTS])) { $cols .= PERSON_PARENTS . ", ";  $vals .= "'" . $data[PERSON_PARENTS] . "', ";}
 if (isset($data[PERSON_CHILDREN])) { $cols .= PERSON_CHILDREN . ", ";  $vals .= "'" . $data[PERSON_CHILDREN] . "', ";}
 if (isset($data[PERSON_RELATIONS])) { $cols .= PERSON_RELATIONS . ", ";  $vals .= "'" . $data[PERSON_RELATIONS] . "', ";}
 if (isset($data[PERSON_BIRTHDATE])) { $cols .= PERSON_BIRTHDATE . ", ";  $vals .= "'" . $data[PERSON_BIRTHDATE] . "', ";}
 if (isset($data[PERSON_DEATHDATE])) { $cols .= PERSON_DEATHDATE . ", ";  $vals .= "'" . $data[PERSON_DEATHDATE] . "', ";}
 if (isset($data[PERSON_ORIGIN])) { $cols .= PERSON_ORIGIN . ", ";  $vals .= "'" . $data[PERSON_ORIGIN] . "', ";}
 if (isset($data[PERSON_RESIDENCE])) { $cols .= PERSON_RESIDENCE . ", ";  $vals .= "'" . $data[PERSON_RESIDENCE] . "', ";}
 if (isset($data[PERSON_ANNOTATION])) { $cols .= PERSON_ANNOTATION . ", ";  $vals .= "'" . $data[PERSON_ANNOTATION] . "', ";}

 $cols .= PERSON_MODIFIED_BY . ", ";
 $vals .= "'" . $_SESSION['user_id'] . "', ";
 $cols .= PERSON_MODIFIED_DATE . ")";
 $vals .= "now())";
 $query .= $cols . $vals;

 if ($db->query($query) && $table != VERSION_TABLE) {
   $code = SUCCESS;
   $id = $db->insert_id($db->link_identifier);
   $this->id = $id;
 } else {
   $code = ERR_QUERY_FAILED;
   $id = 0;
 }

 if (isset($data[FLAG_REC]) && isset($_SESSION['user_id']) && $code == SUCCESS ) {
    $this->toggle_flag($db, $id, $_SESSION['user_id']);
 }

 return $code;
}  // end function UPDATE


function version_backup(&$db) {
  // BAD ABSTRACTION - this assumes that DB column names = object property names.
  $old = array();
  foreach($this as $key => $val)
  {
    $old[$key] = $val;
  }
  $this->insert($old, $db, VERSION_TABLE);
}

// function DELETE - this MARKS A RECORD FOR DELETION or cancels the deletion.
// The record is not removed from the database until the record is purged.
function delete($id, $action, &$db){
    $query = "UPDATE ". PERSON_TABLE ." SET delete_rec='$action' WHERE id='$id'";
    if ($db->query($query)) {
        return SUCCESS;
    } else {
        return ERR_QUERY_FAILED;
    }
} // end function DELETE

// function HAS_CHANGES
// tests an ARRAY of data to see whether the values differ from the values in the DB.
function has_changes(&$db, $data) {
$existing_data = new Person($db, $data[PERSON_ID]);

 foreach ($data as $key => $val) {
    switch($key) {
      case PERSON_LASTNAME:
      case PERSON_FIRSTNAME:
      case PERSON_NICKNAME:
      case PERSON_GENDER:
      case PERSON_OCCUPATION:
      case PERSON_SPOUSE:
      case PERSON_PARENTS:
      case PERSON_CHILDREN:
      case PERSON_RELATIONS:
      case PERSON_BIRTHDATE:
      case PERSON_DEATHDATE:
      case PERSON_ORIGIN:
      case PERSON_RESIDENCE:
      case PERSON_ANNOTATION:
        if (stripslashes($val) != $existing_data->$key) {
           return true;
        }
    }
 }

 return FALSE;
} // end function has_changes

// function HAS_DATA
// tests an ARRAY of data to see whether biographical values are set
function has_data(&$db, $data) {
 foreach ($data as $key => $val) {
    if (!empty($val)){
       switch($key) {
         case PERSON_LASTNAME:
            if($val == LASTNAME_PROMPT) {break;}
         case PERSON_FIRSTNAME:
            if($val == FIRSTNAME_PROMPT) {break;}
         case PERSON_NICKNAME:
            if($val == NICKNAME_PROMPT) {break;}
         case PERSON_GENDER:
         case PERSON_OCCUPATION:
         case PERSON_SPOUSE:
         case PERSON_PARENTS:
         case PERSON_CHILDREN:
         case PERSON_RELATIONS:
         case PERSON_BIRTHDATE:
         case PERSON_DEATHDATE:
         case PERSON_ORIGIN:
         case PERSON_RESIDENCE:
         case PERSON_ANNOTATION:
           return TRUE;
       }
    }
 }
 if (isset($data[FLAG_REC])) {
    return true;
 }
 return FALSE;
} // end function has_data

// function IS_FLAGGED
// tests a record to see it it is flagged by the current user
function is_flagged(&$db, $rec_id, $user_id) {
  $q = "SELECT " . FLAGS_TABLE.".".FLAGS_REC . " FROM " . FLAGS_TABLE . ",". PERSON_TABLE
      . " WHERE " . FLAGS_TABLE.".".FLAGS_REC . "='$rec_id'"
            . " AND ". FLAGS_TABLE.".".FLAGS_USER . "='$user_id'"
            . " AND ". FLAGS_TABLE.".".FLAGS_REC . "=". PERSON_TABLE.".".PERSON_ID
            . " AND ". PERSON_TABLE.".".PERSON_DELETE_REC . "='0'";
  $rslt = $db->query($q);
  if ($db->num_rows($rslt)) {
     return true;
  }
  return false;
} // end function is_flagged

function toggle_flag(&$db, $rec_id, $user_id) {
  if ($this->is_flagged($db, $rec_id, $user_id) ) {  // if there is a flag, delete it.
     $action_q = "DELETE FROM " . FLAGS_TABLE . " WHERE " . FLAGS_REC . "='$rec_id' AND "
             . FLAGS_USER . "='$user_id'";

  } else { // if there is no flag, set one.
     $action_q = "INSERT INTO " . FLAGS_TABLE . "(" . FLAGS_REC . "," . FLAGS_USER . ") VALUES ($rec_id, $user_id)";
  }
  if ( $db->query($action_q) ) {
    return true;
  } else {
   return false;
  }

}  // end function toggle_flag

// function GET_SIMPLENAME
// Sometimes we just want to show one version of the name instead of all variants.
function get_simplename($name) {
   $name_found = FALSE;
   $simple_name = "";
   $articles = array("le", "Le", "la", "La", "les", "Les", "de", "De", "du", "Du", "des", "Des");
   $name = str_replace(",", " ", $name);
   $name_array = explode(" ", $name, 4); //get enough elements to deal with "de la X"

   $count = 0;
   while (!$name_found) {
     $simple_name .= $name_array[$count] . " ";
     if (!in_array($name_array[$count], $articles)){
        $name_found = TRUE;
     }
     $count++;
   }
   return $simple_name;
}  // end func get_simplename


// function SET_ADJACENT
// A little misnamed at this point.
// Returns:
//  - first: first record in filtered record set
//  - prev: record before current
//  - next: record after current
//  - last: last record in record set
//  - position: ordinal position of current record in record set
//  - total: total returned by the current filter
//
function set_adjacent(&$db, $id=0) {
   if (!$id && $this->id) {
      $id = $this->id;
   }

// clear our navigation ids
   $this->first = 0;
   $this->last = 0;
   $this->prev = 0;
   $this->last = 0;
   $this->position = 0;
   $this->total = 0;

// set some flags
   $hold = 0;
   $set_first = TRUE;
   $set_prev = TRUE;
   $set_next = TRUE;
   $set_last = TRUE;
   $set_total = TRUE;
   $set_position = TRUE;

// set some variables we will use later on.
   $order_by = "";
   $filter = "";

// if a filter is set, that provisionally defines our record set
   if (isset($_SESSION['filter']) && $_SESSION['filter']) {
      $filter = $_SESSION['filter'];
   }
   if (isset($_SESSION['flag_filter']) && $_SESSION['flag_filter']) {
      $filter = $_SESSION['flag_filter'];
   }

// if records are flagged and the user has asked to work only with flagged
// records, that trumps the session filter, but we want to use the session filter's ordering
   $flagged_count = 0;
   $curr_user = new User($db, $_SESSION['user_id']);
   if (isset($_SESSION['show_flagged']) && $_SESSION['show_flagged'] && $curr_user->has_flags) {
      if (substr_count($filter, "ORDER BY") == 1) {
         $pattern = "/.*(ORDER BY.*)/";
         $replace = "\$1";
         $order_by = preg_replace($pattern, $replace, $filter);
      } elseif (substr_count($filter, "order by") == 1) {
         $pattern = "/.*(order by.*)/";
         $replace = "\$1";
         $order_by = preg_replace($pattern, $replace, $filter);
      }
      $filter = 'SELECT ' . PERSON_TABLE . '.' . PERSON_ID
         . ' FROM ' . PERSON_TABLE .",". FLAGS_TABLE
         . ' WHERE '. PERSON_DELETE_REC . "='0' AND "
         . FLAGS_TABLE.".".FLAGS_REC . "=" .PERSON_TABLE . '.' . PERSON_ID . " AND "
         . FLAGS_TABLE.".".FLAGS_USER . "='{$curr_user->user_id}' "
         . $order_by;
       $_SESSION['flag_filter'] = $filter;
   }
// if there's a filter, we need to use that to find first, next etc
   if ($filter) {

           // first get our total number of records, not counting recs pending deletion
      if ($record_list = $db->query($filter)) {
            $this->total = $db->num_rows($record_list);
            $set_total = FALSE;
         if ($this->total <= 1) {
            $this->position = ($this->total) ? 1 : 0;
            $set_position = FALSE;  // no need to set our navigation stuff
            $set_last = false;
            $set_next = false;
            $set_prev = false;
            $set_first = false;

           // if we have MORE THAN ONE ROW, run through rows to find position
           // if first, prev, next can't be set, that tells us not to offer navigation to them
         } else {
           $count = 0;

             // if we have an id for current record or a zero for a new record
             // then we can set navigation based on that
           if ($id >=0) {
              while (($row = $db->fetch_assoc($record_list)) && $set_prev) {
                 $count++;

                 if($set_first) {
                    if ($row[PERSON_ID] != $id) {
                       $this->first = $row[PERSON_ID];
                    }
                    $set_first = FALSE;
                 }
                 if ($row[PERSON_ID] == $id) {   // now we have the POSITION
                     $this->position = $count;
                     $set_position = FALSE;
                     $this->prev = $hold;     // PREV is the last valid rec
                     $set_prev = FALSE;
                                              // now we find NEXT
                     while (($row = $db->fetch_assoc($record_list)) && $set_next) {
                       if ($row[PERSON_ID] != $id) {
                          $this->next = $row[PERSON_ID];
                          $set_next = false;
                       }
                     }
                 }
                 $hold = $row[PERSON_ID];
              } // endwhile
           }

             // if we have an id but haven't found first, prev, next, then we won't
           $set_first = false;
           $set_prev = FALSE;
           $set_position = FALSE;
           $set_next = FALSE;

             // now we need to find the last record
           $set_last = false; // if we try and fail, still done
           $last_rec_query = $filter . " LIMIT " . ($this->total - 1) . ", 1";
           if ($last_rec_result = $db->query($last_rec_query)) {
             if ($last_rec = $db->fetch_assoc($last_rec_result)) {
                if ($last_rec[PERSON_ID] != $id) {
                   $this->last = $last_rec[PERSON_ID];
                }
             }
           }
        } // end searches triggered by having MORE THAN ONE ROW returned by our filter
      } // we've gone through the entire record set
   } // end processing based on having a VALID FILTER

//  there is NO FILTER so we do navigation based on the ENTIRE DATABASE
   if($set_first) {
     $query = "SELECT " . PERSON_TABLE . "." . PERSON_ID . " FROM " . PERSON_TABLE . " WHERE " . PERSON_DELETE_REC. "='0' ORDER BY " . PERSON_ID. " LIMIT 1";
     if($result = $db->query($query)) {
        if($row = $db->fetch_assoc($result)) {
           if ($row[PERSON_ID] != $id) {
              $this->first = $row[PERSON_ID];
           }
        }
     }
   }
   if ($set_prev && $id) {    // get prev if there is no filter but there is an id
       $prev_query = "SELECT " . PERSON_TABLE . "." . PERSON_ID . " FROM " . PERSON_TABLE . " WHERE " . PERSON_ID . "<" . $id . " AND " . PERSON_DELETE_REC . "='0' ORDER BY " . PERSON_ID. " DESC LIMIT 1";
       if ($prev_result = $db->query($prev_query)) {
          if ($row = $db->fetch_assoc($prev_result)) {
             if ($row[PERSON_ID] != $id) {
                $this->prev = $row[PERSON_ID];
             }
          }
       }
   } // got previous or we can't

   if ($set_next && $id) {    // get next if there is no filter but there is an id
       $next_query = "SELECT " . PERSON_TABLE . "." . PERSON_ID . " FROM " . PERSON_TABLE . " WHERE " . PERSON_ID . ">" . $id . " AND " . PERSON_DELETE_REC . "='0' ORDER BY " . PERSON_ID. " LIMIT 1";
       if ($next_result = $db->query($next_query)) {
          if ($row = $db->fetch_assoc($next_result)) {
               if ($row[PERSON_ID] != $id) {
                  $this->next = $row[PERSON_ID];
              }
          }
       }
   } // got next or we can't

   if ($set_last) {
     $query = "SELECT " . PERSON_TABLE . "." . PERSON_ID . " FROM " . PERSON_TABLE . " WHERE " . PERSON_DELETE_REC . "='0' ORDER BY " . PERSON_ID. " DESC LIMIT 1";
     if ($result = $db->query($query)) {
        if ($row = $db->fetch_assoc($result)) {
           if ($row[PERSON_ID] != $id) {
              $this->last = $row[PERSON_ID];
           }
        }
     }
   }

   if ($set_total) {
     $query = "SELECT " . PERSON_TABLE . "." . PERSON_ID . " FROM " . PERSON_TABLE . " WHERE " . PERSON_DELETE_REC . "='0'";
     if ($result = $db->query($query)) {
        $this->total = $db->num_rows($result);
     }
   }

   if ($set_position && $id) {
     $query = "SELECT " . PERSON_TABLE . "." . PERSON_ID . " FROM " . PERSON_TABLE . " WHERE id<" . $id . " AND " . PERSON_DELETE_REC . "='0'";
     if ($result = $db->query($query)) {
        $this->position = ($db->num_rows($result) + 1);
     }
   }
   return;

} // end set_adjacent function



} // end class
?>