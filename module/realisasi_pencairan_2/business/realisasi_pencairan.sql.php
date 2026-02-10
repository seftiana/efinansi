<?php

$sql['get_sisa_dana_fpa'] ="
SELECT 
   IFNULL(rp.nominal, 0) - IFNULL(pengajuan.nominalRealisasi, 0) AS sisaDana
FROM
   kegiatan_detail
   JOIN kegiatan_ref
      ON kegdetKegrefId = kegrefId
   JOIN sub_program
      ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref
      ON subprogProgramId = programId
   JOIN kegiatan
      ON kegId = kegdetKegId
   LEFT JOIN (
      SELECT
         rncnpengeluaranKegdetId AS id,
         SUM(IF(UPPER(rncnpengeluaranIsAprove) = 'YA', rncnpengeluaranKomponenTotalAprove, 0)) AS nominal
      FROM rencana_pengeluaran
      GROUP BY rncnpengeluaranKegdetId
   ) AS rp ON rp.id = kegdetId
   LEFT JOIN (
      SELECT
         pengrealKegdetId AS id,
         SUM(IFNULL(pengrealNominal, 0)) AS nominal,         
         SUM(IF(UPPER(pengrealIsApprove) = 'YA', IFNULL(pengrealNominalAprove, 0), IF(UPPER(pengrealIsApprove) = 'TIDAK',0,IFNULL(pengrealNominal, 0)))) AS nominalRealisasi,
         SUM(IF(UPPER(pengrealIsApprove) = 'YA', IFNULL(pengrealNominalAprove, 0), 0)) AS nominalApprove,
         IFNULL(pengrealIsApprove, 'Tidak') AS status_approve
      FROM pengajuan_realisasi
      WHERE 1 = 1
      AND (pengrealId !='%s' OR 1 = %s)
      GROUP BY pengrealKegdetId
   ) AS pengajuan ON pengajuan.id = kegdetId
WHERE kegdetId = %s
";

$sql['get_periode_tahun']   = "
SELECT
   thanggarId AS id,
   thanggarNama AS `name`,
   thanggarIsAktif AS `active`,
   thanggarIsOpen AS `open`,
   renstraTanggalAwal AS `start`,
   renstraTanggalAkhir AS `end`
FROM tahun_anggaran
JOIN renstra
   ON renstraId = thanggarRenstraId
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
ORDER BY thanggarNama
";

$sql['get_setting_name']      = "
SELECT
   settingValue AS `name`
FROM setting
WHERE 1 = 1
AND settingName = '%s'
LIMIT 1
";

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']  = "
SELECT SQL_CALC_FOUND_ROWS
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
   pengrealNominal AS nominalUsulan,
   IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL, pengrealNominalAprove, 0) AS nominalSetuju,
   UPPER(IFNULL(pengrealIsApprove, 'Belum')) AS `status`,
   spp.count AS spp,
   spp.sppId,
   gu.`RealName` AS user_approval
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
   LEFT JOIN `gtfw_user` gu
      ON gu.`UserId` = `pengrealUserId`   
WHERE 1 = 1
AND programThanggarId = %s
AND (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
AND (programId = '%s' OR 1 = %s)
AND kegrefNomor LIKE '%s'
AND kegrefNama LIKE '%s'
AND (jeniskegId = '%s' OR 1 = %s)
AND (MONTH(pengrealTanggal) = %s OR 1 = %s)
AND pengrealNomorPengajuan LIKE '%s'
ORDER BY pengrealTanggal DESC
LIMIT %s, %s
";

$sql['get_data_pengajuan_realisasi_det']  = "
SELECT
   pengrealId AS id,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   thanggarId AS taId,
   thanggarNama AS taNama,
   programId AS programId,
   programNomor AS programKode,
   programNama AS programNama,
   subprogId AS kegiatanId,
   subprogNomor AS kegiatanKode,
   subprogNama AS kegiatanNama,
   kegrefId AS subKegiatanId,
   kegrefNomor AS subKegiatanKode,
   kegrefNama AS subKegiatanNama,
   kegdetId AS kegiatanDetId,
   pengrealNomorPengajuan AS nomorPengajuan,
   pengrealNominal AS nominal,
   pengrealKeterangan AS keterangan,
   pengrealFile AS fpaFile,
   pengrealTanggal AS tanggal,
   pengrealIsApprove AS `status`,
   anggaran.nominalAnggaran,
   anggaran.nominalRealisasi,
   anggaran.nominalPencairan,
   (pengrealNominal/anggaran.nominalAnggaran)*100 AS persen
FROM pengajuan_realisasi
   JOIN kegiatan_detail
      ON pengrealKegdetId = kegdetId
   JOIN kegiatan_ref
      ON kegdetKegrefId = kegrefId
   JOIN sub_program
      ON kegrefSubprogId = subprogId
   JOIN program_ref
      ON subprogProgramId = programId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   JOIN kegiatan
      ON kegdetKegId = kegId
   JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
   LEFT JOIN tahun_anggaran
      ON thanggarId = programThanggarId
   LEFT JOIN (
      SELECT
         kegdetId AS id,
         IFNULL(rp.nominal, 0)-IFNULL(pengajuan.nominal, 0) AS nominalAnggaran,
         IFNULL(pengajuan.nominal, 0) AS nominalRealisasi,
         IFNULL(`real`.nominal, 0) AS nominalPencairan
      FROM
         kegiatan_detail
         LEFT JOIN (
            SELECT
               rncnpengeluaranKegdetId AS id,
               IF(UPPER(rncnpengeluaranIsAprove) = 'YA', SUM((rncnpengeluaranKomponenTotalAprove)), 0) AS nominal
            FROM rencana_pengeluaran
            GROUP BY rncnpengeluaranKegdetId
         ) AS rp ON rp.id = kegdetId
         LEFT JOIN (
            SELECT
               realisasi.id,
               SUM(IFNULL(realisasi.nominal, 0)) AS nominal
            FROM ((SELECT
               pengrealKegdetId AS id,
               SUM(pengrealNominalAprove) AS nominal
            FROM
               pengajuan_realisasi
            WHERE 1 = 1
            AND pengrealIsApprove = 'Ya'
            AND (pengrealId != %s OR 1 = %s)
            GROUP BY pengrealKegdetId)
            UNION
            (SELECT
               kegAdjustAmblKegId AS id,
               SUM(kegAdjustNominal) AS nominal
            FROM kegiatan_adjust
            GROUP BY kegAdjustAmblKegId)) AS realisasi
            GROUP BY realisasi.id
         ) AS `real` ON `real`.id = kegdetId
         LEFT JOIN (
            SELECT
               pengrealKegdetId AS id,
               SUM(IFNULL(pengrealNominal, 0)) AS nominal,
               SUM(IF(UPPER(pengrealIsApprove) = 'YA', IFNULL(pengrealNominalAprove, 0), 0)) AS nominalApprove,
               IFNULL(pengrealIsApprove, 'Tidak') AS status_approve
            FROM pengajuan_realisasi
            WHERE 1 = 1
            AND (pengrealId != %s OR 1 = %s)
            GROUP BY pengrealKegdetId
         ) AS pengajuan ON pengajuan.id = kegdetId
      GROUP BY kegdetId
   ) AS anggaran ON anggaran.id = pengrealKegdetId
WHERE pengrealId = %s
LIMIT 0, 1
";

$sql['get_komponen_anggaran_pengajuan_realisasi']   = "
SELECT
   rncnpengeluaranKegdetId AS kegdetId,
   rncnpengeluaranId AS id,
   rncnpengeluaranKomponenKode AS komponenKode,
   rncnpengeluaranKomponenNama AS komponenNama,   
   pengrealdetDeskripsi AS deskripsi,
   coaId AS makId,
   IF(UPPER(rncnpengeluaranIsAprove) = 'YA', (rncnpengeluaranKomponenTotalAprove), 0) - IFNULL(realisasi.nominal, 0) AS nominal,
   coaKodeAkun AS makKode,
   komp.kompIsPengadaan AS isPengadaan,
   pengrealdetNominalPencairan AS nominalBudget,
   IF(UPPER(rncnpengeluaranIsAprove) = 'YA' , pengrealdetNominalApprove, 0) AS nominalApprove,
   IF(UPPER(rncnpengeluaranIsAprove) = 'YA', (IFNULL(rncnpengeluaranSatuanAprove,0)*rncnpengeluaranKomponenNominalAprove), 0) - IFNULL(realisasi.nominal, 0) - pengrealdetNominalPencairan AS sisaDana
FROM
   rencana_pengeluaran
   LEFT JOIN finansi_ref_pagu_bas
      ON paguBasId = rncnpengeluaranMakId
   JOIN pengajuan_realisasi_detil
      ON pengrealdetRncnpengeluaranId = rncnpengeluaranId
   LEFT JOIN (
      SELECT
         pengrealdetRncnpengeluaranId AS id,
         SUM(pengrealdetNominalPencairan) AS nominal
      FROM pengajuan_realisasi_detil
      JOIN pengajuan_realisasi
         ON pengrealId = pengrealdetPengRealId
      JOIN rencana_pengeluaran
         ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
      WHERE 1 = 1
      AND (pengrealdetPengRealId != %s OR 1 = %s)
      GROUP BY pengrealdetRncnpengeluaranId
   ) AS realisasi ON realisasi.id = rncnpengeluaranId
   LEFT JOIN ( 
        SELECT
          mh.`movementHistoryTahunAnggaranId` AS taId,
          mh.`movementHistoryUnitKerjaIdAsal` AS unitId,
          kd.`kegdetId` AS kdId,
         (0 - SUM(mhd.`movementHistoryDetailNilai`)) AS nilai_revisi
        FROM `finansi_pa_movement_history_detail` mhd
        JOIN `finansi_pa_movement_history` mh 
          ON mh.`movementHistoryId` = mhd.`movementHistoryDetailMovementHistoryId`
        JOIN rencana_pengeluaran rpeng 
          ON rpeng.`rncnpengeluaranId` = mhd.`movementHistoryDetailRncnpengeluaranId` 
        JOIN kegiatan_detail kd
          ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId` 
          AND kd.`kegdetKegrefId` = mh.`movementHistoryKegrefIdAsal`  
        WHERE 
        mhd.`movementHistoryDetailType`='asal'
        AND
        mh.`movementHistoryIsApprove` != 'Tidak'
        AND
        rpeng.`rncnpengeluaranIsAprove` = 'Ya'
        GROUP BY taId,unitId,kdId   
   ) AS revAsal ON revAsal.kdId = rncnpengeluaranKegdetId
   LEFT JOIN komponen komp
	ON komp.`kompKode` = `rncnpengeluaranKomponenKode`
   LEFT JOIN coa c
        ON c.`coaId` =  komp.`kompCoaId`
WHERE 1 = 1
   AND pengrealdetPengRealId = %s
ORDER BY rncnpengeluaranKegdetId ASC,
rncnpengeluaranKomponenKode
";

$sql['get_data_program'] = "
SELECT
   programId AS id,
   programNama AS name
FROM
  program_ref
WHERE
   programThanggarId = '%s'
ORDER BY
  programNama ASC
";

$sql['get_data_jenis_kegiatan'] = "
SELECT
   jeniskegId AS id,
   jeniskegNama AS name
FROM
  jenis_kegiatan_ref
WHERE jeniskegId < 3
ORDER BY
  jeniskegNama ASC
";

//===GET===
$sql['get_data_by_id']="
SELECT
   pr.pengrealId AS id,
   ta.`thanggarId` AS ta_id,
   ta.`thanggarNama` AS ta_label,
   pr.pengrealTanggal AS tanggal,
   pr.pengrealNomorPengajuan AS nomor_pengajuan,
   pr.pengrealNominal AS nominal,
   pr.pengrealKeterangan AS keterangan,
   pr.pengrealFile AS fpa_file,
   kd.kegdetId AS kegiatandetail_id,
   k.kegId AS kegiatanunit_id,

   /*IF (uk.unitkerjaParentId = '0' , uk.unitkerjaId , (SELECT unitkerjaId FROM unit_kerja_ref WHERE unitkerjaId=uk.unitkerjaParentId))*/
   uk.unitkerjaId as unit_id,

   /*IF (uk.unitkerjaParentId = '0' , uk.unitkerjaNama , (SELECT unitkerjaNama FROM unit_kerja_ref WHERE unitkerjaId=uk.unitkerjaParentId))*/
   uk.unitkerjaNama as unit_nama,

   /*IF (uk.unitkerjaParentId != '0' , uk.unitkerjaId,'') subunit_id,
   IF (uk.unitkerjaParentId != '0' , uk.unitkerjaNama,'') subunit_nama,*/

   prog.programId AS program_id,
   prog.programNama AS program_nama,

   sp.subprogId AS kegiatan_id,
   sp.subprogNama AS kegiatan_nama,
   gu.`RealName` AS user_approval,
   kr.kegrefId AS subkegiatan_id,
   kr.kegrefNama AS subkegiatan_nama,
(SELECT
         SUM(rp.rncnpengeluaranKomponenNominalAprove*rp.rncnpengeluaranSatuanAprove)
       FROM
         rencana_pengeluaran rp
       WHERE
         rp.rncnpengeluaranKegdetId=kd.kegdetId
      ) AS total_anggaran,

       (IFNULL((SELECT pengrealNominal FROM pengajuan_realisasi WHERE pengrealKegdetId=kd.kegdetId AND  pengrealIsApprove IS NULL AND pengrealId=%s),0)) AS realisasi_nominal,

       (IFNULL((SELECT pengrealNominalAprove FROM pengajuan_realisasi WHERE pengrealKegdetId=kd.kegdetId AND pengrealIsApprove = 'Ya' AND pengrealId=%s),0)) AS realisasi_pencairan

FROM
   pengajuan_realisasi pr
   JOIN kegiatan_detail kd ON (pr.pengrealKegdetId= kd.kegdetId)
   JOIN kegiatan_ref kr ON (kd.kegdetKegrefId = kr.kegrefId)
   JOIN sub_program sp ON (kr.kegrefSubprogId = sp.subprogId)
   JOIN program_ref prog ON (sp.subprogProgramId = prog.programId)
   LEFT JOIN jenis_kegiatan_ref jk ON ( sp.subprogJeniskegId = jk.jeniskegId)
   JOIN kegiatan k ON (kd.kegdetKegId=k.kegId)
   JOIN unit_kerja_ref uk ON (k.kegUnitkerjaId=uk.unitkerjaId)
   LEFT JOIN `tahun_anggaran` AS ta
   ON ta.`thanggarId` = prog.`programThanggarId`
   LEFT JOIN gtfw_user gu
   ON gu.`UserId` = pr.`pengrealUserId`
WHERE
   pr.pengrealId=%s
LIMIT 1


";

$sql['get_min_tahun']="
SELECT
   IFNULL(MIN(pengrealTanggal), DATE(NOW())) as min
FROM
   pengajuan_realisasi
";

$sql['get_max_tahun']="
SELECT
   IFNULL(MIN(pengrealTanggal), DATE_ADD(DATE(NOW()), INTERVAL 1 YEAR)) as max
FROM
   pengajuan_realisasi
";

$sql['get_jenis_kegiatan']="
   SELECT
      sp.subprogJeniskegId AS jenis_kegiatan
   FROM
      kegiatan_detail kd
      JOIN kegiatan k ON k.kegId=kd.kegdetKegId
      JOIN program_ref pr ON pr.programId = k.kegProgramId
      JOIN sub_program sp ON sp.subprogId = pr.programId
   WHERE kd.kegdetId=%s
   LIMIT 1
";

$sql['get_rencana_nominal']="
SELECT
   SUM(rp.rncnpengeluaranKomponenTotalAprove) AS nominal_approve
FROM
   rencana_pengeluaran rp
WHERE
   rp.rncnpengeluaranKegdetId=%s
";

$sql['get_persentase_rp_per_komponen']="
SELECT
   rp.rncnpengeluaranId AS rp_id,
   rp.rncnpengeluaranKomponenKode AS rp_komp,
   rp.rncnpengeluaranKomponenNominalAprove*rp.rncnpengeluaranSatuanAprove AS nominal_approve_komponen,
   ((rp.rncnpengeluaranKomponenNominalAprove*rp.rncnpengeluaranSatuanAprove)/
   (SELECT SUM(a.rncnpengeluaranKomponenNominalAprove*a.rncnpengeluaranSatuanAprove) FROM rencana_pengeluaran a WHERE a.rncnpengeluaranKegdetId='%s')
   )*100 AS persen
FROM
   rencana_pengeluaran rp
WHERE
   rp.rncnpengeluaranKegdetId='%s'
";

$sql['get_realisasi_nominal']="
SELECT
   SUM(nominal) AS nominal,
   SUM(nominal_approve) AS nominal_approve
FROM
   (
      SELECT
         pengrealKegdetId,
         IF(pengrealIsApprove = 'Ya', 0, pengrealNominal) AS nominal,
         pengrealNominalAprove AS nominal_approve
      FROM
         pengajuan_realisasi
      UNION SELECT
         kegAdjustAmblKegId,
         0,
         kegAdjustNominal
      FROM
         kegiatan_adjust
   ) AS uni
WHERE
   pengrealKegdetId = %s
";

$sql['get_realisasi_nominal_edit']="
SELECT
   'all' AS tipe,
   SUM(nominal) AS nominal,
   SUM(nominal_approve) AS nominal_approve
FROM
   (
      SELECT
         pengrealKegdetId,
         IF(pengrealIsApprove = 'Ya', 0, pengrealNominal) AS nominal,
         pengrealNominalAprove AS nominal_approve
      FROM
         pengajuan_realisasi
      UNION SELECT
         kegAdjustAmblKegId,
         0,
         kegAdjustNominal
      FROM
         kegiatan_adjust
   ) AS uni
WHERE
   pengrealKegdetId = %s

UNION

SELECT
   'single' AS tipe,
   pengrealNominal AS nominal,
   pengrealNominalAprove AS nominal_approve
FROM
   pengajuan_realisasi
WHERE
   pengrealId=%s
";

$sql['get_komponen']="
SELECT
      rncnpengeluaranId AS id,
      rncnpengeluaranKomponenKode AS kode,
      rncnpengeluaranKomponenNama AS nama,
      rncnpengeluaranKomponenNominal * IF(kompFormulaHasil = '0',1,kompFormulaHasil) AS nominal_usulan,
      rncnpengeluaranSatuan AS satuan_usulan,
      (rncnpengeluaranSatuan * rncnpengeluaranKomponenNominal * IF(kompFormulaHasil = 0,1,kompFormulaHasil)) AS jumlah_usulan,
      rncnpengeluaranKomponenNominalAprove * IF(kompFormulaHasil = '0',1,kompFormulaHasil) AS nominal_setuju,
      rncnpengeluaranSatuanAprove  AS satuan_setuju,
      (rncnpengeluaranSatuanAprove * rncnpengeluaranKomponenNominalAprove * IF(kompFormulaHasil = 0,1,kompFormulaHasil)) AS jumlah_setuju,
       rncnpengeluaranKomponenDeskripsi AS deskripsi,
      rncnpengeluaranIsAprove AS approval,
      IF(kompFormulaHasil='0',1,kompFormulaHasil) AS hasil_formula,
      IFNULL(rncnpengeluaranKeterangan, '-') AS keterangan
   FROM
      rencana_pengeluaran
      LEFT JOIN kegiatan_detail ON (kegdetId = rncnpengeluaranKegdetId)
      LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
   WHERE
     rncnpengeluaranKegdetId=%s
   ORDER BY
     rncnpengeluaranKomponenKode;
";

$sql['get_pengajuan_realisasi_detail']   = "
SELECT
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   pengrealId AS id,
   IF(
      UPPER(rncnpengeluaranIsAprove) = 'YA',
      1,
      0
   ) * rncnpengeluaranKomponenTotalAprove * IF(
      kompFormulaHasil = '0',
      1,
      IFNULL(kompFormulaHasil, 1)
   ) AS anggaranApprove,
   -- IF(IFNULL(rncnpengeluaranSatuanAprove, 1) = 0 AND rncnpengeluaranKomponenNominalAprove != 0, rncnpengeluaranSatuan, IFNULL(rncnpengeluaranSatuanAprove, 1))*rncnpengeluaranKomponenNominalAprove AS anggaranApprove,
   pengrealdetNominalPencairan AS nominal,
   pengrealdetNominalApprove AS nominalApprove,
   rncnpengeluaranId,
   coaId AS akunId,
   IFNULL(coaKodeAkun, '-') AS akunKode,
   IFNULL(coaNamaAkun, '-') AS akunNama,
   kompKode AS komponenKode,
   kompNama AS komponenNama,
   kegrefNomor AS kegiatanKode,
   kegrefNama AS kegiatanNama,
   pengrealdetDeskripsi AS keterangan,
   pengrealNomorPengajuan AS nomorPengajuan,
   kompIsPengadaan AS isPengadaan,
   (SELECT SUM(nom.pengrealdetNominalPencairan) FROM pengajuan_realisasi_detil nom WHERE nom.pengrealdetRncnpengeluaranId=rncnpengeluaranId)
   AS totalFpa #add ccp 14-8-2019 request pak edi
FROM
   pengajuan_realisasi
   JOIN pengajuan_realisasi_detil
      ON pengrealdetPengRealId = pengrealId
   LEFT JOIN pengajuan_realisasi_coa
      ON prcoaPengrealId = pengrealId
   JOIN kegiatan_detail
      ON pengrealKegdetId = kegdetId
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
   JOIN rencana_pengeluaran
      ON rncnpengeluaranKegdetId = kegdetId
      AND rncnpengeluaranId = pengrealdetRncnpengeluaranId
   LEFT JOIN komponen
      ON kompKode = rncnpengeluaranKomponenKode
   LEFT JOIN coa
      ON coaId = kompCoaId
WHERE 1 = 1
   AND pengrealId = %s
   AND 
    pengrealdetNominalPencairan > 0
";

//== for combo box ==
$sql['get_data_ta'] =
   "SELECT
      thanggarId AS id,
     thanggarNama AS name
   FROM
     tahun_anggaran
   ORDER BY
     thanggarNama DESC
   ";


$sql['get_data_satuan_komponen'] =
   "SELECT
      satkompNama AS id,
     satkompNama AS name
   FROM
     satuan_komponen
   ORDER BY
     satkompNama ASC
   ";

$sql['get_ta_aktif']=
   "SELECT
      thanggarId AS id,
     thanggarNama AS nama
   FROM
     tahun_anggaran
   WHERE
     thanggarIsAktif='Y'
   LIMIT 1
   ";
$sql['get_unit_kerja']=
   "SELECT
      unitkerjaId AS unitkerja_id,
      unitkerjaKode AS unitkerja_kode,
      unitkerjaNama AS unitkerja_nama
   FROM
     unit_kerja_ref
   WHERE
     unitkerjaParentId LIKE %s AND
     unitkerjaNama LIKE %s
   ORDER BY
     unitkerjaKode, UnitkerjaNama ASC
   LIMIT %s, %s
   ";

$sql['get_count_unit_kerja']=
   "SELECT
      COUNT(unitkerjaId) AS total
   FROM
     unit_kerja_ref
   WHERE
     unitkerjaParentId LIKE %s AND
     unitkerjaNama LIKE %s
   ORDER BY
     unitkerjaKode, UnitkerjaNama ASC
   LIMIT 1
   ";

$sql['get_sql_formula_nomor_pengajuan']="
   SELECT
      formulaFormula
   FROM
      finansi_ref_formula
   WHERE
      formulaCode = 'SELECT_NO_PENGAJUAN_REALISASI'
   AND
      formulaIsAktif = 'Y'
   LIMIT 0,1
";

$sql['get_max_id']="
   SELECT
      MAX(pengrealId) AS max_id
   FROM
      pengajuan_realisasi
   LIMIT 0, 1
";


//===DO===
$sql['do_add'] = "
INSERT INTO pengajuan_realisasi
SET pengrealKegdetId = '%s',
pengrealNomorPengajuan = '%s',
pengrealNominal = '%s',
pengrealKeterangan = '%s',
pengrealUserId = '%s',
pengrealTanggal = '%s',
pengrealIsApprove = NULL,
pengrealNominalAprove = 0
";

$sql['do_add_detail']   = "
INSERT INTO pengajuan_realisasi_detil
SET pengrealdetPengRealId = '%s',
pengrealdetRncnpengeluaranId = '%s',
pengrealdetDeskripsi = '%s',
pengrealdetNominalPencairan = '%s',
pengrealdetNominalApprove = 0,
pengrealdetTanggal = '%s',
pengrealdetUserId = '%s'
";

$sql['do_update'] = "
UPDATE pengajuan_realisasi
SET pengrealKegdetId = '%s',
pengrealNomorPengajuan = '%s',
pengrealNominal = '%s',
pengrealKeterangan = '%s',
pengrealUserId = '%s',
pengrealTanggal = '%s'
WHERE
  pengrealId = %s
";

$sql['do_delete']="
DELETE FROM pengajuan_realisasi
WHERE pengrealId = %s
";

$sql['do_delete_pengajuan_detil']   = "
DELETE FROM pengajuan_realisasi_detil
WHERE pengrealdetPengRealId = '%s'
";

$sql['do_update_file'] = "
UPDATE pengajuan_realisasi
SET pengrealFile = '%s'
WHERE pengrealId = %s
";




//---Get data cetak---------------------------------------------
$sql['get_data_cetak']=
   "SELECT
   (SELECT
     SUM(IF(rncnpengeluaranIsAprove='Ya',1,0) * rncnpengeluaranKomponenTotalAprove) AS is_approve
   FROM rencana_pengeluaran
   WHERE rncnpengeluaranKegdetId = kegdetId) AS jumlah_anggaran,
   pengrealNominal AS jumlah_anggaran_diminta,
   pengrealNominalAprove AS jumlah_anggaran_disetujui,
   pengrealNomorPengajuan AS no_pengajuan,
   thanggarNama AS tahun_anggaran,
   kegPIC AS nama_pejabat,
   rncnpengeluaranKomponenKode AS kode_anggaran,
   kegrefNama AS nama_kegiatan,
   kegdetWaktuMulaiPelaksanaan  AS anggaran_bulan,
   subprogNama AS sk_kegiatan,
   unitkerjaNama AS jurusan,

   subprogId AS kegiatan_id,
   ifnull(CONCAT(CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
                  WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
                  WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.00'),'') AS sk_kegiatan

   FROM
      rencana_pengeluaran
   JOIN kegiatan_detail ON kegdetId=rncnpengeluaranKegdetId
   LEFT JOIN prioritas_ref ON prioritasId=kegdetPrioritasId
   JOIN kegiatan ON kegId=kegdetKegId
   JOIN kegiatan_ref ON kegrefId=kegdetKegrefId
   JOIN unit_kerja_ref ON unitkerjaId=kegUnitkerjaId
   JOIN program_ref ON programId=kegProgramId
   JOIN unit_status ON unitStatusId=unitKerjaUnitStatusId
   JOIN tipe_unit_kerja_ref ON tipeunitId=unitkerjaTipeunitId
   JOIN pengajuan_realisasi ON pengrealKegdetId=kegdetId
   JOIN gtfw_user ON UserId=pengrealUserId
   JOIN tahun_anggaran ON thanggarId=kegThanggarId
   JOIN sub_program ON subprogId=kegrefSubprogId
   WHERE pengrealId=%s ";

$sql['get_transaksi_pencairan']="
SELECT
  transId AS id,
  transReferensi AS nomor_trans,
  transTanggalEntri AS tanggal_trans,
  transNilai AS nominal_trans
FROM transaksi
  JOIN transaksi_detail_pencairan
    ON transId = transdtpencairanTransId
  JOIN pengajuan_realisasi
    ON transdtpencairanPengrealId = pengrealId
WHERE pengrealIsApprove = 'Ya'
    AND transdtpencairanKegdetId = (SELECT
                                      pengrealKegdetId
                                    FROM pengajuan_realisasi
                                    WHERE pengrealId = %s);
";

/**
 * untuk mendapatkan jumlah sub unit
 * @since 3 Januari 2012
 */
$sql['get_total_sub_unit_kerja']="
SELECT
   count(unitkerjaId) as total
FROM unit_kerja_ref
WHERE unitkerjaParentId = %s
";

# untuk mendapatkan komponen anggaran berdasarkan data realisasi pencairan
$sql['get_komponen_anggaran_by_realisasi'] = "
SELECT
    rp.rncnpengeluaranId AS rp_id,
    rp.rncnpengeluaranKomponenKode AS rp_kompkode,
    rp.rncnpengeluaranKomponenNama AS rp_kompnama,
    rd.pengrealdetNominalApprove AS nilai,
    rncnpengeluaranMakId AS mak_id ,
    pb.`paguBasKode` AS mak_kode
FROM
    rencana_pengeluaran AS rp
   RIGHT JOIN pengajuan_realisasi_detil AS rd
      ON rd.`pengrealdetRncnpengeluaranId` = rp.`rncnpengeluaranId`
   LEFT JOIN finansi_ref_pagu_bas pb
    ON pb.`paguBasId` = rncnpengeluaranMakId
WHERE rd.`pengrealdetPengRealId` = '%s'
";

/**
* untuk mengecek tahun anggaran yang aktif
* */
$sql['check_tahun_anggaran'] = "
    SELECT
        `thanggarId` AS id,
        `thanggarNama` AS nama,
        `thanggarIsAktif` AS is_aktif,
        `thanggarIsOpen` AS is_open,
        `thanggarBuka` AS buka,
        `thanggarTutup` AS tutup
    FROM `tahun_anggaran`
    WHERE `thanggarId` = '%s'
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

// check pagu anggaran
$sql['check_pagu_anggaran']   = "
SELECT SQL_CALC_FOUND_ROWS
   paguAnggUnitId,
   paguAnggUnitNominal,
   paguAnggUnitNominalTersedia,
   paguAnggMakId,
   paguAnggUnitBintang
FROM
   finansi_pagu_anggaran_unit
WHERE paguAnggMakId IN (%s)
";

$sql['count_pagu']      = "
SELECT
   COUNT(DISTINCT `paguAnggUnitId`) AS total_pagu
FROM `finansi_pagu_anggaran_unit`
WHERE
   paguAnggUnitUnitKerjaId = '%s'
   AND paguAnggUnitThAnggaranId = '%s'
   AND paguAnggMakId = '%s'
";
?>
