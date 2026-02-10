<?php
$sql['get_rkakl_kegiatan'] ="
SELECT
   SQL_CALC_FOUND_ROWS
   fr.rkaklKegiatanId   AS id,
   fr.rkaklKegiatanKode AS kode,
   fr.rkaklKegiatanNama AS nama,
   fp.rkaklProgramId AS program_id,
   fp.rkaklProgramKode AS kode_program, 
   fp.rkaklProgramNama AS program_nama, 
   IF((SELECT COUNT(rkaklOutputId) FROM finansi_ref_rkakl_output WHERE rkaklOutputKegiatanId = id) != 0, TRUE, FALSE) AS has_output 
FROM finansi_ref_rkakl_kegiatan AS fr 
   INNER JOIN finansi_ref_rkakl_prog AS fp 
      ON (fr.rkaklKegiatanRkaklProgramId = fp.rkaklProgramId)
WHERE fr.rkaklKegiatanKode LIKE '%s'
   AND fr.rkaklKegiatanNama LIKE '%s' 
ORDER BY fp.rkaklProgramKode, fr.rkaklKegiatanKode ASC
LIMIT %s, %s
";

$sql['get_search_count'] = "
   SELECT FOUND_ROWS() AS total
";

$sql['get_rkakl_kegiatan_by_id'] =
"
SELECT
   fr.rkaklKegiatanId   AS id,
     fr.rkaklKegiatanKode AS kode,
     fr.rkaklKegiatanNama AS nama,
     fp.rkaklProgramKode AS kode_program 
   FROM finansi_ref_rkakl_kegiatan AS fr 
   INNER JOIN finansi_ref_rkakl_prog AS fp 
   ON (fr.rkaklKegiatanRkaklProgramId = fp.rkaklProgramId)
   WHERE rkaklKegiatanId = %s
";


$sql['get_count_rkakl_kegiatan'] ="
SELECT
   count(rkaklKegiatanId)   AS `count`
FROM finansi_ref_rkakl_kegiatan
WHERE rkaklKegiatanKode LIKE '%s'
   AND rkaklKegiatanNama LIKE '%s'
";

$sql['insert_rkakl_kegiatan'] = "
   INSERT INTO `finansi_ref_rkakl_kegiatan` 
   (`rkaklKegiatanKode`,`rkaklKegiatanNama`,`rkaklKegiatanRkaklProgramId`)
   VALUES ('%s','%s','%s')
";

$sql['update_rkakl_kegiatan'] = "
   UPDATE `finansi_ref_rkakl_kegiatan` 
   set `rkaklKegiatanKode` = '%s', `rkaklKegiatanNama`='%s', 
   `rkaklKegiatanRkaklProgramId` = '%s'
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

$sql['get_data_program'] = "
   SELECT 
      rkaklProgramId AS id_program,
      rkaklProgramKode AS kode_program,
      rkaklProgramNama AS program_name
      
   FROM 
      finansi_ref_rkakl_prog  
";
?>
