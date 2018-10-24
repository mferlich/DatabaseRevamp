<?php
include_once('genbio_top.php');  // exposes the DB class and declares constants
$page_title = "Geneva Consistory Database Login";
$content = "<form method=\"post\" action=\"". LOGIN_PAGE ."\" class=\"login\">\n";
$head = "<h1>Geneva Consistory Database Login</h1>\n";
if(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], LOGIN_PAGE)) { // if coming from login and no username or pass
   $message = "<h2>You must enter a valid user name and password to use the database</h2>\n";
} else {
   $message = "<h2>Please log in to your account.</h2>\n";
}

$login_form = '<p><span class="labelcol">User ID: </span><span class="datacol"><input type="text" name="username" value="" /></span></p><p><span class="labelcol">Password: </span><span class="datacol"><input type="password" name="password" value="" /></span></p><p><input type="submit" value="Log In" /></p>';

//1-> if user has entered username and password, check for valid login
if(isset($_POST['username']) && $_POST['username'] && isset($_POST['password']) && $_POST['password']) {
   $username = $_POST['username'];
   $password = md5($_POST['password']);
//   echo "<br>". $password;  // IDJ###
   $login_query = "SELECT user_id, userlevel FROM users WHERE username='$username' and password='$password'";
//2-> is there a problem with the query?  Then stop
   if (!$login_result = $db->query($login_query)) {
      $page_title = "Login Failure: problem with account";
      $head = "<h1>Login Failure</h1>\n";
      $message = "<p>Failed to retrieve account information.  Please contact the database adminstrator.</p>\n";
      $login_form = "";

//2=> otherwise, start checking login info
   } else {

//3-> if user id and pass invalid, stop
      if (!$user_info = $db->fetch_assoc($login_result)) {
          $page_title = "Login Failure: invalid user name or password";
          $head = "<h1>Login Failure</h1>";
          $message = "<p>Failed to find either your username or your password or both.  Please check them and try again.  If you continue to have problems, please contact the database adminstrator.</p>\n";

//3=>otherwise, continue to check login info
      } else {
          $_SESSION['user_id'] = $user_info['user_id'];
          $user = new User($db);
//4-> if login is valid, but account is deactivated, stop
          if ($user->userlevel == ACCESS_DENIED) {
             $page_title = "Login Failure: inactive account";
             $head = "<h1>Login Failure</h1>";
             $message = "<p>Your account is listed as inactive.  To reinstate your privileges, please contact the database adminstrator.</p>\n";
             $login_form = "";

//4=> otherwise login is fully valid, let user in, update last login and go to page she wants
          } else {
             $db->query("UPDATE users SET last_login=now() WHERE user_id='". $user->user_id. "'");
	     
	     // IDJ 060802
	     if(DEBUG)
	     {
	     	// IDJ###
		echo "webroot=". WEB_ROOT;
		echo "referer=". $_SESSION['referer'];
	     }
	     
             if (isset($_SESSION['referer']) && $_SESSION['referer'] != WEB_ROOT) {
                $redirect = "Location: " . $_SESSION['referer'];
             } else {
                   // $user->start_page is defined as a constant.  It must be evaluated to get the exact URL
                eval('$start_page = '. $user->start_page .';');
                $redirect = "Location: ". WEB_ROOT . $start_page;
             }
	     //echo "<br>" . $redirect; // IDJ###
             header($redirect);
          } //4*
       } //3*
    } //2*
} // 1*

$browser_message = '<p class="clear">Please note: These pages have been primarily designed with the main <a href="http://www.mozilla.org">Mozilla</a> browser or
Mozilla <a href="http://www.mozilla.org/products/firefox/">Firefox</a> in mind
as these browsers most carefully observe
<a href="http://www.webstandards.org/about/">currently accepted standards</a>.
These pages have also been tested and determined to work adequately in
<a href="http://microsoft.com/ie/">Microsoft Internet Explorer 6.0</a> for Windows.
They currently function properly but look a little sloppy <a href="http://www.opera.com">Opera 6</a>
for Windows.  They have not been tested in any other browsers or operating systems.</p>';

$content .= $head . $message . $login_form . $browser_message . '</form>';

include(DOC_ROOT . DIR_TEMPLATES . DEFAULT_TEMPLATE);
