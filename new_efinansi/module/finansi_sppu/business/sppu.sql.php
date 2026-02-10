<?php
/**
 * @package  SQL-FILE
 */
$sql['get_periode_tahun']  = "
SELECT
   thanggarId AS `id`,
   thanggarNama AS `name`,
   thanggarIsAktif AS `active`,
   thanggarIsOpen AS `open`
FROM tahun_anggaran
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
";

$sql['get_tahun_pembukuan_periode']  = "
SELECT
   tppId AS `id`,
   tppTanggalAwal AS `awal`,
   tppTanggalAkhir AS `akhir`,
   tppIsBukaBuku AS `open`
FROM tahun_pembukuan_periode
WHERE 1 = 1
AND (tppIsBukaBuku = 'Y' OR 1 = %s)
";

$sql['get_date_range'] = "
SELECT
   EXTRACT(YEAR FROM IFNULL(MIN(thanggarBuka), DATE_ADD(DATE(NOW()),INTERVAL -DAY(DATE(NOW()))+1 DAY))) AS minYear,
   EXTRACT(YEAR FROM IFNULL(MAX(thanggarTutup), DATE(LAST_DAY(DATE(NOW()))))) AS maxYear,
   IFNULL(MIN(thanggarBuka), DATE_ADD(DATE(NOW()),INTERVAL -DAY(DATE(NOW()))+1 DAY)) AS tanggalAwal,
   IFNULL(MAX(thanggarTutup), DATE(LAST_DAY(DATE(NOW())))) AS tanggalAkhir
FROM tahun_anggaran
WHERE thanggarIsAktif = 'Y'
AND thanggarIsOpen = 'Y'
";

$sql['get_data_program'] = "
SELECT
   programThanggarId AS taId,
   programId AS id,
   programNama AS `name`
FROM
  program_ref
WHERE 1 = 1
   AND (programThanggarId = '%s' OR 1 = %s)
ORDER BY
  programNama ASC
";

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['count_data_realisasi']     = "
SELECT
  COUNT(DISTINCT pengreal.id) AS count
FROM (
SELECT
   pengrealId AS id,
   pengrealTanggal AS tanggal,
   pengrealNomorPengajuan AS nomorPengajuan,
   IF(pengrealKeterangan IS NULL OR pengrealKeterangan = '', '-', pengrealKeterangan) AS keterangan,
   kegdetId,
   `kegdetDeskripsi` AS lingkup_komponen,
   jeniskegId AS jenisKegiatanId,
   jeniskegNama AS jenisKegiatanNama,
   thanggarId AS taId,
   thanggarNama AS taNama,
   thanggarIsAktif AS taStatus,
   thanggarIsOpen AS taOpen,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   programId AS programId,
   programNomor AS programKode,
   programNama AS programNama,
   subprogId AS kegiatanId,
   IFNULL(subprogNomor, '') AS kegiatanKode,
   CONCAT(IFNULL(subprogNama, ''), IF(jeniskegNama IS NULL, '' , CONCAT(' (',jeniskegNama,')'))) AS kegiatanNama,
   pengajuan_realisasi.`pengrealId` AS subKegiatanId,
   IFNULL(kegrefNomor, '') AS subKegiatanKode,
   kegrefNama AS subKegiatanNama,
   pengrealNominal AS nominalUsulan,
   IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL, spp.nominalSetuju, 0) AS nominalSetuju,
   UPPER(IFNULL(pengrealIsApprove, 'Belum')) AS `status`,
   spp.count AS spp,
   spp.sppId,
   spp.countSpm AS spm,
   spp.spmId,
   spp.sppu AS sppu
FROM
   pengajuan_realisasi
   JOIN kegiatan_detail
      ON pengrealKegdetId = kegdetId
   LEFT JOIN kegiatan_ref
      ON kegdetKegrefId = kegrefId
   LEFT JOIN sub_program
      ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref
      ON subprogProgramId = programId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   LEFT JOIN kegiatan
      ON kegdetKegId = kegId
   LEFT JOIN(
   SELECT
      pengrealdetPengRealId AS id,
      `pengrealdetNominalApprove`,
      COUNT(sppDetId) AS `count`,
      COUNT(spmDetId) AS countSpm,
      sppId,
      spmId,
      COUNT(DISTINCT sppuDetId) AS sppu,
      SUM(pengrealdetNominalPencairan) AS nominal,
      SUM(pengrealdetNominalApprove) AS nominalSetuju
   FROM pengajuan_realisasi_detil
   LEFT JOIN finansi_pa_spp_det
      ON sppDetRealdetId = pengrealdetId
   LEFT JOIN finansi_pa_spp
      ON sppId = sppDetSppId
   LEFT JOIN finansi_pa_spm_det
      ON spmDetRealDetId = pengrealdetId
   LEFT JOIN finansi_pa_spm
      ON spmDetSpmId = spmId
   LEFT JOIN finansi_pa_sppu_det
      ON sppuDetPengrealDetId = pengrealdetId
   LEFT JOIN finansi_pa_sppu
      ON sppuId = sppuDetSppuId
   GROUP BY pengrealdetPengRealId
   ) AS spp ON spp.id = pengrealId
   JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
   JOIN tahun_anggaran
      ON thanggarId = programThanggarId
WHERE 1 = 1
#AND `pengrealNominalAprove`  > 500000
AND UPPER(pengrealIsApprove) = 'YA'
AND programThanggarId = %s
AND (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
AND (programId = '%s' OR 1 = %s)
AND pengrealNomorPengajuan LIKE '%s'
AND kegrefNama LIKE '%s'
AND (jeniskegId = '%s' OR 1 = %s)
AND (MONTH(pengrealTanggal) = '%s' OR 1 = %s)
HAVING sppu = 0
) AS pengreal

   JOIN rencana_pengeluaran rpeng
    ON rpeng.`rncnpengeluaranKegdetId` = pengreal.`kegdetId`
  LEFT JOIN pengajuan_realisasi_detil pengd
    ON pengd.`pengrealdetPengRealId` = pengreal.`id`
    AND pengd.`pengrealdetRncnpengeluaranId` = rpeng.`rncnpengeluaranId`
  LEFT JOIN komponen komp
    ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
  LEFT JOIN coa c
    ON c.`coaId` = komp.`kompCoaId`
    
WHERE 1 = 1
AND pengd.`pengrealdetNominalPencairan` > 0
AND pengd.`pengrealdetNominalApprove` > 0
";

/**
 * Update query get_data_realisasi
 * Untuk mengatur limit data yang ditampilkan berdasarkan FPA
 * since 21/09/2016
 */

$sql['get_data_realisasi'] = "
SELECT SQL_CALC_FOUND_ROWS
   pengreal.*,
  c.`coaKodeAkun` AS kodeAkun,
  rpeng.`rncnpengeluaranId` AS detailBelanjaId,
  rpeng.`rncnpengeluaranKomponenNama` AS detailBelanjaNama,
  pengd.`pengrealdetNominalPencairan` AS nominalDetailBelanjaUsulan,
  pengd.`pengrealdetNominalApprove` AS nominalDetailBelanjaSetuju
FROM (
SELECT
   pengrealId AS id,
   pengrealTanggal AS tanggal,
   pengrealNomorPengajuan AS nomorPengajuan,
   IF(pengrealKeterangan IS NULL OR pengrealKeterangan = '', '-', pengrealKeterangan) AS keterangan,
   kegdetId,
   `kegdetDeskripsi` AS lingkup_komponen,
   jeniskegId AS jenisKegiatanId,
   jeniskegNama AS jenisKegiatanNama,
   thanggarId AS taId,
   thanggarNama AS taNama,
   thanggarIsAktif AS taStatus,
   thanggarIsOpen AS taOpen,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   programId AS programId,
   programNomor AS programKode,
   programNama AS programNama,
   subprogId AS kegiatanId,
   IFNULL(subprogNomor, '') AS kegiatanKode,
   CONCAT(IFNULL(subprogNama, ''), IF(jeniskegNama IS NULL, '' , CONCAT(' (',jeniskegNama,')'))) AS kegiatanNama,
   pengajuan_realisasi.`pengrealId` AS subKegiatanId,
   IFNULL(kegrefNomor, '') AS subKegiatanKode,
   kegrefNama AS subKegiatanNama,
   pengrealNominal AS nominalUsulan,
   IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL, spp.nominalSetuju, 0) AS nominalSetuju,
   UPPER(IFNULL(pengrealIsApprove, 'Belum')) AS `status`,
   spp.count AS spp,
   spp.sppId,
   spp.countSpm AS spm,
   spp.spmId,
   spp.sppu AS sppu
FROM
   pengajuan_realisasi
   JOIN kegiatan_detail
      ON pengrealKegdetId = kegdetId
   LEFT JOIN kegiatan_ref
      ON kegdetKegrefId = kegrefId
   LEFT JOIN sub_program
      ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref
      ON subprogProgramId = programId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   LEFT JOIN kegiatan
      ON kegdetKegId = kegId
   LEFT JOIN(
   SELECT
      pengrealdetPengRealId AS id,
      `pengrealdetNominalApprove`,
      COUNT(sppDetId) AS `count`,
      COUNT(spmDetId) AS countSpm,
      sppId,
      spmId,
      COUNT(DISTINCT sppuDetId) AS sppu,
      SUM(pengrealdetNominalPencairan) AS nominal,
      SUM(pengrealdetNominalApprove) AS nominalSetuju
   FROM pengajuan_realisasi_detil
   LEFT JOIN finansi_pa_spp_det
      ON sppDetRealdetId = pengrealdetId
   LEFT JOIN finansi_pa_spp
      ON sppId = sppDetSppId
   LEFT JOIN finansi_pa_spm_det
      ON spmDetRealDetId = pengrealdetId
   LEFT JOIN finansi_pa_spm
      ON spmDetSpmId = spmId
   LEFT JOIN finansi_pa_sppu_det
      ON sppuDetPengrealDetId = pengrealdetId
   LEFT JOIN finansi_pa_sppu
      ON sppuId = sppuDetSppuId
   GROUP BY pengrealdetPengRealId
   ) AS spp ON spp.id = pengrealId
   JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
   JOIN (SELECT unitkerjaId AS id,
   CASE
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN unitkerjaKodeSistem
   END AS `code`
   FROM unit_kerja_ref) AS tmp_unit
      ON tmp_unit.id = unitkerjaId
   JOIN tahun_anggaran
      ON thanggarId = programThanggarId
WHERE 1 = 1
#AND `pengrealNominalAprove`  > 500000 #update ccp 1-5-2020 request sandi
AND UPPER(pengrealIsApprove) = 'YA'
AND programThanggarId = %s
AND (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
AND (programId = '%s' OR 1 = %s)
AND pengrealNomorPengajuan LIKE '%s'
AND kegrefNama LIKE '%s'
AND (jeniskegId = '%s' OR 1 = %s)
AND (MONTH(pengrealTanggal) = '%s' OR 1 = %s)
HAVING sppu = 0
ORDER BY programId, subprogId,
SUBSTRING_INDEX(tmp_unit.code, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 5), '.', -1)+0,
kegrefId,
pengrealTanggal DESC
LIMIT %s, %s
) AS pengreal

   JOIN rencana_pengeluaran rpeng
    ON rpeng.`rncnpengeluaranKegdetId` = pengreal.`kegdetId`
  LEFT JOIN pengajuan_realisasi_detil pengd
    ON pengd.`pengrealdetPengRealId` = pengreal.`id`
    AND pengd.`pengrealdetRncnpengeluaranId` = rpeng.`rncnpengeluaranId`
  LEFT JOIN komponen komp
    ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
  LEFT JOIN coa c
    ON c.`coaId` = komp.`kompCoaId`
    
WHERE 1 = 1
AND pengd.`pengrealdetNominalPencairan` > 0
AND pengd.`pengrealdetNominalApprove` > 0
";

/*$sql['get_data_realisasi'] = "
SELECT SQL_CALC_FOUND_ROWS
   pengrealId AS id,
   pengrealTanggal AS tanggal,
   pengrealNomorPengajuan AS nomorPengajuan,
   IF(pengrealKeterangan IS NULL OR pengrealKeterangan = '', '-', pengrealKeterangan) AS keterangan,
   `kegdetDeskripsi` AS lingkup_komponen,
   jeniskegId AS jenisKegiatanId,
   jeniskegNama AS jenisKegiatanNama,
   thanggarId AS taId,
   thanggarNama AS taNama,
   thanggarIsAktif AS taStatus,
   thanggarIsOpen AS taOpen,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   programId AS programId,
   programNomor AS programKode,
   programNama AS programNama,
   subprogId AS kegiatanId,
   IFNULL(subprogNomor, '') AS kegiatanKode,
   CONCAT(IFNULL(subprogNama, ''), IF(jeniskegNama IS NULL, '' , CONCAT(' (',jeniskegNama,')'))) AS kegiatanNama,
   pengajuan_realisasi.`pengrealId` AS subKegiatanId,
   IFNULL(kegrefNomor, '') AS subKegiatanKode,
   kegrefNama AS subKegiatanNama,   
  c.`coaKodeAkun` AS kodeAkun,
  rpeng.`rncnpengeluaranId` AS detailBelanjaId,
  rpeng.`rncnpengeluaranKomponenNama` AS detailBelanjaNama,
  pengd.`pengrealdetNominalPencairan` AS nominalDetailBelanjaUsulan,
  pengd.`pengrealdetNominalApprove` AS nominalDetailBelanjaSetuju,
   pengrealNominal AS nominalUsulan,
   IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL, spp.nominalSetuju, 0) AS nominalSetuju,
   UPPER(IFNULL(pengrealIsApprove, 'Belum')) AS `status`,
   spp.count AS spp,
   spp.sppId,
   spp.countSpm AS spm,
   spp.spmId,
   spp.sppu AS sppu
FROM
   pengajuan_realisasi
   JOIN kegiatan_detail
      ON pengrealKegdetId = kegdetId
   LEFT JOIN kegiatan_ref
      ON kegdetKegrefId = kegrefId
   LEFT JOIN sub_program
      ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref
      ON subprogProgramId = programId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   LEFT JOIN kegiatan
      ON kegdetKegId = kegId
   JOIN rencana_pengeluaran rpeng
    ON rpeng.`rncnpengeluaranKegdetId` = kegiatan_detail.`kegdetId`
  LEFT JOIN pengajuan_realisasi_detil pengd
    ON pengd.`pengrealdetPengRealId` = pengajuan_realisasi.`pengrealId`
    AND pengd.`pengrealdetRncnpengeluaranId` = rpeng.`rncnpengeluaranId`
  LEFT JOIN komponen komp
    ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
  LEFT JOIN coa c
    ON c.`coaId` = komp.`kompCoaId`
   LEFT JOIN(
   SELECT
      pengrealdetPengRealId AS id,
      COUNT(sppDetId) AS `count`,
      COUNT(spmDetId) AS countSpm,
      sppId,
      spmId,
      COUNT(DISTINCT sppuDetId) AS sppu,
      SUM(pengrealdetNominalPencairan) AS nominal,
      SUM(pengrealdetNominalApprove) AS nominalSetuju
   FROM pengajuan_realisasi_detil
   LEFT JOIN finansi_pa_spp_det
      ON sppDetRealdetId = pengrealdetId
   LEFT JOIN finansi_pa_spp
      ON sppId = sppDetSppId
   LEFT JOIN finansi_pa_spm_det
      ON spmDetRealDetId = pengrealdetId
   LEFT JOIN finansi_pa_spm
      ON spmDetSpmId = spmId
   LEFT JOIN finansi_pa_sppu_det
      ON sppuDetPengrealDetId = pengrealdetId
   LEFT JOIN finansi_pa_sppu
      ON sppuId = sppuDetSppuId
   GROUP BY pengrealdetPengRealId
   ) AS spp ON spp.id = pengrealId
   JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
   JOIN (SELECT unitkerjaId AS id,
   CASE
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN unitkerjaKodeSistem
   END AS `code`
   FROM unit_kerja_ref) AS tmp_unit
      ON tmp_unit.id = unitkerjaId
   JOIN tahun_anggaran
      ON thanggarId = programThanggarId
WHERE 1 = 1
AND pengd.`pengrealdetNominalApprove`  > 500000
AND UPPER(pengrealIsApprove) = 'YA'
AND programThanggarId = %s
AND (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
AND (programId = '%s' OR 1 = %s)
AND pengrealNomorPengajuan LIKE '%s'
AND kegrefNama LIKE '%s'
AND (jeniskegId = '%s' OR 1 = %s)
AND (MONTH(pengrealTanggal) = '%s' OR 1 = %s)
HAVING sppu = 0
ORDER BY programId, subprogId,
SUBSTRING_INDEX(tmp_unit.code, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 5), '.', -1)+0,
kegrefId,
pengrealTanggal DESC
LIMIT %s, %s
";*/

$sql['get_data_detail_realisasi']   = "
SELECT
   pengrealdetId AS id,

   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   programNomor AS programKode,
   programNama AS programNama,
   subprogNomor AS kegiatanKode,
   subprogNama AS kegiatanNama,
   kegrefNomor AS subKegiatanKode,
   kegrefNama AS subKegiatanNama,      
   pengrealKeterangan AS keterangan,
   pengrealTanggal AS tanggal,
   
   pengrealId AS realisasiId,
   rncnpengeluaranId AS pengajuanId,
   kegdetId AS kegiatanId,
   coaId AS akunId,
   `pengrealNomorPengajuan` AS akunKode,
   coaNamaAkun AS akunNama,
   pengrealKeterangan AS lingkupKomponen,
   mak.paguBasId AS makId,
   mak.paguBasKode AS makKode,
   mak.paguBasKeterangan AS makNama,
   kompId AS komponenId,
   kompKode AS komponenKode,
   kompNama AS komponenNama,
  SUM(pengrealdetNominalApprove) AS nominal
FROM
   pengajuan_realisasi
   JOIN pengajuan_realisasi_detil
      ON pengrealdetPengRealId = pengrealId
   JOIN rencana_pengeluaran
      ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
   JOIN kegiatan_detail
      ON kegdetId = pengrealKegdetId
      AND kegdetId = rncnpengeluaranKegdetId
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN sub_program
      ON subprogId = kegrefSubprogId
   JOIN program_ref prog
      ON programId = subprogProgramId
   JOIN kegiatan
      ON kegId = kegdetKegId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   JOIN unit_kerja_ref
      ON unitkerjaId = kegUnitkerjaId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   LEFT JOIN komponen
      ON kompKode = rncnpengeluaranKomponenKode
   LEFT JOIN coa
      ON coaId = kompCoaId
   LEFT JOIN finansi_ref_pagu_bas AS mak
      ON (mak.paguBasId = rncnpengeluaranMakId OR mak.paguBasId = kompMakId)
   LEFT JOIN finansi_ref_pagu_bas AS bas
      ON bas.paguBasId = mak.paguBasParentId
WHERE 1 = 1
AND pengrealId IN ('%s')
 GROUP BY pengrealId 
HAVING nominal > 0

";

$sql['set_nomor_sppu']  = "
SET @SPPU_NOMOR = ''
";

$sql['do_set_nomor_sppu']  = "
SELECT
   CONCAT_WS(
      '/',
      LPAD(IFNULL(MAX(SUBSTRING_INDEX(sppuNomor, '/', 1)+0)+1, 1), 3,0),
      'P',
	(CASE LPAD(MONTH('%s'),2,0)
		WHEN '01' THEN 'I'
		WHEN '02' THEN 'II'
		WHEN '03' THEN 'III'
		WHEN '04' THEN 'IV'
		WHEN '05' THEN 'V'
		WHEN '06' THEN 'VI'
		WHEN '07' THEN 'VII'
		WHEN '08' THEN 'VIII'
		WHEN '09' THEN 'IX'
		WHEN '10' THEN 'X'
		WHEN '11' THEN 'XI'
		WHEN '12' THEN 'XII'
	END)	,
      EXTRACT(YEAR FROM '%s')
   ) AS nomor_sppu
FROM
   finansi_pa_sppu
WHERE 1 = 1
   AND EXTRACT(MONTH FROM sppuTanggal) = EXTRACT(MONTH FROM '%s')
   AND EXTRACT(YEAR FROM sppuTanggal) = EXTRACT(YEAR FROM '%s')
   AND sppuNomor LIKE CONCAT('%s','/P/',
   (CASE LPAD(MONTH('%s'),2,0)
		WHEN '01' THEN 'I'
		WHEN '02' THEN 'II'
		WHEN '03' THEN 'III'
		WHEN '04' THEN 'IV'
		WHEN '05' THEN 'V'
		WHEN '06' THEN 'VI'
		WHEN '07' THEN 'VII'
		WHEN '08' THEN 'VIII'
		WHEN '09' THEN 'IX'
		WHEN '10' THEN 'X'
		WHEN '11' THEN 'XI'
		WHEN '12' THEN 'XII'
	END)
   ,'/',EXTRACT(YEAR FROM '%s'))

";
/*
$sql['do_set_nomor_sppu']  = "
SELECT
   CONCAT_WS(
      '/',
      LPAD(IFNULL(MAX(SUBSTRING_INDEX(sppuNomor, '/', 1)+0)+1, 1), 3,0),
      'P',
      EXTRACT(YEAR FROM '%s')
   ) INTO @SPPU_NOMOR
FROM
   finansi_pa_sppu
WHERE 1 = 1
   AND EXTRACT(YEAR FROM sppuTanggal) = EXTRACT(YEAR FROM %s)
";
*/
$sql['do_insert_sppu']  = "
INSERT INTO finansi_pa_sppu
SET sppuNomor = '%s',
   sppuNomorBukti = '%s',
   sppuTppId = '%s',
   sppuTanggal = '%s',
   sppuNomorCekGiro  = '%s',
   sppuNomorRekening = '%s',
   sppuBank = '%s',
   sppuNominal = '%s',
   sppuUserId = '%s',
   sppuBankPayment = '%s',
   sppuCashPayment = '%s',
   sppuKeterangan = '%s'
";

$sql['do_insert_sppu_detail_by_fpa_id'] = "
INSERT INTO finansi_pa_sppu_det
(sppuDetSppuId,
   sppuDetPengrealDetId ,
   sppuDetNominal ,
   sppuDetUserId )
 SELECT
  '%s' AS sppuId,
  pengrealdetId AS id,
  pengrealdetNominalApprove AS nominal,
  '%s' AS userId
FROM
   pengajuan_realisasi
   JOIN pengajuan_realisasi_detil
      ON pengrealdetPengRealId = pengrealId
WHERE 1 = 1
AND pengrealId = '%s'
HAVING nominal > 0
";

$sql['get_data_transaksi_bank'] ="
SELECT
   transaksiBankId AS id,
   transaksiBankTanggal AS tanggal,
   transaksiBankNomor AS nomorBank,
   transaksiBankBpkb AS nomorBp,
   transaksiBankSppuId AS sppuId
FROM finansi_pa_transaksi_bank
WHERE transaksiBankSppuId = '%s'
";

$sql['get_last_insert_id']    = "
SELECT LAST_INSERT_ID() AS last_id
";

$sql['do_insert_transaksi_bank'] = "
INSERT INTO finansi_pa_transaksi_bank
SET 
   transaksiBankNomor = '%s',
   transaksiBankBpkb = '%s',
   transaksiBankTanggal = '%s',
   transaksiBankSppuId = '%s',
   transaksiBankCoaIdPenerima = '%s',
   transaksiBankPenerima = '%s',
   transaksiBankRekeningPenerima = '%s',
   transaksiBankCoaIdTujuan = '%s',
   transaksiBankTujuan = '%s',
   transaksiBankRekeningTujuan = '%s',
   transaksiBankNominal = '%s',
   transaksiBankTipe = '%s',
   transaksiBankUserId = '%s'
";

$sql['do_update_transaksi_bank'] = "
UPDATE finansi_pa_transaksi_bank
SET 
   transaksiBankNomor = '%s',
   transaksiBankBpkb = '%s',
   transaksiBankTanggal = '%s',
   transaksiBankSppuId = '%s',
   transaksiBankCoaIdPenerima = '%s',
   transaksiBankPenerima = '%s',
   transaksiBankRekeningPenerima = '%s',
   transaksiBankCoaIdTujuan = '%s',
   transaksiBankTujuan = '%s',
   transaksiBankRekeningTujuan = '%s',
   transaksiBankNominal = '%s',
   transaksiBankTipe = '%s',
   transaksiBankUserId = '%s'
WHERE
  transaksiBankSppuId = %s
";

$sql['do_insert_sppu_detail'] = "
INSERT INTO finansi_pa_sppu_det
SET sppuDetSppuId = '%s',
   sppuDetPengrealDetId = '%s',
   sppuDetNominal = '%s',
   sppuDetUserId = '%s'
";

$sql['get_data_sppu_detail']   = "
SELECT
   sppuId AS id,
   sppuNomor AS nomor,
   sppuBPKBCr AS nomorCr,
   sppuBPKBBp  AS nomorBp,
   sppuTanggal AS tanggal,
   sppuNomorBukti AS nomorBukti,
   sppuBank AS bank,
   sppuNomorRekening AS nomorRekening,
   sppuNomorCekGiro AS nomorCekGiro,
   IFNULL(sppu.nominal, 0) AS nominal,
   sppuBankPayment AS bankPayment,
   sppuCashPayment AS cashReceipt,
   sppuKeterangan AS keterangan
FROM finansi_pa_sppu
JOIN (SELECT
   sppuDetSppuId AS id,
   SUM(sppuDetNominal) AS nominal
FROM finansi_pa_sppu_det
GROUP BY sppuDetSppuId) AS sppu
   ON sppu.id = sppuId
WHERE 1 = 1
AND sppuId = %s
LIMIT 1
";

$sql['get_data_sppu_items']   = "
SELECT
   sppuDetId,
   pengrealdetId AS id,
   pengrealId AS realisasiId,
   rncnpengeluaranId AS pengajuanId,

   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   programNomor AS programKode,
   programNama AS programNama,
   subprogNomor AS kegiatanKode,
   subprogNama AS kegiatanNama,
   kegrefNomor AS subKegiatanKode,
   kegrefNama AS subKegiatanNama,      
   pengrealKeterangan AS keterangan,
   pengrealTanggal AS tanggal,
   
   kegdetId AS kegiatanId,
   coaId AS akunId,
   IFNULL(pengrealNomorPengajuan,'-') AS noFpa,
   IFNULL(coaNamaAkun,'-') AS akunNama,
   mak.paguBasId AS makId,
   mak.paguBasKode AS makKode,
   mak.paguBasKeterangan AS makNama,
   kompId AS komponenId,
   IFNULL(kompKode,'-') AS komponenKode,
   IFNULL(kompNama,'-') AS komponenNama,
   IFNULL(`kegdetDeskripsi`,'-') AS lingkup,
   IFNULL(pengrealKeterangan,'-') AS lingkupKomponen,
   SUM(pengrealdetNominalApprove) AS nominal
FROM finansi_pa_sppu_det
   JOIN finansi_pa_sppu
      ON sppuId = sppuDetSppuId
   JOIN pengajuan_realisasi_detil
      ON pengrealdetId = sppuDetPengrealDetId
   JOIN pengajuan_realisasi
      ON pengrealId = pengrealdetPengrealId
   JOIN rencana_pengeluaran
      ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
   JOIN kegiatan_detail
      ON kegdetId = pengrealKegdetId
      AND kegdetId = rncnpengeluaranKegdetId
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN sub_program
      ON subprogId = kegrefSubprogId
   JOIN program_ref prog
      ON programId = subprogProgramId
   JOIN kegiatan
      ON kegId = kegdetKegId
   JOIN unit_kerja_ref
      ON unitkerjaId = kegUnitkerjaId      
   LEFT JOIN komponen
      ON kompKode = rncnpengeluaranKomponenKode
   LEFT JOIN coa
      ON coaId = kompCoaId
   LEFT JOIN finansi_ref_pagu_bas AS mak
      ON (mak.paguBasId = rncnpengeluaranMakId OR mak.paguBasId = kompMakId)
   LEFT JOIN finansi_ref_pagu_bas AS bas
      ON bas.paguBasId = mak.paguBasParentId
WHERE 1 = 1
AND sppuDetSppuId = %s
 GROUP BY pengrealId
";

$sql['get_data_sppu_items_detail']   = "
SELECT
   sppu.sppuDetId,
   sppu.id AS id,
   sppu.realisasiId AS realisasiId,
   sppu.pengajuanId AS pengajuanId,
   sppu.kegiatanId AS kegiatanId,
   sppu.akunId AS akunId,
   IFNULL(sppu.akunKode,'-') AS akunKode,
   IFNULL(sppu.akunNama,'-') AS akunNama,
   sppu.makId AS makId,
   sppu.makKode AS makKode,
   sppu.makNama AS makNama,
   sppu.komponenId AS komponenId,
   IFNULL(sppu.komponenKode,'-') AS komponenKode,
   IFNULL(sppu.komponenNama,'-') AS komponenNama,
   IFNULL(sppu.lingkup,'-') AS lingkup,
   IFNULL(GROUP_CONCAT(sppu.lingkupKomponen SEPARATOR ', '),'-') AS lingkupKomponen,
   SUM(sppu.nominal) AS nominal
FROM (
SELECT
   sppuDetId,
   pengrealdetId AS id,
   pengrealId AS realisasiId,
   rncnpengeluaranId AS pengajuanId,
   kegdetId AS kegiatanId,
   coaId AS akunId,
   IFNULL(pengrealNomorPengajuan,'-') AS akunKode,
   IFNULL(coaNamaAkun,'-') AS akunNama,
   mak.paguBasId AS makId,
   mak.paguBasKode AS makKode,
   mak.paguBasKeterangan AS makNama,
   kompId AS komponenId,
   IFNULL(kompKode,'-') AS komponenKode,
   IFNULL(kompNama,'-') AS komponenNama,
   IFNULL(`kegdetDeskripsi`,'-') AS lingkup,
   IF(pengrealdetDeskripsi = '','-', pengrealdetDeskripsi) AS lingkupKomponen,
   (pengrealdetNominalApprove) AS nominal
FROM finansi_pa_sppu_det
   JOIN finansi_pa_sppu
      ON sppuId = sppuDetSppuId
   JOIN pengajuan_realisasi_detil
      ON pengrealdetId = sppuDetPengrealDetId
   JOIN pengajuan_realisasi
      ON pengrealId = pengrealdetPengrealId
   JOIN rencana_pengeluaran
      ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
   JOIN kegiatan_detail
      ON kegdetId = pengrealKegdetId
      AND kegdetId = rncnpengeluaranKegdetId
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN sub_program
      ON subprogId = kegrefSubprogId
   JOIN program_ref prog
      ON programId = subprogProgramId
   JOIN kegiatan
      ON kegId = kegdetKegId
   LEFT JOIN komponen
      ON kompKode = rncnpengeluaranKomponenKode
   LEFT JOIN coa
      ON coaId = kompCoaId
   LEFT JOIN finansi_ref_pagu_bas AS mak
      ON (mak.paguBasId = rncnpengeluaranMakId OR mak.paguBasId = kompMakId)
   LEFT JOIN finansi_ref_pagu_bas AS bas
      ON bas.paguBasId = mak.paguBasParentId
WHERE 1 = 1
AND sppuDetSppuId = %s
GROUP BY sppuDetId) sppu
GROUP BY sppu.realisasiId
";

/**
 * @since 2016-11-07 [Ubah query untuk menampilkan data per FPA dengan menggabungkan setiap deskripsi & nominal detail belanja di FPA]
 */

 /** 
$sql['get_data_sppu_items_detail']   = "
SELECT
   sppuDetId,
   pengrealdetId AS id,
   pengrealId AS realisasiId,
   rncnpengeluaranId AS pengajuanId,
   kegdetId AS kegiatanId,
   coaId AS akunId,
   IFNULL(pengrealNomorPengajuan,'-') AS akunKode,
   IFNULL(coaNamaAkun,'-') AS akunNama,
   mak.paguBasId AS makId,
   mak.paguBasKode AS makKode,
   mak.paguBasKeterangan AS makNama,
   kompId AS komponenId,
   IFNULL(kompKode,'-') AS komponenKode,
   IFNULL(kompNama,'-') AS komponenNama,
   IFNULL(`kegdetDeskripsi`,'-') AS lingkup,
   IFNULL(pengrealdetDeskripsi,'-') AS lingkupKomponen,
   (pengrealdetNominalApprove) AS nominal
FROM finansi_pa_sppu_det
   JOIN finansi_pa_sppu
      ON sppuId = sppuDetSppuId
   JOIN pengajuan_realisasi_detil
      ON pengrealdetId = sppuDetPengrealDetId
   JOIN pengajuan_realisasi
      ON pengrealId = pengrealdetPengrealId
   JOIN rencana_pengeluaran
      ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
   JOIN kegiatan_detail
      ON kegdetId = pengrealKegdetId
      AND kegdetId = rncnpengeluaranKegdetId
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN sub_program
      ON subprogId = kegrefSubprogId
   JOIN program_ref prog
      ON programId = subprogProgramId
   JOIN kegiatan
      ON kegId = kegdetKegId
   LEFT JOIN komponen
      ON kompKode = rncnpengeluaranKomponenKode
   LEFT JOIN coa
      ON coaId = kompCoaId
   LEFT JOIN finansi_ref_pagu_bas AS mak
      ON (mak.paguBasId = rncnpengeluaranMakId OR mak.paguBasId = kompMakId)
   LEFT JOIN finansi_ref_pagu_bas AS bas
      ON bas.paguBasId = mak.paguBasParentId
WHERE 1 = 1
AND sppuDetSppuId = %s
";
*/

$sql['update_nomor_bp'] = "
UPDATE `finansi_pa_sppu`
SET `sppuBPKBBp` = '%s'
WHERE `sppuId` = '%s'
";

$sql['get_data_sppu']   = "
SELECT SQL_CALC_FOUND_ROWS
   sppuId AS id,
   sppuNomor AS nomor,
   pr.pengrealNomorPengajuan AS nomorPengajuan,
   sppuTppId AS tpId,
   sppuTanggal AS tanggal,
   sppuNomorBukti AS nomorBukti,
   sppuBank AS bank,
   sppuNomorRekening AS nomorRekening,
   IFNULL(sppu.nominal, 0) AS nominal,
   IF(trb.`transaksiBankId` IS NULL,IFNULL(sppuBPKBBp,''), IFNULL(trb.`transaksiBankBpkb`,'')) AS nomorBp,
   IF(trans_kas.sppu_id IS NULL,IFNULL(sppuBPKBCr,''),IFNULL(trans_kas.no_bpkb,''))  AS nomorCr,
   sppuBankPayment AS bankPayment,
   sppuCashPayment AS cashReceipt,
   sppuIsTransaksiKas AS isTransaksi
FROM finansi_pa_sppu
JOIN (SELECT
   sppuDetSppuId AS id,
   SUM(sppuDetNominal) AS nominal
FROM finansi_pa_sppu_det
GROUP BY sppuDetSppuId) AS sppu
   ON sppu.id = sppuId
LEFT JOIN finansi_pa_sppu_det sppuDet
ON sppuDet.sppuDetSppuId = sppuId
LEFT JOIN pengajuan_realisasi_detil prd
ON sppuDetPengrealDetId = prd.`pengrealdetId`
JOIN pengajuan_realisasi pr
ON pr.`pengrealId` = prd.`pengrealdetPengRealId`
LEFT JOIN `finansi_pa_transaksi_bank` trb
   ON trb.`transaksiBankSppuId` = sppuId  
LEFT JOIN (
SELECT
    sppu_d.`sppuDetSppuId` AS sppu_id,
    trk.`transaksiKasBpkb` AS no_bpkb
FROM 
`finansi_pa_transaksi_kas` trk
JOIN `finansi_pa_transaksi_kas_detil` trkd
    ON trkd.`transaksiKasDetilTransaksiKasId` = trk.`transaksiKasId`
JOIN `finansi_pa_sppu_det` sppu_d 
    ON sppu_d.`sppuDetId` = trkd.`transaksiKasDetilSppuDetId`
GROUP BY sppu_id
) AS trans_kas ON trans_kas.sppu_id = sppuId 
WHERE 1 = 1
AND sppuNomor LIKE '%s'
AND pr.pengrealNomorPengajuan LIKE '%s'
AND sppuTanggal BETWEEN '%s' AND '%s'
GROUP BY sppuId
HAVING (nomorBp like '%s')
LIMIT %s, %s
";

$sql['get_setting_name']      = "
SELECT
   settingValue AS `name`
FROM setting
WHERE 1 = 1
AND settingName = '%s'
LIMIT 1
";

$sql['do_delete_sppu_det'] = "
DELETE FROM finansi_pa_sppu_det WHERE sppuDetSppuId = %s
";

$sql['do_delete_sppu']     = "
DELETE FROM finansi_pa_sppu WHERE sppuId = %s
";

$sql['do_update_sppu']     = "
UPDATE finansi_pa_sppu
SET 
   sppuNomor = '%s',
   sppuNomorBukti = '%s',
   sppuTanggal = '%s',
   sppuNomorCekGiro  = '%s',
   sppuNomorRekening = '%s',
   sppuBank = '%s',
   sppuNominal = '%s',
   sppuUserId = '%s',
   sppuBankPayment = '%s',
   sppuCashPayment = '%s',
   sppuKeterangan = '%s'
WHERE sppuId = '%s'
";

/** 
 * move to module generate_number
 *
 * generate cr dan bp

$sql['get_nomor_bp'] ="
SELECT
   CONCAT(
      SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) , -- TAHUN
      EXTRACT(MONTH FROM DATE('%s')) , -- BULAN
     '05' , -- KODE BP,     
    LPAD(IFNULL(MAX(SUBSTR(`sppuBPKBBp`,-4,4)) + 1,1),4,0)
   ) AS nomorBp
FROM
   `finansi_pa_sppu`
WHERE 1 = 1 
   AND `sppuBPKBBp` LIKE CONCAT(SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) ,EXTRACT(MONTH FROM DATE('%s')) ,'05','%s')
   AND EXTRACT(MONTH FROM `sppuTanggal`) = EXTRACT(MONTH FROM DATE('%s')) -- TANGGAL PEMBUATAN SPPU
   AND EXTRACT(YEAR FROM `sppuTanggal`) = EXTRACT(YEAR FROM DATE('%s')) -- TANGGAL PEMBUATAN SPPU     
";

$sql['get_nomor_cr'] ="
SELECT
   CONCAT(
      SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) , -- TAHUN
      EXTRACT(MONTH FROM DATE('%s')) , -- BULAN
     '06' , -- KODE CR,     
    LPAD(IFNULL(MAX(SUBSTR(`sppuBPKBCr`,-4,4)) + 1,1),4,0)
   ) AS nomorCr
FROM
   `finansi_pa_sppu`
WHERE 1 = 1 
   AND `sppuBPKBCr` LIKE CONCAT(SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) ,EXTRACT(MONTH FROM DATE('%s')) ,'06','%s')
   AND EXTRACT(MONTH FROM `sppuTanggal`) = EXTRACT(MONTH FROM DATE('%s')) -- TANGGAL PEMBUATAN SPPU
   AND EXTRACT(YEAR FROM `sppuTanggal`) = EXTRACT(YEAR FROM DATE('%s')) -- TANGGAL PEMBUATAN SPPU    
";
*/

$sql['get_sppu_tanggal'] ="
SELECT 
  `sppuBPKBCr` AS nomorCr,
  `sppuBPKBBp` AS nomorBp,
  `sppuNomor` AS nomorSPPU,
  (`sppuTanggal`) AS tanggal,
  MONTH(`sppuTanggal`) AS bulan,
  YEAR(`sppuTanggal`) AS tahun 
FROM
  `finansi_pa_sppu` 
WHERE `sppuId` = '%s'
";

/**
 * untuk hapus bp
 */

$sql['update_no_bp_sppu'] ="
UPDATE `finansi_pa_sppu`
SET 
  `sppuBPKBBp` = NULL
WHERE `sppuId` = '%s'        
";

$sql['do_delete_bp'] = "
DELETE FROM `finansi_pa_transaksi_bank`
WHERE 
  `transaksiBankSppuId` = '%s'
";

$sql['do_delete_bp_det'] = "
DELETE FROM  `finansi_pa_transaksi_bank_detil`
WHERE `transaksiBankDetilTransaksiBankId` = (SELECT
  `transaksiBankId`
FROM `finansi_pa_transaksi_bank`
WHERE 
  `transaksiBankSppuId` = '%s')
";

?>
