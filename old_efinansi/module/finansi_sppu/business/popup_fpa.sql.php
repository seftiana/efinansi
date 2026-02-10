<?php


$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_realisasi'] = "
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
AND `pengrealNominalAprove`  > 500000
AND pengd.`pengrealdetNominalApprove`  > 0
AND UPPER(pengrealIsApprove) = 'YA'
AND programThanggarId = %s
AND (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
AND  unitkerjaNama  LIKE '%s'
AND (programNama LIKE  '%s' OR subprogNama LIKE '%s' OR kegrefNama LIKE '%s')
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
";