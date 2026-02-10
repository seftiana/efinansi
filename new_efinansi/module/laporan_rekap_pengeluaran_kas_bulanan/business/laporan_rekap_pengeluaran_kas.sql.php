<?php
/**
 * @package SQL-FILE
 */
$sql['get_periode_tahun']     = "
SELECT
   thanggarId AS id,
   thanggarNama AS `name`,
   thanggarBuka AS startDate,
   thanggarTutup AS endDate,
   renstraTanggalAwal AS minDate,
   renstraTanggalAkhir AS maxDate
FROM tahun_anggaran
JOIN renstra
   ON renstraId = thanggarRenstraId
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
";

$sql['get_rencana_pengeluaran']  = "
SELECT
   rkat.*,
   kompId,
   kompKode,
   kompNama,
   kompNamaSatuan,
   kompDeskripsi,
   IFNULL(detail_belanja.count, 0) AS detailBelanja,
   IFNULL(kompIsPengadaan, 'T') AS pengadaan,
   rncnpengeluaranIsAprove AS statusKomponen,
   IF(kompIsPengadaan IS NULL
      OR UPPER(kompIsPengadaan) = 'T',
      0,
      rncnpengeluaranSatuan
   ) * rncnpengeluaranKomponenNominal * IF(
      kompFormulaHasil = '0',
      1,
      IFNULL(kompFormulaHasil, 1)
   ) AS nominalPengadaan,
   IF(kompIsPengadaan IS NOT NULL
      OR UPPER(kompIsPengadaan) = 'Y',
      0,
      rncnpengeluaranSatuan
   ) * rncnpengeluaranKomponenNominal * IF(
      kompFormulaHasil = '0',
      1,
      IFNULL(kompFormulaHasil, 1)
   ) AS nominalNonPengadaan,
   rncnpengeluaranSatuan * rncnpengeluaranKomponenNominal * IF(
      kompFormulaHasil = '0',
      1,
      IFNULL(kompFormulaHasil, 1)
   ) AS nominal,
   IF(
      UPPER(rncnpengeluaranIsAprove) = 'YA',
      1,
      0
   ) * IF(
      kompIsPengadaan IS NULL
      OR UPPER(kompIsPengadaan) = 'T',
      0,
      rncnpengeluaranSatuanAprove
   ) * rncnpengeluaranKomponenNominalAprove * IF(
      kompFormulaHasil = '0',
      1,
      IFNULL(kompFormulaHasil, 1)
   ) AS nominalApprovePengadaan,
   IF(
      UPPER(rncnpengeluaranIsAprove) = 'YA',
      1,
      0
   ) * IF(
      kompIsPengadaan IS NULL
      OR UPPER(kompIsPengadaan) = 'T',
      rncnpengeluaranSatuanAprove,
      0
   ) * rncnpengeluaranKomponenNominalAprove * IF(
      kompFormulaHasil = '0',
      1,
      IFNULL(kompFormulaHasil, 1)
   ) AS nominalApproveNonPengadaan,
   IF(
      UPPER(rncnpengeluaranIsAprove) = 'YA',
      1,
      0
   ) * rncnpengeluaranSatuanAprove * rncnpengeluaranKomponenNominalAprove * IF(
      kompFormulaHasil = '0',
      1,
      IFNULL(kompFormulaHasil, 1)
   ) AS nominalApprove
FROM (SELECT
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaNama AS unitNama,
   programId AS programId,
   programNomor AS programNomor,
   programNama AS programNama,
   subprogId AS kegiatanId,
   subprogNomor AS kegiatanNomor,
   subprogNama AS kegiatanNama,
   subprogJeniskegId AS jenigKegiatan,
   kegrefId AS subkegiatanId,
   kegrefNomor AS subkegiatanNomor,
   kegrefNama AS subkegiatanNama,
   kegdetId AS id,
   UPPER(kegdetIsAprove) AS statusApprove,
   IF(kegdetDeskripsi IS NULL OR kegdetDeskripsi = '', '-', kegdetDeskripsi) AS deskripsi,
   jeniskegId AS jenisKegiatanId,
   jeniskegNama AS jenisKegiatanNama,
   kegdetRABFile,
   kegThanggarId,
   kegUnitkerjaId
FROM kegiatan_detail
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
WHERE 1 = 1
AND kegThanggarId = '%s'
AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
LIMIT %s, %s) AS rkat
   JOIN rencana_pengeluaran
      ON rncnpengeluaranKegdetId = rkat.id
   LEFT JOIN komponen
      ON kompKode = rncnpengeluaranKomponenKode
   LEFT JOIN (
      SELECT
         rncnpengeluaranKegdetId AS id,
         COUNT(rncnpengeluaranId) AS `count`
      FROM rencana_pengeluaran
      GROUP BY rncnpengeluaranKegdetId
   ) AS detail_belanja ON detail_belanja.id = rkat.id
WHERE 1 = 1
AND UPPER(rncnpengeluaranIsAprove) = 'Ya'
AND DATE(rncnpengeluaranTgl) BETWEEN '%s' AND '%s'
ORDER BY rkat.programNomor,
   rkat.kegiatanNomor,
   rkat.subkegiatanNomor,
   rkat.id,
   kompKode
";

$sql['count_rencana_pengeluaran']   = "
SELECT
   COUNT(DISTINCT kegdetId) AS `count`
FROM
   kegiatan_detail
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN sub_program
      ON subprogId = kegrefSubprogId
   JOIN program_ref
      ON programId = subprogProgramId
   JOIN kegiatan
      ON kegId = kegdetKegId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   JOIN unit_kerja_ref
      ON unitkerjaId = kegUnitkerjaId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   JOIN rencana_pengeluaran
      ON rncnpengeluaranKegdetId = kegdetId
   LEFT JOIN komponen
      ON kompKode = rncnpengeluaranKomponenKode
WHERE 1 = 1
AND kegThanggarId = '%s'
AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
AND UPPER(rncnpengeluaranIsAprove) = 'Ya'
AND DATE(rncnpengeluaranTgl) BETWEEN '%s' AND '%s'
";
?>