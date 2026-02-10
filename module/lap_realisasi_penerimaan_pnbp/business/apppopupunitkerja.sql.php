<?php
/**
$sql['get_count_data_unitkerja'] = 
"
	SELECT 
		COUNT(unitkerjaId) as total
	FROM unit_kerja_ref
		LEFT JOIN 
			(SELECT 
				unitkerjaId AS tempUnitId,
				unitkerjaKode AS tempUnitKode,
				unitkerjaNama AS tempUnitNama,
				unitkerjaParentId AS tempParentId
			FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUnitKerja ON(unitkerjaParentId=tempUnitId)
	WHERE 
		(unitkerjaKode LIKE '%s' OR tempUnitKode LIKE '%s' )
		AND (unitkerjaNama LIKE '%s' OR tempUnitNama LIKE '%s')
		%s
		%s
";
$sql['get_data_unitkerja']="
	SELECT 
		(if(tempUnitKode IS NULL,unitkerjaKode,tempUnitKode)) AS kodesatker,
		(if(tempUnitNama IS NULL,unitkerjaNama,tempUnitNama)) AS satker,
		(if(tempUnitId IS NULL,unitkerjaId,unitkerjaId)) AS id,
		(if(tempUnitKode IS NULL,unitkerjaKode,unitkerjaKode)) AS kodeunit,
		(if(tempUnitNama IS NULL,unitkerjaNama,unitkerjaNama)) AS unit,
		tipeunitNama as tipeunit,
		unitkerjaParentId AS parentId
	FROM unit_kerja_ref
		LEFT JOIN 
			(SELECT 
				unitkerjaId AS tempUnitId,
				unitkerjaKode AS tempUnitKode,
				unitkerjaNama AS tempUnitNama,
				unitkerjaParentId AS tempParentId
			FROM unit_kerja_ref 
			LEFT JOIN tipe_unit_kerja_ref ON (tipeunitId = unitkerjaTipeunitId)
			WHERE unitkerjaParentId = 0) tmpUnitKerja ON(unitkerjaParentId=tempUnitId)
		LEFT JOIN tipe_unit_kerja_ref ON (tipeunitId = unitkerjaTipeunitId)
	WHERE 
		(unitkerjaKode LIKE '%s' OR tempUnitKode LIKE '%s' )
		AND (unitkerjaNama LIKE '%s' OR tempUnitNama LIKE '%s')
		%s
		%s
	ORDER BY kodesatker,parentId, kodeunit
	LIMIT %s, %s
";
*/
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
 
?>