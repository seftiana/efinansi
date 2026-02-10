<?php
$sql['get_periode_tahun']        = "
SELECT
   thanggarId AS id,
   thanggarNama AS `name`
FROM tahun_anggaran
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
ORDER BY thanggarNama
";

//===GET===
$sql['get_count_data_approval'] = "
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
   LEFT JOIN rencana_pengeluaran
      ON rncnpengeluaranKegdetId = kegdetId
   LEFT JOIN komponen
      ON kompKode = rncnpengeluaranKomponenKode
WHERE 1 = 1
AND kegThanggarId = '%s'
AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
AND kegrefNomor LIKE '%s'
AND kegrefNama LIKE '%s'
AND (MONTH(kegdetWaktuMulaiPelaksanaan) = %s OR %s ) 
";

$sql['get_data_approval'] = "
SELECT
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
   IF(rpdet.statusApproveBelum > 0 AND (rpdet.statusApproveYa > 0  OR rpdet.statusApproveTidak > 0 ),
    'Parsial',
    IF(rpdet.statusApproveBelum > 0 AND (rpdet.statusApproveYa = 0  OR rpdet.statusApproveTidak = 0 ),'Belum',
    kegdetIsAprove)
   ) AS statusApprove,
   IF(kegdetDeskripsi IS NULL OR kegdetDeskripsi = '', '-', kegdetDeskripsi) AS deskripsi,
   jeniskegId AS jenisKegiatanId,
   jeniskegNama AS jenisKegiatanNama,
   kegdetRABFile,
   kegThanggarId,
   kegUnitkerjaId,
   rpdet.pengadaan,
   IFNULL(rpdet.nominalPengadaan, 0) AS nominalPengadaan,
   IFNULL(rpdet.nominalNonPengadaan, 0) AS nominalNonPengadaan,
   IFNULL(rpdet.nominal, 0) AS nominal,
   IFNULL(rpdet.nominalApprovePengadaan, 0) AS nominalApprovePengadaan,
   IFNULL(rpdet.nominalApproveNonPengadaan, 0) AS nominalApproveNonPengadaan,
   IFNULL(rpdet.nominalApprove, 0) AS nominalApprove,
   kegdetWaktuMulaiPelaksanaan AS tanggal
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
      JOIN (
      SELECT unitkerjaId AS id,
      CASE
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN unitkerjaKodeSistem
      END AS kode
      FROM unit_kerja_ref
   ) AS tmp_unit ON tmp_unit.id = unitkerjaId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   LEFT JOIN
      (SELECT
         rncnpengeluaranKegdetId AS id,
         kompId AS komponenId,
         COUNT(kompKode) AS pengadaan,
         SUM(IF(rncnpengeluaranIsAprove ='Ya',1,0)) AS statusApproveYa,
         SUM(IF(rncnpengeluaranIsAprove ='Belum',1,0)) AS statusApproveBelum,
         SUM(IF(rncnpengeluaranIsAprove ='Tidak',1,0)) AS statusApproveTidak,
         SUM(
            IF(
               kompIsPengadaan IS NULL
               OR UPPER(kompIsPengadaan) = 'T',
               0,
               rncnpengeluaranSatuan
            ) * rncnpengeluaranKomponenNominal * IF(
               kompFormulaHasil = '0',
               1,
               IFNULL(kompFormulaHasil, 1)
            )
         ) AS nominalPengadaan,
         SUM(
            IF(
               kompIsPengadaan IS NOT NULL
               OR UPPER(kompIsPengadaan) = 'Y',
               0,
               rncnpengeluaranSatuan
            ) * rncnpengeluaranKomponenNominal * IF(
               kompFormulaHasil = '0',
               1,
               IFNULL(kompFormulaHasil, 1)
            )
         ) AS nominalNonPengadaan,
         SUM(
            rncnpengeluaranSatuan * rncnpengeluaranKomponenNominal * IF(
               kompFormulaHasil = '0',
               1,
               IFNULL(kompFormulaHasil, 1)
            )
         ) AS nominal,
         SUM(
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
            )
         ) AS nominalApprovePengadaan,
         SUM(
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
            )
         ) AS nominalApproveNonPengadaan,
         SUM(
            IF(
               UPPER(rncnpengeluaranIsAprove) = 'YA',
               1,
               0
            ) * rncnpengeluaranKomponenTotalAprove * IF(
               kompFormulaHasil = '0',
               1,
               IFNULL(kompFormulaHasil, 1)
            )
         ) AS nominalApprove
      FROM
         rencana_pengeluaran
         LEFT JOIN komponen
            ON kompKode = rncnpengeluaranKomponenKode
      GROUP BY rncnpengeluaranKegdetId) rpdet
      ON rpdet.id = kegdetId
   LEFT JOIN komponen
         ON kompId = rpdet.komponenId
WHERE 1 = 1
AND kegThanggarId = '%s'
AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
AND kegrefNomor LIKE '%s'
AND kegrefNama LIKE '%s'
AND (MONTH(kegdetWaktuMulaiPelaksanaan) = %s OR %s ) 
ORDER BY kegdetWaktuMulaiPelaksanaan ASC,programNomor,
   subprogNomor,
   kegrefNomor,
   SUBSTRING_INDEX(tmp_unit.kode, '.', 1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 2), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 3), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 4), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 5), '.', -1)+0
LIMIT %s, %s
";

//COMBO
$sql['get_combo_tahun_anggaran']="
SELECT
   thanggarId as id,
   thanggarNama as name
FROM
   tahun_anggaran
ORDER BY thanggarNama DESC
";
//aktif
$sql['get_tahun_anggaran_aktif']="
SELECT
   thanggarId as id,
   thanggarNama as name
FROM
   tahun_anggaran
WHERE
   thanggarIsAktif='Y'
";
//===DO===
$sql['do_add_approval'] = "
UPDATE kegiatan_detail SET kegdetIsAprove='Ya' WHERE kegdetId=%s
";
?>