<?php
include('genbio_top.php');  // exposes the DB class as $db and declares constants
include(DOC_ROOT . DIR_LIB . SEARCH_FORMS_LIB);

$page_title = "Search Genevan Biographies";


if(isset($_REQUEST['found']) && empty($_REQUEST['found'])) {
   $page_headline = '<h1>No results returned.  Modify your search and try again</h1>';
} else {
  $page_headline = '<h1>Geneva Consistory Search Form</h1>';
}

$wizard_form ='<h2 id="wizard-mode">Wizard Mode</h2><p class="center"><a href="#sql-mode">Go to SQL Mode</a></p><p><strong>NOTE BENE</strong>:  Please be aware of the <em>operator precedence</em> for the AND/OR operators.  <strong>All</strong> AND clauses will be evaluated before <strong>any</strong> OR clauses.  An example:</p><ul><li><strong>Lastname=<em>Favre</em> OR Lastname=<em>Blanc</em> AND Firstname=<em>Jean</em>.</strong>  This will not give those Favre named Jean and those Blanc named Jean.  Rather, it will yield all Favre plus those Blanc named Jean.  To obtain the desired result, you must select <strong>Lastname=<em>Favre</em> AND Firstname=<em>Jean</em> OR Lastname=<em>Blanc</em> AND Firstname=<em>Jean</em>.</strong></li></ul><p>If you need to use complex queries, you can build them in the Advanced Search window below.</p>';

$wizard_form .= form_fullsearch($db);

$sql_form = '<p>&nbsp;</p><h2 id="sql-mode">SQL Mode</h2><p class="center"><a href="#wizard-mode">Go to Wizard Mode</a></p><p>This box allows complex queries. See below for <a href="#sql-help">help on SQL mode</a>.</p>';

$sql_form .= form_advanced_search();

$sql_help ='<p>&nbsp;</p>
<ul id="sql-help"><li>Searches are not case sensitive, though field names are.  Field names must be exactly as written in the drop-down boxes in <a href="#wizard-mode">Wizard Mode</a></li><li>For <strong>numeric searches</strong> use operators =, &gt;, &lt;, &gt;=, &lt;=, <>, != (the latter two both meaning not equal)</li><li><strong>All search <em>strings</em> (non-numeric searchs) must be enclosed in single quotes</strong>. Any occurrence of single quotes or hash marks within a search string must be <em>escaped</em> with a backslash like so: \\\' or \#.  Failure to do so will cause the query to fail.</li><li><strong>Use wildcards with LIKE and NOT LIKE to match string patterns</strong>.  For more information, see the section on <a href="http://www.mysql.com/doc/en/Pattern_matching.html">Pattern Matching</a> in the MySQL Manual.  Note that only the part after the WHERE should be entered in this box.
<ul><li><strong>%</strong> matches zero or more characters at the end or beginning of a search string. Will not work in the middle of a string.
<ul><li><strong>lastname LIKE \'fav%\'</strong> will match <em>Fav</em>, <em>Favre</em> and <em>Favel</em>, as well as <em>Favre (dit Dorbaz)</em>, etc.</li><li><strong>lastname like \'%fav%\'</strong> will match <em>de Favre</em>, <em>Orfavod</em>, etc.</li><li><strong>lastname NOT LIKE \'%fav%\'</strong> will exclude any string that includes <em>fav</em> anywhere in it.</li><li><strong>lastname like \'f%e\'</strong> will fail.  The % sign can only be place at the beginning or end of the search string.</li></ul></li><li><strong>_  (underscore)</strong> matches a single character.
<ul><li><strong>lastname like \'blond__\'</strong> will match <em>Blondel</em> and <em>Blondin</em> but not <em>Blond</em>.</li><li><strong>lastname like \'bl_nd__\'</strong> will match <em>Blondel</em>, <em>Blondin</em> and <em>Blandin</em>.</li></ul></li></ul></li><li>Parentheses may be used for complex queries.
<ul><li><strong>(lastname like \'favre\' or lastname like \'blondel\') and (firstname like \'jean\' or firstname like \'pierre\')</strong> will match all Favre named either Jean or Pierre and all Blondel named either Jean or Pierre. </li>
<li><strong>((lastname like \'favre\' or lastname like \'blondel\') and (firstname like \'jean\' or firstname like \'pierre\')) or (lastname like \'ameaux\' and firstname like \'claude\')</strong> will match as in the previous example, plus all Ameaux named Claude.</li></ul></li>
<li>To <strong>SORT</strong> results, use an <strong>ORDER BY</strong> clause followed by a comma-separated list of field names.
<ul><li><strong>ORDER BY lastname, firstname</strong> will sort results in alphabetical order first by last name, then by first name.</li>
<li><strong>ORDER BY lastname DESC, firstname</strong> will sort results first by last name in <em>reverse</em> alphabetical order, then by first name in alphabetical order.</li></ul></li></ul>';

$content = $page_headline . $wizard_form . $sql_form . $sql_help;

include(DOC_ROOT . DIR_TEMPLATES . DEFAULT_TEMPLATE);
