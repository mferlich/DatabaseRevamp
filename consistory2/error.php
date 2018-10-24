<?php
$id_string = (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) ? "?id=" . $_REQUEST['id'] : "";
echo "<html><body><h1>ERROR</h1><h2>Problem updating database</h2>"
   . "<p>Go back to <a href=\"person.php" . $id_string . "\">previous record</a> and try again.</p>"
   . "<h2>DATA DUMP</h2><pre>";
if (isset($_REQUEST)) {print_r($_REQUEST);}
echo "</pre></body></html>";
