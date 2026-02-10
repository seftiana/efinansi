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
   c.coaNamaAkun LIKE %s AND c.coaKodeAkun LIKE %s
AND c.coaId NOT IN (SELECT DISTINCT(coaParentAkun) FROM coa)
AND c.coaId NOT IN(SELECT `coaAlokasiAkademikCoaId` FROM `finansi_keu_coa_alokasi_akademik`)
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
AND c.coaId NOT IN(SELECT `coaAlokasiAkademikCoaId` FROM `finansi_keu_coa_alokasi_akademik`)
LIMIT 1
";



?>
