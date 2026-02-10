<?php
$sql['get_count_data_kode_rkakl'] = 
   "SELECT 
      count(rkaklKodePenerimaanId) AS total
   FROM 
      finansi_ref_rkakl_kode_penerimaan
   WHERE
	rkaklKodePenerimaanNama LIKE '%s' AND 
   rkaklKodePenerimaanKode LIKE '%s'
";

$sql['get_data_kode_rkakl'] = 
   "SELECT 
      rkaklKodePenerimaanId as id,
      rkaklKodePenerimaanKode as kode,
      rkaklKodePenerimaanNama as nama
   FROM 
      finansi_ref_rkakl_kode_penerimaan
   WHERE
      rkaklKodePenerimaanNama LIKE '%s' AND 
      rkaklKodePenerimaanKode LIKE '%s'
	LIMIT %s, %s
";
?>