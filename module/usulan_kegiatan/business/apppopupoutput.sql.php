<?php
$sql['get_count_data_output'] = 
   "SELECT 
      count(rkaklOutputId) AS total
   FROM 
      finansi_ref_rkakl_output
   WHERE
	rkaklOutputNama LIKE '%s' AND 
   rkaklOutputKode LIKE '%s'
";

$sql['get_data_output'] = 
   "SELECT 
      rkaklOutputId as id,
      rkaklOutputKode as kode,
      rkaklOutputNama as nama
   FROM 
      finansi_ref_rkakl_output
   WHERE
      rkaklOutputNama LIKE '%s' AND 
      rkaklOutputKode LIKE '%s'
	LIMIT %s, %s
";
?>