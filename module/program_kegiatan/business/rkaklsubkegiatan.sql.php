<?php
$sql['get_count_data_rkakl_subkegiatan'] = 
   "SELECT 
      count(rkaklSubKegiatanId) AS total
   FROM 
      finansi_ref_rkakl_subkegiatan
	WHERE 
		rkaklSubKegiatanKode LIKE '%s'
		AND rkaklSubKegiatanNama LIKE '%s'";

$sql['get_data_rkakl_subkegiatan'] = 
   "SELECT
    rkaklSubKegiatanId AS id, rkaklSubKegiatanKode as kode, rkaklSubKegiatanNama AS nama
   FROM
    finansi_ref_rkakl_subkegiatan
	WHERE 
		rkaklSubKegiatanKode LIKE '%s'
		AND rkaklSubKegiatanNama LIKE '%s'
   ORDER BY 
	  rkaklSubKegiatanNama
   LIMIT %s, %s";


?>