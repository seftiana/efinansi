<?php
$sql['get_count_data_rkakl_kegiatan'] = 
   "SELECT 
      count(rkaklKegiatanId) AS total
   FROM 
      finansi_ref_rkakl_kegiatan
	WHERE 
		rkaklKegiatanKode LIKE '%s'
		AND rkaklKegiatanNama LIKE '%s'";

$sql['get_data_rkakl_kegiatan'] = 
   "SELECT
    rkaklKegiatanId AS id, rkaklKegiatanKode as kode, rkaklKegiatanNama AS nama
   FROM
    finansi_ref_rkakl_kegiatan
	WHERE 
		rkaklKegiatanKode LIKE '%s'
		AND rkaklKegiatanNama LIKE '%s'
   ORDER BY 
	  rkaklKegiatanNama
   LIMIT %s, %s";


?>