<?php
//Constants that can be echoed anywhere.

$msg_missing_name = "<div class=\"user-error\">"
                  . "<h2>Error: records must include first and last names</h2>"
                  . "<p>You may either add the required names (use an &quot;X&quot; when the name is unknown or this is not applicable)"
                  . " or you may <a href=\"" . DETAIL_PAGE . "?id=1\">cancel this addition</a>.</p></div>";
define('MSG_MISSING_NAME', $msg_missing_name);
?>
