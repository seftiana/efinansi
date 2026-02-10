<?php
$sql['get_periode_tahun']     = "
SELECT
   thanggarId AS id,
   thanggarNama AS `name`
FROM tahun_anggaran
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
";

$sql['get_combo_jenis_kegiatan']="
SELECT
   jeniskegId as id,
   jeniskegNama as name
FROM
   jenis_kegiatan_ref
WHERE jeniskegId < 3
ORDER BY jeniskegId
";

$sql['get_data']="
SELECT
   SQL_CALC_FOUND_ROWS
   pencairan.id AS idp,
   kegdetId AS id,
   kegdetIsAprove AS `status`,
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   programId,
   programNomor AS programKode,
   programNama AS programNama,
   subprogId AS kegiatanId,
   IFNULL(subprogNomor, '') AS kegiatanKode,
   CONCAT(IFNULL(subprogNama, ''),' (',IFNULL(jeniskegNama, '-'),')') AS kegiatanNama,
   kegrefId AS subKegiatanId,
   IFNULL(kegrefNomor, '') AS subKegiatanKode,
   IFNULL(kegrefNama, '') AS subKegiatanNama,
   IF(kegdetDeskripsi = '', '-', kegdetDeskripsi) AS deskripsi,
   pencairan.tpr AS nPencairan,
   realisasi.ttr AS nTransaksi,
   IF(rencana_pengeluaran.nominalUsulan > 0,rencana_pengeluaran.nominalUsulan, 0) AS nominalUsulan,
   IF(rencana_pengeluaran.nominalSetuju > 0,rencana_pengeluaran.nominalSetuju,0) AS nominalSetuju,
   rencana_pengeluaran.nominalSetelahRevisi AS nominalSetelahRevisi,
   (IFNULL(revAsal.nilai_revisi,0) + IFNULL(revTujuan.nilai_revisi,0)) AS nominalRevisi,
   pencairan.`nomorPengajuan` AS noPengajuan,
   pencairan.`keterangan` AS keterangan,
   pencairan.statusApprove AS statusApprove,
   IF(pencairan.statusApprove = 'Ya' AND pencairan.statusApprove IS NOT NULL, IFNULL(pencairan.NominalApprove, 0), 0) AS nominalPencairanYa,
   IF(pencairan.statusApprove = 'Tidak' AND pencairan.statusApprove IS NOT NULL, IFNULL(pencairan.NominalApprove, 0), 0) AS nominalPencairanTidak,
   pencairan.totalNominalBelumApprove AS nominalPencairanBelum,
   pencairan.totalNominalApprove AS totalNominalApprove,
   pencairan.totalNominalBelumApprove AS totalNominalBelumApprove,
   pengajuan.totalNominalApprove AS totalApprove,
   pengajuan.totalNominalBelumApprove AS totalBelumApprove,
   IFNULL(tr.transId, transBank.transId) AS idTrans,
   IFNULL(tr.`transReferensi`, transBank.trReferensi) AS noBukti,
   IFNULL(tr.`transTanggalEntri`, transBank.trTanggal) AS tanggalTransaksi,
   IFNULL(tr.`transNilai`, transBank.trNilai) AS nominalRealisasi,
   IF(realisasi.nominal > 0, realisasi.nominal,0) AS nominalTotalRealisasi,
   IF(rencana_pengeluaran.nominalSetuju > 0,rencana_pengeluaran.nominalSetuju,0) - IFNULL(realisasi.nominal, 0) AS sisaDana
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
   LEFT JOIN
      (SELECT
         rncnpengeluaranKegdetId,
         SUM(rncnpengeluaranKomponenNominal * rncnpengeluaranSatuan * IF(kompFormulaHasil > 0, kompFormulaHasil, 1)) AS nominalUsulan,
         SUM(
            CASE
               WHEN rncnpengeluaranIsAprove = 'Ya'
               THEN (
                  rncnpengeluaranKomponenNominalAprove * rncnpengeluaranSatuanAprove * IF(
                     kompFormulaHasil > 0,
                     kompFormulaHasil,
                     1
                  )
               )
               WHEN rncnpengeluaranIsAprove <> 'Ya'
               THEN 0
            END
         ) AS nominalSetuju,
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
         ) AS nominalSetelahRevisi
      FROM
         rencana_pengeluaran
         LEFT JOIN komponen
            ON kompKode = rncnpengeluaranKomponenKode
      GROUP BY rncnpengeluaranKegdetId) AS rencana_pengeluaran
      ON rencana_pengeluaran.rncnpengeluaranKegdetId = kegdetId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   LEFT JOIN (
      SELECT
         pengrealId AS id,
         pengrealKegdetId AS kdId,
         pengrealIsApprove AS statusApprove,
         pengrealNomorPengajuan AS nomorPengajuan,
         pengrealKeterangan AS keterangan,
         pengrealNominalAprove AS NominalApprove,
         IF(pengrealIsApprove IS NULL, IFNULL(`pengrealdetNominalPencairan`, 0), 0) AS nominalPencairanBelum,
         COUNT( pengrealId) AS tpr,
         SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL, IFNULL(pengrealdetNominalApprove, 0), 0)) AS nominalPencairan,
         SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL, IFNULL(pengrealdetNominalApprove, 0), 0)) AS totalNominalApprove,
         SUM(IF(pengrealIsApprove IS NULL, IFNULL(pengrealdetNominalPencairan, 0), 0)) AS totalNominalBelumApprove
      FROM pengajuan_realisasi
      JOIN pengajuan_realisasi_detil
         ON pengrealdetPengRealId = pengrealId
      GROUP BY pengrealKegdetId, pengrealId
   ) AS pencairan ON pencairan.kdId = kegdetId

   LEFT JOIN (
      SELECT
         pengrealId AS id,
         pengrealKegdetId AS kdId,
         pengrealIsApprove AS statusApprove,
         pengrealNomorPengajuan AS nomorPengajuan,
         pengrealKeterangan AS keterangan,
         pengrealNominalAprove AS NominalApprove,
         IF(pengrealIsApprove IS NULL, IFNULL(`pengrealdetNominalPencairan`, 0), 0) AS nominalPencairanBelum,
         COUNT( pengrealId) AS tpr,
         SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL, IFNULL(pengrealdetNominalApprove, 0), 0)) AS nominalPencairan,
         SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL, IFNULL(pengrealdetNominalApprove, 0), 0)) AS totalNominalApprove,
         SUM(IF(pengrealIsApprove IS NULL, IFNULL(pengrealdetNominalPencairan, 0), 0)) AS totalNominalBelumApprove
      FROM pengajuan_realisasi
      JOIN pengajuan_realisasi_detil
         ON pengrealdetPengRealId = pengrealId
      GROUP BY pengrealKegdetId
   ) AS pengajuan ON pengajuan.kdId = kegdetId

   LEFT JOIN(
      SELECT
         transdtanggarKegdetId AS id,
         COUNT( transId) AS ttr,
         SUM(transNilai) AS nominal
      FROM transaksi
      JOIN transaksi_detail_anggaran
         ON transdtanggarTransId = transId
      JOIN pengajuan_realisasi
         ON pengrealId = transdtanggarPengrealId
      WHERE 1 = 1
      GROUP BY transdtanggarKegdetId
   ) AS realisasi ON realisasi.id = kegdetId
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
        mh.`movementHistoryIsApprove` = 'Ya'
        AND
        rpeng.`rncnpengeluaranIsAprove` = 'Ya'
        GROUP BY taId,unitId,kdId   
   ) AS revAsal ON revAsal.taId = kegThanggarId
   AND revAsal.unitId = unitkerjaId
   AND revAsal.kdId = kegdetId
   LEFT JOIN (
        SELECT
          mh.`movementHistoryTahunAnggaranId` AS taId,
          mh.`movementHistoryUnitKerjaIdTujuan` AS unitId,
          kd.`kegdetId` AS kdId,
         SUM(mhd.`movementHistoryDetailNilai`  ) AS nilai_revisi
        FROM `finansi_pa_movement_history_detail` mhd
        JOIN `finansi_pa_movement_history` mh 
          ON mh.`movementHistoryId` = mhd.`movementHistoryDetailMovementHistoryId`
        JOIN rencana_pengeluaran rpeng 
          ON rpeng.`rncnpengeluaranId` = mhd.`movementHistoryDetailRncnpengeluaranId`          
        JOIN kegiatan_detail kd
          ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId` 
          AND kd.`kegdetKegrefId` = mh.`movementHistoryKegrefIdTujuan`  
        WHERE 
        mhd.`movementHistoryDetailType`='tujuan'
        AND
        mh.`movementHistoryIsApprove` = 'Ya'
        AND
        rpeng.`rncnpengeluaranIsAprove` = 'Ya'
        GROUP BY taId,unitId,kdId   
   ) AS revTujuan ON revTujuan.taId = kegThanggarId
   AND revTujuan.unitId = unitkerjaId
   AND revTujuan.kdId = kegdetId
   -- LEFT JOIN pengajuan_realisasi peng_real
   -- ON peng_real.`pengrealKegdetId` = kegiatan_detail.`kegdetId`
   LEFT JOIN (
          SELECT 
            transdtPengeluaranBankId AS tdbId,
            transdtPengeluaranBankTransId AS trId,
            transdtPengeluaranBankTBankId AS tbId,
            pr.pengrealId AS prId,
            kd.kegdetId AS kdId,
            tr.`transId` AS transId,
            tr.`transThanggarId` AS taId,
            tr.`transReferensi` AS trReferensi,
            tr.`transTanggalEntri` trTanggal,
            pr.pengrealNominalAprove AS trNilai
          FROM transaksi_detail_pengeluaran_bank tdb
          JOIN finansi_pa_transaksi_bank tb
            ON tb.`transaksiBankId` = tdb.`transdtPengeluaranBankTBankId`
          LEFT JOIN finansi_pa_sppu sppu
            ON sppu.`sppuId` = tb.`transaksiBankSppuId`
          JOIN transaksi tr
            ON tr.`transId` = tdb.`transdtPengeluaranBankTransId`
          LEFT JOIN finansi_pa_sppu_det sppudet
            ON sppudet.`sppuDetSppuId` = sppu.`sppuId`
          LEFT JOIN pengajuan_realisasi_detil prd
            ON prd.`pengrealdetId` = sppudet.`sppuDetPengrealDetId`
          LEFT JOIN pengajuan_realisasi pr
            ON pr.`pengrealId` = prd.`pengrealdetPengRealId`
          LEFT JOIN kegiatan_detail kd
            ON kd.`kegdetId` = pr.`pengrealKegdetId`
          GROUP BY tr.transId, pr.pengrealId
    ) AS transBank ON transBank.prId = pencairan.id
                  AND transBank.kdId = kegiatan_detail.`kegdetId`
                  -- AND transBank.taId = kegThanggarId

   LEFT JOIN transaksi_detail_anggaran tda
      ON 
      tda.`transdtanggarPengrealId` = pencairan.id
      AND 
      tda.`transdtanggarKegdetId` = kegiatan_detail.`kegdetId`
   LEFT JOIN transaksi tr
    ON tr.`transId` = tda.`transdtanggarTransId`
    AND
     tr.`transThanggarId` =kegThanggarId   
WHERE 1 = 1
   AND kegThanggarId = %s
   AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
   AND (programId = '%s' OR 1 = %s)
   AND (jeniskegId = '%s' OR 1 = %s)
   AND (MONTH(kegdetWaktuMulaiPelaksanaan) = %s OR %s ) 
ORDER BY SUBSTRING_INDEX(tmp_unit.kode, '.', 1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 2), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 3), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 4), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 5), '.', -1)+0,
   programId,
   subprogId,
   kegrefId
LIMIT %s, %s
";

$sql['get_count']    = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_resume']    = "
SELECT SQL_CALC_FOUND_ROWS
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   SUM(IF(rencana_pengeluaran.nominalUsulan > 0,rencana_pengeluaran.nominalUsulan, 0)) AS nominalUsulan,
   SUM(IF(rencana_pengeluaran.nominalSetuju > 0,rencana_pengeluaran.nominalSetuju,0)) AS nominalSetuju,
   SUM(IF(rencana_pengeluaran.nominalSetelahRevisi > 0,rencana_pengeluaran.nominalSetelahRevisi,0)) AS nominalSetelahRevisi,
   SUM(IFNULL(revAsal.nilai_revisi,0) + IFNULL(revTujuan.nilai_revisi,0)) AS nominalRevisi,
   SUM(IFNULL(pencairan.nominalPencairanYa + pencairan.nominalPencairanTidak + pencairan.nominalPencairanBelum, 0)) AS nominalPencairan,
   SUM(IFNULL(realisasi.nominal, 0)) AS nominalRealisasi,
   SUM(IF(rencana_pengeluaran.nominalSetelahRevisi > 0,rencana_pengeluaran.nominalSetelahRevisi,0) - IFNULL(pencairan.nominalPencairanYa, 0) - IFNULL(pencairan.nominalPencairanTidak, 0) - IFNULL(pencairan.nominalPencairanBelum, 0)) AS sisaDana
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
   LEFT JOIN
      (SELECT
         rncnpengeluaranKegdetId,
         SUM(rncnpengeluaranKomponenNominal * rncnpengeluaranSatuan * IF(kompFormulaHasil > 0, kompFormulaHasil, 1)) AS nominalUsulan,
         SUM(
            CASE
               WHEN rncnpengeluaranIsAprove = 'Ya'
               THEN (
                  rncnpengeluaranKomponenNominalAprove * rncnpengeluaranSatuanAprove * IF(
                     kompFormulaHasil > 0,
                     kompFormulaHasil,
                     1
                  )
               )
               WHEN rncnpengeluaranIsAprove <> 'Ya'
               THEN 0
            END
         ) AS nominalSetuju,
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
         ) AS nominalSetelahRevisi
      FROM
         rencana_pengeluaran
         LEFT JOIN komponen
            ON kompKode = rncnpengeluaranKomponenKode
      GROUP BY rncnpengeluaranKegdetId) AS rencana_pengeluaran
      ON rencana_pengeluaran.rncnpengeluaranKegdetId = kegdetId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   LEFT JOIN (
      SELECT
         pengrealKegdetId AS id,
         SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL, IFNULL(pengrealdetNominalApprove, 0), 0)) AS nominalPencairanYa,
         SUM(IF(UPPER(pengrealIsApprove) = 'TIDAK' AND pengrealIsApprove IS NOT NULL, IFNULL(pengrealdetNominalApprove, 0), 0)) AS nominalPencairanTidak,
         SUM(IF(pengrealIsApprove IS NULL, IFNULL(pengrealdetNominalPencairan, 0), 0)) AS nominalPencairanBelum
      FROM pengajuan_realisasi
      JOIN pengajuan_realisasi_detil
         ON pengrealdetPengRealId = pengrealId
      GROUP BY pengrealKegdetId
   ) AS pencairan ON pencairan.id = kegdetId
   LEFT JOIN(
      SELECT
         transdtanggarKegdetId AS id,
         SUM(transNilai) AS nominal
      FROM transaksi
      JOIN transaksi_detail_anggaran
         ON transdtanggarTransId = transId
      JOIN pengajuan_realisasi
         ON pengrealId = transdtanggarPengrealId
      WHERE 1 = 1
      GROUP BY transdtanggarKegdetId
   ) AS realisasi ON realisasi.id = kegdetId
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
        mh.`movementHistoryIsApprove` = 'Ya'
        AND
        rpeng.`rncnpengeluaranIsAprove` = 'Ya'
        GROUP BY taId,unitId,kdId   
   ) AS revAsal ON revAsal.taId = kegThanggarId
   AND revAsal.unitId = unitkerjaId
   AND revAsal.kdId = kegdetId
   LEFT JOIN (
        SELECT
          mh.`movementHistoryTahunAnggaranId` AS taId,
          mh.`movementHistoryUnitKerjaIdTujuan` AS unitId,
          kd.`kegdetId` AS kdId,
         SUM(mhd.`movementHistoryDetailNilai`  ) AS nilai_revisi
        FROM `finansi_pa_movement_history_detail` mhd
        JOIN `finansi_pa_movement_history` mh 
          ON mh.`movementHistoryId` = mhd.`movementHistoryDetailMovementHistoryId`
        JOIN rencana_pengeluaran rpeng 
          ON rpeng.`rncnpengeluaranId` = mhd.`movementHistoryDetailRncnpengeluaranId`          
        JOIN kegiatan_detail kd
          ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId` 
          AND kd.`kegdetKegrefId` = mh.`movementHistoryKegrefIdTujuan`  
        WHERE 
        mhd.`movementHistoryDetailType`='tujuan'
        AND
        mh.`movementHistoryIsApprove` = 'Ya'
        AND
        rpeng.`rncnpengeluaranIsAprove` = 'Ya'
        GROUP BY taId,unitId,kdId   
   ) AS revTujuan ON revTujuan.taId = kegThanggarId
   AND revTujuan.unitId = unitkerjaId
   AND revTujuan.kdId = kegdetId
WHERE 1 = 1
   AND kegThanggarId = %s
   AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
   AND (programId = '%s' OR 1 = %s)
   AND (jeniskegId = '%s' OR 1 = %s)
   AND (MONTH(kegdetWaktuMulaiPelaksanaan) = %s OR %s ) 
GROUP BY kegThanggarId,kegUnitkerjaId
ORDER BY SUBSTRING_INDEX(tmp_unit.kode, '.', 1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 2), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 3), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 4), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 5), '.', -1)+0,
   programId,
   subprogId,
   kegrefId
";

$sql['get_data_detail']       = "
SELECT
   thanggarId,
   thanggarNama,
   programNomor AS programKode,
   programNama,
   subprogNomor AS kegiatanKode,
   subprogNama AS kegiatanNama,
   kegrefNomor AS subKegiatanKode,
   kegrefNama AS subKegiatanNama,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$' THEN 'satker'
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$' THEN 'unit'
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN 'fakultas'
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN 'jurusan'
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN 'prodi'
   END AS unitType,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$' THEN ref.unitkerjaId
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$' THEN satker.unitkerjaId
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN fakultas.id
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN jurusan.id
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN prodi.id
   END AS satkerId,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$' THEN ref.unitkerjaKode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$' THEN satker.unitkerjaKode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN fakultas.kode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN jurusan.kode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN prodi.kode
   END AS satkerKode,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$' THEN ref.unitkerjaNama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$' THEN satker.unitkerjaNama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN fakultas.nama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN jurusan.nama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN prodi.nama
   END AS satkerNama,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$' THEN ref.unitkerjaId
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN fakultas.unitId
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN jurusan.unitId
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN prodi.unitId
   END AS unitId,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$' THEN ref.unitkerjaKode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN fakultas.unitKode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN jurusan.unitKode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN prodi.unitKode
   END AS unitKode,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$' THEN ref.unitkerjaNama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN fakultas.unitNama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN jurusan.unitNama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN prodi.unitNama
   END AS unitNama,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN fakultas.fakultasId
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN jurusan.fakultasId
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN prodi.fakultasId
   END AS fakultasId,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN fakultas.fakultasKode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN jurusan.fakultasKode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN prodi.fakultasKode
   END AS fakultasKode,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN fakultas.fakultasNama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN jurusan.fakultasNama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN prodi.fakultasNama
   END AS fakultasNama,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN jurusan.jurusanId
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN prodi.jurusanId
   END AS jurusanId,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN jurusan.jurusanKode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN prodi.jurusanKode
   END AS jurusanKode,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN jurusan.jurusanNama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN prodi.jurusanNama
   END AS jurusanNama,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN prodi.prodiId
   END AS prodiId,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN prodi.prodiId
   END AS prodiId,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN prodi.prodiKode
   END AS prodiKode,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN prodi.prodiNama
   END AS prodiNama
FROM
   kegiatan_detail
   JOIN kegiatan
      ON kegId = kegdetKegId
   JOIN program_ref
      ON programId = kegProgramId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN sub_program
      ON subprogId = kegrefSubprogId
   JOIN unit_kerja_ref AS ref
      ON ref.unitkerjaId = kegUnitkerjaId
   JOIN (
      SELECT unitkerjaId AS id,
         CASE
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN CONCAT(unitkerjaKodeSistem, '.0')
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$' THEN unitkerjaKodeSistem
         END AS `code`
      FROM
      unit_kerja_ref
   ) AS tmp_unit ON tmp_unit.id = ref.unitkerjaId
   LEFT JOIN unit_kerja_ref AS satker
      ON satker.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$'
      AND satker.unitkerjaId = ref.unitkerjaParentId
   LEFT JOIN (
      SELECT
         satker.unitkerjaId AS id,
         satker.unitkerjaKodeSistem AS kodeSistem,
         satker.unitkerjaKode AS kode,
         satker.unitkerjaNama AS nama,
         unit.unitkerjaId AS unitId,
         unit.unitkerjaKodeSistem AS unitKodeSistem,
         unit.unitkerjaKode AS unitKode,
         unit.unitkerjaNama AS unitNama,
         fakultas.unitkerjaId AS fakultasId,
         fakultas.unitkerjaKodeSistem AS fakultasKodeSistem,
         fakultas.unitkerjaKode AS fakultasKode,
         fakultas.unitkerjaNama AS fakultasNama
      FROM unit_kerja_ref AS satker
         JOIN unit_kerja_ref AS unit
            ON unit.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$'
            AND unit.unitkerjaParentId = satker.unitkerjaId
         LEFT JOIN unit_kerja_ref AS fakultas
            ON fakultas.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$'
            AND fakultas.unitkerjaParentId = unit.unitkerjaId
      WHERE satker.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$'
   ) AS fakultas ON fakultas.fakultasId = kegUnitkerjaId
   LEFT JOIN(
   SELECT
      satker.unitkerjaId AS id,
      satker.unitkerjaKodeSistem AS kodeSistem,
      satker.unitkerjaKode AS kode,
      satker.unitkerjaNama AS nama,
      unit.unitkerjaId AS unitId,
      unit.unitkerjaKodeSistem AS unitKodeSistem,
      unit.unitkerjaKode AS unitKode,
      unit.unitkerjaNama AS unitNama,
      fakultas.unitkerjaId AS fakultasId,
      fakultas.unitkerjaKodeSistem AS fakultasKodeSistem,
      fakultas.unitkerjaKode AS fakultasKode,
      fakultas.unitkerjaNama AS fakultasNama,
      jurusan.unitkerjaId AS jurusanId,
      jurusan.unitkerjaKodeSistem AS jurusanKodeSistem,
      jurusan.unitkerjaKode AS jurusanKode,
      jurusan.unitkerjaNama AS jurusanNama
   FROM unit_kerja_ref AS satker
      JOIN unit_kerja_ref AS unit
         ON unit.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$'
         AND unit.unitkerjaParentId = satker.unitkerjaId
      LEFT JOIN unit_kerja_ref AS fakultas
         ON fakultas.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$'
         AND fakultas.unitkerjaParentId = unit.unitkerjaId
      LEFT JOIN unit_kerja_ref AS jurusan
         ON jurusan.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$'
         AND jurusan.unitkerjaParentId = fakultas.unitkerjaId
   WHERE satker.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$'
   ) AS jurusan ON jurusan.jurusanId = kegUnitkerjaId
   LEFT JOIN(
   SELECT
      satker.unitkerjaId AS id,
      satker.unitkerjaKodeSistem AS kodeSistem,
      satker.unitkerjaKode AS kode,
      satker.unitkerjaNama AS nama,
      unit.unitkerjaId AS unitId,
      unit.unitkerjaKodeSistem AS unitKodeSistem,
      unit.unitkerjaKode AS unitKode,
      unit.unitkerjaNama AS unitNama,
      fakultas.unitkerjaId AS fakultasId,
      fakultas.unitkerjaKodeSistem AS fakultasKodeSistem,
      fakultas.unitkerjaKode AS fakultasKode,
      fakultas.unitkerjaNama AS fakultasNama,
      jurusan.unitkerjaId AS jurusanId,
      jurusan.unitkerjaKodeSistem AS jurusanKodeSistem,
      jurusan.unitkerjaKode AS jurusanKode,
      jurusan.unitkerjaNama AS jurusanNama,
      prodi.unitkerjaId AS prodiId,
      prodi.unitkerjaKodeSistem AS prodiKodeSistem,
      prodi.unitkerjaKode AS prodiKode,
      prodi.unitkerjaNama AS prodiNama
   FROM unit_kerja_ref AS satker
      JOIN unit_kerja_ref AS unit
         ON unit.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4})$'
         AND unit.unitkerjaParentId = satker.unitkerjaId
      LEFT JOIN unit_kerja_ref AS fakultas
         ON fakultas.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$'
         AND fakultas.unitkerjaParentId = unit.unitkerjaId
      LEFT JOIN unit_kerja_ref AS jurusan
         ON jurusan.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$'
         AND jurusan.unitkerjaParentId = fakultas.unitkerjaId
      LEFT JOIN unit_kerja_ref AS prodi
         ON prodi.unitkerjaKodeSistem REGEXP '^([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4}).([0-9]{1,4})$'
         AND prodi.unitkerjaParentId = jurusan.unitkerjaId
   WHERE satker.unitkerjaKodeSistem REGEXP '^([0-9]{1,4})$'
   ) AS prodi ON prodi.prodiId = kegUnitkerjaId
WHERE kegdetId = %s
LIMIT 1
";

$sql['get_data_pengajuan_realisasi']   = "
SELECT
   pengrealId AS id,
   pengrealNomorPengajuan AS nomorPengajuan,
   pengrealKeterangan AS keterangan,
   pengrealTanggal AS tanggal,
   pengrealNominalAprove AS NominalApprove,
   IFNULL(transReferensi,'') AS referensi,
   IFNULL(transTanggalEntri,'') AS transTanggal,
   transNilai
FROM
  pengajuan_realisasi 
  LEFT JOIN transaksi_detail_anggaran 
    ON transdtanggarPengrealId = pengrealId 
  LEFT JOIN transaksi 
    ON transId = transdtanggarTransId 
  LEFT JOIN kegiatan_detail 
    ON kegdetId = pengrealKegdetId 
WHERE
   pengrealId  = %s
";

$sql['get_resume_unit_kerja']="
SELECT
   g.unitkerjaId,
   /*CASE WHEN g.unitkerjaNama IS NOT NULL THEN CONCAT(g.unitkerjaNama,'-',f.unitkerjaNama)
   WHEN g.unitkerjaNama IS NULL THEN f.unitkerjaNama END*/
   g.unitkerjaNama as unitName,
   /*CONCAT(CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
   WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.00.00.00') */programNomor AS kodeProg,
   ifnull(/*CONCAT(
   CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
   WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.','0',d.subprogJeniskegId,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
      WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.00')*/subprogNomor,'') AS kodeKegiatan,
   ifnull(/*CONCAT(
   CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
   WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.','0',d.subprogJeniskegId,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
      WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.',CASE WHEN LENGTH(kegrefNomor) = 1 THEN CONCAT('0',kegrefNomor)
   WHEN LENGTH(kegrefNomor) = 2 THEN kegrefNomor END)*/kegrefNomor,'') AS kodeSubKegiatan,
   programNama AS namaProgram,
   CONCAT(ifnull(subprogNama,''),' (',IFNULL(jeniskegNama, '-'),')') AS namaKegiatan,
   ifnull(kegrefNama,'') AS namaSubKegiatan,
   SUM(IF(h.nominalUsulan > 0,h.nominalUsulan,0)) AS nominalUsulan,
   SUM(IF(h.nominalSetuju > 0,h.nominalSetuju,0)) AS nominalSetuju,
   SUM(pengrealNominalAprove) AS nominal_pencairan,
   SUM(ifnull((
      SELECT
         SUM(transNilai)
      FROM
         transaksi a
         JOIN transaksi_detail_anggaran b ON transdtanggarTransId = transId
      WHERE
         transdtanggarKegdetId = kegdetId
      GROUP BY transdtanggarKegdetId
         ) ,0))
      as nominal_realisasi,
   SUM(IF(h.nominalSetuju > 0,h.nominalSetuju,0)) - SUM(ifnull((SELECT
      SUM(transNilai)
   FROM
      transaksi a
      JOIN transaksi_detail_anggaran b ON transdtanggarTransId = transId
   WHERE
      transdtanggarKegdetId = kegdetId
   GROUP BY transdtanggarKegdetId
      ),0))  as sisa
FROM
   kegiatan_detail b
   LEFT JOIN kegiatan a ON b.kegdetKegId = a.kegId
   LEFT JOIN kegiatan_ref c ON b.kegdetKegrefId = c.kegrefId
   LEFT JOIN sub_program d ON c.kegrefSubprogId = d.subprogId
   LEFT JOIN program_ref e ON d.subprogProgramId = e.programId
   LEFT JOIN unit_kerja_ref g ON kegUnitkerjaId = g.unitkerjaId

   LEFT JOIN (
      SELECT
      rncnpengeluaranKegdetId,
      SUM(rncnpengeluaranKomponenNominal*rncnpengeluaranSatuan*
         IF(kompFormulaHasil >0 ,kompFormulaHasil,1)) AS nominalUsulan,
      SUM(rncnpengeluaranKomponenNominalAprove*rncnpengeluaranSatuanAprove *
         IF(kompFormulaHasil > 0,kompFormulaHasil,1)) AS nominalSetuju
      FROM rencana_pengeluaran
     LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
      GROUP BY rncnpengeluaranKegdetId
   ) h ON h.rncnpengeluaranKegdetId = b.kegdetId
   LEFT JOIN jenis_kegiatan_ref i ON d.subprogJeniskegId = i.jeniskegId
   LEFT JOIN (SELECT pengrealKegdetId,SUM(pengrealNominal) AS pengrealNominal, SUM(pengrealNominalAprove) AS pengrealNominalAprove FROM pengajuan_realisasi WHERE (
      MONTH(pengrealTanggal) = '%s'
      OR 'all' = '%s'
   ) GROUP BY pengrealKegdetId) j ON j.pengrealKegdetId = kegdetId

WHERE
   a.kegThanggarId = %s
   AND (
            g.unitkerjaKodeSistem LIKE
            CONCAT((
               SELECT
                  unitkerjaKodeSistem
               FROM
                  unit_kerja_ref
               WHERE
                  unit_kerja_ref.unitkerjaId='%s'),'.','%s')
            OR
            g.unitkerjaKodeSistem LIKE
            CONCAT((
               SELECT
                  unitkerjaKodeSistem
               FROM
                  unit_kerja_ref
               WHERE
                  unit_kerja_ref.unitkerjaId='%s'))
   )
   AND e.programId LIKE %s AND d.subprogJeniskegId LIKE %s

GROUP BY g.unitkerjaId

ORDER BY unitName, kodeProg, kodeKegiatan


";

$sql['get_resume_program']="
SELECT
   g.unitkerjaId,
   /*CASE WHEN g.unitkerjaNama IS NOT NULL THEN CONCAT(g.unitkerjaNama,'-',f.unitkerjaNama)
   WHEN g.unitkerjaNama IS NULL THEN f.unitkerjaNama END*/
   g.unitkerjaNama as unitName,
   /*CONCAT(CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
   WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.00.00.00')*/programNomor AS kodeProg,
   ifnull(/*CONCAT(
   CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
   WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.','0',d.subprogJeniskegId,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
      WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.00')*/subprogNomor,'') AS kodeKegiatan,
   ifnull(/*CONCAT(
   CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
   WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
      WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.',CASE WHEN LENGTH(kegrefNomor) = 1 THEN CONCAT('0',kegrefNomor)
   WHEN LENGTH(kegrefNomor) = 2 THEN kegrefNomor END)*/kegrefNomor,'') AS kodeSubKegiatan,
   programNama AS namaProgram,
   CONCAT(ifnull(subprogNama,''),' (',IFNULL(jeniskegNama, '-'),')') AS namaKegiatan,
   ifnull(kegrefNama,'') AS namaSubKegiatan,
   SUM(IF(h.nominalUsulan > 0,h.nominalUsulan,0)) AS nominalUsulan,
   SUM(IF(h.nominalSetuju > 0,h.nominalSetuju,0)) AS nominalSetuju,
   SUM(ifnull((
      SELECT
         SUM(transNilai)
      FROM
         transaksi a
         JOIN transaksi_detail_pencairan b ON transdtpencairanTransId = transId
      WHERE
         transdtpencairanKegdetId = kegdetId
      GROUP BY transdtpencairanKegdetId
         ) ,0))
      as nominal_pencairan,
   SUM(ifnull((
      SELECT
         SUM(transNilai)
      FROM
         transaksi a
         JOIN transaksi_detail_anggaran b ON transdtanggarTransId = transId
      WHERE
         transdtanggarKegdetId = kegdetId
      GROUP BY transdtanggarKegdetId
         ) ,0))
      as nominal_realisasi,
   (SUM(if(h.nominalSetuju > 0,h.nominalSetuju,0)) - SUM(ifnull((SELECT
      SUM(transNilai)
   FROM
      transaksi a
      JOIN transaksi_detail_anggaran b ON transdtanggarTransId = transId
   WHERE
      transdtanggarKegdetId = kegdetId
   GROUP BY transdtanggarKegdetId
      ),0) )) as sisa

FROM
   kegiatan_detail b
   LEFT JOIN kegiatan a ON b.kegdetKegId = a.kegId
   LEFT JOIN kegiatan_ref c ON b.kegdetKegrefId = c.kegrefId
   LEFT JOIN sub_program d ON c.kegrefSubprogId = d.subprogId
   LEFT JOIN program_ref e ON d.subprogProgramId = e.programId
   LEFT JOIN unit_kerja_ref g ON kegUnitkerjaId = g.unitkerjaId

   LEFT JOIN (
      SELECT
      rncnpengeluaranKegdetId,
      sum(rncnpengeluaranKomponenNominal*rncnpengeluaranSatuan *
         IF(kompFormulaHasil > 0,kompFormulaHasil,1)) AS nominalUsulan,
      sum(rncnpengeluaranKomponenNominalAprove*rncnpengeluaranSatuanAprove *
         IF(kompFormulaHasil > 0,kompFormulaHasil,1)) AS nominalSetuju
      FROM rencana_pengeluaran
     LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
      GROUP BY rncnpengeluaranKegdetId
   ) h ON h.rncnpengeluaranKegdetId = b.kegdetId
   LEFT JOIN jenis_kegiatan_ref i ON d.subprogJeniskegId = i.jeniskegId
   LEFT JOIN pengajuan_realisasi j ON j.pengrealKegdetId = kegdetId
WHERE
   a.kegThanggarId = %s
   AND (MONTH(pengrealTanggal) = '%s' OR 'all' = '%s')
   AND (
            g.unitkerjaKodeSistem LIKE
            CONCAT((
               SELECT
                  unitkerjaKodeSistem
               FROM
                  unit_kerja_ref
               WHERE
                  unit_kerja_ref.unitkerjaId='%s'),'.','%s')
            OR
            g.unitkerjaKodeSistem LIKE
            CONCAT((
               SELECT
                  unitkerjaKodeSistem
               FROM
                  unit_kerja_ref
               WHERE
                  unit_kerja_ref.unitkerjaId='%s'))
   )
   AND e.programId LIKE %s AND d.subprogJeniskegId LIKE %s

GROUP BY g.unitkerjaId , kodeProg

ORDER BY g.unitkerjaId , kodeProg


";

$sql['get_resume_kegiatan']="
SELECT
   g.unitkerjaId,
   /*CASE WHEN g.unitkerjaNama IS NOT NULL THEN CONCAT(g.unitkerjaNama,'-',f.unitkerjaNama)
   WHEN g.unitkerjaNama IS NULL THEN f.unitkerjaNama END*/
   g.unitkerjaNama as unitName,
   /*CONCAT(CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
   WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.00.00.00')*/programNomor AS kodeProg,
   ifnull(/*CONCAT(
   CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
   WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.','0',d.subprogJeniskegId,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
      WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.00')*/subprogNomor,'') AS kodeKegiatan,
   ifnull(/*CONCAT(
   CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
   WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
      WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.',CASE WHEN LENGTH(kegrefNomor) = 1 THEN CONCAT('0',kegrefNomor)
   WHEN LENGTH(kegrefNomor) = 2 THEN kegrefNomor END)*/kegrefNomor,'') AS kodeSubKegiatan,
   programNama AS namaProgram,
   CONCAT(ifnull(subprogNama,''),' (',IFNULL(jeniskegNama, '-'),')') AS namaKegiatan,
   ifnull(kegrefNama,'') AS namaSubKegiatan,
   SUM(IF(h.nominalUsulan > 0,h.nominalUsulan,0)) AS nominalUsulan,
   SUM(IF(h.nominalSetuju > 0,h.nominalSetuju,0)) AS nominalSetuju,
   SUM(ifnull((
      SELECT
         SUM(transNilai)
      FROM
         transaksi a
         JOIN transaksi_detail_pencairan b ON transdtpencairanTransId = transId
      WHERE
         transdtpencairanKegdetId = kegdetId
      GROUP BY transdtpencairanKegdetId
         ) ,0))
      as nominal_pencairan,
   SUM(ifnull((
      SELECT
         SUM(transNilai)
      FROM
         transaksi a
         JOIN transaksi_detail_anggaran b ON transdtanggarTransId = transId
      WHERE
         transdtanggarKegdetId = kegdetId
      GROUP BY transdtanggarKegdetId
         ) ,0))
      as nominal_realisasi,
   (SUM(IF(h.nominalSetuju > 0,h.nominalSetuju,0)) - SUM(ifnull((SELECT
      SUM(transNilai)
   FROM
      transaksi a
      JOIN transaksi_detail_anggaran b ON transdtanggarTransId = transId
   WHERE
      transdtanggarKegdetId = kegdetId
   GROUP BY transdtanggarKegdetId
      ),0) )) as sisa

FROM
   kegiatan_detail b
   LEFT JOIN kegiatan a ON b.kegdetKegId = a.kegId
   LEFT JOIN kegiatan_ref c ON b.kegdetKegrefId = c.kegrefId
   LEFT JOIN sub_program d ON c.kegrefSubprogId = d.subprogId
   LEFT JOIN program_ref e ON d.subprogProgramId = e.programId
   LEFT JOIN unit_kerja_ref g ON kegUnitkerjaId = g.unitkerjaId

   LEFT JOIN (
      SELECT
      rncnpengeluaranKegdetId,
      sum(rncnpengeluaranKomponenNominal*rncnpengeluaranSatuan *
         IF(kompFormulaHasil > 0,kompFormulaHasil,1)) AS nominalUsulan,
      sum(rncnpengeluaranKomponenNominalAprove*rncnpengeluaranSatuanAprove *
         IF(kompFormulaHasil > 0,kompFormulaHasil,1)) AS nominalSetuju
      FROM rencana_pengeluaran
     LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
      GROUP BY rncnpengeluaranKegdetId
   ) h ON h.rncnpengeluaranKegdetId = b.kegdetId
   LEFT JOIN jenis_kegiatan_ref i ON d.subprogJeniskegId = i.jeniskegId
   LEFT JOIN pengajuan_realisasi j ON j.pengrealKegdetId = kegdetId
WHERE
   a.kegThanggarId = %s
   AND (MONTH(pengrealTanggal) = '%s' OR 'all' = '%s')
   AND (
               g.unitkerjaKodeSistem LIKE
            CONCAT((
               SELECT
                  unitkerjaKodeSistem
               FROM
                  unit_kerja_ref
               WHERE
                  unit_kerja_ref.unitkerjaId='%s'),'.','%s')
            OR
            g.unitkerjaKodeSistem LIKE
            CONCAT((
               SELECT
                  unitkerjaKodeSistem
               FROM
                  unit_kerja_ref
               WHERE
                  unit_kerja_ref.unitkerjaId='%s'))

   )
   AND e.programId LIKE %s AND d.subprogJeniskegId LIKE %s

   GROUP BY g.unitkerjaId , kodeProg , kodeKegiatan

   ORDER BY g.unitkerjaId , kodeProg , kodeKegiatan


";


//untuk popup
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

$sql['get_unit_kerja_id'] = "
   SELECT
      unitkerjaNama,
      unitkerjaNamaPimpinan
   FROM
      unit_kerja_ref
   WHERE
      unitkerjaId = '%s'
";

//===untuk combo box
$sql['get_data_ta'] =
   "SELECT
      thanggarId AS id,
     thanggarNama AS name
   FROM
     tahun_anggaran
   ORDER BY
     thanggarNama DESC
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

?>