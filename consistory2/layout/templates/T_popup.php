<?php
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">' . "\n";
echo '<head>';
#echo '<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />';
echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
echo '<title>' . $page_title . '</title>';

// style sheets
echo '<link rel="stylesheet" type="text/css" href="'. WEB_ROOT . DIR_CSS . 'popup.css" />';

// javascript

if (isset($supplemental_js_files[0]) and !empty($supplemental_js_files[0]))
{
    foreach ($supplemental_js_files as $filename)
    {
       echo "\n" . '<script type="text/javascript" src="' . WEB_ROOT . DIR_JS . $filename . '"></script>';
    }
}


// end HEAD, start BODY
echo '</head><body>';
echo $content;
echo '</body></html>';

if (DEBUG)
{
   include('debug.inc.php');
}
?>
