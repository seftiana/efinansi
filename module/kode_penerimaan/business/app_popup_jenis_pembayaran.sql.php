<?php

//popup sumber dana
$sql['get_data'] =
	"SELECT
		kodeterimaId AS id,
		kodeterimaKode AS kode,
		kodeterimaNama AS nama,
		kodeterimaIsAktif
	FROM
		kode_penerimaan_ref
	WHERE kodeterimaNama LIKE %s
	ORDER BY kodeterimaNama ASC
	LIMIT %s, %s
	";

$sql['get_count'] =
   "SELECT
      COUNT(kodeterimaId) AS total
	FROM
		kode_penerimaan_ref
	WHERE kodeterimaNama LIKE %s
   ";
?>