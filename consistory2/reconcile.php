<?php
// one of two files that can be included by process_edits.php
// the object $edit_person is of type person and is already declared in process_edits.php
// the data for the edits, though, is still in $_REQUEST, which must be reconciled with what is in the DB
// we should have already tested for $_REQUEST['id'] before getting here and the
// database and person classes must be available.


/* function reconcile only used here, so not in function library.
// - $index: name of the column being reconciled
// - $cola, $colb: the two fields being compared
// - $rowclass: basically for highlighting every other row
// RETURNS
//  - an html table row comparing the two version of the date
*/

function reconcile_row($index, $cola, $colb, $rowclass="") {
  $biggest = (strlen($cola) > strlen($colb)) ? strlen($cola) : strlen($colb);
  if (!$biggest) {
    return ""; // don't show rows with no data
  }
  if ($cola == $colb) {
    if($biggest > 250) {
      $cola = htmlspecialchars(substr($cola, 0, 250), ENT_QUOTES) . '<strong>[... continues.  No differences]</strong>';
      $colb = htmlspecialchars(substr($colb, 0, 250), ENT_QUOTES) . '<strong>[... continues.  No differences]</strong>';
      $class = '';
    } else {
      $class = ' class="left"';
    }
    return '<tr' . $rowclass . '><td' . $class . '>' . $cola . '</td><td class="center"><h4>' . $index . '</h4></td><td>' . $colb . '</td></tr>';
  } else {
    $cola = htmlspecialchars($cola, ENT_QUOTES);
    $colb = htmlspecialchars($colb, ENT_QUOTES);
    if ($biggest < 60) {
        $row = '<tr' . $rowclass . '><td class="left"><input type="text" name="' . $index . '[0]" value="' . $cola . '" /></td><td class="center"><h4>' . $index . '</h4></td><td><input type="text" name="' . $index[1] . '" value="' . $colb . '" /></td></tr>';
    } elseif ($biggest < 1000) {
        $rows = settype(ceil($biggest/100), "int");
        $row = '<tr' . $rowclass . '><td class="left"><textarea name="' . $index . '[0]" cols="50" rows="15">' . $cola . '</textarea></td><td class="center"><h4>' . $index . '</h4></td><td><textarea name="' . $index . '[1]" cols="50" rows="15">' . $colb . '</textarea></td></tr>';
    } else {
        $row = '<tr' . $rowclass . '><td class="left"><textarea name="' . $index . '[0]" cols="50" rows="15">' . $cola . '</textarea></td><td class="center"><h4>' . $index . '</h4></td><td><textarea name="' .$index. '[1]" cols="50" rows="15">' . $colb . '</textarea></td></tr>';
    }
    return $row;
  }
}

$db_person = new Person($db, $_REQUEST['id']);  // existing data

if (isset($_REQUEST['reconcile0']) || isset($_REQUEST['reconcile1'])) {
  $extra_apology = ' <strong>ONCE AGAIN</strong>';
} else {
  $extra_apology = '';
}

if (isset($_REQUEST['nav_id']) && !empty($_REQUEST['nav_id'])) {
  $nav_id_input = '<input type="hidden" name="nav_id" value="' . $_REQUEST['nav_id'] . '" />';
} else {
  $nav_id_input = '';
}

$content = '<h1>There has been a conflict</h1><h2>Record ' . $_REQUEST['id'] . ' has' . $extra_apology . ' been updated since you opened it for editing.</h2><h2>The table below will allow you to edit and reconcile those fields with conflicts</h2><form method="post" action="' . PROCESS_EDITS_PAGE . '"><table class="reconcile"><tr class="header"><td class="left"><h4>Edited version</h4>'
         . '<input type="hidden" name="' . PERSON_ID . '" value="' . $_REQUEST['id'] . '" />' . $nav_id_input . '<input type="hidden" name="checked_out_time" value="' . time() . '" /></td><td>&nbsp;</td><td><h4>Database version.</h4><p class="small-text">Last updated by ' . $db_person->modified_by . ' on ' . $db_person->modified_date . '</p></td></tr>';
$content .= reconcile_row(PERSON_LASTNAME, stripslashes($_REQUEST[PERSON_LASTNAME]), $db_person->lastname);
$content .= reconcile_row(PERSON_FIRSTNAME, stripslashes($_REQUEST[PERSON_FIRSTNAME]), $db_person->firstname, " class=\"highlight\"");
$content .= reconcile_row(PERSON_NICKNAME, stripslashes($_REQUEST[PERSON_NICKNAME]), $db_person->nickname);
$content .= reconcile_row(PERSON_GENDER, stripslashes($_REQUEST[PERSON_GENDER]), $db_person->gender, " class=\"highlight\"");
$content .= reconcile_row(PERSON_OCCUPATION, stripslashes($_REQUEST[PERSON_OCCUPATION]), $db_person->occupation);
$content .= reconcile_row(PERSON_SPOUSE, stripslashes($_REQUEST[PERSON_SPOUSE]), $db_person->spouse, " class=\"highlight\"");
$content .= reconcile_row(PERSON_PARENTS, stripslashes($_REQUEST[PERSON_PARENTS]), $db_person->parents);
$content .= reconcile_row(PERSON_CHILDREN, stripslashes($_REQUEST[PERSON_CHILDREN]), $db_person->children, " class=\"highlight\"");
$content .= reconcile_row(PERSON_RELATIONS, stripslashes($_REQUEST[PERSON_RELATIONS]), $db_person->relations);
$content .= reconcile_row(PERSON_BIRTHDATE, stripslashes($_REQUEST[PERSON_BIRTHDATE]), $db_person->birthdate, " class=\"highlight\"");
$content .= reconcile_row(PERSON_DEATHDATE, stripslashes($_REQUEST[PERSON_DEATHDATE]), $db_person->deathdate);
$content .= reconcile_row(PERSON_ORIGIN, stripslashes($_REQUEST[PERSON_ORIGIN]), $db_person->origin, " class=\"highlight\"");
$content .= reconcile_row(PERSON_RESIDENCE, stripslashes($_REQUEST[PERSON_RESIDENCE]), $db_person->residence);
$content .= reconcile_row(PERSON_ANNOTATION, stripslashes($_REQUEST[PERSON_ANNOTATION]), $db_person->annotation, " class=\"highlight\"");

$content .= '<tr><td class="left"><button type="submit" name="reconcile_user_wins" value="1">Overwrite the database<br />with lefthand column</button></td><td>&nbsp;</td><td><button type="submit" name="reconcile_db_wins" value="2">Overwrite database <br />with righthand column*</button></td></tr><tr><td colspan="3"><p class="center"><button type="submit" name="reconcile_no_change"  value="3">Discard changes; no updates to the database</button></p><p class="center"><button type="reset">Reset values on this page and start over</button></p><p>*That is, overwrite with any changes you have made to <strong>this</strong> column on <strong>this</strong> page. Previous edits will be lost and existing record in the database will be overwritten.  If you have made no edits on this page, this is the same as choosing to keep the database values and discard all changes.</p></td></tr></table></form>';
?>
