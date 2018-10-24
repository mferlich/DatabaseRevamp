<?php

include('genbio_top.php');  // exposes the DB and user classes and declares constants

$content = '
<h1>Geneva Consistory Project Home</h1>
<p>If you want acces to the Geneva Consistory Biographical Database, you must apply for access.  Please contact</p>
<ul>
  <li>Isabella Watt by email: "watt at wisc dot edu"</li>
  <li>Tom Lambert by email: "tlambert at wisc dot edu"</li>
</ul>
<p>Obviously, you need to replace the "at" with @ and the "dot" with "."</p>

<p>If you already have been approved, please go to the <a href="http://' . $_SERVER['HTTP_HOST'] . SITE_PREFIX . 'login.php">login form</a> to enter the database.</p>
';

//include($_SERVER['DOCUMENT_ROOT'] . '/layout/templates/T_default.php');
// Changed by IDJ on 060802
include(DOC_ROOT . DIR_TEMPLATES . DEFAULT_TEMPLATE);

?>
