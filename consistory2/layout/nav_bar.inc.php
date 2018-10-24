<?php

$nav_bar = '';
if($logged_in && !strpos($_SERVER['PHP_SELF'], LOGIN_PAGE)) {

    $is_detail_page = strpos($_SERVER['PHP_SELF'], DETAIL_PAGE);
    $is_list_page = strpos($_SERVER['PHP_SELF'], RESULTS_PAGE);
    $is_prefs_page = strpos($_SERVER['PHP_SELF'], PREFERENCES_PAGE);

    $nav_bar .= '<div id="nav">';
    if(!$is_detail_page) {
      $get_string = ($nav_id)? '?id=' . $nav_id : '';
      $nav_bar .= ' [<a href="' . DETAIL_PAGE . $get_string . '">Record View</a>] ';
    }

    if(!$is_prefs_page) {
       $nav_bar .= ' [<a href="' . PREFERENCES_PAGE . '">Preferences</a>] ';
    }


    $id = (isset($id) && !empty($id)) ? $id : 1;
    $filter_nav = "";
    if(isset($_SESSION['filter']) && $_SESSION['filter'] && ($is_list_page || $is_detail_page || $is_prefs_page) ) {
          $filter_nav .='  [<span class="nav-label">Filter:</span>';
          if (!$is_list_page) {
            $filter_nav .= ' <a href="' . RESULTS_PAGE . '">List Records</a> | ';
          }
          $filter_nav .= '<a href="' . DETAIL_PAGE . '?nofilter=1&amp;id=' . $id . '">Remove</a>';
          if(!$is_prefs_page) {
             $filter_nav .=' | <a href="' . PREFERENCES_PAGE . '">Recent</a>';
          }
          $filter_nav .= ']';
    }

    $nav_bar .= $filter_nav;

// are there any flagged records and does user want to see flagged records?
    $has_flags = $user->has_flags;
    $flag_nav = "";

    if (      ($has_flags && ($is_detail_page || $is_list_page) )
           || ($_SESSION['show_flagged'] && ($has_flags || $is_detail_page || $is_list_page) )  ) {
       $nav_bar .= ' [<span class="nav-label">Flags:</span> ';
       if ($_SESSION['show_flagged']) {
           if ($is_detail_page) {
              $flag_nav .= ' <a href="' . DETAIL_PAGE . '?show_flagged=0&amp;id=' . $id . '">Ignore</a>';
           } else {
              $flag_nav .= ' <a href="' . RESULTS_PAGE . '?show_flagged=0">Ignore</a>';
           }
       }
       if (!$_SESSION['show_flagged'] && $has_flags && ($is_detail_page || $is_list_page) ) {
              if ($flag_nav) { $flag_nav .= " | ";}
              if ($is_detail_page) {
                 $flag_nav .= ' <a href="' . DETAIL_PAGE . '?show_flagged=1&amp;id=' . $id . '">Only</a>';
              } else {
                 $flag_nav .= ' <a href="' . RESULTS_PAGE . '?show_flagged=1">Only</a>';
              }
       }
       if ($has_flags && $is_detail_page) {
          if ($flag_nav) {
              $flag_nav .= " | ";
          }
          $flag_nav .= ' <a href="' . RESULTS_PAGE . '?show_flagged=1">List</a>';
       }
       if ($flag_nav) {
          $flag_nav .= '] ';
       }
    }
    $nav_bar .= $flag_nav;
    $nav_bar .= '</div>';

    if ($user->userlevel > READ_ONLY)
    {
       $nav_bar .= '<p id="warn_changes">Changes made.  Submit record or cancel changes</p>';
    }
}
echo $nav_bar;
?>
