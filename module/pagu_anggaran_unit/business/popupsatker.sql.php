<?php
$sql['get_count_data_satker'] = 
   "SELECT 
      count(unitkerjaId) AS total
   FROM 
      unit_kerja_ref
   WHERE
	  unitkerjaParentId=0
      AND unitkerjaNama LIKE '%s'
	  AND unitkerjaNamaPimpinan LIKE '%s'";

$sql['get_data_satker'] = 
   "SELECT 
      unitkerjaId as satker_id,
	  unitkerjaNama as satker_nama,
	  unitkerjaNamaPimpinan as satker_pimpinan
   FROM 
      unit_kerja_ref
	WHERE 
		unitkerjaParentId=0
		AND unitkerjaNama LIKE '%s'
	  AND unitkerjaNamaPimpinan LIKE '%s'
   ORDER BY 
      unitkerjaNama
   LIMIT %s, %s";
?>