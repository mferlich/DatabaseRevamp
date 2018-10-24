<?php
include('genbio_top.php');  // exposes the DB class and declares constants
include(DOC_ROOT . DIR_CLASSES . PERSON_CLASS);
include(DOC_ROOT . DIR_CLASSES . VERSION_CLASS);
include(DOC_ROOT . DIR_CLASSES . 'cl_DifferenceEngine.php').

// compare current version of a record to a given previous version.

// first double check that we have a vaild ID. If not, quit and just show error message.
// SHOULD ALSO CHECK REFERRER AND MAKE SURE THAT THIS REQUEST IS COMING FROM PERSON.PHP
$version_exists = false;
if (    empty($_REQUEST['vid']) || !is_numeric($_REQUEST['vid'])
        || !is_int($_REQUEST['vid'] + 0) || $_REQUEST['vid'] < 1) {

  $content = "<h1>Invalid Version ID Number</h1><p>Use your browser's <strong>BACK</strong> button to return to the record you were looking at</p>";
  include(DOC_ROOT . DIR_TEMPLATES . DEFAULT_TEMPLATE);
  exit;
}

$vid = (int) $_REQUEST['vid'];
$old = new Version($db, $vid);
if (!$old->exists) {
  $content = "<h1>Could Not Find Requested Archived Version</h1><p>Use your browser's <strong>BACK</strong> button to return to the record you were looking at</p>";
  include(DOC_ROOT . DIR_TEMPLATES . DEFAULT_TEMPLATE);
  exit;
}

// GET DATA FOR NEWEST VERSION
$current = new Person($db, $old->id);

$even = ' class="even"'; // row zero is even.
$content = '<table>
<tr class="header"><td>Field</td><td>Current Version</td><td>Older Version</td></tr>
<tr class="header"><td>&nbsp;</td><td>'.$current->modified_date.'</td><td>'.$old->modified_date.'</td></tr>
<tr class="header"><td>&nbsp;</td><td>'.$current->modified_by.'</td><td>'.$old->modified_by.'</td></tr>';

if ($old->lastname != $current->lastname) {
  $even = (empty($even)) ? ' class="even"' : "";
  $content .= "\n    <tr><td class=\"colHeader\">Nom de famille:</td><td>".$current->lastname."</td><td>".$old->lastname."</td></tr>";
  };
if ($old->firstname != $current->firstname) {
  $even = (empty($even)) ? ' class="even"' : "";
  $content .= "\n    <tr" . $even . "><td class=\"colHeader\">Nom:</td><td>".$current->firstname."</td><td>".$old->firstname."</td></tr>";
  };
if ($old->nickname != $current->nickname) {
  $even = (empty($even)) ? ' class="even"' : "";
  $content .= "\n    <tr" . $even . "><td class=\"colHeader\">Surnom:</td><td>".$current->nickname."</td><td>".$old->nickname."</td></tr>";
  };
if ($old->gender != $current->gender) { 
  $even = (empty($even)) ? ' class="even"' : "";
  $content .= "\n    <tr" . $even . "><td class=\"colHeader\">Sexe</td><td>".$current->gender."</td><td>".$old->gender."</td></tr>"; };
if ($old->occupation != $current->occupation) {
  $even = (empty($even)) ? ' class="even"' : "";
   $content .= "\n    <tr" . $even . "><td class=\"colHeader\">Métier</td><td>".$current->occupation."</td><td>".$old->occupation."</td></tr>"; };
if ($old->origin != $current->origin) {
  $even = (empty($even)) ? ' class="even"' : "";
   $content .= "\n    <tr" . $even . "><td class=\"colHeader\">Origine</td><td>".$current->origin."</td><td>".$old->origin."</td></tr>"; };
if ($old->residence != $current->residence) {
  $even = (empty($even)) ? ' class="even"' : "";
   $content .= "\n    <tr" . $even . "><td class=\"colHeader\">Domicile</td><td>".$current->residence."</td><td>".$old->residence."</td></tr>"; };
if ($old->birthdate != $current->birthdate) {
  $even = (empty($even)) ? ' class="even"' : "";
   $content .= "\n    <tr" . $even . "><td class=\"colHeader\">Naissance</td><td>".$current->birthdate."</td><td>".$old->birthdate."</td></tr>"; };
if ($old->deathdate != $current->deathdate) {
  $even = (empty($even)) ? ' class="even"' : "";
   $content .= "\n    <tr" . $even . "><td class=\"colHeader\">Mort</td><td>".$current->deathdate."</td><td>".$old->deathdate."</td></tr>"; };
if ($old->spouse != $current->spouse) {
  $even = (empty($even)) ? ' class="even"' : "";
   $content .= "\n    <tr" . $even . "><td class=\"colHeader\">Epoux</td><td>".$current->spouse."</td><td>".$old->spouse."</td></tr>"; };
if ($old->parents != $current->parents) {
  $even = (empty($even)) ? ' class="even"' : "";
   $content .= "\n    <tr" . $even . "><td class=\"colHeader\">Parents</td><td>".$current->parents."</td><td>".$old->parents."</td></tr>"; };
if ($old->children != $current->children) {
  $even = (empty($even)) ? ' class="even"' : "";
   $content .= "\n    <tr" . $even . "><td class=\"colHeader\">Enfants</td><td>".$current->children."</td><td>".$old->children."</td></tr>"; };
if ($old->relations != $current->relations) {
  $even = (empty($even)) ? ' class="even"' : "";
   $content .= "\n    <tr" . $even . "><td class=\"colHeader\">Relations</td><td>".$current->relations."</td><td>".$old->relations."</td></tr>"; };

if ($old->annotation != $current->annotation)  {

  $content .='<tr><td class="colHeader">NOTES<br /> <span class="helpNote">Shows only <strong>lines that have changed</strong> with 2 additional lines for context.</span></td><td colspan="2"><table>';
  $oa = explode( "\n", str_replace( "\r\n", "\n", $old->annotation ) );
  $ca = explode( "\n", str_replace( "\r\n", "\n", $current->annotation ) );
  $diffs = new Diff( $oa, $ca );
  $formatter = new TableDiffFormatter();

  $content .= $formatter->format( $diffs );
  $content .= "</td></tr></table>";
};
$content .= "</table>";

$content = '<div class="versionCompare"> ' . $content . '</div>';

//3. FEED EVERYTHING TO THE TEMPLATE
include(DOC_ROOT . DIR_TEMPLATES . DEFAULT_TEMPLATE);
?>