<?php
$sql['get_count_data_ikk'] = 
   "SELECT 
      count(ikkId) AS total
   FROM 
      finansi_pa_ref_ikk
   WHERE
	ikkNama LIKE '%s' AND 
   ikkKode LIKE '%s'
";

$sql['get_data_ikk'] = 
   "SELECT 
      ikkId as id,
      ikkKode as kode,
      ikkNama as nama
   FROM 
      finansi_pa_ref_ikk
   WHERE
      ikkNama LIKE '%s' AND 
      ikkKode LIKE '%s'
	LIMIT %s, %s
";
?>