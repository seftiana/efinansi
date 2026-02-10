<?php
/**
* @module skenario
* @author Rendi Kasigi,
* @mail to rkasigi@gmail.com
* @copyright 2008&copy;Gamatechno
*/

$sql['get_data_coa']="
SELECT
   c.coaId AS id,
   c.coaKodeAkun AS kode,
   c.coaNamaAkun AS nama,
   (select c.coaId in(select distinct(coaParentAkun) from coa)) AS isParent
FROM
   coa c
WHERE 
   (c.coaNamaAkun LIKE '%s' OR c.coaKodeAkun like '%s')
   -- AND coaIsDebetPositif = 1
   -- AND coaCoaKelompokId <> 1
   AND c.coaCoaKelompokId IN (1,5)
   -- AND coaParentAkun <> 0
   AND c.coaId NOT IN (SELECT DISTINCT(coaParentAkun) FROM coa)
LIMIT %s,%s
";


$sql['get_count_coa']="
SELECT
   COUNT(coaId) AS total   
FROM
   coa
WHERE 
   (coaNamaAkun LIKE '%s' OR coaKodeAkun like '%s')
   -- AND coaIsDebetPositif = 1
   -- AND coaCoaKelompokId <> 1
   -- AND coaParentAkun <> 0
   AND coaCoaKelompokId IN (1,5)
   AND coaId NOT IN (SELECT DISTINCT(coaParentAkun) FROM coa)
LIMIT 1
";



?>
