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
AND coaId IN (
   SELECT
      coaUnitCoaId
   FROM coa_unit_kerja
   WHERE coaUnitUnitkerjaId = '%s'
)
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
?>