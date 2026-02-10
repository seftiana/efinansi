<?php
$sql['get_rkakl_kegiatan'] = 
"
   SELECT
	  rkaklKegiatanId   AS id,
	  rkaklKegiatanKode AS kode,
	  rkaklKegiatanNama AS nama
	FROM finansi_ref_rkakl_kegiatan
	WHERE rkaklKegiatanKode LIKE '%s'
		 AND rkaklKegiatanNama LIKE '%s' ORDER BY rkaklKegiatanKode ASC
	LIMIT %s, %s
";

$sql['get_rkakl_kegiatan_by_id'] = 
"
   SELECT
	  rkaklKegiatanId   AS id,
	  rkaklKegiatanKode AS kode,
	  rkaklKegiatanNama AS nama
	FROM finansi_ref_rkakl_kegiatan
	WHERE rkaklKegiatanId = %s
";


$sql['get_count_rkakl_kegiatan'] = 
"
   SELECT
	  count(rkaklKegiatanId)   AS `count`
	FROM finansi_ref_rkakl_kegiatan
   WHERE rkaklKegiatanKode LIKE '%s'
		 AND rkaklKegiatanNama LIKE '%s'
";
      
$sql['insert_rkakl_kegiatan'] = "
   INSERT INTO `finansi_ref_rkakl_kegiatan` (`rkaklKegiatanKode`,`rkaklKegiatanNama`) 
	VALUES ('%s','%s')
";

$sql['update_rkakl_kegiatan'] = "
   UPDATE `finansi_ref_rkakl_kegiatan` set `rkaklKegiatanKode` = '%s', `rkaklKegiatanNama`='%s'
	WHERE `rkaklKegiatanId` ='%s'
";

$sql['delete_rkakl_kegiatan'] = "
   DELETE FROM `finansi_ref_rkakl_kegiatan` WHERE `rkaklKegiatanId` ='%s'
";

$sql['delete_rkakl_kegiatan_array'] = "
	DELETE
	FROM finansi_ref_rkakl_kegiatan
	WHERE rkaklKegiatanId IN ('%s')
";
?>
