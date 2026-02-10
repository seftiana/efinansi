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

//===GET===
$sql['get_data'] = "
SELECT SQL_CALC_FOUND_ROWS
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
   IF(rencana_pengeluaran.nominalUsulan > 0,rencana_pengeluaran.nominalUsulan, 0) AS nominalUsulan,
   IF(rencana_pengeluaran.nominalSetuju > 0,rencana_pengeluaran.nominalSetuju,0) AS nominalSetuju,
   (IFNULL(revAsal.nilai_revisi,0) + IFNULL(revTujuan.nilai_revisi,0)) AS nominalRevisi,
   rencana_pengeluaran.nominalSetelahRevisi AS nominalSetelahRevisi,
   pencairan.statusApprove AS statusApprove,
   IFNULL(pencairan.nominalPencairanYa, 0) AS nominalPencairanYa,
   IFNULL(pencairan.nominalPencairanBelum, 0) AS nominalPencairanBelum,
   IFNULL(pencairan.nominalPencairanTidak, 0) AS nominalPencairanTidak,
   (IFNULL(realisasi.nominal, 0) +  IFNULL(  transPengBank.trNilai,0)) AS nominalRealisasi,
   IF(rencana_pengeluaran.nominalSetelahRevisi > 0,rencana_pengeluaran.nominalSetelahRevisi,0) - IFNULL(pencairan.nominalPencairanYa, 0) AS sisaDana
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
         pengrealIsApprove AS statusApprove,
         SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL, IFNULL(pengrealdetNominalApprove, 0), 0)) AS nominalPencairanYa,
         SUM(IF(pengrealIsApprove IS NULL, IFNULL(pengrealdetNominalPencairan, 0), 0)) AS nominalPencairanBelum,
         SUM(IF(UPPER(pengrealIsApprove) = 'TIDAK' AND pengrealIsApprove IS NOT NULL, IFNULL(pengrealdetNominalApprove, 0), 0)) AS nominalPencairanTidak
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
      select 
         kd.kegdetId as trBankKegdetId,
         pr.pengrealId as trBankPengrealId,
         sum(pr_det.pengrealdetNominalApprove) as trNilai,  
         tpb.transaksiBankNominal as totalNominalBank,
         tpb.transaksiBankId AS transId, 
         tpb.transaksiBankBpkb AS trReferensi,
         tpb.transaksiBankTanggal trTanggal
      from
         finansi_pa_transaksi_bank tpb
         join finansi_pa_sppu sppu on sppu.sppuId = tpb.transaksiBankSppuId 
         join finansi_pa_sppu_det sppu_det on sppu_det.sppuDetSppuId = sppu.sppuId 
         join pengajuan_realisasi_detil pr_det on pr_det.pengrealdetId  = sppu_det.sppuDetPengrealDetId 
         join pengajuan_realisasi pr on pr.pengrealId = pr_det.pengrealdetPengRealId
         join kegiatan_detail kd on kd.kegdetId = pr.pengrealKegdetId 
      group by kd.kegdetId 
   ) transPengBank 
      on transPengBank.trBankKegdetId = kegdetId 
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
   AND (MONTH(`kegdetWaktuMulaiPelaksanaan`) = '%s' OR 1 = %s)
ORDER BY programId,
   subprogId,
   SUBSTRING_INDEX(tmp_unit.kode, '.', 1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 2), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 3), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 4), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 5), '.', -1)+0,
   kegrefId
LIMIT %s, %s
";

$sql['get_count_data'] = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_program_by_id']  = "
SELECT
   programId as id,
   programNomor as kode,
   programNama as nama
FROM
   program_ref
WHERE
programId=%s
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

$sql['get_combo_jenis_kegiatan']="
SELECT
   jeniskegId as id,
   jeniskegNama as name
FROM
   jenis_kegiatan_ref
ORDER BY jeniskegId
";
?>