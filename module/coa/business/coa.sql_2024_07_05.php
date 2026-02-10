<?php
$sql['get_combo_coa'] = "
   SELECT 
      concat(coaId,'-',coaLevelAkun) AS id,
      concat(coaKodeAkun,' [',coaNamaAkun,']') AS name
      FROM coa 
   ORDER BY name ASC
";

$sql['get_list_coa'] = "
   SELECT
      coaId,
      coaKodeAkun,
      coaNamaAkun,
      coaLevelAkun,
      coaParentAkun,
      coaIsDebetPositif,
      coaIsKas,
      coaIsLocked
   FROM coa
   ORDER BY
   CASE
      WHEN coaKodeAkun REGEXP '^[a-zA-Z]+' THEN
         0
      ELSE
         CAST(SUBSTRING_INDEX(coaKodeAkun, '-', 1) AS UNSIGNED)
   END,
   CAST(LENGTH(SUBSTRING_INDEX(coaKodeAkun, '-', 1)) AS UNSIGNED),
   CASE
      WHEN coaKodeAkun LIKE '%%-%%' THEN
         CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(coaKodeAkun, '-', -1), '-', 1) AS UNSIGNED)
      ELSE
         0
   END,
   coaKodeAkun
";

$sql['get_list_coa_2'] = "
   SELECT
      coaId,
      coaKodeAkun,
      coaNamaAkun,
      coaLevelAkun,
      coaParentAkun,
      coaIsDebetPositif,
      coaIsKas,
      coaIsLocked
      FROM coa
   ORDER BY
   CASE
      WHEN coaKodeAkun REGEXP '^[a-zA-Z]+' THEN
         0
      ELSE
         CAST(SUBSTRING_INDEX(coaKodeAkun, '-', 1) AS UNSIGNED)
   END,
   CAST(LENGTH(SUBSTRING_INDEX(coaKodeAkun, '-', 1)) AS UNSIGNED),
   CASE
      WHEN coaKodeAkun LIKE '%%-%%' THEN
         CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(coaKodeAkun, '-', -1), '-', 1) AS UNSIGNED)
      ELSE
         0
   END,
   coaKodeAkun
";

$sql['get_coa_from_nama_kode_count'] = "
    SELECT FOUND_ROWS() AS total
";

$sql['get_coa_from_nama_kode'] = "
   SELECT
      SQL_CALC_FOUND_ROWS
      coaId,
      coaKodeAkun,
      coaNamaAkun,
      coaLevelAkun,
      coaParentAkun,
      coaIsDebetPositif,
      coaIsKas,
      coaIsLocked
      FROM coa
   WHERE
      (coaKodeAkun LIKE '%s' OR coaNamaAkun LIKE '%s')
      AND (coaIsKas = 1 OR %s)
      AND (`coaIsLabaRugiThJln` = 1  OR  %s)
      AND (`coaIsLabaRugiThAwal` = 1  OR  %s)
   ORDER BY
   CASE
      WHEN coaKodeAkun REGEXP '^[a-zA-Z]+' THEN
         0
      ELSE
         CAST(SUBSTRING_INDEX(coaKodeAkun, '-', 1) AS UNSIGNED)
   END,
   CAST(LENGTH(SUBSTRING_INDEX(coaKodeAkun, '-', 1)) AS UNSIGNED),
   CASE
      WHEN coaKodeAkun LIKE '%%-%%' THEN
         CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(coaKodeAkun, '-', -1), '-', 1) AS UNSIGNED)
      ELSE
         0
   END,
   coaKodeAkun
   LIMIT %s, %s
";

$sql['get_coa_from_id'] = 
"
   SELECT 
      coaId,      
      coaKodeAkun,
      coaNamaAkun,
      coaLevelAkun,
      coaParentAkun,
      coaIsDebetPositif,
      coaIsKas,
      coaIsLabaRugiThJln,
      coaIsLabaRugiThAwal,
      coaIsLocked,
      coaIsDepMasuk,
      /*(SELECT updCoaPrecode  FROM upd_ref WHERE updId = coaUpdId) AS updCoaPrecode,*/
	  coatipecoaCtrId
      FROM coa 
	  LEFT JOIN coa_tipe_coa ON (coatipecoaCoaId = coaId)
   WHERE coaId = '%s'
";

$sql['get_coa_unit_kerja_by_coa_id'] = "
SELECT
   unitkerjaId AS id,
   unitkerjaKode AS kode,
   unitkerjaNama AS nama
FROM coa_unit_kerja
JOIN unit_kerja_ref
   ON unitkerjaId = coaUnitUnitkerjaId
WHERE coaUnitCoaId = %s
";

$sql['get_coa_tipe_coa_by_coa_id'] = "
   SELECT 
      coatipecoaCoaId as coa_id,
      coatipecoaCtrId as tipe_coa_id,
	  ctrNamaTipe as nama_tipe
   FROM 
	coa_tipe_coa 
	JOIN coa_tipe_ref ON (ctrId = coatipecoaCtrId)
   WHERE 
	coatipecoaCoaId='%s'
";

$sql['get_coa_tipe_ref'] = "
   SELECT 
      ctrId as id,
      ctrNamaTipe as name,
	  ctrCrash as crash_id
   FROM 
	coa_tipe_ref 
";

$sql['get_coa_tipe_ref_by_id'] = "
   SELECT 
      ctrId as id,
      ctrNamaTipe as name,
	  ctrCrash as crash_id
   FROM 
	coa_tipe_ref 
	WHERE ctrId='%s'
";

$sql['get_coa_tipe_ref_by_array_crash_id'] = "
   SELECT 
      ctrId as id,
      ctrNamaTipe as name,
	  ctrCrash as crash_id
   FROM 
	coa_tipe_ref 
	WHERE ctrCrash IN ('%s')
";

$sql['get_combo_unit_kerja'] = "
   SELECT 
      unitkerjaId as id,
      unitkerjaNama as name
   FROM 
	unit_kerja_ref
	ORDER BY unitkerjaId
";

$sql['generate_kode_sistem']="
SELECT
IF((
MAX(
SUBSTR(coaKodeSistem,
LENGTH((SELECT coaKodeSistem FROM coa WHERE `coaId`='%s' ))+2,
(LENGTH(coaKodeSistem) - LENGTH((SELECT coaKodeSistem FROM coa WHERE `coaId`='%s' ))+2)
) + 0) +1 ) > 0,
CONCAT((SELECT coaKodeSistem FROM coa WHERE `coaId`='%s' ),'.',
(MAX(
SUBSTR(coaKodeSistem,
LENGTH((SELECT coaKodeSistem FROM coa WHERE `coaId`='%s' ))+2,
(LENGTH(coaKodeSistem) - LENGTH((SELECT coaKodeSistem FROM coa WHERE `coaId`='%s' ))+2)
) + 0) +1 )
),
(SELECT IF(MAX(coaKodeSistem) IS NULL,0,MAX(coaKodeSistem)) FROM coa WHERE `coaParentAkun`=0) +1 )
AS kodeSistem
FROM
coa
WHERE
`coaId` ='%s' OR `coaParentAkun` ='%s'
";

$sql['update_coa_kodesistem_null']="
UPDATE coa
SET coaKodeSistem = NULL
";

$sql['update_coa_kodesistem']="
UPDATE coa
SET coaKodeSistem = '%s'
WHERE coaId = %s
";

$sql['insert_coa_old'] = "
INSERT INTO coa (  
		coaKodeSistem,
      coaKodeAkun,
      coaNamaAkun,
      coaLevelAkun,
      coaParentAkun,
      coaIsDebetPositif,
      coaIsKas,
      coaIsLabaRugiThJln,
      coaUserId,
      coaCoaKelompokId,
      coaUnitkerjaId
   )
   SELECT (SELECT kode FROM (
SELECT 
	IFNULL(CONCAT(parent.coaKodeSistem,'.',(IFNULL(SUBSTRING_INDEX(child.coaKodeSistem,'.',-1),0)+1)),(SELECT (coaKodeSistem+1) AS coaKodeSistem FROM coa WHERE coaParentAkun = 0 ORDER BY coaId DESC LIMIT 0,1)) AS kode
FROM
(SELECT '%s' AS coaId) AS param
LEFT JOIN coa AS parent ON IF(param.coaId IS NULL, parent.coaId IS NULL, parent.coaId = param.coaId)
LEFT JOIN coa AS child ON IF(param.coaId IS NULL, child.coaParentAkun IS NULL, child.coaParentAkun = param.coaId)
AND LENGTH(child.coaKodeSistem) = LENGTH(parent.coaKodeSistem)+2
ORDER BY
	child.coaKodeSistem DESC
LIMIT 1

) a),'%s','%s','%s','%s','%s','%s','%s','%s', coaCoaKelompokId,
(SELECT userunitkerjaUnitkerjaId FROM user_unit_kerja WHERE userunitkerjaUserId = '%s') FROM coa WHERE coaId = %s;
";

$sql['insert_coa'] = "
INSERT INTO coa
SET
	coaKodeSistem = '%s',
	coaKodeAkun = '%s',
	coaNamaAkun = '%s',
	coaLevelAkun = '%s',
	coaParentAkun = '%s',
	coaIsDebetPositif = '%s',
	coaIsKas = '%s',
    coaIsLabaRugiThJln = '%s',
    coaIsLabaRugiThAwal = '%s',
    coaIsDepMasuk = '%s',
	coaUserId = '%s',
	coaUnitkerjaId = (SELECT userunitkerjaUnitkerjaId FROM user_unit_kerja WHERE userunitkerjaUserId = '%s'),
	coaCoaKelompokId = (SELECT a.coaCoaKelompokId FROM coa a WHERE a.coaId = %s)
";

$sql['insert_coa_tipe_coa'] = "
   REPLACE INTO coa_tipe_coa (
      coatipecoaCoaId,
      coatipecoaCtrId
   )
   VALUES %s
";

$sql['insert_coa_unit_kerja'] = "
INSERT INTO coa_unit_kerja
SET
   coaUnitCoaId = '%s',
   coaUnitUnitkerjaId = '%s'
";

$sql['delete_coa_unit_kerja'] = "
DELETE FROM coa_unit_kerja WHERE coaUnitCoaId = '%s'
";

$sql['delete_unused_coa_tipe_coa'] = "
   DELETE FROM coa_tipe_coa WHERE coatipecoaCoaId = '%s' AND coatipecoaCtrId NOT IN('%s');
";


$sql['update_coa'] = "
UPDATE coa SET     
	  coaKodeSistem = '%s',
      coaKodeAkun = '%s',
      coaNamaAkun = '%s',
      coaLevelAkun = '%s',
      coaParentAkun = '%s',
      coaIsDebetPositif = '%s',
      coaIsKas = '%s',
      coaIsLabaRugiThJln = '%s',
      coaIsLabaRugiThAwal = '%s',
      coaIsDepMasuk = '%s',
      coaUserId = '%s',
      coaCoaKelompokId = (SELECT coaCoaKelompokId FROM (SELECT a.coaCoaKelompokId FROM coa a WHERE a.coaId = %s) a)
   WHERE coaId = '%s';
";

$sql['delete_coa_tipe_coa'] = "
	DELETE FROM coa_tipe_coa
	WHERE coatipecoaCtrId IN ('%s')
	AND coatipecoaCoaId='%s'
";

$sql['delete_coa_tipe_coa'] = "
	DELETE FROM coa_tipe_coa
	WHERE coatipecoaCoaId='%s'
";

//untuk cek coa yang LR / LR at(awal tahun)
$sql['get_num_coa_lr']="
SELECT
  COUNT(`coaId`) AS c_lr
FROM `coa`
WHERE 
`coaIsLabaRugiThJln` = %s
";

$sql['get_num_coa_lr_at']="
SELECT
  COUNT(`coaId`) AS c_lr_at
FROM `coa`
WHERE 
`coaIsLabaRugiThAwal` = %s
";

//untuk cek coa yang Deposit Masuk
$sql['get_num_coa_dep_masuk']="
SELECT
  coaId AS c_id,
  coaKodeAkun AS c_kode,
  coaNamaAkun AS c_nama
FROM `coa`
WHERE 
`coaIsDepMasuk` = 1
LIMIT 1
";

$sql['get_unit_kerja_ref'] = "
SELECT
   SQL_CALC_FOUND_ROWS
   unitkerjaId AS id,
   unitkerjaKode AS kode,
   unitkerjaNama AS nama,
   unitkerjaNamaPimpinan AS namaPimpinan,
   unitkerjaKodeSistem AS kodeSistem,
   unitkerjaParentId AS parentId,
   IFNULL(child.count, 0) AS child
FROM
   `unit_kerja_ref`
   JOIN (
      SELECT unitkerjaId AS id,
      CASE
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN unitkerjaKodeSistem
      END AS kode
      FROM unit_kerja_ref
   ) AS tmp ON tmp.id = unitkerjaId
   LEFT JOIN tipe_unit_kerja_ref
      ON tipeunitId = unitkerjaTipeunitId
   LEFT JOIN(
      SELECT
         unitkerjaParentId AS id,
         COUNT(unitkerjaId) AS `count`
      FROM unit_kerja_ref
      GROUP BY unitkerjaParentId
   ) AS child ON child.id = unitkerjaId
ORDER BY SUBSTRING_INDEX(tmp.kode, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 5), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 6), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 7), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 8), '.', -1)+0
";
