<?php 
$sql['get_program']  = "
SELECT SQL_CALC_FOUND_ROWS 
   rkaklProgramId AS id, 
   rkaklProgramKode AS kode, 
   rkaklProgramNama AS nama 
FROM finansi_ref_rkakl_prog 
WHERE rkaklProgramId IN (SELECT paguAnggUnitProgramId FROM finansi_pagu_anggaran_unit) 
AND (rkaklProgramKode LIKE '%s' OR rkaklProgramNama LIKE '%s') 
ORDER BY rkaklProgramKode
LIMIT %s, %s
";
$sql['get_count']   = "
SELECT FOUND_ROWS() AS total
";
?>