<?php
$sql['get_data'] = "
SELECT SQL_CALC_FOUND_ROWS 
   mak.`paguBasId`,
   mak.`paguBasKode`,
   mak.`paguBasParentId`,
   mak.`paguBasNilaiDefault`,
   mak.`paguBasStatusAktif`,
   mak.`paguBasKeterangan`, 
   bas.paguBasId AS bas_id, 
   bas.paguBasKode AS bas_kode, 
   bas.`paguBasKeterangan` AS bas_nama
FROM `finansi_ref_pagu_bas` AS mak
LEFT JOIN finansi_ref_pagu_bas AS bas 
   ON mak.paguBasParentId = bas.paguBasId
WHERE SUBSTR(mak.`paguBasKode`, 1, 1) = '4' 
AND mak.paguBasParentId != 0 
AND mak.paguBasStatusAktif = 'Y' 
AND (mak.paguBasKode LIKE '%s' OR mak.paguBasKeterangan LIKE '%s') 
LIMIT %s, %s
";

$sql['count_data']   = "
SELECT FOUND_ROWS() AS `total`
";
?>