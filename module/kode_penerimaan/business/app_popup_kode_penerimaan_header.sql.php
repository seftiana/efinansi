<?php

$sql['get_data'] ="
SELECT 
    SQL_CALC_FOUND_ROWS
    kodeterimaId AS kp_id,
	kodeterimaKode AS kp_kode,
	kodeterimaNama AS kp_nama
FROM kode_penerimaan_ref
WHERE
	kodeterimaTipe = 'header'
	AND
	kodeterimaKode LIKE '%s'
	AND
	kodeterimaNama LIKE '%s'
ORDER BY kodeterimaKode
LIMIT %s,%s
";

$sql['get_count']="
SELECT FOUND_ROWS() AS total
";
?>