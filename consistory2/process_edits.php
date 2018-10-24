<?php
include('genbio_top.php');  // exposes the DB class and declares constants
include(DOC_ROOT . DIR_CLASSES . PERSON_CLASS);
// Set a default $id which we may need to change.
if (   isset($_POST['save_on_exit']))
{
    if(isset($_POST['id']) && is_numeric($_POST['id']) && ($_POST['id'] >= 0))
    {
       $id = $_POST['id'];
       $edit_person = new Person($db, $id);
       if ($id && $user->userlevel > READ_ONLY)
       {
          $return_code = $edit_person->update($_POST, $db);
       }
       elseif($user->userlevel > READ_ONLY)
       {
          $return_code = $edit_person->insert($_POST, $db);
       }
    }
   exit();
}
elseif (isset($_POST['update_go_new']) && $_POST['update_go_new'])
{
           $id = 0;
}
elseif (isset($_POST['nav_id']) && is_numeric($_POST['nav_id']) && ($_POST['nav_id'] >= 0) )
{
     $id = $_POST['nav_id'];
}
elseif (isset($_POST['id']) && is_numeric($_POST['id']) && ($_POST['id'] >= 0) )
{
     $id = $_POST['id'];
}
else
{
   $count = 1;
   while (!Person::exists($db, $count)) {
      $count++;
   }
   $id = $count;
}

// if the user has just read-only access, don't bother with data processing
if ($user->userlevel <= READ_ONLY) {
  unset($_POST);
  header("Location: " . WEB_ROOT . DETAIL_PAGE . "?id=" . $id);
  exit();
}

$edit_person = new Person($db, $id);

// the first five conditions process the data and then show the detail page again
//   1+2: update_review/insert_review - record is updated
//   3+4: reconcile = 0 ("user wins") or 1 ("user edits of db win") - new data must be reconciled with DB since it has
//    changed since user started editing.
//   5: reconcile_= 3 ("db wins") - user discards changes and accepts most recent DB data.
// sixth condition allows update and return to list of filtered records.


if (   (isset($_POST['update_review']) && $_POST['update_review'])
    || (isset($_POST['update_go_new']) && $_POST['update_go_new'])) {
   $return_code = $edit_person->update($_POST, $db);
   if ($return_code == SUCCESS)
   {
      $location = "Location: " . WEB_ROOT . DETAIL_PAGE . "?id=" . $id;
   }
} elseif(   (isset($_POST['insert_review']) && $_POST['insert_review'])
        ||  (isset($_POST['insert_go_new']) && $_POST['insert_go_new'])){
   $return_code = $edit_person->insert($_POST, $db);
   if ($return_code == SUCCESS) {
       $id = (isset($_POST['insert_go_new']) && $_POST['insert_go_new']) ? 0 : $edit_person->id;
   } else {
      switch ($return_code) {
         case NO_DATA:
            $id = (isset($_POST['nav_id']) && is_numeric($_POST['nav_id']) && ($_POST['nav_id'] >= 0)) ? $_POST['nav_id'] : 0;
            break;
         case ERR_NO_LASTNAME:
         case ERR_NO_FIRSTNAME:
             $id = 0;
             $_SESSION['user_error'] .= MSG_MISSING_NAME;
             foreach ($_POST as $key=>$val) { // save vals in session to repost after redirect
                $_SESSION['post'][$key] = $val;
             }
            break;
      }
   }
   $location = "Location: " . WEB_ROOT . DETAIL_PAGE . "?id=" . $id;
   unset($_POST);

} elseif(isset($_POST['reconcile_user_wins'])  || isset($_POST['reconcile_db_wins']) ) {
   $index = (isset($_POST['reconcile_user_wins'])) ? 0 : 1;
   foreach($_POST as $key => $val){
     if (is_array($val)) {
       $post_data[$key] = $val[$index];
     } else {
       $post_data[$key] = $val;
     }
   }
   $return_code = $edit_person->update($post_data, $db);
   if ($return_code == SUCCESS) {
       $id = (isset($post_data['nav_id']) && !empty($post_data['nav_id'])) ? $post_data['nav_id'] :  $post_data['id'];
       $location = "Location: " . WEB_ROOT . DETAIL_PAGE . "?id=" . $id;
       unset($_POST);
   }

} elseif(isset($_POST['reconcile_no_change']) && $_POST['reconcile_no_change']){
   $return_code = SUCCESS;
   $id = (isset($_POST['nav_id']) && !empty($_POST['nav_id'])) ? $_POST['nav_id'] :  $_POST['id'];
   $location = "Location: " . WEB_ROOT . DETAIL_PAGE . "?id=" . $id;
   unset($_POST);

} elseif (isset($_POST['update_list']) && $_POST['update_list']) {
   $return_code = $edit_person->update($_POST, $db);
   if ($return_code == SUCCESS) {
      $location = "Location: " . WEB_ROOT . RESULTS_PAGE;
      unset($_POST);
   }
   if ($return_code) {

   }

} elseif (isset($_POST['insert_list']) && $_POST['insert_list']) {
   $return_code = $edit_person->insert($_POST, $db);
   switch ($return_code) {
      case SUCCESS:
      case NO_DATA:
        $location = "Location: " . WEB_ROOT . RESULTS_PAGE;
        unset($_POST);
        break;
      case ERR_NO_LASTNAME:
      case ERR_NO_FIRSTNAME:
        $_SESSION['user_error'] .= "<div class=\"user-error\">"
             . "<h2>Error: records must include first and last names</h2>"
             . "<p>When a name is unknown, use an X as a placeholder.</p></div>";
        foreach ($_POST as $key=>$val) { // save vals in session to repost after redirect
           $_SESSION['post'][$key] = $val;
        }
        $location = "Location: " . WEB_ROOT . DETAIL_PAGE . "?id=0";
        break;
   }

} elseif (isset($_POST['delete_rec'])) {
    $return_code = SUCCESS; // always need a return code.  No query here so always a success.
    $location = 'Location: ' . WEB_ROOT . DETAIL_PAGE . '?id=' . $_POST['id'];
    $_SESSION['warning'] .= '<div class="warning">Record ' .$_POST['id'] . ' pending deletion. '
                         . ' <input type="submit" name="delete_confirm" tabindex="15" value="Confirm" />'
                         . ' <input type="submit" name="delete_cancel" tabindex="15" value="Cancel" />'
                         .'</div>';
    unset($_POST);

} elseif (isset($_POST['delete_confirm'])) {
    $edit_person->set_adjacent($db);
    if ($edit_person->prev) {
       $show_id = $edit_person->prev;
    } elseif ($edit_person->next) {
       $show_id = $edit_person->next;
    } else {
       $show_id = 1;  // if record 1 doesn't exist, we find the next closest
    }
    $return_code = $edit_person->delete($_POST['id'], SET_DELETE, $db);
    if ($return_code == SUCCESS) {
       $location = 'Location: ' . WEB_ROOT . DETAIL_PAGE . '?id=' . $show_id;
      $_SESSION['warning'] .= '<div class="warning">Record ' .$_POST['id'] . ' deleted.</div>';
       unset($_POST);
    }

} elseif (isset($_POST['delete_cancel'])) {
    $return_code = $edit_person->delete($_POST['id'], UNSET_DELETE, $db);
    if ($return_code == SUCCESS) {
       $location = 'Location: ' . WEB_ROOT . DETAIL_PAGE . '?id=' . $_POST['id'];
       $_SESSION['warning'] .= '<div class="warning">Deletion Cancelled</div>';
       unset($_POST);
    }

} else {
  $return_code = FALSE;
}

switch ($return_code) {
  case SUCCESS:
  case ERR_NO_LASTNAME:
  case ERR_NO_FIRSTNAME:
  case NO_DATA:
     header($location);
     exit;
  case ERR_UPDATE_CONFLICT:
     $include_file = RECONCILE_PAGE;
     break;
  default:  // including error codes ERR_NO_PK and ERR_QUERY_FAILED as well as failure to set return code.
     $include_file = ERROR_PAGE;
     break;
}

$page_title = "Reconcile update conflict";

include($include_file);
include(DOC_ROOT . DIR_TEMPLATES . DEFAULT_TEMPLATE);

?>

</body>
</html>

