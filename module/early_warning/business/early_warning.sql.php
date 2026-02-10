<?php
   $sql['get_query_early_warning'] ="
      SELECT
         `query_nama`,
         `query_desc`,
         `query_sql`
      FROM `report_query`
      WHERE `query_rqjenisid` = '2'
   ";
?>