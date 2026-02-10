<?php

//GET
$sql['is_can_access_menu'] = "
SELECT
   count(*) as result
FROM
   gtfw_group_menu m
WHERE 
   MenuName='%s'
   AND groupId='%s'";
   
$sql['get_all_privilege'] = 
   " SELECT 
  	  dmMenuId AS menu_id,
  	  dmMenuName AS menu_name,
  	  dmMenuParentId AS menu_parent_id,
  	  dmMenuDefaultModuleId as default_module_id,
	  dmIsShow as is_show,
	  MenuName
   FROM 
    	dummy_menu
   LEFT JOIN(
SELECT 	
      MenuName
   FROM 
    	gtfw_group g
    	LEFT JOIN gtfw_group_menu gm ON g.GroupId=gm.GroupId
   WHERE
	    g.GroupId = '%s'
) a ON dmMenuName= MenuName
    WHERE DmIsShow = 'Yes'
   ORDER BY DmMenuParentId, DmMenuOrder";

$sql['get_group_privilege'] = 
   "SELECT 
      MenuId AS menu_id,
      MenuName AS menu_name,
      ParentMenuId AS parent_menu_id,  
      ModuleId AS module_id
   FROM 
      gtfw_group g
      JOIN gtfw_group_menu gm ON g.groupId=gm.groupId
   WHERE 
      parentMenuId != 0 AND
      gm.groupId = '%s'";

$sql['get_privilege_by_id'] = 
    "SELECT 
      dmMenuId AS menu_id,
      dmMenuName AS menu_name,
      dmMenuParentId AS menu_parent_id,
      dmMenuDefaultModuleId as default_module_id,
      dmIsShow as is_show 
   FROM 
      dummy_menu
   WHERE 
      dmMenuId = '%s'";

$sql['get_privilege_by_array_id'] = 
    "SELECT 
      dmMenuId AS menu_id,
      dmMenuName AS menu_name,
      dmMenuParentId AS menu_parent_id,
      dmMenuDefaultModuleId as default_module_id,
      dmIsShow as is_show
   FROM 
      dummy_menu
   WHERE 
      dmMenuId IN (%s)";

$sql['get_max_menu_id']= "
   SELECT 
      MAX(MenuId) as max_id
   FROM
      gtfw_group_menu
   ";

$sql['get_data_group_menu'] = 
   "SELECT 
      MenuId AS menu_id,
      MenuName AS menu_name, 
      GroupId AS group_id 
	FROM 
      gtfw_group_menu
  WHERE 
     parentMenuId != 0";  
     
$sql['get_data_group_menu_by_group_id'] = 
   "SELECT 
      MenuId AS menu_id,
      MenuName AS menu_name, 
      GroupId AS group_id 
	FROM 
      gtfw_group_menu
   WHERE 
     ParentMenuId != 0 and GroupId='%s'";    

//DO

$sql['do_add_privilege'] = 
   "INSERT INTO gtfw_user
      (UserName, Password, RealName, Decription, Active, GroupId)
   VALUES 
      ('%s', md5('%s'), '%s', '%s', 'Yes', '%s')";
      
$sql['do_add_group_menu_for_new_group'] = 
   "INSERT INTO gtfw_group_menu
      (MenuName, GroupId, ModuleId, ParentMenuId, IsShow)
   SELECT 
      '%s', MAX(GroupId), '%s', '%s', '%s'
      FROM gtfw_group";
      
$sql['do_add_group_menu'] = 
   "INSERT INTO gtfw_group_menu
      (MenuName, GroupId, ModuleId, ParentMenuId, IsShow)
   VALUES 
      ('%s', '%s', '%s', '%s', '%s')";
      
$sql['do_add_group_module_by_module_name_new_group'] = "
   INSERT INTO 
      gtfw_group_module
   SELECT MAX(g.GroupId), m.ModuleId
      FROM gtfw_module m, gtfw_group g
      WHERE module='%s'
      GROUP BY ModuleId ";
      
$sql['do_add_group_module_by_module_name'] = "
   INSERT INTO 
      gtfw_group_module
   SELECT %d, m.ModuleId
      FROM gtfw_module m
      WHERE module='%s'
      GROUP BY ModuleId ";
      
$sql['do_add_group_module_from_dummy_menu_new_group'] = "
   INSERT INTO 
      gtfw_group_module
   SELECT MAX(g.GroupId), m.ModuleId
      FROM gtfw_module m JOIN dummy_module b ON m.moduleId = b.moduleId , gtfw_group g
      WHERE DmMenuId='%s'
      GROUP BY m.ModuleId ";
      
$sql['do_add_group_module_from_dummy_menu'] = "
   INSERT INTO 
      gtfw_group_module
   SELECT '%s', b.ModuleId FROM gtfw_module a
      JOIN dummy_module b ON a.moduleId = b.moduleId
      WHERE b.DmMenuId='%s' ";

$sql['do_add_group_module'] =
   "INSERT INTO gtfw_group_module
      (GroupId, ModuleId)
   VALUES
      ('%s', '%s')";
      
$sql['do_add_group_module_newgroup'] =
   "INSERT INTO gtfw_group_module
      (GroupId, ModuleId)
   SELECT MAX(GroupId), '%s'
      FROM gtfw_group";
      
         
$sql['do_delete_group_menu'] =
   "DELETE 
   FROM 
      gtfw_group_menu
   WHERE 
      GroupId='%s'";

$sql['do_delete_group_module']  = 
   "DELETE 
   FROM 
      gtfw_group_module
   WHERE 
      GroupId='%s'";

// privileges query builder
$sql['get_all_privilege_report'] = "
SELECT
   child.dummy_id,
   child.dummy_menu,
   child.dummy_parent_menu_id,
   IFNULL(parent.dummy_order, child.dummy_order) AS parent_order,
   menu_group_id
FROM
   report_dummy_menu AS child
   LEFT JOIN report_dummy_menu AS parent ON child.dummy_parent_menu_id = parent.dummy_id
   LEFT JOIN report_menu ON dummy_dummy_id = child.dummy_id AND menu_group_id = %s
WHERE
   child.dummy_is_show = 'Yes'
ORDER BY
   parent_order,
   child.dummy_parent_menu_id,
   child.dummy_order
";

$sql['delete_all_privilege_report'] = "
DELETE FROM
   report_menu
WHERE
   menu_group_id = %s
";

$sql['add_privilege_report'] = "
INSERT INTO
   report_menu (menu_group_id, dummy_dummy_id)
VALUES
   (%s, %s)
";
?>
