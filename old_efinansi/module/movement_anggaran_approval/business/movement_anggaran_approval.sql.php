<?php

$sql['get_periode_tahun'] = "
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
$sql['get_data_movement'] = "
SELECT 
  SQL_CALC_FOUND_ROWS 
  k_asal.ta_id AS ta_id,
  k_asal.bulan AS bulan_asal,
  k_tujuan.bulan AS bulan_tujuan,
  mp.`movementHistoryId` AS id,
  mp.`movementHistoryNomor` AS nomor,
  k_asal.kd_id,
  k_asal.uk_kode AS unit_kerja_kode_asal,
  k_asal.uk_nama AS unit_kerja_nama_asal,
  mp.`movementHistoryKegrefIdAsal` AS keg_asal,
  IFNULL(k_asal.keg_nomor, '') AS nomor_kegiatan_asal,
  k_asal.keg_nama AS kegiatan_asal,
  mp.`movementHistoryNilai` AS nilai,
  SUM(IF(mp_d_t.`movementHistoryDetailType` = 'asal',0,
    (mp_d_t.`movementHistoryDetailNilaiSemula` + mp_d_t.`movementHistoryDetailNilai`) )) AS nilai_sekarang,
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
    LEFT JOIN `finansi_pa_movement_history_detail` mp_d 
        ON mp_d.`movementHistoryDetailMovementHistoryId` = mp.`movementHistoryId` AND
    mp_d.`movementHistoryDetailType` = 'asal'
    LEFT JOIN `finansi_pa_movement_history_detail` mp_d_t 
	ON mp_d_t.`movementHistoryDetailMovementHistoryId` = mp.`movementHistoryId` AND
        mp_d_t.`movementHistoryDetailType` = 'tujuan'
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
	  ) k_asal ON 
		k_asal.uk_id = mp.`movementHistoryUnitKerjaIdAsal` 
		AND k_asal.kref_id = mp.`movementHistoryKegrefIdAsal`
		AND k_asal.db_id = mp_d.`movementHistoryDetailRncnpengeluaranId` 
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
	  ) k_tujuan ON 
		k_tujuan.uk_id = mp.`movementHistoryUnitKerjaIdTujuan`
		AND k_tujuan.kref_id = mp.`movementHistoryKegrefIdTujuan`
		AND k_tujuan.db_id = mp_d_t.`movementHistoryDetailRncnpengeluaranId` 
   WHERE 
   ((k_asal.keg_nomor LIKE '%s' OR k_asal.keg_nama LIKE '%s') OR 1 = %s) 
   AND 
   ((k_tujuan.keg_nomor LIKE '%s' OR k_tujuan.keg_nama LIKE '%s') OR 1 = %s)
   AND 
    (k_asal.ta_id = '%s' 
    AND
    k_tujuan.ta_id = '%s' 
    )
   AND
   (k_asal.bulan = '%s'  OR 1 = %s) 
    AND    
   (k_tujuan.bulan = '%s'  OR 1 = %s) 
   GROUP BY mp.`movementHistoryId`
   ORDER BY tanggal DESC
   LIMIT %s, %s
";

$sql['get_count_movement'] = "
      SELECT FOUND_ROWS() AS total
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

$sql['get_total_row_history'] ="
SELECT
  COUNT(mh.movementHistoryId) AS total_row_history
FROM
  `finansi_pa_movement_history` mh
WHERE
  mh.`movementHistoryId` = '%s'
  AND
  mh.`movementHistoryIsApprove` = 'Ya'
";

$sql['approve_ya'] = "
    UPDATE 
        rencana_pengeluaran rpeng
        INNER JOIN `finansi_pa_movement_history_detail` mhd
        ON mhd.`movementHistoryDetailRncnpengeluaranId`  = rpeng.`rncnpengeluaranId`
        INNER JOIN `finansi_pa_movement_history` mh
        ON mh.`movementHistoryId` = mhd.`movementHistoryDetailMovementHistoryId`
    SET 
        mh.`movementHistoryIsApprove` = 'Ya',
        mh.`movementHistoryTanggalApprove` = NOW(),
            rpeng.rncnpengeluaranKomponenTotalAprove = IF(mhd.`movementHistoryDetailType` = 'asal',
            (rpeng.rncnpengeluaranKomponenTotalAprove - mhd.`movementHistoryDetailNilai`),
            (rpeng.rncnpengeluaranKomponenTotalAprove + mhd.`movementHistoryDetailNilai`)) 
    WHERE  
        mhd.`movementHistoryDetailMovementHistoryId` = '%s'
    ";

$sql['approve_tidak'] = "
    UPDATE `finansi_pa_movement_history`
    SET 
        `movementHistoryTanggalApprove` = NOW(),
        `movementHistoryIsApprove` = 'Tidak'
    WHERE 
        `movementHistoryId` = '%s';
    ";
?>