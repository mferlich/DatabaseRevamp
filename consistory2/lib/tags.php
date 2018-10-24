<?php
function makeTagSelect($db)
{
    //DROP DOWN LIST OF CONSISTORY CASE TAGS
    $q = "SELECT ct.tag AS tag, tc.cat AS cat
          FROM casetags ct, tagcats tc
          WHERE ct.tcid LIKE tc.tcid
          ORDER BY tc.sortpos, tc.tcid, ct.sortpos, ct.tid";
    $r = $db->query($q);
    $tagOptions = '';
    $tagCat = '';
    while ($option = $db->fetch_assoc($r))
    {
      if ($tagCat != $option['cat'])
      {
         if ($tagCat != '')
         {
            $tagOptions .= '
             </optgroup>';
         }
         $tagCat = $option['cat'];
         $tagOptions .= '
             <optgroup label="' . htmlentities(stripslashes($option['cat'])) . '">';
      }
      $tagOptions .= '
                 <option>' . $option['tag'] . '</option>';
    }
    $tagOptions .= (empty($tagCat)) ? '' : '
             </optgroup>';

    $tagSelect = '
               <select name="caseTags" id="caseTags" onclick="setMultiple(\'caseTags\')">' . $tagOptions . '
               </select>
               <input type="button" name="insert" value="Insert tag(s)" onclick="insertCaseTag()" title="Insert one or more tags classifying cases" />';

   return $tagSelect;
}

function makeTagCheckboxes($db)
{
    $tableWidth = 4;

    $button = '<input type="button" name="insert" value="Insert tag(s)" onclick="insertCaseTagFromChild()" title="Insert one or more tags classifying cases" />';

    $q = "SELECT ct.tag AS text, tc.cat AS cat, ct.tid AS id
          FROM casetags ct, tagcats tc
          WHERE ct.tcid LIKE tc.tcid
          ORDER BY tc.sortpos, tc.tcid, ct.sortpos, ct.tid";
    $r = $db->query($q);
    $tagTable = '
    <table class="TagCheckboxes" border="1">
       <tr><td colspan="'.$tableWidth.'">Check of the tags you wish to insert</td></tr>
       <tr>';

    $tagCat = '';
    $count = 0;
    while ($tag = $db->fetch_assoc($r))
    {
      $count++;
      if ($tagCat != $tag['cat'])
      {
         if ($tagCat != '')
         {
           $tagTable .= fillRow($count, $tableWidth);
         }
         $tagCat = $tag['cat'];
         $tagTable .= '
        <td colspan="'.$tableWidth.'"><h3 class="tagcat">' . htmlentities(stripslashes($tag['cat'])) . $button . '</h3></td></tr>
        <tr>';
         $count = 0;
      }
      if ($count >= $tableWidth)
      {
         $tagTable .= '
         </tr>
         <tr>';
         $count = 0;
      }
      $tagTable .= '
             <td style="text-align:top;"><input type="checkbox" name="tag" value="' . $tag['text'] . '" />' . $tag['text'] . '</option>';
    }
    $tagTable .= fillRow(++$count, $tableWidth);

    $tagTable .= '
         <tr><td colspan="'.$tableWidth.'">
               <input type="button" name="insert" value="Insert tag(s)" onclick="insertCaseTagFromChild()" title="Insert one or more tags classifying cases" />
               <input type="reset" class="delete_button" name="reset" value="Reset Form" />
               <input type="reset" class="delete_button" onclick="window.close();return false;" name="close" value="Close" />
         </td></tr>
     </table>';

   return $tagTable;
}

function fillRow($count, $width)
{
  $endRow = '';
  if ($count < 1)
  {
    return '';
  }
  for($count; $count<$width; $count++)
  {
    $endRow .= '
      <td>&nbsp;</td>';
  }
  return $endRow . '
     </tr>';
}

?>