<?php


$sql['get_periode_pembukuan'] ="
SELECT 
  `tppId` AS `tpp_id`,
  IF(YEAR(`tppTanggalAwal`) = YEAR(`tppTanggalAkhir`),
	YEAR(`tppTanggalAwal`),
	CONCAT(YEAR(`tppTanggalAwal`),'-',YEAR(`tppTanggalAkhir`))) AS `nama_periode`,
  `tppTanggalAwal` AS tanggal_awal,
  `tppTanggalAkhir` AS tanggal_akhir
FROM
  `tahun_pembukuan_periode`
WHERE
   `tppIsBukaBuku` = 'Y'
ORDER BY `nama_periode` DESC
";


$sql['get_minmax_tahun_transaksi'] = "
   SELECT
      YEAR(MIN(transTanggalEntri)) - 5 AS minTahun,
      YEAR(MAX(transTanggalEntri)) + 5 AS maxTahun
   FROM
      transaksi
";

$sql['get_periode_nama'] ="
SELECT 
    `tppId` AS `tpp_id`,
    IF(YEAR(`tppTanggalAwal`) = YEAR(`tppTanggalAkhir`), YEAR(`tppTanggalAwal`),
	CONCAT(YEAR(`tppTanggalAwal`),'-',YEAR(`tppTanggalAkhir`))) AS `nama_periode`,
    IF(YEAR(`tppTanggalAwal`) = YEAR(`tppTanggalAkhir`),(YEAR(`tppTanggalAwal`) -1 ),
	CONCAT((YEAR(`tppTanggalAwal`) -1 ),'-',(YEAR(`tppTanggalAkhir`) -1 ))) AS `nama_periode_ts`
FROM
  `tahun_pembukuan_periode`
WHERE
  `tppId` = '%s'
ORDER BY `nama_periode` DESC
";


/**
 * get data pengelompokan laporan
 */
$sql['get_susunan_laporan'] ="
SELECT
  `kellapId` AS kellap_id,
  `kellapKodeSistem` AS kellap_ks,
  `kellapParentId` AS kellap_pid,
  `kellapNama` AS kellap_nama,
  `kellapLevel` AS kellap_level,
  `kellapOrderBy` AS kellap_ord,
  `kellapKelompok` AS kellap_kelompok,
  `kellapIsTambah` AS kellap_is_tambah,
  `kellapIsSummary` AS kellap_is_summary,
  `kellapSummaryDetail` AS kellap_summary_detail
FROM
  `kelompok_laporan_ref`
WHERE 
  `kellapKodeSistem` LIKE '%s'
ORDER BY
  kellapParentId,
  kellapOrderBy
";

$sql['get_kelompok_laporan_coa_detail'] ="
SELECT
  klr.`kellapTppId` AS kellap_tpp_id,
  klr.`kellapId` AS kellap_id,
  cklr.`coakellapCoaId` AS kellap_coa_id,
  c.`coaKodeAkun` AS kellap_coa_kode,
  c.`coaNamaAkun` AS kellap_coa_nama,
  cklr.`coaKellapIsSaldoAwal` AS kellap_is_saldo_awal,
  cklr.`coakellapIsMutasiDK` AS kellap_is_mutasi_dk,
  cklr.`coaKellapIsPositif` AS kellap_is_positif,
  cklr.`coakellapIsMutasiD` AS kellap_is_mutasi_d,
  cklr.`coakellapIsMutasiK` AS kellap_is_mutasi_k
FROM
  `kelompok_laporan_ref` klr
  JOIN coa_kelompok_laporan_ref cklr
    ON cklr.`coakellapIdKellap` = klr.`kellapId`
  JOIN coa c ON c.`coaId` = cklr.`coakellapCoaId`
WHERE
klr.`kellapId` = '%s'
";


$sql['get_kelompok_laporan_coa_detail_sub_account'] ="
SELECT
  klr.`kellapTppId` AS kellap_tpp_id,
  klr.`kellapId` AS kellap_id,
  cklr.`coakellapCoaId` AS kellap_coa_id,
  c.`coaKodeAkun` AS kellap_coa_kode,
  c.`coaNamaAkun` AS kellap_coa_nama,
  cklr.`coaKellapIsSaldoAwal` AS kellap_is_saldo_awal,
  cklr.`coakellapIsMutasiDK` AS kellap_is_mutasi_dk,
  cklr.`coaKellapIsPositif` AS kellap_is_positif,
  cklr.`coakellapIsMutasiD` AS kellap_is_mutasi_d,
  cklr.`coakellapIsMutasiK` AS kellap_is_mutasi_k,
  CONCAT_WS('-',
    IFNULL(bhis.`bbhisSubaccPertamaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeduaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKetigaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeempatKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKelimaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeenamKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKetujuhKode`,'00')) AS kellap_sub_acc
FROM
  `kelompok_laporan_ref` klr
  JOIN coa_kelompok_laporan_ref cklr
    ON cklr.`coakellapIdKellap` = klr.`kellapId`
  JOIN coa c ON c.`coaId` = cklr.`coakellapCoaId`
  LEFT JOIN buku_besar_his bhis ON bhis.`bbCoaId` = cklr.`coakellapCoaId`
  [TANGGAL]
WHERE
klr.`kellapId` = '%s'
GROUP BY kellap_coa_id,kellap_sub_acc
HAVING (kellap_sub_acc LIKE '%s' OR 1 = %s)
ORDER BY
CASE
    WHEN coaKodeAkun REGEXP '^[a-zA-Z]+' THEN
        0
    ELSE
        CAST(SUBSTRING_INDEX(coaKodeAkun, '-', 1) AS UNSIGNED)
END,
CAST(LENGTH(SUBSTRING_INDEX(coaKodeAkun, '-', 1)) AS UNSIGNED),
CASE
    WHEN coaKodeAkun LIKE '%%-%%' THEN
        CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(coaKodeAkun, '-', -1), '-', 1) AS UNSIGNED)
    ELSE
        0
END,
coaKodeAkun
";

$sql['get_kelompok_laporan_coa_ref'] ="
SELECT
  klr.`kellapTppId` AS kellap_tpp_id,
  klr.`kellapId` AS kellap_id,
  cklr.`coakellapCoaId` AS kellap_coa_id,
  cklr.`coaKellapIsSaldoAwal` AS kellap_is_saldo_awal,
  cklr.`coakellapIsMutasiDK` AS kellap_is_mutasi_dk,
  cklr.coakellapIdKellapRef AS kellap_ref,
  cklr.`coaKellapIsPositif` AS kellap_is_positif,
  cklr.`coakellapIsMutasiD` AS kellap_is_mutasi_d,
  cklr.`coakellapIsMutasiK` AS kellap_is_mutasi_k
FROM 
`kelompok_laporan_ref` klr
JOIN coa_kelompok_laporan_ref cklr ON cklr.`coakellapIdKellap` = klr.`kellapId`
    WHERE
    klr.`kellapKodeSistem` LIKE '%s'
";

$sql['get_kelompok_laporan_coa_ref_sub_account'] ="
SELECT
  klr.`kellapTppId` AS kellap_tpp_id,
  klr.`kellapId` AS kellap_id,
  cklr.`coakellapCoaId` AS kellap_coa_id,
  cklr.`coaKellapIsSaldoAwal` AS kellap_is_saldo_awal,
  cklr.`coakellapIsMutasiDK` AS kellap_is_mutasi_dk,
  cklr.coakellapIdKellapRef AS kellap_ref,
  cklr.`coaKellapIsPositif` AS kellap_is_positif,
  cklr.`coakellapIsMutasiD` AS kellap_is_mutasi_d,
  cklr.`coakellapIsMutasiK` AS kellap_is_mutasi_k,
  CONCAT_WS('-',
    IFNULL(bhis.`bbhisSubaccPertamaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeduaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKetigaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeempatKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKelimaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeenamKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKetujuhKode`,'00')) AS kellap_sub_acc
FROM 
    `kelompok_laporan_ref` klr
    JOIN coa_kelompok_laporan_ref cklr ON cklr.`coakellapIdKellap` = klr.`kellapId`
    JOIN coa c ON c.`coaId` = cklr.`coakellapCoaId`
    LEFT JOIN buku_besar_his bhis ON bhis.`bbCoaId` = cklr.`coakellapCoaId`
WHERE
    klr.`kellapKodeSistem` LIKE '%s'
GROUP BY kellap_id,kellap_coa_id,kellap_sub_acc
HAVING (kellap_sub_acc LIKE '%s' OR 1 = %s)
";

$sql['get_kode_sistem'] ="
SELECT
    klr.`kellapKodeSistem` AS kode_sistem
FROM kelompok_laporan_ref klr
WHERE
    klr.`kellapId` = %s
";

$sql['get_kelompok_info'] ="
SELECT
  `kellapId` AS kellap_id,
  `kellapNama` AS kellap_nama,
  `kellapIsTambah` AS kellap_is_tambah
FROM `kelompok_laporan_ref`
WHERE kellapId = %s
";


//untnuk laporan referensi ke kelompok laporan lain
$sql['get_kelompok_ref'] = "
SELECT
    klr.`kellapId` AS kellap_id,
    klr.`kellapIsSummary` AS is_summary,
    klr.`kellapSummaryDetail` AS summary_detail
FROM
    `kelompok_laporan_ref` klr
WHERE klr.`kellapId` IN (%s)
";

$sql['get_kelompok_ref_coa_collection'] ="
SELECT
  `coakellapIdKellap` AS kellap_id,
  `coakellapCoaId` AS kellap_coa_id,
  `coaKellapIsSaldoAwal` AS kellap_is_saldo_awal,
  `coakellapIsMutasiDK` AS kellap_is_mutasi_dk,
  `coaKellapIsPositif` AS kellap_is_positif,
  `coakellapIsMutasiD` AS kellap_is_mutasi_d,
  `coakellapIsMutasiK` AS kellap_is_mutasi_k
FROM `coa_kelompok_laporan_ref`
WHERE
coakellapIdKellap IN (%s)
";


$sql['get_kelompok_ref_kode_sistem'] ="
SELECT
    klr.`kellapKodeSistem` AS kode_sistem
FROM kelompok_laporan_ref klr
WHERE klr.`kellapId` IN (%s)
";

$sql['get_susunan_kellap_ref_id'] ="
SELECT 
    kellapId AS kellap_id
FROM 
    kelompok_laporan_ref
WHERE 
kellapTipe = 'child'
AND
kellapIsSummary = 'T'
";

$sql['get_kelompok_laporan_ref_detail'] ="
SELECT
  cklr.`coakellapIdKellap` AS kellap_main_id,
  cklr.`coakellapIdKellapRef` AS kellap_ref_id,
  klr_ref.`kellapNama` AS kellap_nama
FROM
  `kelompok_laporan_ref` klr
  JOIN coa_kelompok_laporan_ref cklr
    ON cklr.`coakellapIdKellap` = klr.`kellapId`
   JOIN kelompok_laporan_ref klr_ref ON klr_ref.`kellapId` = cklr.`coakellapIdKellapRef`
WHERE
klr.`kellapId` =  '%s'
AND
cklr.`coakellapIdKellapRef` IS NOT NULL
";

?>