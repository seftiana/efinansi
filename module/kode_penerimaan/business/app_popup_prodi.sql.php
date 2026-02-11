<?php

$sql['get_data'] =
	"SELECT
		prodiId AS id,
		prodiKodeProdi AS kode,
		CONCAT(jenjangKode,' ',prodiNamaProdi) AS nama
	FROM pm_program_studi_ref 
	LEFT JOIN pm_jenjang ON prodiJenjangId=jenjangId
	WHERE prodiNamaProdi LIKE %s
	ORDER BY prodiNamaProdi ASC
	LIMIT %s, %s
	";

$sql['get_count'] =
   "SELECT
      COUNT(prodiId) AS total
	FROM
		pm_program_studi_ref
	WHERE prodiNamaProdi LIKE %s
   ";
?>