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
   IF(c.`coaIsDebetPositif` = 1,'D','K') AS saldo_normal,
   (select c.coaId in(select distinct(coaParentAkun) from coa)) AS isParent
FROM
   coa c
WHERE 
   c.coaNamaAkun LIKE %s AND c.coaKodeAkun LIKE %s
AND c.coaId NOT IN (SELECT DISTINCT(coaParentAkun) FROM coa)
LIMIT %s,%s
";


$sql['get_count_coa']="
SELECT
   COUNT(coaId) AS total   
FROM
   coa c
WHERE 
   coaNamaAkun LIKE %s AND c.coaKodeAkun LIKE %s 
AND c.coaId NOT IN (SELECT DISTINCT(coaParentAkun) FROM coa)
LIMIT 1
";



?>