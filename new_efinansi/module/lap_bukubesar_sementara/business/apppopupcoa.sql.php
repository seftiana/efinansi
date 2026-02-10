<?php
/**
* @module lap_bukubesar
* @author Rendi Kasigi,
* @mail to rkasigi@gmail.com
* @copyright 2008-2011 Gamatechno
*/

$sql['get_data_coa']="
SELECT
   coaId AS id,
   coaKodeAkun AS kode,
   coaNamaAkun AS nama
FROM
   coa
WHERE 
   coaKodeAkun LIKE %s 
   AND
   coaNamaAkun LIKE %s
ORDER BY
	coaKodeAkun ASC
LIMIT %s,%s
";

$sql['get_count_coa']="
SELECT
   COUNT(*) AS total   
FROM
   coa
WHERE 
	coaKodeAkun LIKE %s 
	AND
   	coaNamaAkun LIKE %s
LIMIT 1
";

/**
$sql['get_data_coa_old']="
SELECT
   c.coaId AS id,
   c.coaKodeAkun AS kode,
   c.coaNamaAkun AS nama,
   CONCAT(c.coaKodeAkun,' [',c.coaNamaAkun,']') AS nama_lengkap, 
   (select c.coaId in(select distinct(coaParentAkun) from coa)) AS isParent
FROM
   coa c
   LEFT JOIN coa d ON d.coaParentAkun = c.coaId
WHERE 
   c.coaNamaAkun LIKE %s
   AND d.coaId IS NULL
LIMIT %s,%s
";

$sql['get_count_coa_old']="
SELECT
   COUNT(*) AS total   
FROM
   coa c
   LEFT JOIN coa d ON d.coaParentAkun = c.coaId
WHERE 
   c.coaNamaAkun LIKE %s 
   AND d.coaId IS NULL
LIMIT 1
";

*/

?>