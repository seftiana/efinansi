<?php
$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_kegiatan']  = "
SELECT
   kegdetId AS id,
   unitkerjaId,
   unitkerjaKode,
   unitkerjaNama,
   thanggarId,
   thanggarNama,
   programId,
   programNomor AS programKode,
   programNama,
   subprogId AS kegiatanId,
   subprogNomor AS kegiatanKode,
   subprogNama AS kegiatanNama,
   kegrefId AS subkegiatanId,
   kegrefNomor AS subkegiatanKode,
   kegrefNama AS subkegiatanNama
FROM
   kegiatan_detail
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN sub_program
      ON subprogId = kegrefSubprogId
   JOIN program_ref
      ON programId = subprogProgramId
   JOIN tahun_anggaran
      ON thanggarId = programThanggarId
   JOIN kegiatan
      ON kegId = kegdetKegId
   JOIN unit_kerja_ref
      ON unitkerjaId = kegUnitkerjaId
WHERE 1 = 1
   AND kegdetId = %d
";

$sql['get_data_kegiatan_detail']   = "
SELECT SQL_CALC_FOUND_ROWS
   rncnpengeluaranId AS id,
   kegdetId,
   kompId AS komponenId,
   kompKode AS komponenKode,
   kompNama AS komponenNama,
   paguBasId AS makId,
   paguBasKode AS makKode,
   paguBasKeterangan AS makNama,
   IFNULL(rncnpengeluaranKeterangan, '-') AS keterangan,
   IFNULL(rncnpengeluaranFormula,1) AS formulaHasil,
   rncnpengeluaranIsAprove AS `status`,
   rncnpengeluaranKomponenDeskripsi AS deskripsi,
   rncnpengeluaranSatuan AS satuan,
   rncnpengeluaranNamaSatuan AS satuanNama,
   IFNULL(rncnpengeluaranFormula,1)*rncnpengeluaranKomponenNominal AS satuanNominal,
   rncnpengeluaranSatuan*(IFNULL(rncnpengeluaranFormula,1)*rncnpengeluaranKomponenNominal) AS nominalTotal,
   rncnpengeluaranSatuanAprove AS satuanApprove,
   revAsal.rpId AS rpengAsalId,
   revTujuan.rpId AS rpengTujuanId,
   IF(revAsal.rpId IS NOT NULL OR revTujuan.rpId IS NOT NULL, IFNULL(rncnpengeluaranFormula,1)*rncnpengeluaranKomponenTotalAprove, IFNULL(rncnpengeluaranFormula,1)*rncnpengeluaranKomponenNominalAprove) AS satuanNominalApprove,
   -- IFNULL(rncnpengeluaranFormula,1)*rncnpengeluaranKomponenNominalAprove AS satuanNominalApprove,
   IF(revAsal.id IS NOT NULL OR revTujuan.id IS NOT NULL, IFNULL(rncnpengeluaranFormula,1)*rncnpengeluaranKomponenTotalAprove, rncnpengeluaranSatuanAprove*(IFNULL(rncnpengeluaranFormula,1)*rncnpengeluaranKomponenNominalAprove)) AS nominalTotalApprove
   -- rncnpengeluaranSatuanAprove*(IFNULL(rncnpengeluaranFormula,1)*rncnpengeluaranKomponenNominalAprove) AS nominalTotalApprove
FROM
   rencana_pengeluaran
   JOIN kegiatan_detail
      ON kegdetId = rncnpengeluaranKegdetId
   JOIN kegiatan
      ON kegId = kegdetKegId
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN komponen_kegiatan
      ON kompkegKegrefId = kegrefId
   JOIN komponen
      ON kompId = kompkegKompId
      AND kompKode = rncnpengeluaranKomponenKode
   LEFT JOIN finansi_ref_pagu_bas
      ON paguBasId = rncnpengeluaranMakId
   LEFT JOIN ( 
        SELECT
          mhd.movementHistoryDetailId AS id,
          mhd.movementHistoryDetailRncnpengeluaranId AS rpId,
          mh.`movementHistoryTahunAnggaranId` AS taId,
          mh.`movementHistoryUnitKerjaIdAsal` AS unitId,
          kd.`kegdetId` AS kdId,
         (0 - mhd.`movementHistoryDetailNilai`) AS nilai_revisi
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
        AND
        kegdetId = %s
        GROUP BY taId,unitId,kdId, mhd.movementHistoryDetailId
   ) AS revAsal ON revAsal.kdId = kegiatan_detail.kegdetId
   AND revAsal.rpId = rncnpengeluaranId
   LEFT JOIN (
        SELECT
          mhd.movementHistoryDetailId AS id,
          mhd.movementHistoryDetailRncnpengeluaranId AS rpId,
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
        AND
        kegdetId = %s
        GROUP BY taId,unitId,kdId, mhd.movementHistoryDetailId
   ) AS revTujuan ON revTujuan.kdId = kegiatan_detail.kegdetId
   AND revTujuan.rpId = rncnpengeluaranId
WHERE 1 = 1
AND rncnpengeluaranKegdetId = %s
GROUP BY rncnpengeluaranId
ORDER BY kompKode ASC
";

$sql['get_informasi'] = "
SELECT
   kegUnitkerjaId AS unit_kerja_id,
   thanggarNama AS tahun_anggaran_label,
   programNama AS program_label,
   subprogNama AS kegiatan_label,
   kegrefNama AS subkegiatan_label,
   unitkerjaNama AS satker_nama,
   unitkerjaNama AS unit_kerja_nama,
   unitkerjaParentId as is_unit_kerja
FROM
   kegiatan_detail
   JOIN kegiatan_ref ON (kegrefId = kegdetKegrefId)
   JOIN sub_program ON (subprogId = kegrefSubprogId)
   JOIN program_ref ON (programId = subprogProgramId)
   JOIN tahun_anggaran ON (thanggarId = programThanggarId)
   JOIN kegiatan ON (kegId = kegdetKegId)
    JOIN unit_kerja_ref ON (unitkerjaId = kegUnitkerjaId)
WHERE
   kegdetId=%s
";

$sql['get_count_data'] = "
SELECT
      COUNT(*) as total
   FROM
      rencana_pengeluaran
   WHERE
     rncnpengeluaranKegdetId=%s
";

$sql['get_data'] = "
SELECT
      rncnpengeluaranId as id,
      rncnpengeluaranKomponenKode as kode,
      rncnpengeluaranKomponenNama as nama,
      rncnpengeluaranKomponenNominal * IF(kompFormulaHasil = '0',1,IFNULL( kompFormulaHasil,1)) as nominal_usulan,
      rncnpengeluaranSatuan as satuan_usulan,
      (rncnpengeluaranSatuan * rncnpengeluaranKomponenNominal * IF(kompFormulaHasil = '0',1,
      IFNULL( kompFormulaHasil,1))) as jumlah_usulan,
      rncnpengeluaranKomponenNominalAprove * IF(kompFormulaHasil = '0',1,
      IFNULL( kompFormulaHasil,1)) as nominal_setuju,
      rncnpengeluaranSatuanAprove  as satuan_setuju,
      (rncnpengeluaranSatuanAprove * rncnpengeluaranKomponenNominalAprove * IF(kompFormulaHasil = '0',1,
      IFNULL( kompFormulaHasil,1))) as jumlah_setuju,
       rncnpengeluaranKomponenDeskripsi as deskripsi,
      rncnpengeluaranIsAprove as approval,
      IF(kompFormulaHasil = '0',1,kompFormulaHasil) as hasil_formula,
      IFNULL(rncnpengeluaranKeterangan, '-') AS keterangan
   FROM
      rencana_pengeluaran
      LEFT JOIN kegiatan_detail ON (kegdetId = rncnpengeluaranKegdetId)
      LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
      /*LEFT JOIN kegiatan_ref ON (kegrefId = kegdetId)*/
      /*LEFT JOIN komponen_kegiatan ON (kompkegKegrefId = kegrefId)*/
   WHERE
     rncnpengeluaranKegdetId=%s
   ORDER BY
     rncnpengeluaranKomponenKode
   LIMIT %s, %s
";
$sql['get_status_approval'] = "
SELECT
      rncnpengeluaranIsAprove as `approval`,
      COUNT(*) as jml
   FROM
      rencana_pengeluaran
   WHERE
     rncnpengeluaranKegdetId=%s
   GROUP BY `approval`
";

$sql['do_update_detil_approval'] = "
UPDATE
   rencana_pengeluaran
SET
   rncnpengeluaranKomponenTotalAprove = '%s',
   rncnpengeluaranKomponenNominalAprove = '%s',
   rncnpengeluaranSatuanAprove = '%s',
   rncnpengeluaranKeterangan = '%s',
   rncnpengeluaranIsAprove = '%s'
WHERE rncnpengeluaranId = %s
";

$sql['get_last_log_kegiatan_detail'] = "
SELECT 
    kegdetstatusId AS id,
    kegdetstatusKegdetId AS kegdet_id,
    kegdetstatusRncnpengeluaranId AS rp_id,
    kegdetstatusUserId AS user_id,
    kegdetstatusTanggal AS tanggal,
    kegdetstatusKeterangan AS keterangan,
    kegdetstatusLogAktifitas AS kodeaksi
FROM kegiatan_detail_status
WHERE kegdetstatusRncnpengeluaranId ='%s'
ORDER BY id DESC
LIMIT 1
";

$sql['do_insert_kegdet_status'] = "
INSERT INTO
    kegiatan_detail_status
SET
    kegdetstatusKegdetId = '%s',
    kegdetstatusRncnpengeluaranId = '%s',
    kegdetstatusUserId = '%s',
    kegdetstatusTanggal = '%s',
    kegdetstatusKeterangan = '%s',
    kegdetstatusLogAktifitas = '%s'
";
/*
$sql['do_update_status_approval'] =
   "UPDATE rencana_pengeluaran
   SET
      rncnpengeluaranIsAprove=%s
   WHERE
      rncnpengeluaranId IN ('%s')
";
*/
$sql['do_update_kegiatan_detil_status_approval'] = "
UPDATE
   kegiatan_detail
SET
   kegdetIsAprove = %s
WHERE kegdetId = '%s'
";

?>