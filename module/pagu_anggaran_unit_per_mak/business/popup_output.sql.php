<?php
$sql['get_output']  = "
SELECT 
   output.rkaklOutputId AS id,
   output.rkaklOutputKegiatanId AS kegiatan_id,
   output.rkaklOutputKode AS kode,
   output.rkaklOutputNama AS nama,
   keg.rkaklKegiatanId AS keg_id,
   keg.rkaklKegiatanKode AS keg_kode,
   keg.rkaklKegiatanNama AS keg_nama 
FROM
   finansi_ref_rkakl_output AS output 
   LEFT JOIN finansi_ref_rkakl_kegiatan AS keg 
      ON output.rkaklOutputKegiatanId = keg.rkaklKegiatanId 
WHERE ((output.rkaklOutputKode LIKE '%s' OR output.rkaklOutputNama LIKE '%s') OR 1 = %s) 
AND ((keg.`rkaklKegiatanKode` LIKE '%s' OR keg.`rkaklKegiatanNama` LIKE '%s') OR 1 = %s)
ORDER BY keg.rkaklKegiatanId, output.rkaklOutputKode ASC 
LIMIT %s, %s
";
$sql['count_output']    = "
SELECT
   COUNT(DISTINCT rkaklOutputId) AS total
FROM
   finansi_ref_rkakl_output AS output 
   LEFT JOIN finansi_ref_rkakl_kegiatan AS keg 
      ON output.rkaklOutputKegiatanId = keg.rkaklKegiatanId 
WHERE ((output.rkaklOutputKode LIKE '%s' OR output.rkaklOutputNama LIKE '%s') OR 1 = %s) 
AND ((keg.`rkaklKegiatanKode` LIKE '%s' OR keg.`rkaklKegiatanNama` LIKE '%s') OR 1 = %s)
";
?>
