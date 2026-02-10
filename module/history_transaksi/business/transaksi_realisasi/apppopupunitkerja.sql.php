<?php
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
	ORDER BY satker, unit
	LIMIT %s, %s
";

$sql['get_data_tipe_unit'] = 
   "SELECT 
      tipeunitId		as id,
	  tipeunitNama		as name
   FROM 
      tipe_unit_kerja_ref
   ORDER BY 
      tipeunitNama";
?>