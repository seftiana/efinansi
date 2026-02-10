<?php
$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']  = "
SELECT SQL_CALC_FOUND_ROWS
   rncnpengeluaranId AS id,
   rncnpengeluaranKegdetId AS kegdet_id,
   IF(revAsal.rpId = rncnpengeluaranId OR revTujuan.rpId = rncnpengeluaranId, 'Y', 'T') AS is_revisi,
   rncnpengeluaranKomponenKode AS kode,
   rncnpengeluaranKomponenNama AS nama,
   rncnpengeluaranKomponenNominal * IF(
      kompFormulaHasil = '0',
      1,
      kompFormulaHasil
   ) AS nominal_usulan,
   rncnpengeluaranSatuan AS satuan_usulan,
   (
      rncnpengeluaranSatuan * rncnpengeluaranKomponenNominal * IF(
         kompFormulaHasil = '0',
         1,
         kompFormulaHasil
      )
   ) AS jumlah_usulan,
   rncnpengeluaranKomponenTotalAprove * IF(
      kompFormulaHasil = '0',
      1,
      kompFormulaHasil
   ) AS nominal_setuju,
   rncnpengeluaranSatuanAprove AS satuan_setuju,
   (
      rncnpengeluaranKomponenTotalAprove * IF(
         kompFormulaHasil = '0',
         1,
         kompFormulaHasil
      )
   ) AS jumlah_setuju,
   rncnpengeluaranKomponenDeskripsi AS deskripsi,
   rncnpengeluaranIsAprove AS approval,
   IF(IFNULL(realisasi.count, 0) > 0, 'YA', 'BELUM') AS approval_realisasi
FROM
   rencana_pengeluaran
   JOIN kegiatan_detail
      ON kegdetId = rncnpengeluaranKegdetId
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN sub_program
      ON subprogId = kegrefSubprogId
   JOIN program_ref
      ON programId = subprogProgramId
   JOIN kegiatan
      ON kegId = kegdetKegId
   LEFT JOIN komponen
      ON kompKode = rncnpengeluaranKomponenKode
   LEFT JOIN
      (SELECT
         pengrealKegdetId AS id,
         COUNT(DISTINCT pengrealId) AS `count`
      FROM
         pengajuan_realisasi
      GROUP BY pengrealKegDetId) AS realisasi
      ON realisasi.id = kegdetId
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
        mh.`movementHistoryIsApprove` != 'Tidak'
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
        mh.`movementHistoryIsApprove` != 'Tidak'
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
ORDER BY
  rncnpengeluaranKomponenKode
LIMIT %s, %s
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
   unitkerjaParentId AS is_unit_kerja
FROM
   kegiatan_detail
   JOIN kegiatan_ref
      ON (kegrefId = kegdetKegrefId)
   JOIN sub_program
      ON (subprogId = kegrefSubprogId)
   JOIN program_ref
      ON (programId = subprogProgramId)
   JOIN tahun_anggaran
      ON (thanggarId = programThanggarId)
   JOIN kegiatan
      ON (kegId = kegdetKegId)
   JOIN unit_kerja_ref
      ON (unitkerjaId = kegUnitkerjaId)
WHERE kegdetId = %s
";

$sql['get_count_data'] = "
SELECT
      COUNT(*) as total
   FROM
      rencana_pengeluaran
   WHERE
     rncnpengeluaranKegdetId=%s
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

$sql['do_update_detil_approval'] =
   "UPDATE rencana_pengeluaran
   SET
      rncnpengeluaranSatuanAprove='0',
      rncnpengeluaranIsAprove='Belum'
   WHERE
      rncnpengeluaranId=%s
";

$sql['get_last_log_kegiatan_detail'] = "
SELECT 
    kegdetstatusId AS id,
    kegdetstatusUserId AS user_id,
    kegdetstatusLogAktifitas AS kodeaksi
FROM kegiatan_detail_status
WHERE kegdetstatusKegdetId ='%s'
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
$sql['do_update_kegiatan_detil_status_approval'] =
   "UPDATE kegiatan_detail
   SET
      kegdetIsAprove='Belum'
   WHERE
      kegdetId='%s'
";

?>