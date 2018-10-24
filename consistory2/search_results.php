<?php

include('genbio_top.php');  // exposes the DB class as $db and declares constants
include(DOC_ROOT . DIR_CLASSES . PERSON_CLASS);
include(DOC_ROOT . DIR_LIB . SEARCH_FORMS_LIB);

$flagged_query = "SELECT rec_id as id FROM flags, genbios WHERE flags.user_id='{$user->user_id}'"
            . " AND flags.rec_id = genbios.id"
            . " AND genbios.delete_rec='0'";

if (isset($_REQUEST['set_flags'])) {
  $_SESSION['show_flagged'] = 1;
  if (isset($_REQUEST['flag_checked'])) {
    foreach ($_REQUEST['flag_checked'] as $id => $val) {
      $is_flagged = Person::is_flagged($db, $id, $user->user_id);
      if (!$is_flagged) {
         $db->query("INSERT INTO flags (rec_id, user_id) VALUES ('$id', '{$user->user_id}')");
      }
    }
  }
  if (isset($_REQUEST['flagged'][0])) {
    foreach ($_REQUEST['flagged'] as $key => $id) {
      $is_flagged = Person::is_flagged($db, $id, $user->user_id);
      if (!isset($_REQUEST['flag_checked'][$id]) && $is_flagged) {
         $q = "DELETE FROM flags WHERE rec_id='$id' AND user_id='{$user->user_id}'";
         $_SESSION['debug'] = $q;
         $db->query($q);
      }
    }
  }
  $num_flagged = $db->num_rows($db->query($flagged_query));
  if (!$num_flagged) {
      $_SESSION['show_flagged'] = 0;
      header("Location: " . WEB_ROOT . DETAIL_PAGE);
      exit;
  }
}



// get SQL filter based on POST data
$filter = "";

if(isset($_SESSION['filter']) && $_SESSION['filter']) {
   $filter = $_SESSION['filter'];
}

if (isset($_REQUEST['search_wizard']) || isset($_REQUEST['search_sql']) ) {
   $_SESSION['show_flagged'] = 0;
   if ($filter = $db->make_filter($_REQUEST)) {
      $_SESSION['filter'] = $filter;  // we unset this if we don't get multiple records
   }
}

if (isset($_REQUEST['filter_id']) && !empty($_REQUEST['filter_id']) ) {
   $filter_query = 'SELECT filter FROM filters WHERE filter_id=' . $_REQUEST['filter_id'];
   if($filter_result = $db->query($filter_query)) {
      if($db_filter = $db->fetch_assoc($filter_result)) {
         $filter = $db_filter['filter'];
         $_SESSION['filter'] = $filter;  // we unset this if we don't get multiple records
      }
   }
}

$list_flagged = false;
// if user wants to show flagged, select flagged records
if (isset($_SESSION['show_flagged']) && $_SESSION['show_flagged'] && $db->num_rows($db->query($flagged_query))) {
  $filter = $flagged_query;
  $list_flagged = true;
// if user wants to ignore flags, show results from last filter; if no filter or 0 result from filter, show default page
} else if (isset($_SESSION['show_flagged']) && !$_SESSION['show_flagged']) {
   if (!isset($_SESSION['filter']) || empty($_SESSION['filter']) || !$db->num_rows($db->query($_SESSION['filter'])) ) {
      header("Location: " . WEB_ROOT . DETAIL_PAGE);
      exit;
   }
}

// if there's a problem with the filter query, go back to the search page.
if (!$filter) {
  header("Location: " . WEB_ROOT . SEARCH_PAGE . "?found=0");
  exit;
}

// query's okay; get a record list
$record_list = $db->query($filter);
$num_found = $db->num_rows($record_list);
$all_results = $num_found;

// if exact match fails, get closest;
if (!$num_found && isset($_REQUEST['match_type'][0]) && $_REQUEST['match_type'][0] == 1) {
    $pattern = "/(.*WHERE.*" . $_REQUEST['haystack'][0] . " )=(.*)/";
    $replace = "\$1<\$2 DESC LIMIT 1";
    $next_lower = preg_replace($pattern, $replace, $filter);
    $replace ="\$1>\$2 LIMIT 1";
    $next_higher = preg_replace($pattern, $replace, $filter);
    echo "$pattern<br>$replace<br>$filter<br>$next_higher<br>$next_lower"; exit();
    $db->query("DROP TABLE IF EXISTS nearest"); // MySQL does not allow DROP TEMPORARY until 4.1
    $db->query("CREATE TEMPORARY TABLE nearest " . $next_lower);
    $db->query("INSERT INTO nearest (id) ". $next_higher);
    $record_list = $db->query("SELECT * FROM nearest");
    $num_found = $db->num_rows($record_list);
    $message="\n<h2>Your search returned no results.  The next closest records are given below.</h2>\n";
    if (isset($_SESSION['filter'])) {unset($_SESSION['filter']);}
  }

// if we still don't have anything, go back to searching
if(!$num_found) {
   if (isset($_SESSION['filter'])) {unset($_SESSION['filter']);}
   header("Location: " . WEB_ROOT . SEARCH_PAGE . "?found=0");
   exit;
}

// if just one result, go straight to that record
if ($num_found == 1 && !$list_flagged) {
   if (isset($_SESSION['filter'])) {unset($_SESSION['filter']);}
   if ($row = $db->fetch_array($record_list)) {
       header("Location: " . WEB_ROOT . DETAIL_PAGE . "?id=" . $row[PERSON_ID]);
       exit;
   } else {
       header("Location: " . WEB_ROOT . SEARCH_PAGE . "?found=0");
       exit;
   }
}

// now determine how many pages we have

if(isset($_REQUEST['recs_per_page']) && $_REQUEST['recs_per_page'] && is_numeric($_REQUEST['recs_per_page'])) {
    $recs_per_page = $_REQUEST['recs_per_page'];
    $_SESSION['recs_per_page'] = $_REQUEST['recs_per_page'];
} elseif(isset($_SESSION['recs_per_page']) && $_SESSION['recs_per_page']) {
    $recs_per_page = $_SESSION['recs_per_page'];
} else {
    $recs_per_page = ($user->recs_per_page) ? $user->recs_per_page : DEFAULT_RECS_PER_PAGE;
}

// let's do some navigation within the result set
$total_pages = ceil($num_found/$recs_per_page);
settype($total_pages, "int");
$pages = "\n<form action=\"" . $_SERVER['PHP_SELF'] . "\" class=\"page_list\">\n<p>";
if ($total_pages > 1) {
  $pages .= "PAGE: ";
  if(empty($_REQUEST['page']) || !is_numeric($_REQUEST['page'])) {
     $current_page = 1;
  } else {
     $current_page = $_REQUEST['page'];
  }
  $start = (($current_page - 5)>1) ? $current_page - 5 : 2;
  $end = (($current_page + 5)<$total_pages) ? $current_page + 6 : $total_pages;

  $pages .= ($current_page != 1) ? "<a href=\"" . $_SERVER['PHP_SELF'] . "?page=1\">TOP</a>, \n" : "<strong>TOP</strong>, \n";
  if($start > 2) {
     $pages .= "...";
  }

  for($i=$start; $i<$end; $i++) {
      if($i==$current_page) {
         $pages .= '<strong>' . $i . '</strong>, ';
      } else {
         $pages .= ' <a href="' . $_SERVER['PHP_SELF'] . '?page=' . $i . '">'. $i .'</a>, ';
      }
  }
  if($end < $total_pages) {
     $pages .= "...";
  }
  $pages .= ($current_page != $total_pages) ? '<a href="' . $_SERVER['PHP_SELF'] . '?page=' . $total_pages . '">END</a>' : '<strong>END</strong></p>';

// get record set for this page
  $limit_offset = ($current_page - 1) * $recs_per_page;
  settype($limit_offset, "int");
  $page_split = " LIMIT " . $limit_offset . ", " . $recs_per_page;
  // new query for page splittin
  $record_list = $db->query($filter . $page_split);
  $num_found = $db->num_rows($record_list);
}
  if ($total_pages > 10) {
     $pages .= '&nbsp; &nbsp; &nbsp; &nbsp; Jump to Page: <input type="text" name="page" class="very-short" />';
  }
$pages .= '&nbsp; &nbsp; &nbsp; &nbsp; Records per page: <input type="text" name="recs_per_page" class="very-short" value="' . $recs_per_page . '" /><input type="submit" value="GO" /></p></form>';

// TITLE
$page_title = 'Search Results';
$page_headline = '<h1>Search Results</h1>';

$quicksearch = form_quicksearch($db, $user, $person, HIDE_NAV_ARROWS);

$form_start = '<form method="post" action="' . RESULTS_PAGE . '">';
$table_head = '<table summary="Search Results" class="results"><thead><tr>'
          . '<th scope="col" class="ncol"><button type="submit" name="set_flags" value="1">Reset<br />Flags</button></th><th scope="col" class="ncol">ID</th><th scope="col">Last Name</th>'
          . '<th scope="col">First Name</th><th scope="col">Nickname</th><th scope="col" class="ncol">Gender</th>'
          . '<th scope="col">Job</th><th scope="col">Spouse</th><th scope="col">Parents</th><th scope="col">Children</th>'
          . '<th scope="col">Other<br />Family</th><th scope="col">Birth</th><th scope="col">Death</th>'
          . '<th scope="col">Place of Origin</th><th scope="col">Residence</th><th scope="col">Detailed<br />Notes</th>'
          . '<th scope="col">Updated By</th><th scope="col">Last<br />Updated</th></tr></thead><tbody>';

$count=0;
$table_rows = "";

while($ids = $db->fetch_array($record_list)) {
  if (!$count) {
    $nav_id = $ids[PERSON_ID];
  }
  $count++;
  $person = new Person($db, $ids[PERSON_ID]);
  if($count % 2) {
    $row_class=" class=\"highlight\"";
  }

  if($person->flagged) {
    $flag = '<input type="hidden" name="flagged[]" value="'.$person->id.'" /><input type="checkbox" name="flag_checked['.$person->id.']" checked="checked" />';
  } else {
     $flag = '<input type="checkbox" name="flag_checked['.$person->id.']" />';
  }
  $table_rows .= '<tr' . $row_class . '><td class="center">' . $flag . '</td><td><strong>'
       . '<a href="' . DETAIL_PAGE . '?id=' . $person->id . '">' . $person->id . '</a></strong></td>'
       . '<td>' . htmlspecialchars($person->lastname, ENT_QUOTES) . '</td><td>' . htmlspecialchars($person->firstname, ENT_QUOTES) . '</td><td>' . htmlspecialchars($person->nickname, ENT_QUOTES) . '</td><td>' . $person->gender . '</td>'
       . '<td>' . htmlspecialchars($person->occupation, ENT_QUOTES) . '</td><td>' . htmlspecialchars($person->spouse, ENT_QUOTES) . '</td><td>' . htmlspecialchars($person->parents, ENT_QUOTES) . '</td><td>' . htmlspecialchars($person->children, ENT_QUOTES) . '</td>'
       . '<td>' . htmlspecialchars($person->relations, ENT_QUOTES) . '</td><td>' . htmlspecialchars($person->birthdate, ENT_QUOTES) . '</td><td>' . htmlspecialchars($person->deathdate, ENT_QUOTES) . '</td><td>' . htmlspecialchars($person->origin, ENT_QUOTES) . '</td>'
       . '<td>' . htmlspecialchars($person->residence, ENT_QUOTES) . '</td><td><a href="' . DETAIL_PAGE . '?id=' . $person->id . '">View</a></td><td>' . $person->modified_by . '</td>'
       . '<td>' . $person->modified_date . '</td></tr>';
}

$results_table = $form_start . $table_head . $table_rows . "\n</tbody>\n</table></form>\n\n";

$content = $quicksearch . $page_headline . $message . $pages . $results_table . $quicksearch;

include(DOC_ROOT . DIR_TEMPLATES . DEFAULT_TEMPLATE);
?>
