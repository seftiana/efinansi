<?php
$sql['get_count_data_program'] = "
SELECT
   count(programId) AS total
FROM
   program_ref
WHERE
 programThanggarId='%s'
AND programNama LIKE '%s'
AND programNomor LIKE '%s'
";

$sql['get_data_program'] = "
SELECT
   programId as id,
   programNomor as kode,
   programNama as nama
FROM
   program_ref
WHERE
   programThanggarId='%s'
AND programNama LIKE '%s'
AND programNomor LIKE '%s'
LIMIT %s, %s
";
?>