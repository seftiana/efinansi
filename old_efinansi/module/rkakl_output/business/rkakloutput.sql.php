<?php
$sql['get_rkakl_output'] = "
SELECT 
   fo.rkaklOutputId AS id,
   fo.rkaklOutputKode AS kode,
   fo.rkaklOutputNama AS nama,
   fk.rkaklKegiatanKode AS kode_kegiatan,
   fk.rkaklKegiatanId AS kegiatan_id,
   fk.rkaklKegiatanNama AS kegiatan_nama, 
   fp.rkaklProgramId AS program_id, 
   fp.rkaklProgramKode AS program_kode, 
   fp.rkaklProgramNama AS program_nama
FROM
   finansi_ref_rkakl_output AS fo 
   LEFT JOIN finansi_ref_rkakl_kegiatan AS fk 
      ON fo.rkaklOutputKegiatanId = fk.rkaklKegiatanId 
   LEFT JOIN finansi_ref_rkakl_prog AS fp 
      ON fk.rkaklKegiatanRkaklProgramId = fp.rkaklProgramId
WHERE fo.rkaklOutputKode LIKE '%s' 
   AND fo.rkaklOutputNama LIKE '%s' 
   AND ((fk.`rkaklKegiatanKode` LIKE '%s' OR fk.`rkaklKegiatanNama` LIKE '%s') OR 1 = %s)
ORDER BY fk.rkaklKegiatanKode,
   fo.rkaklOutputKode ASC 
LIMIT %s, %s
";

$sql['get_rkakl_output_by_id'] = 
"
   SELECT
     fo.rkaklOutputId   AS id,
     fo.rkaklOutputKode AS kode,
     fo.rkaklOutputNama AS nama,
     fo.rkaklOutputKegiatanId AS id_kegiatan,
     fk.rkaklKegiatanKode AS kode_kegiatan
   FROM finansi_ref_rkakl_output AS fo
   LEFT JOIN finansi_ref_rkakl_kegiatan AS fk 
   ON fo.rkaklOutputKegiatanId = fk.rkaklKegiatanId
   WHERE rkaklOutputId = %s
";


$sql['get_count_rkakl_output'] = "
SELECT
  count(rkaklOutputId)   AS `count`
FROM
   finansi_ref_rkakl_output AS fo 
   LEFT JOIN finansi_ref_rkakl_kegiatan AS fk 
      ON fo.rkaklOutputKegiatanId = fk.rkaklKegiatanId 
   LEFT JOIN finansi_ref_rkakl_prog AS fp 
      ON fk.rkaklKegiatanRkaklProgramId = fp.rkaklProgramId
WHERE fo.rkaklOutputKode LIKE '%s' 
   AND fo.rkaklOutputNama LIKE '%s' 
   AND ((fk.`rkaklKegiatanKode` LIKE '%s' OR fk.`rkaklKegiatanNama` LIKE '%s') OR 1 = %s)
";
      
$sql['insert_rkakl_output'] = "
   INSERT INTO `finansi_ref_rkakl_output` 
   (`rkaklOutputKode`,`rkaklOutputNama`,`rkaklOutputTglUbah`,`rkaklOutputUserId`,`rkaklOutputKegiatanId`) 
   VALUES ('%s','%s',NOW(),'%s','%s')
";

$sql['update_rkakl_output'] = "
   UPDATE `finansi_ref_rkakl_output` set `rkaklOutputKode` = '%s', `rkaklOutputNama`='%s', `rkaklOutputTglUbah`=NOW(),`rkaklOutputUserId`='%s', `rkaklOutputKegiatanId` = '%s'
   WHERE `rkaklOutputId` ='%s'
";

$sql['delete_rkakl_output'] = "
   DELETE FROM `finansi_ref_rkakl_output` WHERE `rkaklOutputId` ='%s'
";

$sql['delete_rkakl_output_array'] = "
   DELETE
   FROM finansi_ref_rkakl_output
   WHERE rkaklOutputId IN ('%s')
";

#kegiatan
$sql['get_rkakl_kegiatan'] = "SELECT rkaklKegiatanId AS kegiatan_id, 
                      rkaklKegiatanKode AS kegiatan_kode, 
                      rkaklKegiatanNama AS kegiatan_nama 
                      FROM finansi_ref_rkakl_kegiatan 
                      ORDER BY kegiatan_id DESC";
?>
