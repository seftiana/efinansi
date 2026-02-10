<?php
$sql['nav'] = "
	select distinct
		dm.DmMenuId,
		gm.MenuName,
		gmod.Module,
		gmod.SubModule,
		gmod.Action,
		gmod.Type,
		CONCAT('&mid=',gm.MenuId, '&dmmid=',dm.DmMenuId) AS url,
		a.MenuName as subMenu,
		a.Module AS subMenuModule,
		a.SubModule AS subMenuSubModule,
		a.Action AS subMenuAction,
		a.Type AS subMenuType
	from
		gtfw_group_menu gm
	left join
		gtfw_module gmod on (gm.ModuleId = gmod.ModuleId)
	left join
		gtfw_group_module ggm on (gm.ModuleId = ggm.ModuleId)
	LEFT JOIN
		gtfw_user_def_group gdg
	ON gdg.GroupId = gm.GroupId
   LEFT JOIN
      gtfw_user gu
   ON (gdg.UserId = gu.UserId)
	inner join
		dummy_menu dm on (dm.DmMenuName = gm.MenuName)
	LEFT JOIN
	(
	select
		distinct gm.ParentMenuId,
		gm.MenuName,
		gmod.Module,
		gmod.SubModule,
		gmod.Action,
		gmod.Type,
		DmMenuOrder
	from gtfw_group_menu gm
	left join gtfw_module gmod on (gm.ModuleId = gmod.ModuleId)
	left join gtfw_group_module ggm on (gm.ModuleId = ggm.ModuleId)
   inner join dummy_menu dm on (dm.DmMenuName = gm.MenuName)
	where IsShow='Yes'
   order by dm.DmMenuOrder ASC
	) a ON a.ParentMenuId = gm.MenuId
	where (gm.ParentMenuId = 0)
		and gu.userName = '%s'
		AND dm.DmIsShow = '%s'
	order by dm.DmMenuOrder, dm.DmMenuId, a.DmMenuOrder ASC
";

$sql['nav_report'] = "
SELECT DISTINCT
   CONCAT('report',menuParent.dummy_id) AS DmMenuId,
   menuParent.dummy_menu AS MenuName,
   moduleParent.Module,
   moduleParent.SubModule,
   moduleParent.Action,
   moduleParent.Type,
   CONCAT('&mid=report',menuParent.dummy_id,'&dmmid=report',menuParent.dummy_id) AS url,
   menuChild.dummy_id AS subMenuId,
   menuChild.dummy_menu AS subMenu,
   moduleChild.Module AS subMenuModule,
   moduleChild.SubModule AS subMenuSubModule,
   moduleChild.Action AS subMenuAction,
   moduleChild.Type AS subMenuType,
   layout_id
FROM
   report_dummy_menu AS menuParent
   JOIN gtfw_module AS moduleParent ON moduleParent.ModuleId = menuParent.dummy_module_id
   JOIN report_dummy_menu AS menuChild ON menuChild.dummy_parent_menu_id = menuParent.dummy_id
   JOIN gtfw_module AS moduleChild ON moduleChild.ModuleId = menuChild.dummy_module_id
   JOIN report_menu ON dummy_dummy_id = menuChild.dummy_id
   LEFT JOIN gtfw_user_def_group gdg ON menu_group_id = gdg.GroupId
   JOIN gtfw_user gu ON gu.UserId = gdg.UserId
   JOIN report_layout ON layout_dummy_id = menuChild.dummy_id
WHERE
   menuParent.dummy_parent_menu_id = 0 AND
   gu.UserName = '%s' AND
   menuParent.dummy_is_show = menuChild.dummy_is_show AND
   menuParent.dummy_is_show = '%s'
ORDER BY
   menuParent.dummy_order,
   menuChild.dummy_order
";

$sql['list_available_menu_with_flag_show'] =
"select distinct
   gm.MenuId,
   gm.MenuName,
   gmod.Module,
   gmod.SubModule,
   gmod.Action,
   gmod.Type,
   gmod.Description,
   gm.ParentMenuId,
   dm.DmMenuId
from
   gtfw_group_menu gm
left join
   gtfw_module gmod on (gm.ModuleId = gmod.ModuleId)
left join
   gtfw_group_module ggm on (gm.ModuleId = ggm.ModuleId)
LEFT JOIN
		gtfw_user_def_group gdg
	ON gdg.GroupId = gm.GroupId
   LEFT JOIN
   gtfw_user gu
   ON (gdg.UserId = gu.UserId)
inner join
   dummy_menu dm on (dm.DmMenuName = gm.MenuName)
where (gm.ParentMenuId = 0)
   and gu.userName = '%s'
   AND dm.DmIsShow = '%s'
order by dm.DmMenuOrder
";

$sql['list_available_menu'] =
"select distinct
   gm.MenuId,
   gm.MenuName,
   gmod.Module,
   gmod.SubModule,
   gmod.Action,
   gmod.Type,
   gmod.Description,
   gm.ParentMenuId
from
   gtfw_group_menu gm
left join
   gtfw_module gmod on (gm.ModuleId = gmod.ModuleId)
left join
   gtfw_group_module ggm on (gm.ModuleId = ggm.ModuleId)
LEFT JOIN
		gtfw_user_def_group gdg
	ON gdg.GroupId = gm.GroupId
   LEFT JOIN
   gtfw_user gu
   ON (gdg.UserId = gu.UserId)
inner join
   dummy_menu dm on (dm.DmMenuName = gm.MenuName)
where (gm.ParentMenuId = 0) and gu.UserName= '%s'
order by dm.DmMenuOrder
";
// where (gm.ParentMenuId = 0) and gm.GroupId= '%s'";

$sql['list_available_menu_report'] = "
SELECT DISTINCT
   CONCAT('report',menuParent.dummy_id) AS MenuId,
   menuParent.dummy_menu AS MenuName,
   moduleParent.Module,
   moduleParent.SubModule,
   moduleParent.Action,
   moduleParent.Type,
   moduleParent.Description,
   menuParent.dummy_menu AS ParentMenuId
FROM
   report_dummy_menu AS menuParent
   JOIN gtfw_module AS moduleParent ON moduleParent.ModuleId = menuParent.dummy_module_id
   JOIN report_dummy_menu AS menuChild ON menuChild.dummy_parent_menu_id = menuParent.dummy_id
   JOIN report_menu ON dummy_dummy_id = menuChild.dummy_id
   JOIN gtfw_user ON GroupId = menu_group_id
WHERE
   menuParent.dummy_parent_menu_id = 0 AND
   gtfw_user.UserName = '%s'
ORDER BY
   menuParent.dummy_order
";

$sql['list_all_available_submenu_for_group'] =
"select distinct
   gm.MenuId,
   gm.MenuName,
   gmod.Module,
   gmod.SubModule,
   gmod.Action,
   gmod.Type,
   gmod.Description,
   gm.ParentMenuId,
   dm.DmIconPath,
   dm.DmMenuId
from
   gtfw_group_menu gm
left join
   gtfw_module gmod on (gm.ModuleId = gmod.ModuleId)
left join
   gtfw_group_module ggm on (gm.ModuleId = ggm.ModuleId)
LEFT JOIN
		gtfw_user_def_group gdg
	ON gdg.GroupId = gm.GroupId
   LEFT JOIN
   gtfw_user gu
   ON (gdg.UserId = gu.UserId)
left join
   dummy_menu dm on (dm.DmMenuName = gm.MenuName)
where gu.UserName= '%s' and dm.DmMenuParentId = '%s' AND dm.DmIsShow='Yes'
order by dm.DmMenuOrder, MenuName ASC

";

$sql['list_all_available_submenu_for_group_report'] = "
SELECT DISTINCT
   CONCAT('report',menuParent.dummy_id) AS MenuId,
   menuParent.dummy_menu AS MenuName,
   moduleParent.Module,
   moduleParent.SubModule,
   moduleParent.Action,
   moduleParent.Type,
   moduleParent.Description,
   menuParent.dummy_parent_menu_id AS ParentMenuId,
   menuParent.dummy_icon_path AS DmIconPath,
   CONCAT('report',menuParent.dummy_id) AS DmMenuId,
   CONCAT('report',layout_id) AS LayId
FROM
   report_dummy_menu AS menuParent
   JOIN gtfw_module AS moduleParent ON moduleParent.ModuleId = menuParent.dummy_module_id
   JOIN report_layout ON layout_dummy_id = dummy_id
   JOIN report_menu ON dummy_dummy_id = menuParent.dummy_id
   JOIN gtfw_user ON GroupId = menu_group_id
WHERE
   IFNULL(gtfw_user.UserName,'nobody') LIKE '%s' AND
   CONCAT('report',menuParent.dummy_parent_menu_id) = '%s'
ORDER BY
   menuParent.dummy_order,
   menuParent.dummy_menu
";

$sql['list_available_submenu'] =
"select distinct
   gm.MenuId,
   gm.MenuName,
   gmod.Module,
   gmod.SubModule,
   gmod.Action,
   gmod.Type,
   gmod.Description
from
   gtfw_group_menu gm
left join
   gtfw_module gmod on (gm.ModuleId = gmod.ModuleId)
left join
   gtfw_group_module ggm on (gm.ModuleId = ggm.ModuleId)
where (gm.ParentMenuId = '%s')
order by gm.MenuName ASC
";

$sql['list_available_submenu_report'] = "
SELECT DISTINCT
   CONCAT('report',menuParent.dummy_id) AS MenuId,
   menuParent.dummy_menu AS MenuName,
   moduleParent.Module,
   moduleParent.SubModule,
   moduleParent.Action,
   moduleParent.Type,
   moduleParent.Description
FROM
   report_dummy_menu AS menuParent
   JOIN gtfw_module AS moduleParent ON moduleParent.ModuleId = menuParent.dummy_module_id
   JOIN report_menu ON dummy_dummy_id = menuParent.dummy_id
   JOIN gtfw_user ON GroupId = menu_group_id
WHERE
   CONCAT('report',menuParent.dummy_parent_menu_id) = '%s'
ORDER BY
   menuParent.dummy_menu
";

$sql['list_available_submenu_with_flag_show'] =
"select distinct
   gm.MenuId,
   gm.MenuName,
   gmod.Module,
   gmod.SubModule,
   gmod.Action,
   gmod.Type,
   gmod.Description,
   gm.ParentMenuId
from
   gtfw_group_menu gm
left join
   gtfw_module gmod on (gm.ModuleId = gmod.ModuleId)
left join
   gtfw_group_module ggm on (gm.ModuleId = ggm.ModuleId)
where gm.ParentMenuId = '%s'
AND IsShow='%s'
order by gm.MenuOrder ASC
";

$sql['list_available_submenu_with_flag_show_report'] = "
SELECT DISTINCT
   CONCAT('report',menuParent.dummy_id) AS MenuId,
   menuParent.dummy_menu AS MenuName,
   moduleParent.Module,
   moduleParent.SubModule,
   moduleParent.Action,
   moduleParent.Type,
   moduleParent.Description,
   menuParent.dummy_parent_menu_id AS ParentMenuId
FROM
   report_dummy_menu AS menuParent
   JOIN gtfw_module AS moduleParent ON moduleParent.ModuleId = menuParent.dummy_module_id
   JOIN report_menu ON dummy_dummy_id = menuParent.dummy_id
   JOIN gtfw_user ON GroupId = menu_group_id
WHERE
   CONCAT('report',menuParent.dummy_parent_menu_id) = '%s' AND
   gtfw_user.UserName = '%s'
ORDER BY
   menuParent.dummy_menu
";

?>