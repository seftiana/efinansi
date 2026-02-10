<?php


$sql['get_kellap_ref_id'] ="
SELECT
 cklr.`coakellapIdKellap` AS kellap_main,
  cklr.`coakellapIdKellapRef` AS kellap_ref
FROM
  `kelompok_laporan_ref` klr
   JOIN coa_kelompok_laporan_ref cklr ON cklr.`coakellapIdKellap` = klr.`kellapId`
WHERE
  `kellapKodeSistem` LIKE '%s'
  AND
  cklr.`coakellapIdKellapRef` IS NOT NULL
";

$sql['get_kellap_ref_data'] ="
SELECT
  cklr.`coakellapIdKellap` AS kellap_main_id,
  klr.`kellapId` AS kellap_id,  
  klr.`kellapNama` AS kellap_nama,
  klr.`kellapKodeSistem` AS kellap_kode_sistem,
  klr.`kellapIsTambah` AS kellap_is_tambah,
  klr.`kellapIsSummary` AS kellap_is_summary,
  klr.`kellapSummaryDetail` AS kellap_summary_detail
FROM
    `kelompok_laporan_ref` klr
    JOIN coa_kelompok_laporan_ref cklr ON cklr.`coakellapIdKellapRef` = klr.`kellapId`
WHERE
    (kellapId  IN (%s) OR 1=%s)
    AND
    (cklr.`coakellapIdKellap` IN (%s) OR 1=%s)
";


$sql['get_kellap_ref_kode_sistem'] ="
SELECT
    klr.`kellapKodeSistem` AS kode_sistem
FROM 
    kelompok_laporan_ref klr
WHERE 
    klr.`kellapId` ='%s'
";

$sql['get_kellap_ref_data_coa'] ="
SELECT
  [kellap_id_main] AS kellap_main_id,
  [kellap_main_is_summary] AS kellap_main_is_summary,
  cklr.`coakellapIdKellap` AS kellap_id,
  klr.`kellapKodeSistem` AS kellap_kode_sistem,
  klr.`kellapTipe` AS kellap_tipe,
  klr.`kellapIsTambah` AS kellap_is_tambah,
  cklr.`coaKellapIsSaldoAwal` AS kellap_is_saldo_awal,
  cklr.`coakellapIsMutasiDK` AS kellap_is_mutasi_dk,
  cklr.`coaKellapIsPositif` AS kellap_is_positif,
  cklr.`coakellapIsMutasiD` AS kellap_is_mutasi_d,
  cklr.`coakellapIsMutasiK` AS kellap_is_mutasi_k,
  cklr.`coakellapCoaId` AS kellap_coa_id
FROM
  `kelompok_laporan_ref` klr
  JOIN coa_kelompok_laporan_ref cklr
    ON cklr.`coakellapIdKellap` = klr.`kellapId`
WHERE
  klr.`kellapTipe` ='child'
  AND
  kellapIsSummary = 'T'
  /* --- digunakan untuk melihat data laporan setelah posting ---
    AND
    cklr.`coakellapCoaId` IN (SELECT bhis.`bbCoaId` FROM buku_besar_his bhis)
  */
";

?>