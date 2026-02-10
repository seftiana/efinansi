<?php

//===GET===
$sql['get_data_excel'] = "
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
	ORDER BY kodesatker,parentId, kodeunit
";


$sql['get_unit_kerja'] = "

SELECT 
      a.unitkerjaId,
		a.unitkerjaKode,
		a.unitkerjaNama,
		b.tipeunitNama,
		a.unitkerjaNamaPimpinan,
      c.unitkerjaId AS idSubUnit,
		c.unitkerjaKode AS kodeSubUnit,
		c.unitkerjaNama AS namaSubUnit,
		c.tipeunitNama AS tipeSubUnit,
		c.unitkerjaNamaPimpinan AS pimpinanSubUnit
	FROM 
		unit_kerja_ref a
	LEFT JOIN tipe_unit_kerja_ref b ON a.unitkerjaTipeunitId = b.tipeunitId
	LEFT JOIN (
	SELECT 
		a.unitkerjaParentId,
		a.unitkerjaKode,
		a.unitkerjaNama,
		b.tipeunitNama,
		a.unitkerjaNamaPimpinan
	FROM 
		unit_kerja_ref a
	LEFT JOIN tipe_unit_kerja_ref b ON a.unitkerjaTipeunitId = b.tipeunitId
	WHERE 
		a.unitkerjaParentId != 0
	AND 
		(a.unitkerjaKode LIKE '%s'
	OR
		a.unitkerjaNama LIKE '%s')
		%s
	) c ON c.unitkerjaParentId = a.unitkerjaId
	WHERE 
		a.unitkerjaParentId = 0
	AND 
		(a.unitkerjaKode LIKE '%s'
	OR
		a.unitkerjaNama LIKE '%s')
	%s
   ORDER BY unitkerjaKode
	LIMIT %s, %s
";

//old sql
$sql['get_count_data_unitkerja'] = 
   "SELECT 
      count(unitkerjaId) AS total
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
		%s";

$sql['get_data_unitkerja'] = "
	SELECT 
		(if(tempUnitKode IS NULL,unitkerjaKode,tempUnitKode)) AS kodesatker,
		(if(tempUnitNama IS NULL,unitkerjaNama,tempUnitNama)) AS satker,
		(if(tempUnitId IS NULL,unitkerjaId,unitkerjaId)) AS id,
		(if(tempUnitKode IS NULL,unitkerjaKode,unitkerjaKode)) AS kodeunit,
		(if(tempUnitNama IS NULL,unitkerjaNama,unitkerjaNama)) AS unit,
		((LENGTH(`unitkerjaKodeSistem`) - LENGTH(REPLACE(`unitkerjaKodeSistem`,'.',''))) + 1) AS unitkerjaLevel,
		tipeunitNama as tipeunit,
		unitkerjaParentId AS parentId,
		unitkerjaKodeSistem AS kodesistem
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
	ORDER BY 
		unitkerjaKode ASC /*Sistem ASC*/
	LIMIT %s, %s
";
//--unitkerjaParentId = %s AND--
//--kodesatker,parentId, kodeunit,--
/*
   "SELECT 
      ukr.unitkerjaId				as unitkerja_id,
	  ukr.unitkerjaKode				as unitkerja_kode,
	  ukr.unitkerjaNama				as unitkerja_nama,
	  ukr.unitkerjaNamaPimpinan	as unitkerja_pimpinan,
	  skr.unitkerjaId				as satuankerja_id,
	  skr.unitkerjaNama				as satuankerja_nama,
	  tukr.tipeunitNama				as tipeunit_nama
   FROM 
      unit_kerja_ref ukr
	  LEFT JOIN unit_kerja_ref skr ON (skr.unitkerjaId = ukr.unitkerjaParentId)
	  LEFT JOIN tipe_unit_kerja_ref tukr ON (tukr.tipeunitId = ukr.unitkerjaTipeunitId)
	WHERE 
		ukr.unitkerjaKode LIKE '%s'
		AND ukr.unitkerjaNama LIKE '%s'
		AND ukr.unitkerjaParentId <> 0
		%s
		%s
   ORDER BY 
      skr.unitkerjaNama, 
	  ukr.unitkerjaNama
   LIMIT %s, %s";
*/
$sql['get_data_unitkerja_by_id'] = 
   "SELECT 
      unitkerjaId					as unitkerja_id,
	  unitkerjaKode					as unitkerja_kode,
	  unitkerjaNama					as unitkerja_nama,
	  unitkerjaNamaPimpinan			as unitkerja_pimpinan,
	  unitkerjaParentId		as satuankerja_id,
	  unitkerjaTipeunitId			as tipeunit_id,
	  unitkerjaNamaPimpinan			as unitkerja_pimpinan_nama,
	  unitKerjaUnitStatusId  		AS statusunit
   FROM 
      unit_kerja_ref
   WHERE
      unitkerjaId='%s'
	  AND unitkerjaId <> 0";

$sql['get_data_unitkerja_by_array_id'] = 
   "SELECT 
      unitkerjaId					as unitkerja_id,
	  unitkerjaNama					as unitkerja_nama
   FROM 
      unit_kerja_ref
   WHERE
      unitkerjaId IN ('%s')
	  AND unitkerjaId <> 0";

//untuk combo box
/** @deprecated
$sql['get_data_satker'] = 
   "SELECT 
      unitkerjaId		as id,
	  unitkerjaNama		as name
   FROM 
      unit_kerja_ref
	WHERE unitkerjaParentId=0
   ORDER BY 
      unitkerjaNama";
*/

/**
 * new feature
 * @since 27 Desember 2011
 */
 $sql['get_data_satker'] = 
   "SELECT 
      unitkerjaId		as id,
      concat(unitkerjaKode,' - ',unitkerjaNama	)as name
   FROM 
      unit_kerja_ref
   %s
   ORDER BY 
      unitkerjaNama";

 /**
  * end
  */
$sql['get_data_tipe_unit'] = 
   "SELECT 
      tipeunitId		as id,
	  tipeunitNama		as name
   FROM 
      tipe_unit_kerja_ref
   ORDER BY 
      tipeunitNama";

$sql['get_kode_system_unit']="
	SELECT (unitkerjaKodeSistem+1) AS unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitkerjaParentId = 0 ORDER BY unitkerjaId DESC LIMIT 0,1
";

$sql['get_kode_system_subunit']="
	SELECT 
		CONCAT(parent.unitkerjaKodeSistem,'.',(IFNULL(SUBSTRING_INDEX(child.unitkerjaKodeSistem,'.',-1),0)+1)) AS kode
	FROM
	(SELECT '%s' AS unitkerjaId) AS param
	LEFT JOIN unit_kerja_ref AS parent ON IF(param.unitkerjaId IS NULL, parent.unitkerjaId IS NULL, parent.unitkerjaId = param.unitkerjaId)
	LEFT JOIN unit_kerja_ref AS child ON IF(param.unitkerjaId IS NULL, child.unitkerjaParentId IS NULL, child.unitkerjaParentId = param.unitkerjaId)
	WHERE LENGTH(child.unitkerjaKodeSistem) = LENGTH(parent.unitkerjaKodeSistem)+2
	ORDER BY
		child.unitkerjaKodeSistem DESC
	LIMIT 1
";

//===DO===

$sql['do_add_unitkerja'] = 
   "INSERT INTO unit_kerja_ref
      (unitkerjaKode, unitkerjaKodeSistem, unitkerjaNama, unitkerjaTipeunitId, unitKerjaUnitStatusId, unitkerjaParentId, unitkerjaNamaPimpinan)
   VALUES 
      ('%s','%s',
		'%s','%s','%s','%s','%s')";
		
$sql['do_add_unitkerja_old'] = 
   "INSERT INTO unit_kerja_ref
      (unitkerjaKode, unitkerjaKodeSistem, unitkerjaNama, unitkerjaTipeunitId, unitKerjaUnitStatusId, unitkerjaParentId, unitkerjaNamaPimpinan)
   VALUES 
      ('%s',(SELECT kode FROM (SELECT 
					IFNULL(CONCAT(parent.unitkerjaKodeSistem,'.',(IFNULL(SUBSTRING_INDEX(child.unitkerjaKodeSistem,'.',-1),0)+1000)),(SELECT (unitkerjaKodeSistem+1) AS unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitkerjaParentId = 0 ORDER BY unitkerjaId DESC LIMIT 0,1)) AS kode
				FROM
				(SELECT '%s' AS unitkerjaId) AS param
				LEFT JOIN unit_kerja_ref AS parent ON IF(param.unitkerjaId IS NULL, parent.unitkerjaId IS NULL, parent.unitkerjaId = param.unitkerjaId)
				LEFT JOIN unit_kerja_ref AS child ON IF(param.unitkerjaId IS NULL, child.unitkerjaParentId IS NULL, child.unitkerjaParentId = param.unitkerjaId)
				AND LENGTH(child.unitkerjaKodeSistem) = LENGTH(parent.unitkerjaKodeSistem)+2
				ORDER BY
					child.unitkerjaKodeSistem DESC
				LIMIT 1) a),
		'%s','%s','%s','%s','%s')";
$sql['do_update_unitkerja'] = 
   "UPDATE unit_kerja_ref
   SET 
      unitkerjaKode = '%s',     
      unitkerjaNama = '%s',
      unitkerjaTipeunitId = '%s',
      unitKerjaUnitStatusId = '%s',
      unitkerjaParentId = '%s',
	  unitkerjaNamaPimpinan = '%s'
   WHERE 
      unitkerjaId = '%s'";
/**
 * @since 21 Desember 2012
 */
$sql['do_update_kode_sistem'] ="
UPDATE unit_kerja_ref
SET
 unitkerjaKodeSistem = CONCAT('%s',(SUBSTR(`unitkerjaKodeSistem`,LENGTH('%s')+1,LENGTH(`unitkerjaKodeSistem`))))
WHERE 
  unitkerjaKodeSistem   LIKE '%s'
  OR
  unitkerjaKodeSistem   ='%s'
";

$sql['do_update_unitkerja_old'] = 
   "UPDATE unit_kerja_ref
   SET 
      unitkerjaKode = '%s',
      unitkerjaKodeSistem=(SELECT IF((SELECT unitkerjaParentId FROM (SELECT unitkerjaParentId FROM unit_kerja_ref WHERE unitKerjaId = '%s')a)<> %s, kode, (SELECT unitkerjaKodeSistem FROM (SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitKerjaId = '%s') a)) FROM (SELECT 
					IFNULL(CONCAT(parent.unitkerjaKodeSistem,'.',(IFNULL(SUBSTRING_INDEX(child.unitkerjaKodeSistem,'.',-1),0)+1)),(SELECT (unitkerjaKodeSistem+1) AS unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitkerjaParentId = 0 ORDER BY unitkerjaId DESC LIMIT 0,1)) AS kode
				FROM
				(SELECT '%s' AS unitkerjaId) AS param
				LEFT JOIN unit_kerja_ref AS parent ON IF(param.unitkerjaId IS NULL, parent.unitkerjaId IS NULL, parent.unitkerjaId = param.unitkerjaId)
				LEFT JOIN unit_kerja_ref AS child ON IF(param.unitkerjaId IS NULL, child.unitkerjaParentId IS NULL, child.unitkerjaParentId = param.unitkerjaId)
				AND LENGTH(child.unitkerjaKodeSistem) = LENGTH(parent.unitkerjaKodeSistem)+2
				ORDER BY
					child.unitkerjaKodeSistem DESC
				LIMIT 1) a),
      unitkerjaNama = '%s',
      unitkerjaTipeunitId = '%s',
		unitKerjaUnitStatusId = '%s',
      unitkerjaParentId = '%s',
	  unitkerjaNamaPimpinan = '%s'
   WHERE 
      unitkerjaId = '%s'";

$sql['do_delete_unitkerja_by_id'] = 
   "DELETE from unit_kerja_ref
   WHERE 
      unitkerjaId='%s' OR unitkerjaParentId='%s'";

$sql['do_delete_unitkerja_by_array_id'] = 
   "DELETE from unit_kerja_ref
   WHERE 
      unitkerjaId IN ('%s')";
// OR unitkerjaParentId IN ('%s')";

$sql['get_kode_unit_kerja'] = "
SELECT `unitkerjaKode` FROM `unit_kerja_ref`
WHERE `unitkerjaId` IN ('%s')
";

$sql['get_combo_unit_kerja'] = "
   SELECT 
      unitkerjaId AS id,
      unitkerjaNama AS name
   FROM unit_kerja_ref
";

$sql['get_status_unit_kerja'] = "
	SELECT 
		unitStatusId AS `id`, 
		unitStatusNama AS `name`
	FROM
		unit_status
	";

$sql['cek_unit_parent']="
	SELECT 
		COUNT(`unitkerjaId`) as total
	FROM 
		`unit_kerja_ref` 
	WHERE 
		`unitkerjaParentId` = %s
";

$sql['generate_kode_sistem']="
SELECT 
IF((
MAX(
SUBSTR(unitkerjaKodeSistem,
	LENGTH((SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE `unitkerjaId`='%s' ))+2,
	(LENGTH(unitkerjaKodeSistem) - LENGTH((SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE `unitkerjaId`='%s' ))+2)
	 ) + 0) +1 ) > 0,
CONCAT((SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE `unitkerjaId`='%s' ),'.',
(MAX(
SUBSTR(unitkerjaKodeSistem,
	LENGTH((SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE `unitkerjaId`='%s' ))+2,
	(LENGTH(unitkerjaKodeSistem) - LENGTH((SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE `unitkerjaId`='%s' ))+2)
	 ) + 0) +1 )
)

	,
	(SELECT MAX(unitkerjaKodeSistem) FROM unit_kerja_ref WHERE `unitkerjaParentId`=0 ) +1 ) 
	AS kode
 FROM 
 	unit_kerja_ref
 WHERE 
 	`unitkerjaId` ='%s' OR `unitkerjaParentId` ='%s' 
	
";

$sql['get_kode_sistem']="
SELECT
     unitkerjaKodeSistem  as kode_sistem,
     unitkerjaParentId as parent_id
FROM 
    unit_kerja_ref
WHERE 
    unitkerjaId = '%s'

";

/**
 * unttuk tree view 
 */
$sql['get_unit_kerja_by_parent_id']="
SELECT
     unitkerjaParentId AS parent_id,
     unitkerjaKode AS unit_kode,
     unitkerjaNama AS unit_nama,
     unitkerjaId AS unit_id
FROM 
    unit_kerja_ref
WHERE 
    unitkerjaParentId ='%s'
    
ORDER BY unitkerjaKode asc
";

$sql['get_count_child']="
SELECT
     count(unitkerjaId) AS total
FROM 
    unit_kerja_ref
WHERE 
    unitkerjaParentId ='%s'

";

/**
 * query tree view baru
 */
 
$sql['get_unit_kerja_by_parent_id_v2']="
SELECT 
tb.unit_id,
tb.unit_kode,
tb.unit_nama,
tb.unit_tipe,
tb.parent_id
FROM (
/*gabungan*/
(SELECT DISTINCT
	
	m.`unitkerjaId` AS unit_id,
    m.`unitkerjaKode` AS unit_kode,
	m.`unitkerjaNama` AS unit_nama,
	m.`unitkerjaTipeunitId` AS unit_tipe,
	m.`unitkerjaParentId` AS parent_id
FROM
        `unit_kerja_ref` c 
	LEFT JOIN
		`unit_kerja_ref` m ON m.`unitkerjaId` = c.`unitkerjaParentId`
WHERE 

        ((c.`unitkerjaNama` LIKE '%s' OR  m.`unitkerjaNama` LIKE '%s' )
        AND
        (c.`unitkerjaKode` LIKE '%s' OR  m.`unitkerjaKode` LIKE '%s' )
        %s
        )
      ORDER BY m.`unitkerjaId`
)
UNION
(
SELECT 
	
	c.`unitkerjaId` AS unit_id,
    c.`unitkerjaKode` AS unit_kode,
	c.`unitkerjaNama` AS unit_nama,
	c.`unitkerjaTipeunitId` AS unit_tipe,
	c.`unitkerjaParentId` AS parent_id
FROM
        `unit_kerja_ref` c 
	LEFT JOIN
		`unit_kerja_ref` m ON m.`unitkerjaId` = c.`unitkerjaParentId`
WHERE 
	    c.`unitkerjaParentId` = 0 OR
        ((c.`unitkerjaNama` LIKE '%s' OR  m.`unitkerjaNama` LIKE '%s' )
         AND
        (c.`unitkerjaKode` LIKE '%s' OR   m.`unitkerjaKode` LIKE '%s' )
        %s
        )
      ORDER BY m.`unitkerjaId`
)
 
/*end gabungan*/
 ) tb
WHERE tb.parent_id = %s
";