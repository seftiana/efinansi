<?php
   $sql['check_triggers'] ="
      SELECT SUM(IF(`DEFINER`!='root@localhost',0,1)) DEFINER FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE() GROUP BY TRIGGER_SCHEMA
   ";

   $sql['get_data_module'] = "
      SELECT
         `ModuleId`,
         `Module`,
         `SubModule`,
         `Action`,
         `Type`,
         `Access`
      FROM `gtfw_module`
   ";

   $sql['check_count_referensi'] = "
      SELECT
         CONCAT(\"SELECT COUNT(*) AS total, '\",table_name,\"' AS nama_table FROM \",table_name,\" \") AS DYN_QUERY
      FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name LIKE '%ref%';

   ";
?>