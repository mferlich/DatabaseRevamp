<?php
include('genbio_top.php');  // exposes the DB class and declares constants
include(DOC_ROOT . DIR_CLASSES . PERSON_CLASS);
include(DOC_ROOT . DIR_LIB . SEARCH_FORMS_LIB);
include(DOC_ROOT . DIR_LIB . 'tags.php');

// get the correct record based on our GET params.
//   If id is set, but empty(): new record
//   If id is set to an integer: try to get that record
//   If id is not set or is set to anything other than empty or integer:
//  - first record based on flags set if appropriate
//  - first record based on filter set if appropriate
//  - first record in db
//


// if no special conditions - flags, filters, specific id - we just get the first record.
$query = "SELECT " . PERSON_TABLE .".". PERSON_ID . " FROM " . PERSON_TABLE
       . " WHERE " . PERSON_DELETE_REC . "='0'  ORDER BY ". PERSON_ID ." LIMIT 1";

$id_exists = false;
if (    isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])
        && is_int($_REQUEST['id'] + 0) && $_REQUEST['id'] >= 0) {
  $id = $_REQUEST['id'] + 0;
  $id_exists = (Person::exists($db, $id) || $id == 0) ? true : false;
}

$flagged_only = false;
$id_is_flagged = false;
if (isset($_SESSION['show_flagged']) && $_SESSION['show_flagged']) {
   $flagged_only = true;
   if ($id_exists && $id && Person::is_flagged($db, $id, $user->user_id) ) {
      $id_is_flagged = true;
   }
}

// asking for specific id - but make sure
//  - (user isn't restricting results to flagged only
//  - OR user is restricting to flagged, but requested record is flagged)
//  - AND make sure it's at least a valid integer (already checked if determining flagged_id).
if (  (isset($id) && !$flagged_only) || ($flagged_only && $id_is_flagged) ) {
     if ($id) {  // it's a positive integer, so we're not creating a new record
        if ($id_exists) { // it's a valid record.  No need to query, just get it via id.
            $query = "";

        } else {  // it's not a valid record.  Can we find the next closest value?
           $bad_id = new Person($db, $id);
           $bad_id->set_adjacent($db, $id);
           if ($bad_id->prev) {
               $id = $bad_id->prev;
               $query = "";
           } elseif ($bad_id->next) {
               $id = $bad_id->next;
               $query = "";
           }
       }

     } else {  // it's an integer, but its value is '0'
        $query = "";
     }

// $id is set and user wants only flagged records and current rec is not flagged
// we already know from previous "if" that if $id is set
// then flagged only is true and the current record is not flagged.
} elseif (isset($id) && $id ) {
   $query = "SELECT " . PERSON_TABLE . "." . PERSON_ID . " FROM " . PERSON_TABLE . ", " . FLAGS_TABLE
          ." WHERE " . PERSON_TABLE . "." . PERSON_DELETE_REC . "='0'"
               ." AND " . FLAGS_TABLE . "." . FLAGS_REC . "=" . PERSON_TABLE . "." . PERSON_ID
               ." AND " . FLAGS_TABLE . "." . FLAGS_USER . "='" . $user->user_id . "'"
          ." ORDER BY ". PERSON_ID ." LIMIT 1";

// On the other hand, if $id is zero, we want to create a new record
} elseif (isset($id) && !$id ) {
   $query = "";
// $id is not set, but there is a filter
} elseif (isset($_SESSION['filter']) && !empty($_SESSION['filter']) && !$flagged_only) {
    $id_query = $_SESSION['filter'] . " LIMIT 1";
    if ($id_result = $db->query($id_query)) {
       if ($id = $db->fetch_assoc($id_result)) {
         $id = $id[PERSON_ID];
         $query = "";  // we have a valid id, so we won't query for it
       }
    }
}

if ($query) {
  if ($result = $db->query($query)) {
     if ($row = $db->fetch_assoc($result)) {
        $id = $row['id'];
     }
  }
}

// if we still don't have an $id, we must need to make a new record
if (!isset($id)) {
  $id = 0;
}

// DEFINE $person, get all person data and info about which record should be next
$person = new Person($db, $id);

// make sure this record is NOT PENDING DELETION
if ($person->delete_rec){
    $person->set_adjacent($db, $person->id);
    if ($person->prev) {$id = $person->prev;}
    elseif ($person->next) {$id = $person->next;}
    else {$id=1;}
}

// get our PAGE TITLE for this person
if ($id){
   $page_title = $id . ': ' . $person->get_simplename($person->firstname)
                 . ' ' . $person->get_simplename($person->lastname);

// QUICKSEARCH BAR needs personal info for NAVIGATION ARROWS
   $quicksearch = form_quicksearch($db, $user, $person, $id);
   $db->query('UPDATE users SET last_viewed=' . $id . ' WHERE user_id=' . $user->user_id);

// generic title and navigation if CREATING A NEW RECORD
} else {
  $page_title = 'Create new biographical record';
  $quicksearch = form_quicksearch($db, $user, $person, CREATE_NEW_RECORD);
  if (isset($_SESSION['post'])) { // we've had a problem and we need to repost data
     $person_vars = get_object_vars($person);
     foreach ($_SESSION['post'] as $key => $val) {
        if(isset($person_vars[$key])) {
           $person->$key = $val;
        }
     }
     unset($_SESSION['post']);
  }
}

// define our MAIN DATA ENTRY FORM
// separate off the opening tag so that we can fold
// errors and warnings into the form


// We'll want to know field lengths to limit inputs in our person form
$db->get_field_lengths(PERSON_TABLE);

//don't let read only users make changes.
if ($user->userlevel > READ_ONLY)
{
  $readonly = "";
}
else
{
  $readonly = 'readonly="readonly"';
}

$person_form_head = '<form method="post" action="'. PROCESS_EDITS_PAGE . '" id="person_form">';

// START NORMAL DATA FORM - after errors and warnings processed.
$person_form =   '<p>'
               . '<input ' . $readonly . ' type="hidden" name="'. PERSON_ID .'" value="' . $person->id. '" />'
               . '<label for="' . FLAG_REC . '">Flag: </label><input type="checkbox" tabindex="1" id="'. FLAG_REC .'" name="'. FLAG_REC .'" value="1"';
$person_form .= ($person->flagged) ? ' checked="checked" />' : ' />';
$person_form .= ($person->id) ? ' ID: <strong>' . $person->id . '</strong> ' : ' ';
$lastname = (isset($person->lastname) && !empty($person->lastname) && $person->lastname != LASTNAME_PROMPT) ? htmlspecialchars($person->lastname, ENT_QUOTES) : LASTNAME_PROMPT;
$firstname = ($id) ? htmlspecialchars($person->firstname, ENT_QUOTES) : FIRSTNAME_PROMPT;
$nickname = ($id) ? htmlspecialchars($person->nickname, ENT_QUOTES) : NICKNAME_PROMPT;
$person_form .= '<input ' . $readonly . ' type="text" name="'. PERSON_LASTNAME .'" maxlength="' . $db->lengths[PERSON_TABLE][PERSON_LASTNAME] . '" tabindex="1" class="long" value="' . $lastname . '" />,'
               . ' <input ' . $readonly . ' type="text" name="'. PERSON_FIRSTNAME .'" maxlength="' . $db->lengths[PERSON_TABLE][PERSON_FIRSTNAME] . '" tabindex="2" class="medium" value="' .$firstname. '" /> dit'
               . ' <input ' . $readonly . ' type="text" name="'. PERSON_NICKNAME .'" maxlength="' . $db->lengths[PERSON_TABLE][PERSON_NICKNAME] . '" tabindex="3" class="medium" value="' . $nickname . '" />';

// GENDER DROPDOWN
   $gender_options ='<option value=""';
   $gender_options .= ($person->gender == '') ? ' selected="selected"' : '';
   $gender_options .= '>Gender</option>';
   $gender_options .= '<option value="m"';
   $gender_options .= ($person->gender == 'm') ? ' selected="selected"' : '';
   $gender_options .= '>Male</option>';
   $gender_options .= '<option value="f"';
   $gender_options .= ($person->gender == 'f') ? ' selected="selected"' : '';
   $gender_options .= '>Female</option>';
   $gender_options .= '<option value="?"';
   $gender_options .= ($person->gender == '?') ? ' selected="selected"' : '';
   $gender_options .= '>Uncertain</option>';
   $gender_options .= '<option value="na"';
   $gender_options .= ($person->gender == 'na') ? ' selected="selected"' : '';
   $gender_options .= '>Not Applicable</option>';
$person_form .= '<select name="'. PERSON_GENDER .'" class="short" tabindex="4">'. $gender_options . '</select>'
             . '</p>'

// LEFT COLUMN - BASIC INFO
             . '<div class="leftpane">'
               . '<p><span class="labelcol">Occupation:</span><span class="datacol"><input ' . $readonly . ' type="text" name="'. PERSON_OCCUPATION .'" maxlength="' . $db->lengths[PERSON_TABLE][PERSON_OCCUPATION] . '" tabindex="5" class="medium" value="'. htmlspecialchars($person->occupation, ENT_QUOTES) . '" /></span></p>'
               . '<p><span class="labelcol">Birth:</span><span class="datacol"><input ' . $readonly . ' type="text" name="'. PERSON_BIRTHDATE .'" maxlength="' . $db->lengths[PERSON_TABLE][PERSON_BIRTHDATE] . '" tabindex="6" class="medium" value="'. htmlspecialchars($person->birthdate, ENT_QUOTES) . '" /></span></p>'
               . '<p><span class="labelcol">Death:</span><span class="datacol"><input ' . $readonly . ' type="text" name="'. PERSON_DEATHDATE .'" maxlength="' . $db->lengths[PERSON_TABLE][PERSON_DEATHDATE] . '" tabindex="7" class="medium" value="'. htmlspecialchars($person->deathdate, ENT_QUOTES) . '" /></span></p>'
               . '<p><span class="labelcol">Origin:</span><span class="datacol"><input ' . $readonly . ' type="text" name="'. PERSON_ORIGIN .'" maxlength="' . $db->lengths[PERSON_TABLE][PERSON_ORIGIN] . '" tabindex="8" class="medium" value="'. htmlspecialchars($person->origin, ENT_QUOTES) . '" /></span></p>'
               . '<p><span class="labelcol">Residence:</span><span class="datacol"><input ' . $readonly . ' type="text" name="'. PERSON_RESIDENCE .'" maxlength="' . $db->lengths[PERSON_TABLE][PERSON_RESIDENCE] . '" tabindex="9" class="medium" value="'. htmlspecialchars($person->residence, ENT_QUOTES) . '" /></span></p>'
               . '<p><span class="labelcol">Spouse:</span><span class="datacol"><input ' . $readonly . ' type="text" name="'. PERSON_SPOUSE .'" maxlength="' . $db->lengths[PERSON_TABLE][PERSON_SPOUSE] . '" tabindex="10" class="medium" value="'. htmlspecialchars($person->spouse, ENT_QUOTES) . '" /></span></p>'
               . '<p><span class="labelcol">Parents:</span><span class="datacol"><input ' . $readonly . ' type="text" name="'. PERSON_PARENTS .'" maxlength="' . $db->lengths[PERSON_TABLE][PERSON_PARENTS] . '" tabindex="11" class="medium" value="'. htmlspecialchars($person->parents, ENT_QUOTES) . '" /></span></p>'
               . '<p><span class="labelcol">Children:</span><span class="datacol"><input ' . $readonly . ' type="text" name="'. PERSON_CHILDREN .'" maxlength="' . $db->lengths[PERSON_TABLE][PERSON_CHILDREN] . '" tabindex="12" class="medium" value="'. htmlspecialchars($person->children, ENT_QUOTES) . '" /></span></p>'
               . '<p><span class="labelcol">Other<br />Relations:</span><span class="datacol"><textarea ' . $readonly . '  name="'. PERSON_RELATIONS .'" tabindex="13" rows="'. $user->relations_rows .'" cols="'. $user->relations_cols .'">'. htmlspecialchars($person->relations, ENT_QUOTES) . '</textarea></span></p>'
               . '<p class="small-text">Last updated by '. $person->modified_by . ' on '. $person->modified_date . '<input type="hidden" name="checked_out_time" value="'. time() .'" /></p>';




// create correct SUBMIT BUTTONS depending on the situation (provided user has change privileges)
if ($user->userlevel > READ_ONLY){
    if($id) { // then we're NOT creating a new record
        $buttons ='<input type="submit" class="submit_button" name="update_review" tabindex="15" value="Update and Review" /><br />';
        if(isset($_SESSION['filter']) && $_SESSION['filter']) {
          $buttons .= '&nbsp;<input type="submit" class="submit_button" name="update_list" tabindex="15" value="Update and Return to List" /><br />';
        }
        $buttons .= '<input type="submit" class="delete_button" name="delete_rec" tabindex="15" value="Delete Current Record" /><br />';
    } else { // we ARE creating a new record
      $buttons = '<input type="submit" class="submit_button" name="insert_review" tabindex="15" value="Insert and Review" /><br />';
      $buttons .= '<input type="submit" class="submit_button" name="insert_go_new" tabindex="15" value="Insert and Add Another" /><br />';
      if(isset($_SESSION['filter']) && $_SESSION['filter']) {
        $buttons .= '&nbsp;<input type="submit" name="insert_list" tabindex="15" value="Insert and Return to List" /><br />';
      }
    }
   $person_form .= '<p class="action_buttons">' . $buttons . '&nbsp;<input type="reset" class="delete_button" name="reset" id="reset_button" tabindex="16" value="Cancel Changes" /></p>';
}
if (!empty($person->versions) && is_array($person->versions) ) {

 $count = sizeof($person->versions);
 if ($count > 10) {
   $maxcount = 10;
   $more_link = "<p>Listing 10 of $count versions. Click to <a href=\"version_list.php?id={$person->id}\">list all versions</a></p>";
 } else {
   $maxcount = $count;
   $more_link = "<p>Listing all $count versions</a></p>";
 }

 $person_form .= '
     <h2 style="clear:left; width: 100%; text-align:center; margin-top: 1em;">Other Versions</h2>' . $more_link . '
     <ul style="clear:left;">';

 for($i=0; $i<$maxcount; $i++) {
    $version = $person->versions[$i];
    $person_form .= '
        <li><a href="version_view.php?vid=' . $version['versionId'] . '">' . $version['modified_date'] . '</a> ('.$version['modified_by'].')</li>';
 }
 $person_form .= '
     </ul>';
}

// END LEFT COL on main data entry form
$person_form .= '</div>';



//  ANNOTATIONS TEXTAREA
$link = 'annotation_popup.php?id='.$id;
$js_link = "openNotesPopup('" . $link . "','".$id."'); return false;";
$popup_text = ($person->id) ? '<p><a href="'. $link .'" onclick="'. $js_link .'">Open full screen</a></p>' : '';

if ($user->userlevel > READ_ONLY)
{
  if ($user->tags_popup)
  {
    $tagSelect = '<p><a href="#" onclick="openTagsPopup(\'tags_popup.php\',\''.$user->tags_popup_height.'\',\''.$user->tags_popup_width.'\');">Insert TAGS</a></p>';
  }
  else
  {
     $tagSelect = makeTagSelect($db);
     $supplemental_js_files[] = 'insertCaseTags.js';
  }
}
else
{
   $tagSelect = "";
}

$person_form .=
    '<div>'
       . '<div id="annotation_title">'
           . '<h4>Notes</h4>'
           . $popup_text
           . $tagSelect
       . '</div>'
       . '<p><textarea ' . $readonly . '  name="'. PERSON_ANNOTATION .'" id="'. PERSON_ANNOTATION .'" cols="'. $user->annotation_cols .'" rows="'. $user->annotation_rows .'" tabindex="14">'. htmlspecialchars($person->annotation, ENT_QUOTES) . '</textarea></p>'
    . '</div>'
    . '<div style="clear:both;">&nbsp;</div>' //make sure both columns stay within form area.
  . '</form>';

// PUT OUR FORM TOGETHER with and ERRORS and WARNINGS encountered AT THE TOP
// Want them folded in so they can interact with the form and submit post data
// as necessary, as when CANCELLING A DELETION


if (isset($_SESSION['user_error']) and !empty($_SESSION['user_error'])) {
  $person_form_head .= $_SESSION['user_error'];
  $_SESSION['user_error'] = "";
}

if (isset($_SESSION['warning']) and !empty($_SESSION['warning'])) {
  $person_form_head .= $_SESSION['warning'];
  $_SESSION['warning'] = "";
}

$person_form = $person_form_head . $person_form;

// *********  ADD SPECIAL JAVASCRIPT FOR PROCESSING DETAILED RECORDS
$supplemental_js_files[] = 'details.js';

// *****  LAYOUT ORDER DEFINED HERE

//1. TOP SEARCH BAR
$content = $quicksearch;

//2. MAIN FORM, BOTTOM SEARCH BAR
$content .= $person_form . $quicksearch;

//3. FEED EVERYTHING TO THE TEMPLATE
include(DOC_ROOT . DIR_TEMPLATES . DEFAULT_TEMPLATE);
?>