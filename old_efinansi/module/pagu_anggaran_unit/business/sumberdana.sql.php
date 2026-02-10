<?php
$sql['get_count_data_sumber_dana'] = 
   "SELECT 
      count(sumberdanaId) AS total
   FROM 
      finansi_ref_sumber_dana
	WHERE 
		sumberdanaNama LIKE '%s'
		AND 
		isAktif = 'Y'
		";

$sql['get_data_sumber_dana'] = 
   "SELECT
    sumberdanaId AS id, sumberdanaNama AS nama
   FROM
    finansi_ref_sumber_dana
	WHERE 
		sumberdanaNama LIKE '%s'
	AND 
		isAktif = 'Y'
   ORDER BY 
	  sumberdanaNama
   LIMIT %s, %s";


?>