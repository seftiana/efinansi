<?php

$sql['get_count_data'] = "
SELECT FOUND_ROWS() AS total
";

$sql['get_data'] = "
SELECT
   SQL_CALC_FOUND_ROWS
  `rkaklOutputId` AS id,
  `rkaklOutputKode` AS kode,
  `rkaklOutputNama` AS nama
FROM 
 `finansi_ref_rkakl_output`
WHERE 
   `rkaklOutputKode` LIKE '%s'
   AND
   `rkaklOutputNama` LIKE '%s'
LIMIT %s,%s   
";
?>