<?php
/**
 * @package SQL-FILE
 */
$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_pagu_bas'] = "
SELECT
   SQL_CALC_FOUND_ROWS paguBasId AS id,
   paguBasKode AS kode,
   paguBasKeterangan AS nama
FROM
   finansi_ref_pagu_bas
WHERE 1 = 1
   AND paguBasParentId = 0
   AND paguBasKode LIKE '%s'
   AND paguBasKeterangan LIKE '%s'
ORDER BY paguBasKode
LIMIT %s, %s
";

$sql['get_coa']      = "
SELECT SQL_CALC_FOUND_ROWS
   coaId AS akunId,
   IF(coaKodeSistem REGEXP '^[[.period.]]', SUBSTR(coaKodeSistem,2), coaKodeSistem) AS kodeSistem,
   coaKodeAkun AS akunKode,
   coaNamaAkun AS akunNama,
   IFNULL(tmp_coa.count, 0) AS child
FROM
   coa
   JOIN
      (SELECT
         coaId AS id,
         @kode_sistem := IF(coaKodeSistem REGEXP '^[[.period.]]', SUBSTR(coaKodeSistem,2), coaKodeSistem) AS ks,
         CASE
            WHEN @kode_sistem REGEXP '^([0-9]{1,3})$' THEN CONCAT(@kode_sistem, '.0.0.0.0.0.0.0.0.0')
            WHEN @kode_sistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(@kode_sistem, '.0.0.0.0.0.0.0.0')
            WHEN @kode_sistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(@kode_sistem, '.0.0.0.0.0.0.0')
            WHEN @kode_sistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(@kode_sistem, '.0.0.0.0.0.0')
            WHEN @kode_sistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(@kode_sistem, '.0.0.0.0.0')
            WHEN @kode_sistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(@kode_sistem, '.0.0.0.0')
            WHEN @kode_sistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(@kode_sistem, '.0.0.0')
            WHEN @kode_sistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(@kode_sistem, '.0.0')
            WHEN @kode_sistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(@kode_sistem, '.0')
            WHEN @kode_sistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN @kode_sistem
         END AS kode
      FROM coa) AS tmp ON tmp.id = coaId
   LEFT JOIN
      (SELECT
         coaParentAkun AS id,
         COUNT(coaId) AS `count`
      FROM
         coa
      GROUP BY coaParentAkun) AS tmp_coa
      ON tmp_coa.id = coaId
WHERE 1 = 1
AND coaKodeAkun LIKE '%s'
AND coaNamaAkun LIKE '%s'
HAVING child IS NULL
   OR child = 0
ORDER BY SUBSTRING_INDEX(tmp.kode, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 5), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 6), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 7), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 8), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 9), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 10), '.', -1)+0
LIMIT %s, %s
";
?>