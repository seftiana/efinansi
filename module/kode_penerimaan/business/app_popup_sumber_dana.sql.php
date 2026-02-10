<?php

//popup sumber dana
$sql['get_data_sumber_dana'] =
	"SELECT
		sumberdanaId AS id_sumber_dana,
		sumberdanaNama AS nama_sumber_dana
	FROM
		finansi_ref_sumber_dana
	WHERE
		(sumberdanaNama LIKE %s AND isAktif ='Y')
		AND isAktif ='Y'
	ORDER BY sumberdanaNama ASC
	LIMIT %s, %s
	";

$sql['get_count_sumber_dana'] =
   "SELECT
      COUNT(sumberdanaId) AS total
	FROM
		finansi_ref_sumber_dana
	WHERE
		(sumberdanaNama LIKE %s AND isAktif ='Y')
		AND isAktif ='Y'
   ";
?>