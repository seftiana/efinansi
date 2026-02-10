<?php
$sql['count']        = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']     = "
SELECT
   SQL_CALC_FOUND_ROWS ikuId AS id,
   ikuKode AS kode,
   ikuNama AS nama
FROM
   finansi_pa_ref_iku
WHERE 1 = 1
   AND ikuKode LIKE '%%'
   AND ikuNama LIKE '%%'
ORDER BY ikuKode
LIMIT 0, 20
";
?>