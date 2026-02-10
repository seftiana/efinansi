<?php
$sql['get_data']  = "
SELECT SQL_CALC_FOUND_ROWS
   sumberdanaId AS id,
   sumberdanaNama AS nama
FROM
   finansi_ref_sumber_dana
WHERE sumberdanaNama LIKE '%s'
   AND isAktif = 'Y'
ORDER BY sumberdanaNama
LIMIT %s, %s
";

$sql['count']    = "
SELECT FOUND_ROWS() AS `count`
";
?>