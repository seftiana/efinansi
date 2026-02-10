<?php
$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']  = "
SELECT SQL_CALC_FOUND_ROWS
   kegrefId AS id,
   IFNULL(kegrefNomor, '') AS kode,
   kegrefNama AS nama,
   kegdetId,
   programId,
   programNomor,
   programNama,
   kegUnitkerjaId,
   subprogId,
   subprogNomor,
   subprogNama,
   subprogJeniskegId,
   IFNULL(rp.nominal, 0) + IF(revAsal.is_approve = 'Belum', revAsal.nilai_revisi, 0) AS nominalAnggaran,
   IFNULL(pengajuan.nominal, 0) AS nominalPengajuanRealisasi,
   IFNULL(pengajuan.nominalRealisasi, 0) AS nominalPencairan,
   IFNULL(pengajuan.nominalApprove,0) AS nominalRealisasi,
   IFNULL(rp.nominal, 0) + IF(revAsal.is_approve = 'Belum', revAsal.nilai_revisi, 0) - IFNULL(pengajuan.nominalRealisasi, 0) AS sisaDana,
   MONTH(`kegdetWaktuMulaiPelaksanaan`) AS bulan,
  `kegdetIsAprove` AS statusApprove
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
         SUM(IF(UPPER(pengrealIsApprove) = 'YA', IFNULL(pengrealNominalAprove, 0), IF(UPPER(pengrealIsApprove) = 'TIDAK',0,IFNULL(pengrealNominal, 0)))) AS nominalRealisasi,
         SUM(IF(UPPER(pengrealIsApprove) = 'YA', IFNULL(pengrealNominalAprove, 0), 0)) AS nominalApprove,
         IFNULL(pengrealIsApprove, 'Tidak') AS status_approve
      FROM pengajuan_realisasi
      WHERE 1 = 1
      AND (pengrealId != %s OR 1 = %s)
      GROUP BY pengrealKegdetId
   ) AS pengajuan ON pengajuan.id = kegdetId
   LEFT JOIN ( 
        SELECT
          mh.`movementHistoryTahunAnggaranId` AS taId,
          mh.`movementHistoryUnitKerjaIdAsal` AS unitId,
          kd.`kegdetId` AS kdId,
         (0 - SUM(mhd.`movementHistoryDetailNilai`)) AS nilai_revisi,
          mh.`movementHistoryIsApprove` AS is_approve
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
        mh.`movementHistoryIsApprove` = 'Belum'
        AND
        rpeng.`rncnpengeluaranIsAprove` = 'Ya'
        GROUP BY taId,unitId,kdId   
   ) AS revAsal ON revAsal.taId = kegThanggarId
   AND revAsal.kdId = kegdetId
WHERE 1 = 1
   AND kegdetIsAprove = 'Ya'
   AND (kegrefNama LIKE '%s' OR kegrefNomor LIKE '%s')
   AND kegUnitkerjaId = %s
   AND programThanggarId = '%s'
   AND MONTH(`kegdetWaktuMulaiPelaksanaan`)  =MONTH(NOW())
LIMIT %s, %s
";

$sql['get_komponen_anggaran'] = "
SELECT
   rncnpengeluaranKegdetId AS kegdetId,
   rncnpengeluaranId AS id,
   rncnpengeluaranKomponenKode AS komponenKode,
   rncnpengeluaranKomponenNama AS komponenNama,
   '' AS deskripsi,
   coaId AS makId,
   IFNULL(rncnpengeluaranKomponenTotalAprove,0) AS nominalPengeluaranApprove,
   IFNULL(rp.nominal, 0) + IF(revAsal.is_approve = 'Belum', revAsal.nilai_revisi, 0) AS nominalAnggaran,
   IFNULL(realisasi.nominal, 0) AS nominalPengajuan,
   IF(UPPER(rncnpengeluaranIsAprove) = 'YA', IFNULL(realisasi.nominalApprove, 0), 0) AS nominalPengajuanApprove,
   -- (rncnpengeluaranKomponenTotalAprove - IFNULL(realisasi.nominal, 0))+(IFNULL(realisasi.nominal, 0)-IFNULL(realisasi.nominalApprove, 0)) AS nominal,
   IFNULL(pengajuan.nominalRealisasi, 0) AS nominalPencairan,
   IFNULL(rncnpengeluaranKomponenTotalAprove,0) + IF(revAsal.is_approve = 'Belum', revAsal.nilai_revisi, 0) - IF(UPPER(rncnpengeluaranIsAprove) = 'YA', IFNULL(realisasi.nominalApprove, 0), 0) AS nominal,
   coaKodeAkun AS makKode
FROM
   rencana_pengeluaran
   JOIN (
      SELECT DISTINCT
         kegdetId
      FROM
         kegiatan_detail
         JOIN kegiatan_ref
            ON kegdetKegrefId = kegrefId
         JOIN sub_program
            ON kegrefSubprogId = subprogId
         LEFT JOIN pengajuan_realisasi
            ON kegdetId = pengrealKegdetId
         LEFT JOIN program_ref
            ON subprogProgramId = programId
         JOIN kegiatan
            ON kegId = kegdetKegId
      WHERE 1 = 1
         AND kegdetIsAprove = 'Ya'
         AND ( kegrefNama LIKE '%s' OR kegrefNomor LIKE '%s')
         AND kegUnitkerjaId = %s
         AND programThanggarId = '%s'
         AND MONTH(`kegdetWaktuMulaiPelaksanaan`)  =MONTH(NOW())
     /* LIMIT %s, %s */
   ) keg ON keg.kegdetId = rncnpengeluaranKegdetId
   LEFT JOIN finansi_ref_pagu_bas
      ON paguBasId = rncnpengeluaranMakId
   LEFT JOIN (
      SELECT
         pengrealdetRncnpengeluaranId AS id,
         SUM(pengrealdetNominalPencairan) AS nominal,         
         SUM(IF(UPPER(pengrealIsApprove) = 'YA', pengrealdetNominalApprove, IF(UPPER(pengrealIsApprove) = 'TIDAK',0,pengrealdetNominalPencairan))) AS nominalApprove
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
         rncnpengeluaranKegdetId AS id,
         SUM(IF(UPPER(rncnpengeluaranIsAprove) = 'YA', rncnpengeluaranKomponenTotalAprove, 0)) AS nominal
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
      AND (pengrealId != '' OR 1 = 1)
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
         SUM(IF(UPPER(pengrealIsApprove) = 'YA', IFNULL(pengrealNominalAprove, 0), IF(UPPER(pengrealIsApprove) = 'TIDAK',0,IFNULL(pengrealNominal, 0)))) AS nominalRealisasi,
         SUM(IF(UPPER(pengrealIsApprove) = 'YA', IFNULL(pengrealNominalAprove, 0), 0)) AS nominalApprove,
         IFNULL(pengrealIsApprove, 'Tidak') AS status_approve
      FROM pengajuan_realisasi
      WHERE 1 = 1
      AND (pengrealId != '' OR 1 = 1)
      GROUP BY pengrealKegdetId
   ) AS pengajuan ON pengajuan.id = kegdetId
   LEFT JOIN ( 
        SELECT
          mh.`movementHistoryTahunAnggaranId` AS taId,
          mh.`movementHistoryUnitKerjaIdAsal` AS unitId,
          kd.`kegdetId` AS kdId,
          rpeng.rncnpengeluaranId AS rpId,
         (0 - mhd.`movementHistoryDetailNilai`) AS nilai_revisi,
          mh.`movementHistoryIsApprove` AS is_approve
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
        mh.`movementHistoryIsApprove` = 'Belum'
        AND
        rpeng.`rncnpengeluaranIsAprove` = 'Ya'
        GROUP BY taId,unitId,kdId,rpeng.rncnpengeluaranId   
   ) AS revAsal ON revAsal.kdId = kegdetId
   AND revAsal.rpId = rncnpengeluaranId
   
   LEFT JOIN komponen komp
	ON komp.`kompKode` = `rncnpengeluaranKomponenKode`
   LEFT JOIN coa c
        ON c.`coaId` =  komp.`kompCoaId`
        
WHERE 1 = 1
   AND rncnpengeluaranIsAprove = 'Ya'
ORDER BY rncnpengeluaranKegdetId ASC,
rncnpengeluaranKomponenKode
";
?>