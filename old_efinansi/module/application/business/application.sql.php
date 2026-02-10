<?php

$sql['get_setting_value'] = "
SELECT
	`settingValue` AS `value`
FROM `setting`
WHERE
	`settingName` = '%s'
";

$sql['get_application_service_address']="
SELECT
  `ApplicationServiceAddress` AS url_address
FROM 
	`gtfw_application`
WHERE 
	`ApplicationStatusAktif` = 'Y'
	AND 
	`ApplicationOwner` = '%s'
	AND 
	`ApplicationId` ='%s'
";

?>