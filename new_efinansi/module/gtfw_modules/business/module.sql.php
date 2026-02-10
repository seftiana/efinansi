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

$sql['clean_dummy_module'] = "
DELETE FROM dummy_module WHERE ModuleId IN(
   SELECT ModuleId FROM gtfw_module WHERE Module NOT IN('user','report')
)
";

$sql['insert_dummy_module']   = "
INSERT IGNORE INTO dummy_module(DmMenuId,ModuleId)
SELECT DmMenuId, b.ModuleId FROM dummy_menu
LEFT JOIN gtfw_module a ON DmMenuDefaultModuleId = a.moduleId
LEFT JOIN gtfw_module b ON b.Module = a.Module
WHERE DmMenuParentId <>0
AND a.Module NOT IN('user','report')
AND a.Access <> 'All'
ORDER BY a.Module
";

$sql['clean_gtfw_group_module']  = "
TRUNCATE gtfw_group_module
";

$sql['insert_gtfw_group_module'] = "
INSERT IGNORE INTO `gtfw_group_module`
            (`GroupId`,
             `ModuleId`)
SELECT GroupId, dummy_module.ModuleId
FROM gtfw_group
JOIN gtfw_group_menu USING (GroupId)
LEFT JOIN dummy_menu ON MenuName = dmMenuName
LEFT JOIN dummy_module USING (dmMenuId)
WHERE dummy_module.ModuleId IS NOT NULL
ORDER BY groupId, dummy_module.ModuleId
";
?>