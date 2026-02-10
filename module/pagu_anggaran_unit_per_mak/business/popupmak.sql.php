<?php
$sql['get_count_data_mak'] = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_mak'] = "
SELECT SQL_CALC_FOUND_ROWS
   mak.paguBasId AS akunId,
   mak.paguBasKode AS akunKode,
   mak.paguBasKeterangan AS akunNama,
   bas.paguBasId AS basId,
   bas.paguBasKode AS basKode,
   bas.paguBasKeterangan AS basNama
FROM finansi_ref_pagu_bas AS mak
JOIN finansi_ref_pagu_bas AS bas
   ON bas.paguBasParentId = 0
   AND bas.paguBasId = mak.paguBasParentId
WHERE 1 = 1
AND mak.paguBasParentId <> 0
AND (bas.paguBasKode NOT IN ('%s') OR 1 = %s)
AND ((mak.paguBasKode LIKE '%s' OR mak.paguBasKeterangan LIKE '%s') OR 1 = %s)
AND ((bas.paguBasKode LIKE '%s' OR bas.paguBasKeterangan LIKE '%s') OR 1 = %s)
ORDER BY bas.paguBasKode, mak.paguBasKode
LIMIT %s, %s
";
?>