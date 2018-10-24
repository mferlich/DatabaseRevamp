<?php
class User {
  var $user_id = 0;
  var $username = "";
  var $lastname = "";
  var $firstname = "";
  var $userlevel = 0;
  var $email = "";
  var $last_login = "";
  var $recs_per_page = "";
  var $start_page = "PREFERENCES_PAGE";
  var $resolution = 1024;
  var $has_flags = false;
  var $annotation_cols = 60;
  var $annotation_rows = 32;
  var $relations_cols = 20;
  var $relations_rows = 5;
  var $popup_cols = 120;
  var $popup_rows = 35;
  var $tags_popup = 1;
  var $tags_popup_width = 500;
  var $tags_popup_height= 0;
  var $tags_popup_x = 100;
  var $tags_popup_y = 100;

function User(&$db, $id=0) {
   if (!$id && isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) ) {
      $id = $_SESSION['user_id'];
   }
   if ($id) {
       $query = "SELECT * FROM " . USER_TABLE . " WHERE user_id=" . $id;
       if ($result = $db->query($query)) {
          if($row = $db->fetch_assoc($result)) {
             foreach($row as $key => $val) {
                if(is_numeric($val)) {
                  $this->$key = $val + 0;
                } else {
                  $this->$key = $val;
                }
             }
          }
       }
       $query = "SELECT COUNT(*) AS flag_count FROM " . FLAGS_TABLE . "," . PERSON_TABLE
             . " WHERE " . FLAGS_TABLE.".".FLAGS_USER . "=" . $id
             . " AND " . FLAGS_TABLE.".".FLAGS_REC . "=" . PERSON_TABLE.".".PERSON_ID
             . " AND " . PERSON_TABLE.".".PERSON_DELETE_REC . "='0'";
       if ($result = $db->query($query)) {
          if($row = $db->fetch_assoc($result)) {
             if ($row['flag_count']) {
                $this->has_flags = true;
             }
          }
       }
    }
} // end constructor

} // end class User

?>
