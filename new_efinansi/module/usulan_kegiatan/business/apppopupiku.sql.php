<?php
$sql['get_count_data_iku'] = 
   "SELECT 
      count(ikuId) AS total
   FROM 
      finansi_pa_ref_iku
   WHERE
	ikuNama LIKE '%s' AND 
   ikuKode LIKE '%s'
";

$sql['get_data_iku'] = 
   "SELECT 
      ikuId as id,
      ikuKode as kode,
      ikuNama as nama
   FROM 
      finansi_pa_ref_iku
   WHERE
      ikuNama LIKE '%s' AND 
      ikuKode LIKE '%s'
	LIMIT %s, %s
";
?>