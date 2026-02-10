<?php
    //COMBO
    $sql['get_combo_tahun_anggaran']="
	    SELECT
		    thanggarId as id,
		    thanggarNama as name
	    FROM
		    tahun_anggaran
	    ORDER BY thanggarNama DESC
    ";
    
    //COMBO
    $sql['get_combo_tahun_anggaran']="
	    SELECT
		    thanggarId as id,
		    thanggarNama as name
	    FROM
		    tahun_anggaran
	    ORDER BY thanggarNama DESC
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

	$sql['get_count_data_kegiatanref'] = "
		SELECT FOUND_ROWS() AS total
	";

    $sql['get_data_kegiatanref'] = "
    SELECT   
          SQL_CALC_FOUND_ROWS
          kegrefId as id,
          ifnull(/*CONCAT(
			    CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
			    WHEN LENGTH(programNomor) = 2 THEN programNomor END programNomor ,'.',subprogJeniskegId,'.',
			    CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
				    WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END subprogNomor,'.',
			    CASE WHEN LENGTH(kegrefNomor) = 1 THEN CONCAT('0',kegrefNomor)
			        WHEN LENGTH(kegrefNomor) = 2 THEN kegrefNomor END kegrefNomor)*/kegrefNomor,'') as kode,
          kegrefNama as nama,
          kegUnitkerjaId as unit_kerja_id,
          uk.`unitkerjaNama` AS unit_kerja_nama,
          kd.kegdetId as kegiatandetail_id,
          (SELECT
             SUM(rp.rncnpengeluaranKomponenTotalAprove)
           FROM
             rencana_pengeluaran rp
           WHERE
             rp.rncnpengeluaranIsAprove = 'Ya'
             AND rp.rncnpengeluaranKegdetId=kd.kegdetId
          ) AS total_anggaran,
           (IFNULL(
            (SELECT SUM(pengrealNominalAprove) 
            FROM 
                (SELECT pengrealKegdetId,pengrealNominalAprove 
                FROM pengajuan_realisasi 
                WHERE pengrealIsApprove = 'Ya' 
                /*UNION SELECT kegAdjustAmblKegId,kegAdjustNominal 
                FROM kegiatan_adjust*/
                ) AS data_approve_tambah_adjust 
            WHERE pengrealKegdetId=kd.kegdetId),0)) AS realisasi_pencairan,#pencairan_di_terima
           (IFNULL((
            SELECT SUM(pengrealNominal) 
            FROM pengajuan_realisasi 
            WHERE pengrealKegdetId=kd.kegdetId AND 
            IFNULL(pengrealIsApprove,'Tidak') = 'Tidak'),0)) AS realisasi_nominal,#pencairan_usulan
          programId,
          programNomor,
          programNama,
          kegUnitkerjaId,
          subprogId,
          subprogNomor,
          subprogNama,
          subprogJeniskegId,
          kegdetDeskripsi  AS deskripsi,
          MONTH(`kegdetWaktuMulaiPelaksanaan`) AS bulan
    FROM
         kegiatan_detail kd
         JOIN kegiatan_ref kr ON (kd.kegdetKegrefId=kr.kegrefId)
         JOIN sub_program sp ON (kr.kegrefSubprogId=sp.subprogId)
         /*LEFT JOIN pengajuan_realisasi pr ON (kd.kegdetId=pr.pengrealKegdetId)*/
         LEFT JOIN program_ref ON subprogProgramId = programId
         JOIN kegiatan ON kegId = kegdetKegId
         LEFT JOIN unit_kerja_ref uk
         ON uk.`unitkerjaId` =  kegiatan.`kegUnitkerjaId`  
    WHERE
        (uk.unitkerjaId = %s) AND
        kr.kegrefNama LIKE %s AND
        (MONTH(`kegdetWaktuMulaiPelaksanaan`) = %s OR 1 = %s)
        AND `kegThanggarId` = (SELECT
          `thanggarId`
        FROM `tahun_anggaran`
        WHERE thanggarIsAktif = 'Y')
        AND kd.kegdetIsAprove = 'Ya' 
          AND kd.`kegdetId` NOT IN 
  (SELECT 
    rpeng.rncnpengeluaranKegdetId 
  FROM
    rencana_pengeluaran rpeng
    INNER JOIN `finansi_pa_movement_history_detail` mhd
    ON mhd.`movementHistoryDetailRncnpengeluaranId` = rpeng.`rncnpengeluaranId`
    INNER JOIN  `finansi_pa_movement_history` mh
    ON mh.`movementHistoryId` = mhd.`movementHistoryDetailMovementHistoryId`
    WHERE mh.`movementHistoryIsApprove` IN('Belum'))  
   AND (SUBSTR(uk.`unitkerjaKodeSistem`,1,
   		(SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = 
   		(SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') 
   		OR uk.unitkerjaId = '%s'
   )
    LIMIT %s, %s
    ";

    $sql['get_komponen_anggaran'] = "
SELECT 
   rpeng.rncnpengeluaranId AS rp_id,
   rpeng.rncnpengeluaranKomponenKode AS rp_kompkode,
   rpeng.rncnpengeluaranKomponenNama AS rp_kompnama,
   (rpeng.rncnpengeluaranKomponenTotalAprove  -( IFNULL(pencairan.nominal,0)  +  IFNULL(pencairan.nominal_usulan,0) ))AS nilai,  
   rpeng.rncnpengeluaranKegdetId 
FROM
  rencana_pengeluaran rpeng 
  LEFT JOIN (
  SELECT
   peng_real_det.`pengrealdetRncnpengeluaranId` AS rpengId,
   peng_real_det.`pengrealdetId` AS pdId,
   peng_real.`pengrealId` AS pId,
   peng_real.`pengrealKegdetId` AS kegDetId,
   SUM(IF(peng_real.`pengrealIsApprove` = 'Ya',peng_real_det.`pengrealdetNominalApprove`,0)) AS nominal,
   SUM(IF(peng_real.`pengrealIsApprove` IS NULL ,peng_real_det.`pengrealdetNominalPencairan`,0)) AS nominal_usulan
FROM `pengajuan_realisasi_detil` peng_real_det
JOIN `pengajuan_realisasi` peng_real
ON peng_real.`pengrealId` = peng_real_det.`pengrealdetPengRealId`

GROUP BY peng_real_det.`pengrealdetRncnpengeluaranId`
  ) pencairan ON pencairan.rpengId = rpeng.`rncnpengeluaranId` AND 
  pencairan.kegDetId = rpeng.`rncnpengeluaranKegdetId`
        WHERE rncnpengeluaranKegdetId = '%s'
            AND rncnpengeluaranIsAprove = 'Ya' 
#HAVING nilai > 0
";
    $sql['get_komponen_anggaran_tujuan'] = "
        SELECT
            rncnpengeluaranId AS rp_id,
            rncnpengeluaranKomponenKode AS rp_kompkode,
            rncnpengeluaranKomponenNama AS rp_kompnama,
            rncnpengeluaranKomponenTotalAprove AS nilai
        FROM rencana_pengeluaran
        WHERE rncnpengeluaranKegdetId = '%s'
            AND rncnpengeluaranIsAprove = 'Ya' 
    ";
?>