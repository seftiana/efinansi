<?php
$sql['count'] = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_program']   = "
SELECT SQL_CALC_FOUND_ROWS
   programId AS id,
   programNomor AS kode,
   programNama AS nama
FROM
   program_ref
WHERE programThanggarId = '%s'
   AND (programNama LIKE '%s' OR programNomor LIKE '%s')
LIMIT %s, %s
";
?>