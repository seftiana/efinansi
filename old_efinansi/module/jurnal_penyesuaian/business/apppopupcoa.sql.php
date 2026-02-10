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
   LEFT JOIN coa d ON d.coaParentAkun = c.coaId
WHERE 
   c.coaNamaAkun LIKE %s
   AND d.coaId IS NULL
LIMIT %s,%s
";


$sql['get_count_coa']="
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



?>