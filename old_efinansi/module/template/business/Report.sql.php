<?php

$sql['get_query'] ="
   SELECT 
      *
   FROM
      report_query
   ORDER BY 
      query_nama";

$sql['get_query_by_id'] ="
   SELECT 
      *
   FROM
      report_query
   WHERE
      query_id=%d";
      
$sql['show_tables'] ="
   SHOW TABLES";

$sql['show_colums_tables'] ="
   DESCRIBE %s";

//table
$sql['get_table'] ="
   SELECT 
      *
   FROM
      report_table";

$sql['get_table_by_id'] ="
   SELECT 
      *
   FROM
      report_table
   WHERE
      table_id=%d";
      
//===

$sql['get_layout'] ="
   SELECT 
      a.*, b.group_menu_name, c.table_nama, d.group_name
   FROM
      report_layout a
      LEFT JOIN group_menu_trans b ON a.layout_sub_menu=b.group_menu_id
      LEFT JOIN report_table c ON a.layout_template=c.table_id
      LEFT JOIN group_mst d ON b.group_group_id=d.group_id";

$sql['get_layout_by_id'] ="
   SELECT 
      *
   FROM
      report_layout a
	   left join report_table b ON a.layout_template=b.table_id
   WHERE
      layout_id=%d";
      
$sql['get_bridge_by_id'] ="
   SELECT 
      *
   FROM
      report_bridge
   WHERE
      bridge_layout_id=%d";

$sql['get_sub_menu'] ="
   select 
      group_menu_id, group_menu_name,group_id, group_name
   from 
      group_menu_trans a
	left join group_mst b ON a.group_group_id=b.group_id
   where 
      group_module_id=11  
   order by 
      group_menu_name";

$sql['get_graphic_by_id_layout'] ="
   SELECT 
      a.*, b.table_nama, c.layout_nama, b.table_param
   FROM
      report_graphic a
      left join report_table b ON a.graphic_table_id=b.table_id
      left join report_layout c ON a.graphic_layout_id=c.layout_id
   WHERE
      graphic_layout_id=%d";

$sql['get_param_by_id'] ="
   SELECT 
      *
   FROM
      report_param
   WHERE
      param_id=%d";

?>
