<?php
$sql['get_rkakl_sub_kegiatan'] =
"
   SELECT
      SQL_CALC_FOUND_ROWS
	  rkaklSubKegiatanId   AS id,
	  rkaklSubKegiatanKode AS kode,
	  rkaklSubKegiatanNama AS nama
	FROM finansi_ref_rkakl_subkegiatan
	WHERE rkaklSubKegiatanKode LIKE '%s'
		 %s rkaklSubKegiatanNama LIKE '%s' ORDER BY rkaklSubKegiatanKode ASC
	LIMIT %s, %s
";


$sql['get_search_count'] = "
   SELECT FOUND_ROWS() AS total
";

$sql['get_rkakl_sub_kegiatan_by_id'] =
"
   SELECT
	  rkaklSubKegiatanId   AS id,
	  rkaklSubKegiatanKode AS kode,
	  rkaklSubKegiatanNama AS nama
	FROM finansi_ref_rkakl_subkegiatan
	WHERE rkaklSubKegiatanId = %s
";


$sql['get_count_rkakl_sub_kegiatan'] =
"
   SELECT
	  count(*)   AS `count`
	FROM finansi_ref_rkakl_subkegiatan
   WHERE rkaklSubKegiatanKode LIKE '%s'
		 %s rkaklSubKegiatanNama LIKE '%s'
";

$sql['insert_rkakl_sub_kegiatan'] = "
   INSERT INTO `finansi_ref_rkakl_subkegiatan` (`rkaklSubKegiatanKode`,`rkaklSubKegiatanNama`)
	VALUES ('%s','%s')
";

$sql['update_rkakl_sub_kegiatan'] = "
   UPDATE `finansi_ref_rkakl_subkegiatan` set `rkaklSubKegiatanKode` = '%s', `rkaklSubKegiatanNama`='%s'
	WHERE `rkaklSubKegiatanId` ='%s'
";

$sql['delete_rkakl_sub_kegiatan'] = "
   DELETE FROM `finansi_ref_rkakl_subkegiatan` WHERE `rkaklSubKegiatanId` ='%s'
";

$sql['delete_rkakl_sub_kegiatan_array'] = "
	DELETE
	FROM finansi_ref_rkakl_subkegiatan
	WHERE rkaklSubKegiatanId IN ('%s')
";

?>
