<?php
/**
 * @package SQL-FILE
 */
$sql['get_tipe_unit']   = "
SELECT
   tipeunitId AS `id`,
   tipeunitNama AS `name`
FROM tipe_unit_kerja_ref
WHERE 1 = 1
ORDER BY tipeunitNama ASC
";

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_unit']   = "
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
   SUBSTRING_INDEX(tmp.kode, '.', 1)+0 AS a,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 2), '.', -1)+0 AS b,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 3), '.', -1)+0 AS c,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 4), '.', -1)+0 AS d,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 5), '.', -1)+0 AS e
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
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '1')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '1') OR unitkerjaId = '1'
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
?>