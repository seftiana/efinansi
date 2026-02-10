<?php
$sql['get_kinerja_utama'] = "
SELECT 
   SQL_CALC_FOUND_ROWS
   fi.ikuId AS id,
   fi.ikuKode AS kode,
   fi.ikuNama AS nama,
   fp.rkaklProgramKode AS kode_program 
FROM
   finansi_pa_ref_iku AS fi 
   LEFT JOIN finansi_ref_rkakl_prog AS fp 
      ON (
         fi.ikuProgramId = fp.rkaklProgramId
      )
WHERE fi.ikuKode LIKE '%s' AND fi.`ikuNama` LIKE '%s' 
LIMIT %s,%s
";

$sql['get_kinerja_utama_by_id'] = "
SELECT
	fi.ikuId AS id, 
	fi.ikuKode AS kode, 
	fi.ikuNama AS nama, 
	fp.rkaklProgramKode AS kode_program, 
	fi.ikuProgramId AS id_program 
FROM 
	finansi_pa_ref_iku AS fi 
LEFT JOIN finansi_ref_rkakl_prog AS fp  
ON (fi.ikuProgramId = fp.rkaklProgramId)
WHERE fi.ikuId = %s
";


$sql['get_count_kinerja_utama'] = "
SELECT FOUND_ROWS() AS `total`
";
      
$sql['insert_kinerja_utama'] = "
   INSERT INTO `finansi_pa_ref_iku` (`ikuKode`,`ikuNama`,`ikuProgramId`) VALUES ('%s','%s','%s')
";

$sql['update_kinerja_utama'] = "
UPDATE `finansi_pa_ref_iku` SET `ikuKode` = '%s', 
   `ikuNama`='%s', 
   `ikuProgramId` = '%s' 
WHERE `ikuId` ='%s'
";

$sql['delete_kinerja_utama'] = "
DELETE FROM `finansi_pa_ref_iku` WHERE `ikuId` ='%s'
";

$sql['delete_kinerja_utama_array'] = "
DELETE
FROM finansi_pa_ref_iku
	WHERE ikuId IN ('%s')
";

?>
