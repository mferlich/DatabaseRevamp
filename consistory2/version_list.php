<?php
include('genbio_top.php');  // exposes the DB class and declares constants
include(DOC_ROOT . DIR_CLASSES . PERSON_CLASS);

$id_exists = false;
if (    isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])
        && is_int($_REQUEST['id'] + 0) && $_REQUEST['id'] >= 0) {
  $id = $_REQUEST['id'] + 0;
  $id_exists = (Person::exists($db, $id) || $id == 0) ? true : false;
}

if (!$id_exists) {
  $content = "<h1>Can't find a corresponding record</h1><p>Use your browser's <strong>BACK</strong> button to return to the record you were looking at</p>";
  include(DOC_ROOT . DIR_TEMPLATES . DEFAULT_TEMPLATE);
  exit;
}

$person = new Person($db, $id);

$page_title = $id . ': ' . $person->get_simplename($person->firstname)
                 . ' ' . $person->get_simplename($person->lastname);

if (!empty($person->versions) && is_array($person->versions) ) {
 $content = '
     <h1>Alternate Versions</h1>
     <h2>Record ' . $page_title . ' </h2>
     <ul>';
 $count = sizeof($person->versions);
 for($i=0; $i<$count; $i++) {
    $version = $person->versions[$i];
    $content .= '
        <li><a href="version_view.php?vid=' . $version['versionId'] . '">' . $version['modified_date'] . '</a> ('.$version['modified_by'].')</li>';
 }
 $content .= '
     </ul>';
}
  include(DOC_ROOT . DIR_TEMPLATES . DEFAULT_TEMPLATE);

