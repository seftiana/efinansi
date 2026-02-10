<?php
$sql['count']		= "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_count_data_unitkerja'] = "
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
$sql['get_data_unitkerja']="
SELECT
   SQL_CALC_FOUND_ROWS unitkerjaNama AS satker,
   unitkerjaId AS id,
   unitkerjaKode AS kodeunit,
   unitkerjaNama AS unit,
   tipeunitNama AS tipeunit,
   unitkerjaParentId AS parentId,
   unitkerjaKodeSistem AS kodesistem
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
WHERE 1 = 1
AND unitkerjaKode LIKE '%s'
AND unitkerjaNama LIKE '%s'
AND (tipeunitId = '%s' OR 1 = %s)
AND SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s'
ORDER BY SUBSTRING_INDEX(tmp.kode, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 5), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 6), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 7), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 8), '.', -1)+0
LIMIT %s, %s
";

//untuk combo box

$sql['get_data_tipe_unit'] =
   "SELECT
      tipeunitId		as id,
	  tipeunitNama		as name
   FROM
      tipe_unit_kerja_ref
   ORDER BY
      tipeunitNama";