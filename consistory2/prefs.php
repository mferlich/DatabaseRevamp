<?php
include("genbio_top.php");
include(DOC_ROOT . DIR_LIB . SEARCH_FORMS_LIB);

if (isset($_REQUEST['update_prefs'])) {
  $update_query = "UPDATE users SET ";
  $field_names = $db->get_field_name_array('users');
  foreach($_REQUEST as $key => $val) {
     if ($val != '' && in_array($key, $field_names)) {
        $update_query .= "$key='$val', ";
     }
  }
  if (!empty($_REQUEST['old_pass']) || !empty($_REQUEST['new_pass']) || !empty($_REQUEST['confirm_pass'])) {
    if (!empty($_REQUEST['old_pass']) && !empty($_REQUEST['new_pass']) && !empty($_REQUEST['confirm_pass'])) {
       if (md5($_REQUEST['old_pass']) != $user->password) {
          $message .="<h2>INCORRECT PASSWORD</h2><p class=\"center\">Password not updated.  You must correctly enter your current password.</p>";
       } elseif ($_REQUEST['new_pass'] != $_REQUEST['confirm_pass']) {
          $message .="<h2>PASSWORDS DO NOT MATCH</h2><p class=\"center\">Password not updated.  You must enter exactly the same password both for the new password and for the confirmation.</p>";
       } elseif (strlen($_REQUEST['new_pass']) < 6 || strlen($_REQUEST['new_pass']) > 12) {
          $message .="<h2>INVALID PASSWORD</h2><p class=\"center\">Passwords must contain between 6 and 12 characters.</p>";
       } elseif (!ctype_alnum($_REQUEST['new_pass'])) {
          $message .="<h2>INVALID PASSWORD</h2><p class=\"center\">Passwords must contain only alpha-numeric characters (a-z, A-Z, 0-9).</p>";
       } else {
          $update_query .= "password='". md5($_REQUEST['new_pass']) . "', ";
       }
    } else {
         $message .="<h2>Password not updated</h2><p class=\"center\">You must fill in all blanks: your current password, your new password, confirmation of the new password.</p>";
    }
  }

  $update_query = substr($update_query, 0, -2) . " WHERE user_id={$_SESSION['user_id']}";
//  echo $update_query;
//  echo "<pre>" . print_r($_POST);
//  exit;
  if (!$db->query($update_query)) {
    $message .= "<h2>UPDATE FAILED</h2><p class=\"center\">If the problem persists, please contact the database administrator.</p>";
  } else {
    unset($user);
    $user = new User($db);
  }
}

$page_title = "User Preferences and Settings";
$page_headline = "
<h1>User Preferences and Settings</h1>
<p>If you want to go directly to record view upon login, change your settings below</p>
";
$start_page_select =  "<p><span>Upon login, go to:</span><select name=\"start_page\">";
if ($user->start_page == "PREFERENCES_PAGE") {
    $start_page_select .= "<option value=\"PREFERENCES_PAGE\" selected=\"selected\">User Preferences</option>";
} else {
    $start_page_select .= "<option value=\"PREFERENCES_PAGE\">User Preferences</option>";
}
if ($user->start_page == "SEARCH_PAGE") {
    $start_page_select .= "<option value=\"SEARCH_PAGE\" selected=\"selected\">Search Page</option>";
} else {
    $start_page_select .= "<option value=\"SEARCH_PAGE\">Search Page</option>";
}
if ($user->start_page == "DETAIL_PAGE") {
    $start_page_select .= "<option value=\"DETAIL_PAGE\" selected=\"selected\">Record View</option>";
} else {
    $start_page_select .= "<option value=\"DETAIL_PAGE\">Record View</option>";
}
$start_page_select .= "</select></p>";

$resolution_select =  "<p><span>Screen Resolution:</span><select name=\"resolution\">";
if ($user->resolution == "800") {
    $resolution_select .= "<option value=\"800\" selected=\"selected\">800x600</option>";
} else {
    $resolution_select .= "<option value=\"800\">800x600</option>";
}
if ($user->resolution == "1024") {
    $resolution_select .= "<option value=\"1024\" selected=\"selected\">1024x768</option>";
} else {
    $resolution_select .= "<option value=\"1024\">1024x768</option>";
}
$resolution_select .= "</select></p>";

$annotation_cols = "<p><span>Cols in Annotation field:</span><select name=\"annotation_cols\">";
for ($i=30; $i<120; $i++)
{
    $selected = ($i == $user->annotation_cols) ? ' selected="selected"' : '';
    $annotation_cols .= "<option value=\"$i\"$selected>$i</option>";
}
$annotation_cols .= "</select></p>";

$annotation_rows = "<p><span>Rows in Annotation field:</span><select name=\"annotation_rows\">";
for ($i=20; $i<80; $i++)
{
    $selected = ($i == $user->annotation_rows) ? ' selected="selected"' : '';
    $annotation_rows .= "<option value=\"$i\"$selected>$i</option>";
}
$annotation_rows .= "</select></p>";


$relations_cols = "<p><span>Cols in Other Relations field:</span><select name=\"relations_cols\">";
for ($i=5; $i<50; $i++)
{
    $selected = ($i == $user->relations_cols) ? ' selected="selected"' : '';
    $relations_cols .= "<option value=\"$i\"$selected>$i</option>";
}
$relations_cols .= "</select></p>";

$relations_rows = "<p><span>Rows in Other Relations field:</span><select name=\"relations_rows\">";
for ($i=1; $i<20; $i++)
{
    $selected = ($i == $user->relations_rows) ? ' selected="selected"' : '';
    $relations_rows .= "<option value=\"$i\"$selected>$i</option>";
}
$relations_rows .= "</select></p>";


$popup_cols = "<p><span>Cols in Notes Popup Window:</span><select name=\"popup_cols\">";
for ($i=30; $i<250; $i++)
{
    $selected = ($i == $user->popup_cols) ? ' selected="selected"' : '';
    $popup_cols .= "<option value=\"$i\"$selected>$i</option>";
}
$popup_cols .= "</select></p>";

$popup_rows = "<p><span>Rows in Notes Popup Window:</span><select name=\"popup_rows\">";
for ($i=10; $i<75; $i++)
{
    $selected = ($i == $user->popup_rows) ? ' selected="selected"' : '';
    $popup_rows .= "<option value=\"$i\"$selected>$i</option>";
}
$popup_rows .= "</select></p>";

if ($user->tags_popup)
{
  $popup_checked = ' checked="checked"';
  $dd_checked = '';
  $size_display = ' style="display:block;"';
}
else
{
  $dd_checked = ' checked="checked"';
  $popup_checked = '';
  $size_display = ' style="display:none;"';
}

$tags_popup_check = '
    <p><span>Tag insertion via </span>
       popup:<input type="radio" class="short" name="tags_popup" value="1"'.$popup_checked.' onclick="setDisplay(\'tagsPopupSize\', 1)" />
       drop-down:<input type="radio" class="short" name="tags_popup" value="0"'.$dd_checked.' onclick="setDisplay(\'tagsPopupSize\', 0)" />
    </p>';

$tags_popup_size = '
    <div id="tagsPopupSize"'.$size_display.'>
         <p><span>Width of tag insertion popup in pixels:</span>
            <input type="text" class="short" name="tags_popup_width" value="'.$user->tags_popup_width.'" />
         </p>
         <p><span>Height of tag insertion popup:</span>
             <input type="text" class="short" name="tags_popup_height" value="'.$user->tags_popup_height.'" />
         </p>
    </div>';


$user_info = '<div class="user-settings">'
      . '<form method="post" action="' . PREFERENCES_PAGE . '">'

      . '<h2 class="highlight">Account Information</h2>'
      . '<p><span>Last name:</span><input type="text" name="lastname" tabindex="5" class="medium" value="' . htmlspecialchars($user->lastname, ENT_QUOTES) . '" /></p>'
      . '<p><span>First name:</span><input type="text" name="firstname" tabindex="5" class="medium" value="' . htmlspecialchars($user->firstname, ENT_QUOTES) . '" /></p>'
      . '<p><span>Email:</span><input type="text" name="email" tabindex="5" class="medium" value="' . htmlspecialchars($user->email, ENT_QUOTES) . '" /></p>'

      . '<h2 class="highlight">Change Password</h2>'
      . '<p><span>Current:</span><input type="password" name="old_pass" tabindex="5" class="medium" /></p>'
      . '<p><span>New:</span><input type="password" name="new_pass" tabindex="5" class="medium" /></p>'
      . '<p><span>Confirm New:</span><input type="password" name="confirm_pass" tabindex="5" class="medium" /></p>'
      . '<p>&nbsp;</p>'

      . '<h2 class="highlight">Settings</h2>
          <h3>Default start page</h3>' . $start_page_select . '
          <h3>Default resolution</h3>' . $resolution_select . '
          <h3>Adjust annotations box</h3>' . $annotation_cols . $annotation_rows . '
          <h3>Adjust &quot;other relations&quot; box</h3>'. $relations_cols . $relations_rows . '
          <h3>Size annotations edit popup</h3>' . $popup_cols . $popup_rows .'
          <h3>Tag insertion settings</h3>' . $tags_popup_check . $tags_popup_size . '
          <h3>Search Results Display</h3>
          <p><span>Search results per page:</span>
            <input type="text" name="recs_per_page" tabindex="5" class="medium" value="' . htmlspecialchars($user->recs_per_page, ENT_QUOTES) . '" /></p>
          <p class="center">
            <button type="submit" name="update_prefs" class="submit">Update Preferences</button><br />
            <button type="reset" name="reset_prefs" class="submit">Reset Values</button>
          </p>
       </form></div>
     <div class="user-filters">';

$last_viewed_query = "SELECT last_viewed FROM users WHERE user_id={$user->user_id}";
$last_viewed_result = $db->query($last_viewed_query);
if ($last_viewed_id = $db->fetch_assoc($last_viewed_result)) {
   include(DOC_ROOT . DIR_CLASSES . PERSON_CLASS);
   $person = new Person($db, $last_viewed_id['last_viewed']);
   $last_viewed = "<h2 class=\"highlight\">Last Record Viewed</h2><p class=\"center\"><a href=\"" . DETAIL_PAGE ."?id={$person->id}\">{$person->id}, ". $person->get_simplename($person->firstname) . " " . $person->get_simplename($person->lastname) . "</a></p>";
}


$filter_query = "SELECT filter, filter_id FROM filters WHERE user_id={$user->user_id} ORDER BY time_added DESC LIMIT 10";
if($filter_result = $db->query($filter_query)) {
  $filters_table ="<table summary=\"User Filters\"><caption class=\"highlight\">Recent Searches</caption><thead><tr><th>Link</th><th>Records returned</th><th>Filter</th></tr></thead>";

  $highlight = FALSE;
  while($row = $db->fetch_assoc($filter_result)) {
     if ($recs_returned_result = $db->query($row['filter'])) {
         $recs_returned = $db->num_rows($recs_returned_result);
         if (empty($recs_returned)) {$recs_returned = 0;}
         if($highlight) {
           $highlight = FALSE;
           $row_class = " class=\"highlight\"";
         } else {
           $highlight = TRUE;
           $row_class = "";
         }
         $filters_table .= "<tr$row_class><td><a href=\"". RESULTS_PAGE ."?filter_id={$row['filter_id']}\">Apply</a></td>"
                            .  "<td><p class=\"center\">$recs_returned</p></td>"
                            .  "<td>{$row['filter']}</td></tr>";
     }
  }
  $filters_table .= "</table>";
}
$content .= $page_headline . $message . $user_info . $last_viewed . $filters_table . "</div>";

$supplemental_js_files[] = 'prefs.js';
include(DOC_ROOT . DIR_TEMPLATES . DEFAULT_TEMPLATE);
?>
