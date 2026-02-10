<?php
$sql['count'] = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_ikk'] = "
SELECT
   SQL_CALC_FOUND_ROWS ikkId AS id,
   ikkKode AS kode,
   ikkNama AS nama
FROM
   finansi_pa_ref_ikk
WHERE 1 = 1
   AND ikkKode LIKE '%s'
   AND ikkNama LIKE '%s'
ORDER BY ikkKode
LIMIT %s, %s
";
?>