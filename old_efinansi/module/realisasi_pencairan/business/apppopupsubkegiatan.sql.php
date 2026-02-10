<?php
$sql['get_count_data_kegiatanref'] = "
SELECT 
     count(kegrefId) AS total
FROM      
     kegiatan_detail kd 
     JOIN kegiatan_ref kr ON (kd.kegdetKegrefId=kr.kegrefId)      
     JOIN sub_program sp ON (kr.kegrefSubprogId=sp.subprogId)
WHERE     
    kd.kegdetKegId = %s
	AND kr.kegrefSubprogId = %s
	AND kr.kegrefNama LIKE %s
	AND kd.kegdetIsAprove = 'Ya'
";

$sql['get_data_kegiatanref'] = "
SELECT 
      DISTINCT
      kegrefId as id,
      ifnull(CONCAT(			
			CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
			WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.',subprogJeniskegId,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor) 
				WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.',CASE WHEN LENGTH(kegrefNomor) = 1 THEN CONCAT('0',kegrefNomor)
			WHEN LENGTH(kegrefNomor) = 2 THEN kegrefNomor END),'') as kode, 
      kegrefNama as nama,      
      kd.kegdetId as kegiatandetail_id, 
      (SELECT
         SUM(rp.rncnpengeluaranKomponenNominalAprove*rp.rncnpengeluaranSatuanAprove) 
       FROM
         rencana_pengeluaran rp
       WHERE
         rp.rncnpengeluaranKegdetId=kd.kegdetId
      ) AS total_anggaran,
      
       (IFNULL((SELECT pengrealNominal FROM pengajuan_realisasi WHERE pengrealKegdetId=kd.kegdetId AND  pengrealIsApprove IS NULL),0)) AS realisasi_nominal,
      
       (IFNULL((SELECT pengrealNominalAprove FROM pengajuan_realisasi WHERE pengrealKegdetId=kd.kegdetId AND pengrealIsApprove = 'Ya'),0))       AS realisasi_pencairan
      
FROM      
     kegiatan_detail kd
     JOIN kegiatan_ref kr ON (kd.kegdetKegrefId=kr.kegrefId)      
     JOIN sub_program sp ON (kr.kegrefSubprogId=sp.subprogId)
     LEFT JOIN pengajuan_realisasi pr ON (kd.kegdetId=pr.pengrealKegdetId)
     LEFT JOIN program_ref ON subprogProgramId =  programId
WHERE     
    kd.kegdetKegId = %s
    AND kr.kegrefSubprogId = %s	
    AND kr.kegrefNama LIKE %s
    AND kd.kegdetIsAprove = 'Ya'
	
LIMIT %s, %s
";
?>