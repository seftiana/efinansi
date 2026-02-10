<?php
/**
 * SQL-File
 */
$sql['get_gtfw_application']  = "
SELECT
   ApplicationId AS `id`,
   ApplicationName AS `name`
FROM
   gtfw_application
ORDER BY ApplicationId
";

$sql['get_registered_module'] = "
SELECT
   SQL_CALC_FOUND_ROWS `ModuleId`,
   `Module`,
   `LabelModule`,
   `SubModule`,
   `Action`,
   `Type`,
   `Description`,
   `Access`,
   `Show`,
   `IconPath`,
   `ApplicationId`
FROM
   `gtfw_module`
WHERE 1 = 1
   AND ApplicationId = %s
   AND (LOWER(Module) = '%s' OR 1 = %s)
   AND (SubModule = '%s' OR 1 = %s)
   AND (LOWER(`Action`) = %s OR 1 = %s)
   AND (LOWER(`Type`) = %s OR 1 = %s)
ORDER BY Module ASC, `Action` DESC
";

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['do_delete_gtfw_module']    = "
DELETE
FROM gtfw_module
WHERE ModuleId = '%s'
";

$sql['do_insert_gtfw_module']    = "
INSERT INTO gtfw_module
SET `Module` = '%s',
   `LabelModule` = '%s',
   `SubModule` = '%s',
   `Action` = '%s',
   `Type` = '%s',
   `Description` = '%s',
   `Access` = '%s',
   `ApplicationId` = '%s'
";

$sql['get_menu_query']     = "
SELECT
   CONCAT('INSERT IGNORE INTO gtfw_module ( `Module`, `LabelModule`, `SubModule`, `Action`, `Type`, `Description`, `Access`, `Show`, `IconPath`, `ApplicationId` ) VALUE(\'', `Module`, '\',\'', `LabelModule`, '\',\'', `SubModule`, '\',\'', `Action`, '\',\'', `Type`, '\',\'', IFNULL(`Description`,''), '\',\'', `Access`, '\',\'', `Show`, '\',\'', IFNULL(`IconPath`,''), '\',\'', `ApplicationId`,'\') ON DUPLICATE KEY UPDATE Module = VALUES(Module), SubModule = VALUES(SubModule), LabelModule = VALUES(LabelModule), `Action` = VALUES(`Action`), `Type` = VALUES(`Type`), ApplicationId = VALUES(ApplicationId);') AS module
FROM gtfw_module
WHERE 1 = 1
AND (Module = '%s' OR 1 = %s)
ORDER BY Module, SubModule
";
?>