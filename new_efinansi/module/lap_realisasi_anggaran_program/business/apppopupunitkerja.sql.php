<?php
//untuk combo box
$sql['get_data_tipe_unit'] = "
SELECT
   tipeunitId AS `id`,
   tipeunitNama AS `name`
FROM
   tipe_unit_kerja_ref
ORDER BY tipeunitNama
";

$sql['count']           = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']        = "
SELECT
   SQL_CALC_FOUND_ROWS
   unitkerjaId AS id,
   unitkerjaKode AS kode,
   unitkerjaNama AS nama,
   unitkerjaNamaPimpinan AS namaPimpinan,
   unitkerjaKodeSistem AS kodeSistem,
   unitkerjaParentId AS parentId,
   tipeunitId AS tipeId,
   tipeunitNama AS tipeNama,
   tmp_unit.status,
   tmp_unit.child,
   CASE
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN 'a'
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN 'b'
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN 'c'
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN 'd'
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN 'e'
   END AS `level`
FROM
   `unit_kerja_ref`
   JOIN (
      SELECT unitkerjaId AS id,
      CASE
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN unitkerjaKodeSistem
      END AS kode,
      COUNT(DISTINCT tmp.unitId) AS child,
      IF(COUNT(DISTINCT tmp.unitId) <> 0, 'parent', 'child') AS `status`
      FROM unit_kerja_ref
      LEFT JOIN(
         SELECT
            unitkerjaId AS unitId,
            unitkerjaKode AS unitKode,
            unitkerjaNama AS unitNama,
            unitkerjaKodeSistem AS ks
         FROM unit_kerja_ref
      ) AS tmp ON SUBSTR(tmp.ks, 1, LENGTH(CONCAT(unitkerjaKodeSistem, '.'))) = CONCAT(unitkerjaKodeSistem, '.')
      GROUP BY unitkerjaId
   ) AS tmp_unit ON tmp_unit.id = unitkerjaId
   LEFT JOIN tipe_unit_kerja_ref
      ON tipeunitId = unitkerjaTipeunitId
WHERE 1 = 1
AND unitkerjaKode LIKE '%s'
AND unitkerjaNama LIKE '%s'
AND (tipeunitId = '%s' OR 1 = %s)
AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
ORDER BY SUBSTRING_INDEX(tmp_unit.kode, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 5), '.', -1)+0
LIMIT %s, %s
";
?>