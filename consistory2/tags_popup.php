<?php
include('genbio_top.php');  // exposes the DB class and declares constants
include(DOC_ROOT . DIR_LIB . 'tags.php');


//don't let read only users make changes.
if ($user->userlevel > READ_ONLY)
{
  $readonly = "";
}
else
{
  $readonly = 'readonly="readonly"';
}

$page_title = 'Insert Case Tags';


// define our MAIN DATA ENTRY FORM
// separate off the opening tag so that we can fold
// errors and warnings into the form

$tags_form = '<form method="post" action="" id="tagCheckboxes" onsubmit="insertTags();">';



$tags_form .=  makeTagCheckboxes($db);
$tags_form .= '</form>';

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

$supplemental_js_files[] = 'insertCaseTags.js';


$content = $tags_form;

// FEED EVERYTHING TO THE TEMPLATE
include(DOC_ROOT . DIR_TEMPLATES . POPUP_TEMPLATE);
?>