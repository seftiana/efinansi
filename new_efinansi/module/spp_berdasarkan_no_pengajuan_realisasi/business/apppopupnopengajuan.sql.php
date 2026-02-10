<?php

$sql['get_count_data'] = "
SELECT FOUND_ROWS() AS `total`
";

$sql['get_data'] = "
SELECT 
  SQL_CALC_FOUND_ROWS 
  pr.`pengrealId` AS pengajuan_id,
  prd.`pengrealdetId` AS pengajuan_detail_id,
  pr.`pengrealNomorPengajuan` AS pengajuan_nomor,
  pr.`pengrealTanggal` AS pengajuan_tgl,
  pb.`paguBasKode` AS kode_ma,
  rpeng.`rncnpengeluaranKomponenNama` AS nama_index,
  (prd.`pengrealdetNominalApprove`- (IFNULL(SUM(spd.`sppPengRealDtNominal`),0))) AS `total_approve`,
  pr.`pengrealKeterangan` AS pengajuan_keterangan 
FROM
  pengajuan_realisasi pr 
  LEFT JOIN pengajuan_realisasi_detil prd 
    ON prd.`pengrealdetPengRealId` = pr.`pengrealId` 
  LEFT JOIN finansi_pa_spp_pengajuan_real_detail spd
    ON spd.`sppPengRealDtPengRealDetId` = prd.`pengrealdetId`  
  LEFT JOIN rencana_pengeluaran rpeng 
    ON rpeng.`rncnpengeluaranId` = prd.`pengrealdetRncnpengeluaranId` 
  LEFT JOIN finansi_ref_pagu_bas pb 
    ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId` 
  LEFT JOIN kegiatan_detail kd 
    ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId` 
  LEFT JOIN kegiatan k 
    ON k.`kegId` = kd.`kegdetKegId` 
  LEFT JOIN kegiatan_ref kr 
    ON kr.`kegrefId` = kd.`kegdetKegrefId` 
  LEFT JOIN sub_program sp 
    ON sp.`subprogId` = kr.`kegrefSubprogId` 
  LEFT JOIN unit_kerja_ref uk 
    ON uk.`unitkerjaId` = k.`kegUnitkerjaId` 
  LEFT JOIN program_ref prog 
    ON prog.`programId` = sp.`subprogProgramId` 
WHERE 
	pr.`pengrealIsApprove` IN('Ya')
	AND
	uk.`unitkerjaId` = '%s'
	AND
	prog.`programId` ='%s'
	AND
	pr.`pengrealNomorPengajuan` LIKE '%s'	
GROUP BY pengajuan_detail_id
ORDER BY pengajuan_nomor ASC 
LIMIT %s,%s
";

$sql['get_data_old'] = "
SELECT SQL_CALC_FOUND_ROWS
	pr.`pengrealId` AS pengajuan_id,
	spp.`sppId` AS spp_id,
	pr.`pengrealNomorPengajuan` AS pengajuan_nomor,
	pr.`pengrealTanggal` AS pengajuan_tgl,
	uk.`unitkerjaKode` AS unit_kode,
	prog.`programNomor` AS prog_kode,
	prog.`programNama` AS prog_nama,
	sp.`subprogNomor` AS sub_prog_kode,
	sp.`subprogNama` AS sub_prog_nama,
	kr.`kegrefNomor` AS kegiatan_kode,
	kr.`kegrefNama` AS kegiatan_nama,
	pb.`paguBasKode` AS ma_kode,
	SUM(sd.`sppDetNominal`) AS nilai_total_spp,
	pr.`pengrealKeterangan` AS pengajuan_keterangan
FROM 
	pengajuan_realisasi pr
	LEFT JOIN pengajuan_realisasi_detil prd ON prd.`pengrealdetPengRealId` = pr.`pengrealId`
	LEFT JOIN finansi_pa_spp_det sd ON sd.`sppDetRealdetId` = prd.`pengrealdetId`
	LEFT JOIN finansi_pa_spp spp ON spp.`sppId` = sd.`sppDetSppId`	
	LEFT JOIN rencana_pengeluaran rpeng ON rpeng.`rncnpengeluaranId` = prd.`pengrealdetRncnpengeluaranId`
	LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
	LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
	LEFT JOIN kegiatan k ON k.`kegId` = kd.`kegdetKegId`
	LEFT JOIN kegiatan_ref kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
	LEFT JOIN sub_program sp ON sp.`subprogId` = kr.`kegrefSubprogId`
	LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
	LEFT JOIN program_ref prog ON prog.`programId` =sp.`subprogProgramId`
WHERE 
	(pr.`pengrealIsApprove` NOT IN('Ya') OR pr.`pengrealIsApprove` IS NULL)
	AND
	uk.`unitkerjaId` = '%s'
	AND
	prog.`programId` ='%s'
	AND
	pr.`pengrealNomorPengajuan` LIKE '%s'	
	AND 
	sd.`sppDetSppId` IS NOT NULL
	/*AND 
	pr.`pengrealId` NOT IN (SELECT `sppPengRealDtPengRealId` FROM  `finansi_pa_spp_pengajuan_real_detail`)*/
GROUP BY kd.`kegdetId`,pengajuan_nomor 
ORDER BY pengajuan_tgl DESC,pengajuan_nomor 
LIMIT %s,%s
";

?>