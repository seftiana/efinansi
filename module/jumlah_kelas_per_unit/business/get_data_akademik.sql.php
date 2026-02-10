<?php

$sql['get_data_souce_address'] = "
SELECT
  `ApplicationName` AS app_name,
  `ApplicationAddress` AS app_address,
  `ApplicationStatusAktif` AS app_status,
  `ApplicationServiceAddress` AS app_service_address
FROM `gtfw_application`
WHERE ApplicationId = '%s'
AND `ApplicationStatusAktif` ='Y'
";
?>