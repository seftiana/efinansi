<?php
$sql['get_range_tanggal']  = "
SELECT
   MIN(tppTanggalAwal) AS tanggalAwal,
   MAX(tppTanggalAkhir) AS tanggalAkhir
FROM tahun_pembukuan_periode
";

$sql['get_periode_tahun']  = "
SELECT
   thanggarId AS `id`,
   thanggarNama AS `name`
FROM tahun_anggaran
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
";

$sql['count']        = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']     = "
SELECT SQL_CALC_FOUND_ROWS
   kegdetId AS kid,
   pengrealId AS id,
   pengrealTanggal AS tanggal,
   pengrealNomorPengajuan AS nomorPengajuan,
   IF(pengrealKeterangan IS NULL OR pengrealKeterangan = '', '-', pengrealKeterangan) AS keterangan,
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
   kegrefId AS subKegiatanId,
   IFNULL(kegrefNomor, '') AS subKegiatanKode,
   kegrefNama AS subKegiatanNama,
   kegdetDeskripsi AS lungkupKomponen,
   rencana_pengeluaran.nominalUsulan AS nominalAnggaranAwal,
   rencana_pengeluaran.nominalSetuju AS nominalAnggaranSetuju,
   (rencana_pengeluaran.nominalSetuju - rencana_pengeluaran.nominalUsulan) AS nominalAnggaranRevisi,
   pengrealNominal AS nominalUsulan,
   IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL, pengrealNominalAprove, 0) AS nominalSetuju,
   sub.total_per_subkegiatan AS total_per_subkegiatan,
   UPPER(IFNULL(pengrealIsApprove, 'Belum')) AS `status`,
   spp.count AS spp,
   spp.sppId
FROM
   pengajuan_realisasi
   JOIN kegiatan_detail
      ON pengrealKegdetId = kegdetId
   LEFT JOIN(
   SELECT 
     pengrealId AS prId,
      pengrealKegdetId AS id, 
      SUM(pengrealNominalAprove) AS total_per_subkegiatan
   FROM pengajuan_realisasi
   WHERE
      UPPER(pengrealIsApprove) = 'YA'
   AND pengrealIsApprove IS NOT NULL
   GROUP BY id
   ) AS sub ON sub.id = pengrealKegDetId 
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
      COUNT(sppDetId) AS `count`,
      sppId
   FROM pengajuan_realisasi_detil
   LEFT JOIN finansi_pa_spp_det
      ON sppDetRealdetId = pengrealdetId
   LEFT JOIN finansi_pa_spp
      ON sppId = sppDetSppId
   GROUP BY pengrealdetPengRealId
   ) AS spp ON spp.id = pengrealId
   JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
   JOIN (SELECT unitkerjaId AS id,
   CASE
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN unitkerjaKodeSistem
   END AS `code`
   FROM unit_kerja_ref) AS tmp_unit
      ON tmp_unit.id = unitkerjaId
   JOIN tahun_anggaran
      ON thanggarId = programThanggarId
   LEFT JOIN
      (SELECT
         rncnpengeluaranKegdetId,
         /* SUM(rncnpengeluaranKomponenNominal * rncnpengeluaranSatuan * IF(kompFormulaHasil > 0, kompFormulaHasil, 1)) AS nominalUsulan,*/
          SUM(
            CASE
               WHEN rncnpengeluaranIsAprove = 'Ya'
               THEN (
                  (rncnpengeluaranKomponenNominalAprove * rncnpengeluaranSatuanAprove ) * IF(
                     kompFormulaHasil > 0,
                     kompFormulaHasil,
                     1
                  )
               )
               WHEN rncnpengeluaranIsAprove <> 'Ya'
               THEN 0
            END
         ) AS nominalUsulan,/** anggaran yang sudah di setujui di awal **/
         SUM(
            CASE
               WHEN rncnpengeluaranIsAprove = 'Ya'
               THEN (
                  rncnpengeluaranKomponenTotalAprove * IF(
                     kompFormulaHasil > 0,
                     kompFormulaHasil,
                     1
                  )
               )
               WHEN rncnpengeluaranIsAprove <> 'Ya'
               THEN 0
            END
         ) AS nominalSetuju
      FROM
         rencana_pengeluaran
         LEFT JOIN komponen
            ON kompKode = rncnpengeluaranKomponenKode
      GROUP BY rncnpengeluaranKegdetId) AS rencana_pengeluaran
      ON rencana_pengeluaran.rncnpengeluaranKegdetId = kegdetId      
WHERE 1 = 1
AND UPPER(pengrealIsApprove) = 'YA'
AND programThanggarId = %s
AND (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
AND pengrealTanggal BETWEEN '%s' AND '%s'
ORDER BY programId, subprogId, kegdetId,
SUBSTRING_INDEX(tmp_unit.code, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 5), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 6), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 7), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 8), '.', -1)+0,
kegrefId,
pengrealTanggal DESC
LIMIT %s, %s
";
?>