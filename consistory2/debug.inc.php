<?php
  echo "<h1 style=\"padding-top:350px; clear:both;\">Debugging information</h1><pre>\n\n<hr />\nSESSION VARS\n\n";
  if (isset($_SESSION)) {
      print_r($_SESSION);
  } else {
     echo "\nSESSION VARS not set\n\n";
  }
  echo "<hr />\n\nREQUEST VARS\n\n";
  if (isset($_REQUEST)) {
     print_r($_REQUEST);
  } else {
    echo "\nREQUEST VARS not set\n\n";
  }
  echo "<hr />\n\nUSER object\n\n";
  if(isset($user)) {
     print_r(get_object_vars($user));
  } else {
     echo "\n\nobject \$user not set\n\n";
  }
  echo "<hr />\n\nPERSON object\n\n";
  if(isset($person)) {
     print_r(get_object_vars($person));
  } else {
     echo "\n\nobject \$person not set\n\n";
  }
  echo "</pre>";

// clear error reporting variables
$_SESSION['debug'] = "";
$_SESSION['application_error'] = "";
$_SESSION['user_error'] = "";
$_SESSION['warning'] = "";
?>
