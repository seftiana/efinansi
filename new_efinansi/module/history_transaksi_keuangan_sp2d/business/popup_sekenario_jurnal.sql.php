<?php
$sql['get_data_skenario']   = "
SELECT
   skenario.id,
   skenario.kode,
   skenario.nama,
   coaId AS akunId,
   coaKodeAkun AS akunKode,
   coaNamaAkun AS akunNama,
   IF(jurkodedtIsDebet = 0,'K','D') AS akun_dk
FROM jurnal_kode_detail
JOIN coa
   ON coaId = jurkodedtCoaId
JOIN (SELECT
   jurkodeId AS id,
   jurkodeKode AS kode,
   jurkodeNama AS nama
FROM jurnal_kode
JOIN (
   SELECT
      jurkodedtJurkodeId AS id
   FROM jurnal_kode_detail
   GROUP BY jurkodedtJurkodeId
) AS det ON det.id = jurkodeId
WHERE 1 = 1
AND jurkodeStatusAktif = 'Y'
AND jurkodeNama LIKE '%s'
LIMIT %s, %s) AS skenario
ON skenario.id = jurkodedtJurkodeId
WHERE 1 = 1
ORDER BY skenario.kode,
jurkodedtIsDebet,
jurkodedtIsDebet
";

$sql['get_count_skenario']    = "
SELECT
   COUNT(DISTINCT jurkodeId) AS `count`
FROM jurnal_kode
JOIN (
   SELECT
      jurkodedtJurkodeId AS id
   FROM jurnal_kode_detail
   GROUP BY jurkodedtJurkodeId
) AS det ON det.id = jurkodeId
WHERE 1 = 1
AND jurkodeStatusAktif = 'Y'
AND jurkodeNama LIKE '%s'
";
?>