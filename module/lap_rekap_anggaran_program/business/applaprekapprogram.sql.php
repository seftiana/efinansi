<?php
$sql['get_periode_tahun']     = "
SELECT
   thanggarId AS `id`,
   thanggarNama AS `name`
FROM tahun_anggaran
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
ORDER BY thanggarNama
";

$sql['get_unit_kerja_id'] = "
SELECT
   unitkerjaNama,
   unitkerjaNamaPimpinan
FROM
   unit_kerja_ref
WHERE
   unitkerjaId = '%s'
";
//===GET===
$sql['get_count_data'] = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data'] = "
SELECT SQL_CALC_FOUND_ROWS
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   programId AS programId,
   programNomor AS programKode,
   programNama AS programNama,
   subProgId AS kegiatanId,
   jeniskegId,
   jeniskegNama,
   IFNULL(subprogNomor, '') AS kegiatanKode,
   CONCAT(IFNULL(subprogNama, ''),' (',IFNULL(jeniskegNama, '-'),')') AS kegiatanNama,
   kegrefId AS subKegiatanId,
   IFNULL(kegrefNomor, '') AS subKegiatanKode,
   IFNULL(kegrefNama, '') AS subKegiatanNama,
   IF(detail.nominalUsulan > 0, detail.nominalUsulan, 0) AS nominalUsulan,
   IF(detail.nominalSetuju > 0, detail.nominalSetuju, 0) AS nominalSetuju
FROM
   kegiatan_detail
   LEFT JOIN kegiatan
      ON kegdetKegId = kegId
   LEFT JOIN kegiatan_ref
      ON kegdetKegrefId = kegrefId
   LEFT JOIN sub_program
      ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref
      ON subprogProgramId = programId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
   JOIN
      (SELECT
         unitkerjaId AS id,
         CASE
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$'
            THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$'
            THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$'
            THEN CONCAT(unitkerjaKodeSistem, '.0.0')
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$'
            THEN CONCAT(unitkerjaKodeSistem, '.0')
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$'
            THEN unitkerjaKodeSistem
         END AS kode
      FROM
         unit_kerja_ref) AS tmp_unit
      ON tmp_unit.id = unitkerjaId
   LEFT JOIN
      (SELECT
         rncnpengeluaranKegdetId,
         SUM(
            rncnpengeluaranSatuan * rncnpengeluaranKomponenNominal * IF(
               kompFormulaHasil = '0',
               1,
               IFNULL(kompFormulaHasil, 1)
            )
         ) AS nominalUsulan,
         SUM(
            IF(
               UPPER(rncnpengeluaranIsAprove) = 'YA',
               1,
               0
            ) * rncnpengeluaranSatuanAprove * rncnpengeluaranKomponenNominalAprove * IF(
               kompFormulaHasil = '0',
               1,
               IFNULL(kompFormulaHasil, 1)
            )
         ) AS nominalSetuju
      FROM
         rencana_pengeluaran
         LEFT JOIN komponen
            ON kompKode = rncnpengeluaranKomponenKode
      GROUP BY rncnpengeluaranKegdetId) detail
      ON detail.rncnpengeluaranKegdetId = kegdetId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
WHERE 1 = 1
   AND (kegdetIsAprove = 'Ya' OR 1 = 1)
   AND kegThanggarId = %s
   AND (programId = '%s' OR 1 = %s)
   AND (SUBSTR(`unitkerjaKodeSistem`, 1,(SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`, '.')) FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`, '.') FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
   AND (subprogJeniskegId = '%s' OR 1 = %s)
ORDER BY programId,
   subprogId,
   kegrefId,
   SUBSTRING_INDEX(tmp_unit.kode, '.', 1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 2), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 3), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 4), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 5), '.', -1)+0
LIMIT %s, %s
";

$sql['get_resume'] = "
SELECT
   programId AS id,
   programNomor AS kode,
   programNama AS nama,
   GROUP_CONCAT(kegdetIsAprove),
   SUM(IFNULL(det.nominalUsulan, 0)) AS nominalUsulan,
   SUM(IFNULL(det.nominalApprove, 0)) AS nominalSetuju
FROM
   kegiatan_detail
   LEFT JOIN kegiatan
      ON kegdetKegId = kegId
   LEFT JOIN kegiatan_ref
      ON kegdetKegrefId = kegrefId
   LEFT JOIN sub_program
      ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref
      ON subprogProgramId = programId
   LEFT JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   LEFT JOIN(
      SELECT
         rncnpengeluaranKegdetId AS id,
         SUM(
            rncnpengeluaranSatuan * rncnpengeluaranKomponenNominal * IF(
               kompFormulaHasil = '0',
               1,
               IFNULL(kompFormulaHasil, 1)
            )
         ) AS nominalUsulan,
         SUM(
            IF(
               UPPER(rncnpengeluaranIsAprove) = 'YA',
               1,
               0
            ) * rncnpengeluaranSatuanAprove * rncnpengeluaranKomponenNominalAprove * IF(
               kompFormulaHasil = '0',
               1,
               IFNULL(kompFormulaHasil, 1)
            )
         ) AS nominalApprove
      FROM
         rencana_pengeluaran
         LEFT JOIN komponen
            ON kompKode = rncnpengeluaranKomponenKode
      GROUP BY rncnpengeluaranKegdetId
   ) AS det ON det.id = kegdetId
WHERE 1 = 1
   AND (kegdetIsAprove = 'Ya' OR 1 = 1)
   AND kegThanggarId = %s
   AND (subprogJeniskegId= %s OR 1 = %s)
   AND (programId= %s OR 1 = %s)
   AND (1 = 1 AND (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s))
GROUP BY programId, jeniskegId
";

$sql['get_combo_jenis_kegiatan']="
   SELECT
      jeniskegId as id,
      jeniskegNama as name
   FROM
      jenis_kegiatan_ref
   ORDER BY jeniskegId
";
?>