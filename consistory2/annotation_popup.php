<?php
include('genbio_top.php');  // exposes the DB class and declares constants
include(DOC_ROOT . DIR_CLASSES . PERSON_CLASS);

function showName($name)
{
   $name_array = explode(',', $name);
   if ($name_array[0] != $name)
   {
      $display_name =  htmlspecialchars($name_array[0], ENT_QUOTES);
      $display_name .= ' ('. htmlspecialchars($name, ENT_QUOTES) . ')';
      return $display_name;
   }
   else
   {
      return $name;
   }
}

if(isset($_POST['update_review']) && !empty($_POST['update_review']))
{
  $q = "UPDATE ". PERSON_TABLE ." SET ". PERSON_ANNOTATION ."='". $_POST['annotation']."'";
  $q .= " WHERE ".PERSON_ID."=" . $_REQUEST['id'];
  $db->query($q);
}

//don't let read only users make changes.
if ($user->userlevel > READ_ONLY)
{
  $readonly = "";
}
else
{
  $readonly = 'readonly="readonly"';
}

// DEFINE $person, get all person data and info about which record should be next
$person = new Person($db, $_REQUEST['id']);

// get our PAGE TITLE for this person
$page_title = $_REQUEST['id'] . ': ' . $person->get_simplename($person->firstname)
                  . ' ' . $person->get_simplename($person->lastname);

// brief help line for users
$help = '
        <p class="help">To adjust the size of the data-entry window, change "Popup cols" and "Popup rows" in your preferences window.</p>';


// define our MAIN DATA ENTRY FORM
// separate off the opening tag so that we can fold
// errors and warnings into the form

$notes_form_head = '<form method="post" action="annotation_popup.php" id="annotation_form" onsubmit="warnChanges(true);">';

// START NORMAL DATA FORM - after errors and warnings processed.
$notes_form =   '<p class="heading">'
               . '<input type="hidden" name="'. PERSON_ID .'" value="' . $person->id. '" />';
$notes_form .= ' ID: <strong>' . $person->id . '</strong> ';

$lastname = showName($person->lastname, ENT_QUOTES);
$firstname = showName($person->firstname, ENT_QUOTES);
$nickname = showName($person->nickname, ENT_QUOTES);
$notes_form .=  $firstname . ' ' . $lastname;

if ($nickname)
{
   $notes_form .=  ' dit ' . $nickname;
}

$notes_form .= '</p>';

//  ANNOTATIONS TEXTAREA
$notes_form .= '
        <p><textarea ' . $readonly . '  name="'. PERSON_ANNOTATION .'" cols="'. $user->popup_cols .'" rows="'. $user->popup_rows .'" tabindex="14">'. htmlspecialchars($person->annotation, ENT_QUOTES) . '</textarea></p>';

// create correct SUBMIT BUTTONS depending on the situation (provided user has change privileges)
if ($user->userlevel > READ_ONLY){
    $buttons =  '
          <input type="submit" class="submit_button" name="update_review" value="Update Record" />';
    $buttons .= '
          <input type="reset" class="delete_button" name="reset" value="Cancel Changes" />';
    $buttons .= '
          <input type="reset" class="delete_button" onclick="window.location.reload();" name="reload" value="Reload" />';
    $buttons .= '
          <input type="reset" class="delete_button" onclick="window.close();return false;" name="close" value="Close" />';
    $notes_form .= '<p class="buttons">' . $buttons . '</p>';
}
$notes_form .= '</form>';

// PUT OUR FORM TOGETHER with and ERRORS and WARNINGS encountered AT THE TOP
// Want them folded in so they can interact with the form and submit post data
// as necessary, as when CANCELLING A DELETION


if (isset($_SESSION['user_error']) and !empty($_SESSION['user_error'])) {
  $notes_form_head .= $_SESSION['user_error'];
  $_SESSION['user_error'] = "";
}

if (isset($_SESSION['warning']) and !empty($_SESSION['warning'])) {
  $notes_form_head .= $_SESSION['warning'];
  $_SESSION['warning'] = "";
}

$supplemental_js_files[] = 'notesWin.js';

$content = $help . $notes_form_head . $notes_form;

// FEED EVERYTHING TO THE TEMPLATE
include(DOC_ROOT . DIR_TEMPLATES . POPUP_TEMPLATE);
?>