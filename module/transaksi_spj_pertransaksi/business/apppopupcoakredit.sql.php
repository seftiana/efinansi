<?php
$sql['get_popup_coa_kredit']="
SELECT
   coaId AS id,
   coaKodeAkun AS kode,
   coaNamaAkun AS name
FROM
   coa 
WHERE
   (coaKodeAkun LIKE '%s' OR coaNamaAkun LIKE '%s')
   -- AND coaIsKas = 1 
	AND coaCoaKelompokId IN (1)
   AND coaId NOT IN (SELECT DISTINCT(coaParentAkun) FROM coa)
LIMIT
   %s, %s
";

$sql['get_count'] = "
SELECT
   COUNT(coaId) AS total
FROM
   coa
WHERE
   (coaKodeAkun LIKE '%s' OR coaNamaAkun LIKE '%s')
   -- AND coaIsKas = 1 
	AND coaCoaKelompokId IN (1)
   AND coaId NOT IN (SELECT DISTINCT(coaParentAkun) FROM coa)
";
?>
