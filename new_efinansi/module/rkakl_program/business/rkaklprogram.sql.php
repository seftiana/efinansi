<?php
$sql['get_rkakl_program'] = "
SELECT
   rkaklProgramId   AS id,
   rkaklProgramKode AS kode,
   rkaklProgramNama AS nama, 
   IF((SELECT COUNT(rkaklKegiatanId) FROM finansi_ref_rkakl_kegiatan WHERE rkaklKegiatanRkaklProgramId = id) != 0, TRUE, FALSE) AS has_kegiatan
FROM finansi_ref_rkakl_prog
WHERE rkaklProgramKode LIKE '%s'
   AND rkaklProgramNama LIKE '%s' 
ORDER BY rkaklProgramKode ASC
LIMIT %s, %s
";

$sql['get_rkakl_program_by_id'] = "
SELECT
   rkaklProgramId   AS id,
   rkaklProgramKode AS kode,
   rkaklProgramNama AS nama
FROM finansi_ref_rkakl_prog
WHERE rkaklProgramId = %s
";


$sql['get_count_rkakl_program'] = "
SELECT
   count(rkaklProgramId)   AS `count`
FROM finansi_ref_rkakl_prog
WHERE rkaklProgramKode LIKE '%s'
   AND rkaklProgramNama LIKE '%s'
";
      
$sql['insert_rkakl_program'] = "
   INSERT INTO `finansi_ref_rkakl_prog` (`rkaklProgramKode`,`rkaklProgramNama`) 
   VALUES ('%s','%s')
";

$sql['update_rkakl_program'] = "
   UPDATE `finansi_ref_rkakl_prog` set `rkaklProgramKode` = '%s', `rkaklProgramNama`='%s'
   WHERE `rkaklProgramId` ='%s'
";

$sql['delete_rkakl_program'] = "
   DELETE FROM `finansi_ref_rkakl_prog` WHERE `rkaklProgramId` ='%s'
";

$sql['delete_rkakl_program_array'] = "
   DELETE
   FROM finansi_ref_rkakl_prog
   WHERE rkaklProgramId IN ('%s')
";

?>
