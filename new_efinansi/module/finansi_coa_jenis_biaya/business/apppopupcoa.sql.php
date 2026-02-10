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
   c.coaUnitkerjaId as unitkerja,
   (select c.coaId in(select distinct(coaParentAkun) from coa)) AS isParent
FROM
   coa c
WHERE 
   c.coaKodeAkun LIKE %s and
   c.coaNamaAkun LIKE %s

LIMIT %s,%s
";


$sql['get_count_coa']="
SELECT
   COUNT(coaId) AS total   
FROM
   coa
WHERE 
   coaKodeAkun LIKE %s and
   coaNamaAkun LIKE %s
LIMIT 1
";

$sql['get_unitkerja_by_id']="
SELECT
   unitkerjaNama AS nama   
FROM
   unit_kerja_ref
WHERE 
   unitkerjaId = %s
LIMIT 1
";


?>