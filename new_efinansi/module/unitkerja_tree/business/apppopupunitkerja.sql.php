<?php

//untuk combo box

$sql['get_data_tipe_unit'] = 
   "SELECT 
      tipeunitId		as id,
	  tipeunitNama		as name
   FROM 
      tipe_unit_kerja_ref
   ORDER BY 
      tipeunitNama";
   
/**
 * add 
 * @since 4 Januari 2012
 */
$sql['get_total_sub_unit_kerja']="
SELECT 
	count(unitkerjaId) as total
FROM unit_kerja_ref
WHERE unitkerjaParentId = %s
";

$sql['get_list_unit_kerja']="
SELECT 
    unitkerjaKode AS kodesatker,
	unitkerjaNama AS satker,
	unitkerjaId AS id,
	unitkerjaKode AS kodeunit,
	unitkerjaNama AS unit,
	unitkerjaParentId AS parentId,
	unitkerjaKodeSistem AS kodesistem,
	tipeunitNama AS tipeunit
FROM 
	unit_kerja_ref
	LEFT JOIN tipe_unit_kerja_ref 
		ON tipe_unit_kerja_ref.tipeunitId = unit_kerja_ref.unitkerjaTipeunitId 
WHERE 
(unit_kerja_ref.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'%s') OR 
	unit_kerja_ref.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)	
	AND unitkerjaKode LIKE '%s'
	AND unitkerjaNama LIKE '%s'
	AND unitkerjaTipeunitId LIKE '%s'
ORDER BY
	unitkerjaKode ASC
LIMIT %s, %s
";

$sql['get_count_list_unit_kerja']="
SELECT 
	count(unitkerjaId) AS total
FROM 
	unit_kerja_ref
	LEFT JOIN tipe_unit_kerja_ref 
		ON tipe_unit_kerja_ref.tipeunitId = unit_kerja_ref.unitkerjaTipeunitId 
WHERE 
	(unit_kerja_ref.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'%s') OR 
	unit_kerja_ref.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)
	AND unitkerjaKode LIKE '%s'
	AND unitkerjaNama LIKE '%s'
	AND unitkerjaTipeunitId LIKE '%s'
";
/**
 * end
 */