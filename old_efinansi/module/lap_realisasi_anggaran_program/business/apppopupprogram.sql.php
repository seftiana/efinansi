<?php
$sql['get_count_data_program'] = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_program'] = "
SELECT SQL_CALC_FOUND_ROWS
   programId as id,
   programNomor as kode,
   programNama as nama
FROM
   program_ref
WHERE 1 = 1
AND programThanggarId='%s'
AND programNomor LIKE '%s'
ORDER BY programNomor
LIMIT %s, %s
";
?>