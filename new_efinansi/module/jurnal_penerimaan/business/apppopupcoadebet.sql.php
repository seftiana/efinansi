<?php
$sql['get_popup_coa_debet']="
SELECT
   coaId AS id,
   coaKodeAkun AS kode,
   coaNamaAkun AS name
FROM
   coa 
WHERE
   -- coaIsKas LIKE 1 
   coaId NOT IN (SELECT DISTINCT(coaParentAkun) FROM coa)
   AND (coaKodeAkun LIKE '%s' OR coaNamaAkun LIKE '%s')
   AND coaCoaKelompokId IN (1,5)
LIMIT
   %s, %s
";

$sql['get_count'] = "
SELECT
   COUNT(coaId) AS total
FROM
   coa
WHERE
   -- coaIsKas LIKE 1 
   coaId NOT IN (SELECT DISTINCT(coaParentAkun) FROM coa)
   AND (coaKodeAkun LIKE '%s' OR coaNamaAkun LIKE '%s')
   AND coaCoaKelompokId IN (1,5)
";

?>