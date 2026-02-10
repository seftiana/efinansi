<?php
/**
* @module skenario
* @author Rendi Kasigi,
* @mail to rkasigi@gmail.com
* @copyright 2008&copy;Gamatechno
*/

$sql['count']        = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']     = "
SELECT SQL_CALC_FOUND_ROWS
   coaId AS id,
   coaKodeAkun AS kode,
   coaNamaAkun AS nama,
   IF(tmp.count IS NOT NULL, 'parent', 'child') AS `status`,
   tmp_coa.kodeSistem
FROM coa
LEFT JOIN (SELECT
   COUNT(coaId) AS `count`,
   coaParentAkun AS id
FROM coa
GROUP BY coaParentAkun
) AS tmp ON tmp.id = coaId
JOIN (SELECT
   coaId AS id,
   CASE
      WHEN coaKodeSistem REGEXP '^([0-9]{1,3})$' THEN CONCAT(coaKodeSistem, '.0.0.0.0.0.0.0')
      WHEN coaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(coaKodeSistem, '.0.0.0.0.0.0')
      WHEN coaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(coaKodeSistem, '.0.0.0.0.0')
      WHEN coaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(coaKodeSistem, '.0.0.0.0')
      WHEN coaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(coaKodeSistem, '.0.0.0')
      WHEN coaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(coaKodeSistem, '.0.0')
      WHEN coaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(coaKodeSistem, '.0')
      WHEN coaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN coaKodeSistem
   END AS kodeSistem
FROM coa) AS tmp_coa ON tmp_coa.id = coaId
WHERE 1 = 1
AND (tmp.count IS NULL OR tmp.count = 0)
AND coaKodeAkun LIKE '%s'
AND coaNamaAkun LIKE '%s'
ORDER BY SUBSTRING_INDEX(tmp_coa.kodeSistem, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_coa.kodeSistem, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_coa.kodeSistem, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_coa.kodeSistem, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_coa.kodeSistem, '.', 5), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_coa.kodeSistem, '.', 6), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_coa.kodeSistem, '.', 7), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_coa.kodeSistem, '.', 8), '.', -1)+0
LIMIT %s, %s
";
?>