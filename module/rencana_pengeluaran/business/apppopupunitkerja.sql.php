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
		(if(tempUnitNama IS NULL,unitkerjaNama,tempUnitNama)) 	AS satker,
		(if(tempUnitId IS NULL,unitkerjaId,unitkerjaId)) 		AS id,
		(if(tempUnitKode IS NULL,unitkerjaKode,unitkerjaKode)) 	AS kodeunit,
		(if(tempUnitNama IS NULL,unitkerjaNama,unitkerjaNama)) 	AS unit,
		tipeunitNama as tipeunit,
		unitkerjaParentId AS parentId
	FROM unit_kerja_ref
		LEFT JOIN 
			(SELECT 
				unitkerjaId 		AS tempUnitId,
				unitkerjaKode 		AS tempUnitKode,
				unitkerjaNama 		AS tempUnitNama,
				unitkerjaParentId 	AS tempParentId
				FROM unit_kerja_ref
			LEFT JOIN 
				tipe_unit_kerja_ref 
			ON (tipeunitId = unitkerjaTipeunitId)
			WHERE 
				unitkerjaParentId = 0
			)
		tmpUnitKerja ON
		(unitkerjaParentId=tempUnitId)
		LEFT JOIN 
			tipe_unit_kerja_ref 
		ON 
			(tipeunitId = unitkerjaTipeunitId)
	WHERE 
		(unitkerjaKode LIKE '%s' OR tempUnitKode LIKE '%s' )
		AND (unitkerjaNama LIKE '%s' OR tempUnitNama LIKE '%s')
		%s
		%s
	ORDER BY 
		unitkerjaKodeSistem ASC
	LIMIT %s, %s
	";
*/	
#	ORDER BY satker, unit
#	LIMIT %s, %s
#";
/*
$sql['get_data_unitkerja'] = 
"
SELECT 
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
				unitkerjaParentId AS tempParentId,
				tipeunitNama AS tempTipeunitNama
			FROM unit_kerja_ref 
			LEFT JOIN tipe_unit_kerja_ref ON (tipeunitId = unitkerjaTipeunitId)
			WHERE unitkerjaParentId = 0) tmpUniKerja ON(unitkerjaParentId=tempUnitId)
		LEFT JOIN tipe_unit_kerja_ref ON (tipeunitId = unitkerjaTipeunitId)
	WHERE 
		unitkerjaKode LIKE '%s' 
		OR unitkerjaNama LIKE '%s' 
		OR tempUnitKode LIKE '%s' 
		OR tempUnitNama LIKE '%s'
		%s
	LIMIT %s, %s
";
*/
/*
$sql['get_data_unitkerja'] = 
   "SELECT 
      ukr.unitkerjaId				as unitkerja_id,
	  ukr.unitkerjaKode				as unitkerja_kode,
	  ukr.unitkerjaNama				as unitkerja_nama,
	  ukr.unitkerjaNamaPimpinan		as unitkerja_pimpinan,
	  tukr.tipeunitNama				as tipeunit_nama
   FROM 
      unit_kerja_ref ukr
	  LEFT JOIN tipe_unit_kerja_ref tukr ON (tukr.tipeunitId = ukr.unitkerjaTipeunitId)
	WHERE 
		ukr.unitkerjaKode LIKE '%s'
		AND ukr.unitkerjaNama LIKE '%s'
		AND ukr.unitkerjaParentId <> 0
		%s
		%s
   ORDER BY 
	  ukr.unitkerjaNama
   LIMIT %s, %s";
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
 * @since 3 Januari 2012
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
	)		AND unitkerjaKode LIKE '%s'
	AND unitkerjaNama LIKE '%s'
	AND unitkerjaTipeunitId LIKE '%s'
";
/**
 * end
 */