<?php
// building blocks first, then build forms.
// functions
//  - colnames_dd (&$db, $control_name, $table, $selected="", $tabindex=30)
//  - match_types_dd();
//  - form_quicksearch(&$db, $id=1, $prev="", $next="", $tabindex=30, $action=RESULTS_PAGE) {
//  - form_fullsearch(&$db)


// create a drop down select list with column names
function colnames_dd (&$db, $control_name, $table, $selected="", $tabindex=0) {

  $field_list = $db->list_fields($table, DB_NAME);
  $field_count = $db->num_fields($field_list);
  $tabindex = ($tabindex) ? " tabindex=\"$tabindex\"" : "";

  $dd = "<select name=\"" . $control_name. "[]\" class=\"short\"$tabindex>\n";
  if(empty($selected)) {
      $dd .= "<option value=\"0\" selected=\"selected\">Select field</option>";
  }
  for ($i=0; $i<$field_count; $i++) {
    $field_name = $db->field_name($field_list, $i);
    $select_attr = ($field_name == $selected) ? " selected=\"selected\"" : "";
    $dd .= "\n<option value=\"$field_name\"$select_attr>$field_name</option>";
  }
  return $dd . "\n</select>";
} // end form_colnames_dd function

function match_types_dd($tabindex=0){
  $tabindex = ($tabindex) ? " tabindex=\"$tabindex\"" : "";
  $dd = "<select name=\"match_type[]\" class=\"short\"$tabindex>"
          . "\n<option value=\"0\" selected=\"selected\">Includes</option>"
          . "\n<option value=\"1\">Exact</option>"
          . "\n<option value=\"2\">Begins With</option>"
          . "\n<option value=\"3\">Ends With</option>"
          . "\n<option value=\"4\">Greater Than</option>"
          . "\n<option value=\"5\">Less Than</option>"
          . "\n</select>";
  return $dd;
}


// nav form builds the navigational form at the top of the person edit page.
// PARAMETERS
// - $db and $person objects sent by reference
// - $id: if $id = 0 = HIDE_NAV_ARROWS, don't show prev, next etc arrows
// - $tabindex: set high so that up to 30 fields can be tabbed before going here
// - $action: defaults to search results page.
// RETURNS
// - HTML to generate the simple search form and navigation arrows.
// NOTES
// - nav arrows are *only* returned if on the page DETAIL page and the JavaScript links depend on
//      the javascript in the header of DETAIL page.

function form_quicksearch(&$db, &$user, &$person, $id=1, $tabindex=30, $action=RESULTS_PAGE) {

         // We need our top quicksearch box to have a unique id because
         // we want to use Javascript/CSS/DOM to show/hide nav box
   $_SESSION['search_box_num']++;
   $search_box_id = ' id="searchBox' . $_SESSION['search_box_num'] . '"';
   $form = "\n<form method=\"post\" action=\"$action\" class=\"quicksearch\">"
         . "<p class=\"left\">";
   $nav_arrows = "";
   if($id >= 0) {
       $person->set_adjacent($db, $id);
       if($person->first && $person->total > 1) {
          if($id) {
            $nav_arrows .= "<a href=\"javascript:submitForm('person_form','" . $person->first . "','update_review')\" onclick=\"okToLeave();\"><img src=\"" . WEB_ROOT . DIR_IMAGES . "first.png\" alt=\"Jump to first record\"  title=\"Jump to first record\" width=\"30\" height=\"22\" /></a>";
          } else {
            $nav_arrows .= "<a href=\"javascript:submitForm('person_form','" . $person->first . "','insert_review')\" onclick=\"okToLeave();\"><img src=\"" . WEB_ROOT . DIR_IMAGES . "first.png\" alt=\"Jump to first record\"  title=\"Jump to first record\" width=\"30\" height=\"22\" /></a>";
          }
       } else {
         $nav_arrows .= "<img src=\"" . WEB_ROOT . DIR_IMAGES . "first-disabled.png\" alt=\"At top of result set\"  title=\"Top of result set\" width=\"30\" height=\"22\" />";
       }
       if($person->prev && $id && $person->total > 1){
         $nav_arrows .= "<a href=\"javascript:submitForm('person_form','" . $person->prev . "','update_review')\" onclick=\"okToLeave();\"><img src=\"" . WEB_ROOT . DIR_IMAGES . "prev.png\" alt=\"Jump to previous record\"  title=\"Jump to previous record\" width=\"22\" height=\"22\" /></a>";
       } elseif (!$id && $person->last) {
         $nav_arrows .="<a href=\"javascript:submitForm('person_form','" . $person->last . "','insert_review')\" onclick=\"okToLeave();\"><img src=\"" . WEB_ROOT . DIR_IMAGES . "prev.png\" alt=\"Jump to previous record\"  title=\"Jump to previous record\" width=\"22\" height=\"22\" /></a>";
       } else {
         $nav_arrows .= "<img src=\"" . WEB_ROOT . DIR_IMAGES . "prev-disabled.png\" alt=\"No previous record\"  title=\"No previous record\" width=\"22\" height=\"22\" />";
       }
       if($person->next && $id && $person->total > 1) {
         $nav_arrows .= "<a href=\"javascript:submitForm('person_form','" . $person->next . "','update_review')\" onclick=\"okToLeave();\"><img src=\"" . WEB_ROOT . DIR_IMAGES . "next.png\" alt=\"Jump to next record\"  title=\"Jump to next record\" width=\"22\" height=\"22\" /></a>";
       } else {
         $nav_arrows .= "<img src=\"" . WEB_ROOT . DIR_IMAGES . "next-disabled.png\" alt=\"No next record\"  title=\"No next record\" width=\"22\" height=\"22\" />";
       }
       if($person->last && $person->total > 1) {
          if ($id) {
            $nav_arrows .= "<a href=\"javascript:submitForm('person_form','" . $person->last . "','update_review')\" onclick=\"okToLeave();\"><img src=\"" . WEB_ROOT . DIR_IMAGES . "last.png\" alt=\"Jump to last record\"  title=\"Jump to last record\" width=\"30\" height=\"22\" /></a>";
          } else {
            $nav_arrows .= "<a href=\"javascript:submitForm('person_form','" . $person->last . "','insert_review')\" onclick=\"okToLeave();\"><img src=\"" . WEB_ROOT . DIR_IMAGES . "last.png\" alt=\"Jump to last record\"  title=\"Jump to last record\" width=\"30\" height=\"22\" /></a>";
          }
       } else {
         $nav_arrows .= "<img src=\"" . WEB_ROOT . DIR_IMAGES . "last-disabled.png\" alt=\"At end of result set\" title=\"End of result set\" width=\"30\" height=\"22\" border=\"0\" />";
       }
       if ($id && $user->userlevel > READ_ONLY) {
          $nav_arrows .= "<a href=\"javascript:submitForm('person_form','0','update_go_new')\" onclick=\"okToLeave();\"><img src=\"" . WEB_ROOT . DIR_IMAGES . "new.png\" alt=\"Create new record\"  title=\"Create new record\" width=\"40\" height=\"22\"  /></a>";
       } elseif($user->userlevel > READ_ONLY) {
          $nav_arrows .= "<a href=\"javascript:submitForm('person_form','0','insert_go_new')\" onclick=\"okToLeave();\"><img src=\"" . WEB_ROOT . DIR_IMAGES . "new.png\" alt=\"Create new record\"  title=\"Create new record\" width=\"40\" height=\"22\"  /></a>";
       }
       if($person->position && $person->total) {
          $nav_arrows .= " Record " . $person->position . " of " . $person->total;
       }
   }

   if ($nav_arrows) {
        $form .= $nav_arrows;
   } else {
        $form .= "&nbsp; &nbsp;";
   }

   $form .= "</p>\n<p class=\"right\"". $search_box_id .">"
            . "<input type=\"hidden\" name=\"tables[]\" value=\"" . PERSON_TABLE . "\" />"
            . "<input type=\"hidden\" name=\"keys[]\" value=\"" . PERSON_ID . "\" />"
            . colnames_dd($db, 'haystack', PERSON_TABLE, 'lastname', $tabindex)
            . match_types_dd($tabindex)
            . "\n<input type=\"text\" name=\"needle[]\" class=\"short\" tabindex=\"$tabindex\" />"
            . "\n sort by " . colnames_dd($db, 'order', PERSON_TABLE, 'firstname', $tabindex)
            . "\n<button type=\"submit\" name=\"search_wizard\" value=\"1\" tabindex=\"$tabindex\">Find</button>";

   if(!strpos($_SERVER['PHP_SELF'], SEARCH_PAGE)) {
      $form .= ' <a href="' . SEARCH_PAGE . '" class="adv-search">advanced</a>';
   }

   return $form . "</p></form>";
} // end func form_quicksearch


function form_fullsearch(&$db) {
  $form = "<form method=\"post\" action=\"" . RESULTS_PAGE . "\" class=\"fullsearch\">"
        . "<input type=\"hidden\" name=\"tables[]\" value=\"" . PERSON_TABLE . "\" />"
        . "<input type=\"hidden\" name=\"keys[]\" value=\"" . PERSON_ID . "\" />";
  $form .= "<div><p class=\"short\">&nbsp;</p>"
        . "<p>Search string: <input type=\"text\" name=\"needle[]\" class=\"short\" />"
        . " Field: " . colnames_dd($db, 'haystack', PERSON_TABLE, 'lastname')
        . " Match type: " . match_types_dd() . "</p></div>";

  $search_row = "<p>Search string: <input type=\"text\" name=\"needle[]\" class=\"short\" />"
           . " Field: " . colnames_dd($db, 'haystack', PERSON_TABLE) . " Match type: "
           . match_types_dd() . "</p>";
  $operator_select = "<p class=\"short\"><select name=\"operator[]\"><option>AND</option><option>OR</option></select></p>";

  for ($i=1; $i<12; $i++){
     $form .= "<div>" . $operator_select . $search_row . "</div>";
  }
  $form .= "<div><p>Sort by up to three criteria: "
         . colnames_dd($db, 'order', PERSON_TABLE, 'lastname')
         . colnames_dd($db, 'order', PERSON_TABLE, 'firstname')
         . colnames_dd($db, 'order', PERSON_TABLE, 'id') . "</p></div>";
  $form .="<div><p><button type=\"submit\" name=\"search_wizard\" value=\"1\">Search</button></p></div>";
  $form .= "</form>";

  return $form;
} // end function form_fullsearch


function form_advanced_search() {
  $form = "<form method=\"post\" action=\"" . RESULTS_PAGE . "\" class=\"fullsearch\">"
        . "<p><input type=\"hidden\" name=\"tables[]\" value=\"" . PERSON_TABLE . "\" />"
        . "<input type=\"hidden\" name=\"keys[]\" value=\"" . PERSON_ID . "\" />"
        . "<textarea name=\"where_clause\">(lastname LIKE '%d\'arbey%' AND firstname LIKE 'jean%') \nOR (birthdate&gt;1540 and birthdate&lt;1560) \nORDER BY lastname DESC, firstname</textarea></p>"
        . "<div><p><button type=\"submit\" name=\"search_sql\" value=\"2\">Search</button></p></div>"
        . "</form>";
  return $form;
} // end function advanced search

?>
