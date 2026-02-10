<?php
//===GET===

$sql['get_data_group'] = 
   "SELECT 
      GroupId AS id,
      GroupName AS name,
      Description AS group_description
   FROM 
      gtfw_group
   WHERE
      GroupName like '%s' 
   /*AND 
      GroupId != '2'*/
   ORDER BY 
      GroupName";
   
$sql['get_data_group_with_privilege_old'] = 
   "SELECT 
      g.GroupId AS group_id,
      g.GroupName AS group_name,
      g.Description AS group_description,
      `MenuId`  AS `menu_id`,
      `MenuName` AS `menu_name`,
      `ParentMenuId` AS `parent_menu`
   FROM 
      gtfw_group g
      LEFT JOIN gtfw_group_menu gm ON g.GroupId=gm.GroupId
   WHERE
      GroupName like '%s' /*AND g.GroupId>2*/
   ORDER BY GroupName, MenuName";

// added privileges for query builder
$sql['get_data_group_with_privilege'] = "
SELECT 
      g.GroupId AS group_id,
      g.GroupName AS group_name,
      g.Description AS group_description,
      gm.`MenuName` AS `menu_name`,
      gm.subMenuName AS sub_menu
   FROM 
      gtfw_group g
      LEFT JOIN
      (
      	SELECT
      		a.GroupId,
      		a.MenuName,
      		b.MenuName AS subMenuName,
            'menu' AS asal
      	FROM gtfw_group_menu a 
      	LEFT JOIN gtfw_group_menu b ON a.MenuId = b.ParentMenuId
      	WHERE a.ParentMenuId = 0
         UNION
         SELECT
            menu_group_id,
            parent.dummy_menu,
            child.dummy_menu,
            'report'
         FROM
            report_menu
            JOIN report_dummy_menu AS child ON child.dummy_id = dummy_dummy_id
            JOIN report_dummy_menu AS parent ON parent.dummy_id = child.dummy_parent_menu_id
      	ORDER BY asal, MenuName, subMenuName
      ) gm ON g.GroupId=gm.GroupId
   WHERE
      GroupName like '%s'
   ORDER BY GroupName, asal, MenuName
";

$sql['get_data_group_by_id'] = 
   "SELECT 
      g.GroupId AS group_id,
      g.GroupName AS group_name,
      g.Description AS group_description,
      gm.`MenuName` AS `menu_name`,
      gm.subMenuName AS sub_menu
   FROM 
      gtfw_group g
      LEFT JOIN
      (
      	SELECT
      		a.GroupId,
      		a.MenuName,
      		b.MenuName AS subMenuName,
            'menu' AS asal
      	FROM gtfw_group_menu a 
      	LEFT JOIN gtfw_group_menu b ON a.MenuId = b.ParentMenuId
      	WHERE a.ParentMenuId = 0
         UNION
         SELECT
            menu_group_id,
            parent.dummy_menu,
            child.dummy_menu,
            'report'
         FROM
            report_menu
            JOIN report_dummy_menu AS child ON child.dummy_id = dummy_dummy_id
            JOIN report_dummy_menu AS parent ON parent.dummy_id = child.dummy_parent_menu_id
      	ORDER BY asal, MenuName, subMenuName
      ) gm ON g.GroupId=gm.GroupId
   WHERE
      g.GroupId = '%s'
   ORDER BY GroupName, asal, MenuName";

$sql['get_data_group_by_id_old']= 
   "SELECT 	
	    g.GroupId AS group_id,
      g.GroupName AS group_name,
      g.Description AS group_description,
      GROUP_CONCAT(`MenuId` ORDER BY `MenuName` SEPARATOR '|') AS `menu_id`,
      GROUP_CONCAT(`MenuName` ORDER BY `MenuName` SEPARATOR '|') AS `menu_name`,
      GROUP_CONCAT(`ParentMenuId` ORDER BY `MenuName` SEPARATOR '|') AS `parent_menu`
   FROM 
    	gtfw_group g
    	LEFT JOIN gtfw_group_menu gm ON g.GroupId=gm.GroupId
   WHERE
	    g.GroupId = '%s'
   GROUP BY g.GroupId";

$sql['get_last_group_id'] = 
   "SELECT MAX(GroupId) 
      FROM gtfw_group;";

//===DO===
$sql['do_add_group'] = 
   "INSERT INTO gtfw_group 
      (GroupName, Description)
   VALUES
      ('%s','%s')";
 
$sql['do_update_group'] =
   "UPDATE gtfw_group
   SET 
      GroupName='%s',
      Description='%s'
   WHERE 
      GroupId='%s'";
   
$sql['do_delete_group'] = 
   "DELETE FROM gtfw_group
   WHERE GroupId = '%s'";

?>
