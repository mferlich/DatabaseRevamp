<?php
if(file_exists("/home/jconsist/settings/remote_settings.php"))
{
  include("/home/jconsist/settings/remote_settings.php");
}
elseif(file_exists($_SERVER['DOCUMENT_ROOT'] . "/local/local_settings.php"))
{
  include($_SERVER['DOCUMENT_ROOT'] . "/local/local_settings.php");
}
include($_SERVER['DOCUMENT_ROOT'] . '/classes/cl_Database.php');  // exposes the DB class

$q = array();
$q[] = "DELETE FROM `version_count_all`";
$q[] = "DELETE FROM `version_count_filtered`";
$q[] = "INSERT INTO `version_count_filtered` (`recordId`, `oldVersionCount`, `mostRecentOld`)
   SELECT `recordId`, COUNT(`versionId`) as `oldVersionCount`, MAX(`modified_date`)
     FROM `versions`
     WHERE `modified_date` < DATE_SUB(CURDATE(), INTERVAL 30 DAY)  
     GROUP BY `recordId`";
$q[] = "INSERT INTO `version_count_all` (`recordId`, `allVersionCount`)
   SELECT `recordId`, COUNT(`versionId`) as `allVersionCount`
      FROM `versions`
      GROUP BY `recordID`";
$q[] = "UPDATE `version_count_filtered` vf, `version_count_all` va SET vf.allVersionCount = va.allVersionCount WHERE va.recordId LIKE vf.recordId";
$q[] = "DELETE v.* FROM versions v, version_count_filtered vf
WHERE ((vf.allVersionCount - vf.oldVersionCount) > 7) AND v.modified_date < vf.mostRecentOld AND v.recordId LIKE vf.recordId";

$db = new DB();

foreach ($q as $query) {
 if (!$db->query($query)) {
     exit;
 }
}



