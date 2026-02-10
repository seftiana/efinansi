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


/**
  $sql['get_data'] = "
  SELECT
  mh.`movementHistoryId` AS id,
  md.movementHistoryDetailTanggal AS tanggal_movement,
  mh.`movementHistoryAsalRencanaPengeluaranId` AS id_asal,
  mh.`movementHistoryTujuanRencanaPengeluaranId` AS id_tujuan,
  mh.`movementHistoryNilai` AS nilai,
  mh.`movementHistoryTanggalUbah` AS tanggal,
  mh.`movementHistoryUserId` AS user_id,
  tempKompAsal.rp_id AS komponen_asal_id,
  tempKompAsal.rp_kompkode AS komponen_asal_kode,
  tempKompAsal.rp_kompnama AS komponen_asal_nama,
  tempKompAsal.nilai+mh.`movementHistoryNilai` AS komponen_asal_nilai,
  tempKompTujuan.rp_id AS komponen_tujuan_id,
  tempKompTujuan.rp_kompkode AS komponen_tujuan_kode,
  tempKompTujuan.rp_kompnama AS komponen_tujuan_nama,
  tempKompTujuan.nilai AS komponen_tujuan_nilai
  FROM `finansi_pa_movement_history` AS mh
  JOIN (SELECT
  rncnpengeluaranId AS rp_id,
  rncnpengeluaranKomponenKode AS rp_kompkode,
  rncnpengeluaranKomponenNama AS rp_kompnama,
  rncnpengeluaranKomponenTotalAprove AS nilai
  FROM rencana_pengeluaran) AS tempKompAsal ON tempKompAsal.rp_id = `movementHistoryAsalRencanaPengeluaranId`
  JOIN (SELECT
  rncnpengeluaranId AS rp_id,
  rncnpengeluaranKomponenKode AS rp_kompkode,
  rncnpengeluaranKomponenNama AS rp_kompnama,
  rncnpengeluaranKomponenTotalAprove AS nilai
  FROM rencana_pengeluaran) AS tempKompTujuan ON tempKompTujuan.rp_id = `movementHistoryTujuanRencanaPengeluaranId`
  WHERE (tempKompTujuan.rp_kompkode LIKE '%s' OR tempKompTujuan.rp_kompnama LIKE '%s')
  LIMIT %s,%s
  ";

  $sql['count_data'] = "
  SELECT
  COUNT(DISTINCT mh.`movementHistoryId`) AS total_data
  FROM `finansi_pa_movement_history` AS mh
  JOIN (SELECT
  rncnpengeluaranId AS rp_id,
  rncnpengeluaranKomponenKode AS rp_kompkode,
  rncnpengeluaranKomponenNama AS rp_kompnama,
  rncnpengeluaranKomponenTotalAprove AS nilai
  FROM rencana_pengeluaran) AS tempKompAsal ON tempKompAsal.rp_id = `movementHistoryAsalRencanaPengeluaranId`
  JOIN (SELECT
  rncnpengeluaranId AS rp_id,
  rncnpengeluaranKomponenKode AS rp_kompkode,
  rncnpengeluaranKomponenNama AS rp_kompnama,
  rncnpengeluaranKomponenTotalAprove AS nilai
  FROM rencana_pengeluaran) AS tempKompTujuan ON tempKompTujuan.rp_id = `movementHistoryTujuanRencanaPengeluaranId`
  WHERE (tempKompTujuan.rp_kompkode LIKE '%s' OR tempKompTujuan.rp_kompnama LIKE '%s')
  ";
 */

$sql['get_data_movement_optimized'] = "
SELECT SQL_CALC_FOUND_ROWS 
  ta_id,
  tanggal,
  unit_kerja_nama_asal,
  unit_kerja_nama_tujuan,
  nomor_kegiatan_asal,  
  kegiatan_asal,
  nomor_kegiatan_tujuan,
  kegiatan_tujuan,
  id,  
  nomor,
  keg_asal,  
  bulan_asal,
  bulan_tujuan,
  nilai,    
  nilai_sekarang_asal,
  nilai_sekarang_tujuan,     
  keg_tujuan,
  user_id,
  status_approval
FROM (
SELECT 
  k.`kegThanggarId` AS ta_id,
  ukr_asal.`unitkerjaNama` AS unit_kerja_nama_asal,
  ukr_tujuan.`unitkerjaNama` AS unit_kerja_nama_tujuan,
  kr_asal.`kegrefNomor` AS nomor_kegiatan_asal,  
  kr_asal.`kegrefNama` AS kegiatan_asal,
  kr_tujuan.`kegrefNomor` AS nomor_kegiatan_tujuan,
  kr_tujuan.`kegrefNama` AS kegiatan_tujuan,
  mp.`movementHistoryId` AS id,
  mp.`movementHistoryTanggal` AS tanggal,
  mp.`movementHistoryNomor` AS nomor,
  mp.`movementHistoryKegrefIdAsal` AS keg_asal,  
  MAX(IF(mp_d.`movementHistoryDetailType` ='asal',MONTH(kd.`kegdetWaktuMulaiPelaksanaan`),NULL)) AS bulan_asal,
  MAX(IF(mp_d.`movementHistoryDetailType` ='tujuan',MONTH(kd.`kegdetWaktuMulaiPelaksanaan`),NULL)) AS bulan_tujuan,
   mp.`movementHistoryNilai` AS nilai,  
  (SUM(IF(mp_d.`movementHistoryDetailType` ='asal',mp_d.`movementHistoryDetailNilaiSemula`,0))-
   SUM(IF(mp_d.`movementHistoryDetailType` ='asal',mp_d.`movementHistoryDetailNilai`,0) )) AS nilai_sekarang_asal,
  (SUM(IF(mp_d.`movementHistoryDetailType` ='tujuan',mp_d.`movementHistoryDetailNilaiSemula`,0))+
   SUM(IF(mp_d.`movementHistoryDetailType` ='tujuan',mp_d.`movementHistoryDetailNilai`,0) )) AS nilai_sekarang_tujuan,   
  mp.`movementHistoryKegrefIdTujuan` AS keg_tujuan,
  mp.`movementHistoryUserId` AS user_id,
  mp.`movementHistoryIsApprove` AS status_approval
FROM
    `finansi_pa_movement_history` mp 
    JOIN unit_kerja_ref ukr_asal ON ukr_asal.`unitkerjaId` = mp.`movementHistoryUnitKerjaIdAsal`
    JOIN unit_kerja_ref ukr_tujuan ON ukr_tujuan.`unitkerjaId` = mp.`movementHistoryUnitKerjaIdTujuan`
    JOIN `finansi_pa_movement_history_detail` mp_d ON
	mp_d.`movementHistoryDetailMovementHistoryId`  =  mp.`movementHistoryId`
    JOIN rencana_pengeluaran rpeng
	ON rpeng.`rncnpengeluaranId` = mp_d.`movementHistoryDetailRncnpengeluaranId`	
    JOIN kegiatan_detail kd ON 
     kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId` 
    JOIN kegiatan k  ON
    k.`kegId` = kd.`kegdetKegId`    
    JOIN kegiatan_ref kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
    LEFT JOIN kegiatan_ref kr_asal ON
     kr_asal.`kegrefId` = mp.`movementHistoryKegrefIdAsal` 
    LEFT JOIN kegiatan_ref kr_tujuan ON
    kr_tujuan.`kegrefId` = mp.`movementHistoryKegrefIdTujuan`
    
WHERE
k.`kegThanggarId` = '%s'
AND
(mp.`movementHistoryUnitKerjaIdAsal` = '%s' OR %s)
AND
(mp.`movementHistoryUnitKerjaIdTujuan` = '%s' OR %s)
   GROUP BY mp.`movementHistoryId`
) hf
WHERE 
 ((hf.nomor_kegiatan_asal LIKE '%s' OR hf.kegiatan_asal LIKE '%s') OR %s)
 AND
 ((hf.nomor_kegiatan_tujuan LIKE '%s' OR hf.kegiatan_tujuan LIKE '%s') OR %s)
 AND
(hf.bulan_asal = '%s' OR %s)
AND
(hf.bulan_tujuan = '%s' OR %s)
ORDER BY tanggal DESC
";

$sql['get_limit'] = " LIMIT %s, %s ";
/*
$sql['get_data_movement'] = "
SELECT 
  SQL_CALC_FOUND_ROWS 
  k_asal.ta_id AS ta_id,
  k_asal.bulan AS bulan_asal,
  k_tujuan.bulan AS bulan_tujuan,
  mp.`movementHistoryId` AS id,
  mp.`movementHistoryNomor` AS nomor,
  k_asal.kd_id,
  k_asal.uk_id AS unit_kerja_id_asal,
  k_asal.uk_kode AS unit_kerja_kode_asal,
  k_asal.uk_nama AS unit_kerja_nama_asal,
  mp.`movementHistoryKegrefIdAsal` AS keg_asal,
  IFNULL(k_asal.keg_nomor, '') AS nomor_kegiatan_asal,
  k_asal.keg_nama AS kegiatan_asal,
  mp.`movementHistoryNilai` AS nilai,
  mp_d_asal.`nilaiSemula` - mp_d_asal.`nilaiMovement` AS nilai_sekarang_asal,
  mp_d_tujuan.`nilaiSemula` + mp_d_tujuan.`nilaiMovement` AS nilai_sekarang_tujuan,
  k_tujuan.uk_id AS unit_kerja_id_tujuan,
  k_tujuan.uk_kode AS unit_kerja_kode_tujuan,
  k_tujuan.uk_nama AS unit_kerja_nama_tujuan,
  mp.`movementHistoryKegrefIdTujuan` AS keg_tujuan,
  IFNULL(k_tujuan.keg_nomor, '') AS nomor_kegiatan_tujuan,
  k_tujuan.keg_nama AS kegiatan_tujuan ,
  mp.`movementHistoryTanggal` AS tanggal,
  mp.`movementHistoryUserId` AS user_id,
  mp.`movementHistoryIsApprove` AS status_approval
FROM
    `finansi_pa_movement_history` mp 
    LEFT JOIN (
      SELECT
        mp_d.`movementHistoryDetailId` AS id_det,
        mp_d.`movementHistoryDetailMovementHistoryId` AS id,
        mp_d.`movementHistoryDetailRncnpengeluaranId` AS id_rp,
        SUM(mp_d.`movementHistoryDetailNilaiSemula`) AS nilaiSemula,
        SUM(mp_d.`movementHistoryDetailNilai`) AS nilaiMovement,
        mp_d.`movementHistoryDetailType` AS tipe
      FROM
        `finansi_pa_movement_history_detail` mp_d 
      WHERE mp_d.movementHistoryDetailType = 'asal'
      GROUP BY mp_d.`movementHistoryDetailMovementHistoryId`) AS mp_d_asal
              ON mp_d_asal.`id` = mp.`movementHistoryId` AND
          mp_d_asal.`tipe` = 'asal'
    
    LEFT JOIN (
      SELECT
        mp_d.`movementHistoryDetailId` AS id_det,
        mp_d.`movementHistoryDetailMovementHistoryId` AS id,
        mp_d.`movementHistoryDetailRncnpengeluaranId` AS id_rp,
        SUM(mp_d.`movementHistoryDetailNilaiSemula`) AS nilaiSemula,
        SUM(mp_d.`movementHistoryDetailNilai`) AS nilaiMovement,
        mp_d.`movementHistoryDetailType` AS tipe
      FROM
        `finansi_pa_movement_history_detail` mp_d 
      WHERE mp_d.movementHistoryDetailType = 'tujuan'
      GROUP BY mp_d.`movementHistoryDetailMovementHistoryId`) AS mp_d_tujuan
              ON mp_d_tujuan.`id` = mp.`movementHistoryId` AND
          mp_d_tujuan.`tipe` = 'tujuan'

    LEFT JOIN (
  SELECT 
    rpeng.`rncnpengeluaranId` AS db_id,
    kd.`kegdetId` AS kd_id,
    kd.`kegdetKegrefId` AS kref_id,
    k.`kegUnitkerjaId` AS uk_id,
    MONTH(kd.`kegdetWaktuMulaiPelaksanaan`) AS bulan,
    k.`kegThanggarId` AS ta_id ,
    uk.`unitkerjaKode` AS uk_kode,
    uk.`unitkerjaNama` AS uk_nama,
    kref.`kegrefNomor` AS keg_nomor,
    kref.`kegrefNama` AS keg_nama
  FROM
    rencana_pengeluaran rpeng 
    JOIN kegiatan_detail kd 
      ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId` 
    JOIN kegiatan k 
      ON k.`kegId` = kd.`kegdetKegId` 
    JOIN kegiatan_ref kref
      ON kref.`kegrefId` = kd.`kegdetKegrefId`   
    JOIN unit_kerja_ref uk 
      ON uk.`unitkerjaId` = k.`kegUnitkerjaId`  
  WHERE k.`kegThanggarId` = %s
    AND
      ((kref.`kegrefNomor` LIKE '%s' OR kref.`kegrefNama` LIKE '%s') OR 1 = %s)
    AND
      (k.`kegThanggarId` = '%s')
    AND
      (k.`kegUnitkerjaId` = '%s' OR 1 = %s)
    AND
      (MONTH(kd.`kegdetWaktuMulaiPelaksanaan`) = '%s'  OR 1 = %s) 
    ) k_asal ON 
    k_asal.uk_id = mp.`movementHistoryUnitKerjaIdAsal` 
    AND k_asal.kref_id = mp.`movementHistoryKegrefIdAsal`
    AND k_asal.db_id = mp_d_asal.`id_rp` 
    LEFT JOIN (
  SELECT 
    rpeng.`rncnpengeluaranId` AS db_id,
    kd.`kegdetId` AS kd_id,
    kd.`kegdetKegrefId` AS kref_id,
    k.`kegUnitkerjaId` AS uk_id,
    MONTH(kd.`kegdetWaktuMulaiPelaksanaan`) AS bulan,
    k.`kegThanggarId` AS ta_id ,
    uk.`unitkerjaKode` AS uk_kode,
    uk.`unitkerjaNama` AS uk_nama,
    kref.`kegrefNomor` AS keg_nomor,
    kref.`kegrefNama` AS keg_nama
  FROM
    rencana_pengeluaran rpeng 
    JOIN kegiatan_detail kd 
      ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId` 
    JOIN kegiatan k 
      ON k.`kegId` = kd.`kegdetKegId` 
    JOIN kegiatan_ref kref
      ON kref.`kegrefId` = kd.`kegdetKegrefId`   
    JOIN unit_kerja_ref uk 
      ON uk.`unitkerjaId` = k.`kegUnitkerjaId`  
  WHERE k.`kegThanggarId` = %s
    AND
      ((kref.`kegrefNomor` LIKE '%s' OR kref.`kegrefNama` LIKE '%s') OR 1 = %s)
    AND
      (k.`kegThanggarId` = '%s')
    AND
      (k.`kegUnitkerjaId` = '%s' OR 1 = %s)
    AND
      (MONTH(kd.`kegdetWaktuMulaiPelaksanaan`) = '%s'  OR 1 = %s) 
    ) k_tujuan ON 
    k_tujuan.uk_id = mp.`movementHistoryUnitKerjaIdTujuan`
    AND k_tujuan.kref_id = mp.`movementHistoryKegrefIdTujuan`
    AND k_tujuan.db_id = mp_d_tujuan.`id_rp`
  WHERE 
    k_asal.uk_id IS NOT NULL 
    AND 
    k_tujuan.uk_id IS NOT NULL
   GROUP BY mp.`movementHistoryId`
   ORDER BY tanggal DESC
   LIMIT %s, %s
";*/

$sql['get_data_movement_export'] = "
SELECT 
  SQL_CALC_FOUND_ROWS 
  k_asal.ta_id AS ta_id,
  k_asal.bulan AS bulan_asal,
  k_tujuan.bulan AS bulan_tujuan,
  mp.`movementHistoryId` AS id,
  mp.`movementHistoryNomor` AS nomor,
  k_asal.kd_id,
  k_asal.uk_id AS unit_kerja_id_asal,
  k_asal.uk_kode AS unit_kerja_kode_asal,
  k_asal.uk_nama AS unit_kerja_nama_asal,
  mp.`movementHistoryKegrefIdAsal` AS keg_asal,
  IFNULL(k_asal.keg_nomor, '') AS nomor_kegiatan_asal,
  k_asal.keg_nama AS kegiatan_asal,
  mp.`movementHistoryNilai` AS nilai,
  mp_d_asal.`nilaiSemula` - mp_d_asal.`nilaiMovement` AS nilai_sekarang_asal,
  mp_d_tujuan.`nilaiSemula` + mp_d_tujuan.`nilaiMovement` AS nilai_sekarang_tujuan,
  k_tujuan.uk_id AS unit_kerja_id_tujuan,
  k_tujuan.uk_kode AS unit_kerja_kode_tujuan,
  k_tujuan.uk_nama AS unit_kerja_nama_tujuan,
  mp.`movementHistoryKegrefIdTujuan` AS keg_tujuan,
  IFNULL(k_tujuan.keg_nomor, '') AS nomor_kegiatan_tujuan,
  k_tujuan.keg_nama AS kegiatan_tujuan ,
  mp.`movementHistoryTanggal` AS tanggal,
  mp.`movementHistoryUserId` AS user_id,
  mp.`movementHistoryIsApprove` AS status_approval
FROM
    `finansi_pa_movement_history` mp 
    LEFT JOIN (
      SELECT
        mp_d.`movementHistoryDetailId` AS id_det,
        mp_d.`movementHistoryDetailMovementHistoryId` AS id,
        mp_d.`movementHistoryDetailRncnpengeluaranId` AS id_rp,
        SUM(mp_d.`movementHistoryDetailNilaiSemula`) AS nilaiSemula,
        SUM(mp_d.`movementHistoryDetailNilai`) AS nilaiMovement,
        mp_d.`movementHistoryDetailType` AS tipe
      FROM
        `finansi_pa_movement_history_detail` mp_d 
      WHERE mp_d.movementHistoryDetailType = 'asal'
      GROUP BY mp_d.`movementHistoryDetailMovementHistoryId`) AS mp_d_asal
              ON mp_d_asal.`id` = mp.`movementHistoryId` AND
          mp_d_asal.`tipe` = 'asal'
    
    LEFT JOIN (
      SELECT
        mp_d.`movementHistoryDetailId` AS id_det,
        mp_d.`movementHistoryDetailMovementHistoryId` AS id,
        mp_d.`movementHistoryDetailRncnpengeluaranId` AS id_rp,
        SUM(mp_d.`movementHistoryDetailNilaiSemula`) AS nilaiSemula,
        SUM(mp_d.`movementHistoryDetailNilai`) AS nilaiMovement,
        mp_d.`movementHistoryDetailType` AS tipe
      FROM
        `finansi_pa_movement_history_detail` mp_d 
      WHERE mp_d.movementHistoryDetailType = 'tujuan'
      GROUP BY mp_d.`movementHistoryDetailMovementHistoryId`) AS mp_d_tujuan
              ON mp_d_tujuan.`id` = mp.`movementHistoryId` AND
          mp_d_tujuan.`tipe` = 'tujuan'

    LEFT JOIN (
  SELECT 
    rpeng.`rncnpengeluaranId` AS db_id,
    kd.`kegdetId` AS kd_id,
    kd.`kegdetKegrefId` AS kref_id,
    k.`kegUnitkerjaId` AS uk_id,
    MONTH(kd.`kegdetWaktuMulaiPelaksanaan`) AS bulan,
    k.`kegThanggarId` AS ta_id ,
    uk.`unitkerjaKode` AS uk_kode,
    uk.`unitkerjaNama` AS uk_nama,
    kref.`kegrefNomor` AS keg_nomor,
    kref.`kegrefNama` AS keg_nama
  FROM
    rencana_pengeluaran rpeng 
    JOIN kegiatan_detail kd 
      ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId` 
    JOIN kegiatan k 
      ON k.`kegId` = kd.`kegdetKegId` 
    JOIN kegiatan_ref kref
      ON kref.`kegrefId` = kd.`kegdetKegrefId`   
    JOIN unit_kerja_ref uk 
      ON uk.`unitkerjaId` = k.`kegUnitkerjaId`  
  WHERE k.`kegThanggarId` = %s
    AND
      ((kref.`kegrefNomor` LIKE '%s' OR kref.`kegrefNama` LIKE '%s') OR 1 = %s)
    AND
      (k.`kegThanggarId` = '%s')
    AND
      (k.`kegUnitkerjaId` = '%s' OR 1 = %s)
    AND
      (MONTH(kd.`kegdetWaktuMulaiPelaksanaan`) = '%s'  OR 1 = %s) 
    ) k_asal ON 
    k_asal.uk_id = mp.`movementHistoryUnitKerjaIdAsal` 
    AND k_asal.kref_id = mp.`movementHistoryKegrefIdAsal`
    AND k_asal.db_id = mp_d_asal.`id_rp` 
    LEFT JOIN (
  SELECT 
    rpeng.`rncnpengeluaranId` AS db_id,
    kd.`kegdetId` AS kd_id,
    kd.`kegdetKegrefId` AS kref_id,
    k.`kegUnitkerjaId` AS uk_id,
    MONTH(kd.`kegdetWaktuMulaiPelaksanaan`) AS bulan,
    k.`kegThanggarId` AS ta_id ,
    uk.`unitkerjaKode` AS uk_kode,
    uk.`unitkerjaNama` AS uk_nama,
    kref.`kegrefNomor` AS keg_nomor,
    kref.`kegrefNama` AS keg_nama
  FROM
    rencana_pengeluaran rpeng 
    JOIN kegiatan_detail kd 
      ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId` 
    JOIN kegiatan k 
      ON k.`kegId` = kd.`kegdetKegId` 
    JOIN kegiatan_ref kref
      ON kref.`kegrefId` = kd.`kegdetKegrefId`   
    JOIN unit_kerja_ref uk 
      ON uk.`unitkerjaId` = k.`kegUnitkerjaId`  
  WHERE k.`kegThanggarId` = %s
    AND
      ((kref.`kegrefNomor` LIKE '%s' OR kref.`kegrefNama` LIKE '%s') OR 1 = %s)
    AND
      (k.`kegThanggarId` = '%s')
    AND
      (k.`kegUnitkerjaId` = '%s' OR 1 = %s)
    AND
      (MONTH(kd.`kegdetWaktuMulaiPelaksanaan`) = '%s'  OR 1 = %s) 
    ) k_tujuan ON 
    k_tujuan.uk_id = mp.`movementHistoryUnitKerjaIdTujuan`
    AND k_tujuan.kref_id = mp.`movementHistoryKegrefIdTujuan`
    AND k_tujuan.db_id = mp_d_tujuan.`id_rp`
  WHERE 
    k_asal.uk_id IS NOT NULL 
    AND 
    k_tujuan.uk_id IS NOT NULL
   GROUP BY mp.`movementHistoryId`
   ORDER BY tanggal DESC
";

$sql['get_count_movement'] = "
      SELECT FOUND_ROWS() AS total
   ";

$sql['get_data_movement_by_id'] = "
    SELECT 
      mp.`movementHistoryId` AS id,
      mp.`movementHistoryNomor` AS nomor,
      uk.`unitkerjaKode` AS unit_kerja_kode_asal,
      uk.`unitkerjaNama` AS unit_kerja_nama_asal,
      mp.`movementHistoryKegrefIdAsal` AS keg_asal,
      IFNULL(kra.`kegrefNomor`, '') AS nomor_kegiatan_asal,
      kra.`kegrefNama` AS kegiatan_asal,
      mp.`movementHistoryNilai` AS nilai,
      uk_tujuan.`unitkerjaKode` AS unit_kerja_kode_tujuan,
      uk_tujuan.`unitkerjaNama` AS unit_kerja_nama_tujuan,
      mp.`movementHistoryKegrefIdTujuan` AS keg_tujuan,
      IFNULL(krt.kegrefNomor, '') AS nomor_kegiatan_tujuan,
      krt.`kegrefNama` AS kegiatan_tujuan ,
      mp.`movementHistoryTanggal` AS tanggal,
      mp.`movementHistoryUserId` AS user_id,
      mp.`movementHistoryIsApprove` AS status_approval
    FROM
      `finansi_pa_movement_history` AS mp 
      LEFT JOIN kegiatan_ref AS kra 
        ON kra.`kegrefId` = mp.`movementHistoryKegrefIdAsal` 
      LEFT JOIN kegiatan_ref AS krt 
        ON krt.`kegrefId` = mp.`movementHistoryKegrefIdTujuan` 
      LEFT JOIN unit_kerja_ref uk
        ON uk.`unitkerjaId` = mp.`movementHistoryUnitKerjaIdAsal` 
      LEFT JOIN unit_kerja_ref uk_tujuan
        ON uk_tujuan.`unitkerjaId` = mp.`movementHistoryUnitKerjaIdTujuan`
       WHERE
       mp.`movementHistoryId` = '%s'      
   ";

$sql['get_detail_apbnp'] = "
    SELECT
        mp.`movementHistoryId` AS id,
        mp.`movementHistoryTahunAnggaranId` AS tahun_anggaran_id,
        ta.`thanggarNama` AS tahun_anggaran_nama,
        md.movementHistoryDetailTanggal AS tgl_movement, 
        mp.`movementHistoryNomor` AS nomor,
        mp.`movementHistoryUnitKerjaIdAsal` AS unit_kerja_id_asal,
        uk.`unitkerjaKode` AS unit_kerja_kode_asal,
        uk.`unitkerjaNama` AS unit_kerja_nama_asal,
        mp.`movementHistoryUnitKerjaIdTujuan` AS unit_kerja_id_tujuan,
        uk_tujuan.`unitkerjaKode` AS unit_kerja_kode_tujuan,
        uk_tujuan.`unitkerjaNama` AS unit_kerja_nama_tujuan,    
        mp.`movementHistoryKegrefIdAsal` AS keg_asal,
        mp.`movementHistoryKegrefIdTujuan` AS keg_tujuan,
        mp.`movementHistoryNilai` AS nilai,
        mp.`movementHistoryTanggalUbah` AS tanggal_ubah,
        mp.`movementHistoryUserId` AS user_id,
        IFNULL(kra.`kegrefNomor`, '') AS nomor_kegiatan_asal,
        kra.`kegrefNama` AS kegiatan_asal,
        IFNULL(krt.kegrefNomor, '') AS nomor_kegiatan_tujuan,
        krt.`kegrefNama` AS kegiatan_tujuan,
        md.`movementHistoryDetailRncnpengeluaranId` AS rp_id,        
        rp.`rncnpengeluaranKegdetId` AS kegiatan_detail_id,
        md.`movementHistoryDetailNilai` AS nilai_movement,
        md.`movementHistoryDetailType` AS type_movement,
        rp.`rncnpengeluaranKomponenKode` AS kode_komponen,
        rp.`rncnpengeluaranKomponenNama` AS nama_komponen, 
        md.`movementHistoryDetailNilaiSemula` AS nilai_komponen_semula,
        md.`movementHistoryDetailNilai` AS nilai_komponen_movement,
        IF(md.`movementHistoryDetailType` = 'asal', MONTH(kd.`kegdetWaktuMulaiPelaksanaan`),NULL) AS bulan_anggaran_asal,
        IF(md.`movementHistoryDetailType` = 'tujuan', MONTH(kd.`kegdetWaktuMulaiPelaksanaan`),NULL) AS bulan_anggaran_tujuan
    FROM `finansi_pa_movement_history` AS mp 
    LEFT JOIN kegiatan_ref AS kra 
        ON kra.`kegrefId` = mp.`movementHistoryKegrefIdAsal` 
    LEFT JOIN kegiatan_ref AS krt 
        ON krt.`kegrefId` = mp.`movementHistoryKegrefIdTujuan` 
    LEFT JOIN finansi_pa_movement_history_detail AS md 
        ON md.`movementHistoryDetailMovementHistoryId` = mp.`movementHistoryId` 
    LEFT JOIN rencana_pengeluaran AS rp 
        ON md.`movementHistoryDetailRncnpengeluaranId` = rp.`rncnpengeluaranId`
    LEFT JOIN unit_kerja_ref uk
        ON uk.`unitkerjaId` = mp.`movementHistoryUnitKerjaIdAsal`
    LEFT JOIN unit_kerja_ref uk_tujuan
        ON uk_tujuan.`unitkerjaId` = mp.`movementHistoryUnitKerjaIdTujuan`        
    LEFT JOIN tahun_anggaran ta
       ON ta.thanggarId = mp.`movementHistoryTahunAnggaranId`
    LEFT JOIN rencana_pengeluaran rpeng
       ON rpeng.`rncnpengeluaranId` = md.`movementHistoryDetailRncnpengeluaranId`
    LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`         
    WHERE mp.`movementHistoryId` = '%s' 
    ORDER BY `movementHistoryDetailType` ASC
    ";

$sql['do_delete_apbnp'] = "
        DELETE  FROM `finansi_pa_movement_history`
        WHERE `movementHistoryId` = '%s'
    ";

$sql['do_delete_apbnp_detail'] = "
		DELETE
		FROM `finansi_pa_movement_history_detail`
		WHERE 
		`movementHistoryDetailMovementHistoryId` = '%s'
    ";

/// untuk proses update

$sql['get_combo_bas'] = "
      SELECT
         paguBasId AS id,
         CONCAT(paguBasKode, ' - ',paguBasKeterangan) AS name
      FROM finansi_ref_pagu_bas
      WHERE
          paguBasStatusAktif = 'Y'
   ";

$sql['update_history_movement'] = "
    UPDATE  
        `finansi_pa_movement_history`
    SET 
      `movementHistoryTahunAnggaranId` = '%s',
      `movementHistoryUnitKerjaIdAsal` = '%s',      
      `movementHistoryKegrefIdAsal` =  '%s',      
      `movementHistoryUnitKerjaIdTujuan` = '%s',
      `movementHistoryKegrefIdTujuan` = '%s',
      `movementHistoryNilai` = '%s',
      `movementHistoryTanggalUbah` = NOW(),
      `movementHistoryUserId` = '%s'
    WHERE `movementHistoryId` = '%s'
    ";

$sql['insert_into_history_movement'] = "
    INSERT INTO `finansi_pa_movement_history`
                (`movementHistoryId`,
                 `movementHistoryNomor`,
                 `movementHistoryTahunAnggaranId`,
                 `movementHistoryUnitKerjaIdAsal`,
                 `movementHistoryKegrefIdAsal`,
                 `movementHistoryUnitKerjaIdTujuan`,
                 `movementHistoryKegrefIdTujuan`,
                 `movementHistoryNilai`,
                 `movementHistoryTanggal`,
                 `movementHistoryTanggalUbah`,
                 `movementHistoryUserId`)
    VALUES (NULL,
            (SELECT 
                IFNULL(MAX(tmp.`movementHistoryNomor`),0)+1 AS nomor 
            FROM `finansi_pa_movement_history` AS tmp
            LIMIT 0,1),
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            NOW(),
            NOW(),
            '%s')
    ";

$sql['get_last_insert_id_apbnp'] = "
        SELECT MAX(movementHistoryId) AS last_id FROM finansi_pa_movement_history
    ";

$sql['insert_into_apbnp_detail'] = "
        INSERT INTO `finansi_pa_movement_history_detail`
                    (`movementHistoryDetailId`,
                     `movementHistoryDetailMovementHistoryId`,
                     `movementHistoryDetailRncnpengeluaranId`,
                     `movementHistoryDetailNilaiSemula`,
                     `movementHistoryDetailNilai`,
                     `movementHistoryDetailType`,
                     `movementHistoryDetailTanggal`,
                     `movementHistoryDetailUserId`)
        VALUES (NULL,
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                NOW(),
                '%s')
    ";

$sql['get_data_apbnp_detail'] = "
        SELECT
          `movementHistoryDetailId`
        FROM `finansi_pa_movement_history_detail`
        WHERE `movementHistoryDetailMovementHistoryId` =  '%s'
    ";


$sql['get_tahun_anggaran_aktif'] = "
        SELECT
            thanggarId as id,
            thanggarNama as name
        FROM
            tahun_anggaran
        WHERE
            thanggarIsAktif='Y'
    ";
?>