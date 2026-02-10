<?php
$sql['get_kinerja_kegiatan'] = "
SELECT SQL_CALC_FOUND_ROWS
   f.ikkId AS id, 
	f.ikkKode AS kode, 
	f.ikkNama AS nama, 
	k.rkaklKegiatanKode AS kode_kegiatan, 
	k.rkaklKegiatanId AS id_kegiatan
FROM
    finansi_pa_ref_ikk AS f
LEFT JOIN finansi_ref_rkakl_kegiatan AS k 
ON (f.ikkRkaklKegiatanId = k.rkaklKegiatanId)
WHERE f.ikkKode LIKE '%s'
	AND f.ikkNama LIKE '%s' ORDER BY f.ikkKode ASC
LIMIT %s, %s
";

$sql['get_kinerja_kegiatan_by_id'] = "
SELECT
   f.ikkId AS id, 
	f.ikkKode AS kode, 
	f.ikkNama AS nama, 
	k.rkaklKegiatanKode AS kode_kegiatan, 
	k.rkaklKegiatanId AS id_kegiatan
FROM
    finansi_pa_ref_ikk AS f
LEFT JOIN finansi_ref_rkakl_kegiatan AS k 
ON (f.ikkRkaklKegiatanId = k.rkaklKegiatanId)
WHERE f.ikkId = %s
";


$sql['get_count_kinerja_kegiatan'] = "
SELECT FOUND_ROWS() AS `count`
";
      
$sql['insert_kinerja_kegiatan'] = "
   INSERT INTO `finansi_pa_ref_ikk` (`ikkKode`,`ikkNama`,`ikkRkaklKegiatanId`) 
   VALUES ('%s','%s','%s')
";

$sql['update_kinerja_kegiatan'] = "
   UPDATE `finansi_pa_ref_ikk` set `ikkKode` = '%s', `ikkNama`='%s', 
   `ikkRkaklKegiatanId` = '%s' WHERE `ikkId` ='%s'
";

$sql['delete_kinerja_kegiatan'] = "
   DELETE FROM `finansi_pa_ref_ikk` WHERE `ikkId` ='%s'
";

$sql['delete_kinerja_kegiatan_array'] = "
	DELETE
	FROM finansi_pa_ref_ikk
	WHERE ikkId IN ('%s')
";

?>
