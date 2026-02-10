<?php

$sql['get_daftar_mak']="
SELECT
	SQL_CALC_FOUND_ROWS
 	kr.kegrefNama AS nama
FROM
    kegiatan_detail kd
    JOIN kegiatan_ref kr ON (kr.kegrefId = kd.kegdetKegrefId)
WHERE
	kr.kegrefNama LIKE '%s'
GROUP BY nama
";

$sql['get_count_data'] = "
   SELECT FOUND_ROWS() as total
";