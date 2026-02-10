<?php
/**
* @module skenario
* @author Rendi Kasigi,
* @mail to rkasigi@gmail.com
* @copyright 2008&copy;Gamatechno
*/

$sql['get_data_coa']="
SELECT * FROM(
SELECT
   c.coaId AS id,
   c.coaKodeAkun AS kode,
   c.coaNamaAkun AS nama,
   (select c.coaId in(select distinct(coaParentAkun) from coa)) AS isParent
FROM
   coa c
WHERE 
   c.coaNamaAkun LIKE %s)a
WHERE isParent= 0
LIMIT %s,%s
";


$sql['get_count_coa']="
SELECT  COUNT(id) AS total  FROM(
SELECT
   c.coaId AS id,
   c.coaKodeAkun AS kode,
   c.coaNamaAkun AS nama,
   (select c.coaId in(select distinct(coaParentAkun) from coa)) AS isParent
FROM
   coa c
WHERE 
   c.coaNamaAkun LIKE %s)a
WHERE isParent= 0
LIMIT 1
";



?>