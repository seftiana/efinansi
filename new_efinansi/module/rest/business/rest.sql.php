<?php
/**
 * @package SQL-FILE
 */
$sql['rest_service']    = "
SELECT 
   ApplicationAddress AS `address`, 
   ApplicationId AS id, 
   ApplicationName AS `name`, 
   ApplicationServiceAddress AS `servicePath`, 
   MD5(CONCAT(ApplicationId, '.', ApplicationAddress)) AS `token`, 
   ApplicationStatusAktif AS `status`
FROM gtfw_application 
WHERE 1 = 1 
AND ApplicationId = '%s' 
LIMIT 0, 1
";

$sql['get_token']    = "
SELECT
   settingName,
   settingValue AS `token`
FROM setting 
WHERE settingName = 'token_%s'
LIMIT 0, 1
";
?>