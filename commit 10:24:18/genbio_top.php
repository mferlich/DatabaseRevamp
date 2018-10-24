<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

if(file_exists("./settings/remote_settings.php"))
{
  include("./settings/remote_settings.php");
}
elseif(file_exists($_SERVER['DOCUMENT_ROOT'] . "/local/local_settings.php"))
{
  include($_SERVER['DOCUMENT_ROOT'] . "/local/local_settings.php");
}

// Turns on debugging output
if (!defined('DEBUG')) {
   define('DEBUG', true);
}

// define paths and file names

if( $_SERVER['HTTP_HOST'] == '130.74.110.2' )
{
    define('WEB_ROOT', "http://" . $_SERVER['HTTP_HOST'] .'/' );
}
else
{

    define('WEB_ROOT', "http://" . $_SERVER['SERVER_NAME'] . SITE_PREFIX);
}

 define('DOC_ROOT', $_SERVER['DOCUMENT_ROOT'] . SITE_PREFIX);

define('DIR_IMAGES', "images/");
define('DIR_CLASSES', "classes/");
define('DIR_LAYOUT', "layout/");
define('DIR_CSS', DIR_LAYOUT . 'css/');
define('DIR_TEMPLATES', DIR_LAYOUT . 'templates/');
define('DIR_JS', 'js/');
define('DIR_LIB', 'lib/');
define('DIR_LANG', 'lang/');

define('DEFAULT_LANG', 'en');
$lang = (isset($_SESSION['lang']) && !empty($_SESSION['lang']))
            ? $_SESSION['lang']
            : DEFAULT_LANG;

define('DATABASE_CLASS', 'cl_Database.php');
define('USER_CLASS', 'cl_User.php');
define('PERSON_CLASS', 'cl_Person.php');
define('VERSION_CLASS', 'cl_Version.php');

define('SEARCH_FORMS_LIB', 'search_forms.inc.php');

define('DEFAULT_TEMPLATE', 'T_default.php');
define('POPUP_TEMPLATE', 'T_popup.php');



define('MESSAGE_FILE', 'messages.php');
define('LOGIN_PAGE', 'login.php');
define('DETAIL_PAGE', 'person.php');
define('SEARCH_PAGE', 'search.php');
define('PREFERENCES_PAGE', 'prefs.php');
define('RESULTS_PAGE', 'search_results.php');
define('PROCESS_EDITS_PAGE', 'process_edits.php');
define('RECONCILE_PAGE', 'reconcile.php');
define('ERROR_PAGE', 'error.php');


// All files defined, we do any includes needed

include(DOC_ROOT. DIR_LANG . $lang . '/' . MESSAGE_FILE);

//  ERROR codes
define('SUCCESS', 1);
define('NO_DATA', 7);
define('ERR_NO_PK', 2); // if data sent to update has no primary key value
define('ERR_UPDATE_CONFLICT', 3); // record has been changed since data checked out
define('ERR_QUERY_FAILED', 4);
define('ERR_NO_LASTNAME', 5);
define('ERR_NO_FIRSTNAME', 5);
define('ERR_INVALID_DATE', 6); // unable to convert date string to timestamp

// Declare our TABLE constants

define('PERSON_TABLE', 'genbios');
define('USER_TABLE', 'users');
define('FLAGS_TABLE', 'flags');
define('VERSION_TABLE', 'versions');

// Column names
//Constants for col names *MUST* be set to the name of the actual field
// in the database.  Seems like bad abstraction, but permits higher level
// of abstraction later in the script.
define('FLAGS_REC', 'rec_id');
define('FLAGS_USER', 'user_id');

define('PERSON_ID', 'id');
define('PERSON_LASTNAME', 'lastname');
define('PERSON_FIRSTNAME', 'firstname');
define('PERSON_NICKNAME', 'nickname');
define('PERSON_GENDER', 'gender');
define('PERSON_OCCUPATION', 'occupation');
define('PERSON_SPOUSE', 'spouse');
define('PERSON_PARENTS', 'parents');
define('PERSON_CHILDREN', 'children');
define('PERSON_RELATIONS', 'relations');
define('PERSON_BIRTHDATE', 'birthdate');
define('PERSON_DEATHDATE', 'deathdate');
define('PERSON_ORIGIN', 'origin');
define('PERSON_RESIDENCE', 'residence');
define('PERSON_ANNOTATION', 'annotation');
define('PERSON_MODIFIED_DATE', 'modified_date');
define('PERSON_MODIFIED_BY', 'modified_by');
define('FLAG_REC', 'flagged');
define('PERSON_DELETE_REC', 'delete_rec');

// versioning
define('VERSION_ID', 'versionId');
define('RECORD_ID', 'recordId');


// class DB allows default to DEFAULT_TABLE and DEFAULT_KEY for
// apps where one table is used a lot
define('DEFAULT_TABLE', PERSON_TABLE);
define('DEFAULT_KEY', PERSON_ID);

// various text tokens we want to reuse and check against

define('LASTNAME_PROMPT', "Last Name");
define('FIRSTNAME_PROMPT', "First Name");
define('NICKNAME_PROMPT', "Nickname");

// other constants to make code easier to read

define('HIDE_NAV_ARROWS', -1);
define('CREATE_NEW_RECORD', 0);
define('SET_DELETE', 1);
define('UNSET_DELETE', 0);

// set up mnemonics for user levels, status and login
define('ACCESS_DENIED', 0);
define('READ_ONLY', 1);
define('EDIT', 2);
define('ADMIN', 3);

// define some default values for user-set values
define('DEFAULT_RECS_PER_PAGE', 50);


// we don't want any caching on any of these pages
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

// content - head
$page_title = "";
$supplemental_stylesheet = "";
$supplemental_js_files = array();
$supplemental_js_inline = "";
$supplemental_meta_tags = "";

// content - body
$nav_id = 0; // will be set to another value if to be used by nav links.
$message = "";
$content = "";

// start our session
session_save_path($_SERVER['DOCUMENT_ROOT'] . '/settings/phpsessions');
ini_set('session.gc_probability', 1);
session_start();

if (!session_id()) {
  $content = "<h1>Error.  Unable to start session.</h1><h2>Please contact the site adminstrator with this error message.</h2>";
  $page_title = "Error: Problem starting session";
  include(DOC_ROOT . DIR_TEMPLATES . DEFAULT_TEMPLATE);
  exit();
}


// check LOGIN - could be made more secure by checking IP and so on.
if (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
  $logged_in = TRUE;
} else {
  $logged_in = FALSE;
}

// if not logged in and not already requesting login page, send to login page.
if (isset($_SESSION['referer'])) { unset($_SESSION['referer']); }
if (    !$logged_in
     && !strpos($_SERVER['REQUEST_URI'], LOGIN_PAGE)
     && $_SERVER['REQUEST_URI'] != "/")
{
 header("Location: ". WEB_ROOT . LOGIN_PAGE);
 exit;
}

if(isset($_REQUEST['nofilter']) && $_REQUEST['nofilter'] && isset($_SESSION['filter'])) {
  unset($_SESSION['filter']);
}

// include our major classes and instantiate if needed

include(DOC_ROOT . DIR_CLASSES . DATABASE_CLASS);
$db = new DB;

include(DOC_ROOT . DIR_CLASSES . USER_CLASS);
$user = new User($db);

// has user toggled "show flagged" ?
// is there already flag filter?
//  If not, make sure default values are set.
if (isset($_GET['show_flagged'])) {
   if ($_GET['show_flagged'] && !$user->has_flags) {
      $_GET['show_flagged'] = 0;
   }
   $_SESSION['show_flagged'] = $_GET['show_flagged'];
}
if (!isset($_SESSION['show_flagged'])) {
   $_SESSION['show_flagged'] = 0;
}
if (!$_SESSION['show_flagged'] || !isset($_SESSION['flag_filter'])) {
   $_SESSION['flag_filter'] = "";
}

// we want only each search box to get its own id
$_SESSION['search_box_num'] = 0;
?>
