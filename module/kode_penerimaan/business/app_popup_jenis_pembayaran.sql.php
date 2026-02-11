<?php

//popup sumber dana
$sql['get_data'] =
	"SELECT
		jenisBiayaId AS id,
		jenisBiayaKode AS kode,
		jenisBiayaNama AS nama,
		jenisBiayaStatus
	FROM
		pm_jenis_biaya
	WHERE jenisBiayaNama LIKE %s
	AND jenisBiayaId > 0
	ORDER BY jenisBiayaNama ASC
	LIMIT %s, %s
	";

$sql['get_count'] =
   "SELECT
      COUNT(jenisBiayaId) AS total
	FROM
		pm_jenis_biaya
	WHERE jenisBiayaNama LIKE %s
   ";
?>