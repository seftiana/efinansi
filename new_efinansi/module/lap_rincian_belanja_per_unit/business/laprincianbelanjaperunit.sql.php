<?php

$sql['get_count_data_laporan'] = "
SELECT FOUND_ROWS() AS total
";
$sql['get_data_laporan'] = "
SELECT
	SQL_CALC_FOUND_ROWS
	pr.`programThanggarId`,
	uk.`unitkerjaId` AS unit_id,
	uk.`unitkerjaKode` AS unit_kode,
	uk.`unitkerjaNama` AS unit_nama,	
	CONCAT(uk.`unitkerjaId`,pr.programNomor) AS program_id,
	pr.programNomor AS program_kode,
	pr.programNama AS program_nama,
	IFNULL(rkakl_k.`rkaklKegiatanNama`,'-') AS rkakl_kegiatan_nama,
	CONCAT(uk.`unitkerjaId`,pr.programNomor,sp.subprogNomor) AS kegiatan_id,
	sp.subprogNomor AS kegiatan_kode,
	sp.subprogNama AS kegiatan_nama,
	IFNULL(rkakl_sk.`rkaklSubKegiatanNama`,'-') AS rkakl_sub_kegiatan_nama,
	CONCAT(uk.`unitkerjaId`,pr.programNomor,sp.subprogNomor,kr.kegrefNomor) AS sub_kegiatan_id,
	kr.kegrefNomor AS sub_kegiatan_kode,
	kr.kegrefNama AS sub_kegiatan_nama,
	IFNULL(rkakl_o.`rkaklOutputNama`,'-') AS rkakl_output_nama,
	kegdetId AS kegiatan_detil_id,
	CONCAT(uk.`unitkerjaId`,pr.programNomor,sp.subprogNomor,kr.kegrefNomor,pb_p.`paguBasKode`) AS mak_parent_id,
	pb_p.`paguBasKode` AS mak_parent_kode,
	pb_p.`paguBasKeterangan` AS mak_parent_nama,
	CONCAT(uk.`unitkerjaId`,pr.programNomor,sp.subprogNomor,kr.kegrefNomor,pb_p.`paguBasKode`,pb.`paguBasKode` ) AS mak_id,
	pb.`paguBasKode` AS mak_kode,
	pb.`paguBasKeterangan` AS mak_nama,
	CONCAT(rpeng.`rncnpengeluaranKomponenNama`,' - ', pr.`programThanggarId`) AS komponen_nama,
	SUM(IF( pr.`programThanggarId`=%s,
	rpeng.`rncnpengeluaranSatuan`,0)) AS volume_sebelum,
	SUM(IF( pr.`programThanggarId`=%s,
	rpeng.`rncnpengeluaranSatuan`,0)) AS volume_sekarang,
SUM(IF( pr.`programThanggarId`=%s,(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )),0)) AS nominal_sebelum,
SUM(IF( pr.`programThanggarId`=%s,(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )),0)) AS nominal_sekarang,   
    GROUP_CONCAT(sd.`sumberdanaNama`) AS sumber_dana,
CONCAT(uk.`unitkerjaId`,pr.programNomor,sp.subprogNomor,kr.kegrefNomor,pb_p.`paguBasKode`,pb.`paguBasKode`, komp.`kompKode` ) AS komKode
FROM
rencana_pengeluaran rpeng
 LEFT JOIN komponen komp ON komp.kompKode = rpeng.rncnpengeluaranKomponenKode 
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k ON  kd.kegdetKegId = k.kegId
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
LEFT JOIN kegiatan_ref kr ON kr.kegrefId = kd.kegdetKegrefId
LEFT JOIN sub_program sp ON kr.kegrefSubprogId = sp.subprogId
LEFT JOIN program_ref pr ON sp.subprogProgramId = pr.programId
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN finansi_ref_pagu_bas pb_p ON pb_p.`paguBasId` = pb.`paguBasParentId`
LEFT JOIN finansi_ref_rkakl_kegiatan rkakl_k ON rkakl_k.`rkaklKegiatanId` =  pr.`programRKAKLKegiatanId`
LEFT JOIN finansi_ref_rkakl_subkegiatan rkakl_sk ON rkakl_sk.`rkaklSubKegiatanId` = kr.`kegrefRkaklSubKegiatanId`
LEFT JOIN finansi_ref_rkakl_output rkakl_o ON rkakl_o.`rkaklOutputId` = sp.`subprogRKAKLOutputId`
LEFT JOIN finansi_ref_sumber_dana sd ON sd.`sumberdanaId` = komp.`kompSumberDanaId`
WHERE
 pr.`programThanggarId` IN( '%s','%s')
 AND
   (uk.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	uk.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)

GROUP BY komKode
ORDER BY unit_kode,program_kode,kegiatan_kode,sub_kegiatan_kode
%s
";

$sql['get_data_laporan_old'] = "
SELECT
	SQL_CALC_FOUND_ROWS
	uk.`unitkerjaId` AS unit_id,
	uk.`unitkerjaKode` AS unit_kode,
	uk.`unitkerjaNama` AS unit_nama,	
	CONCAT(uk.`unitkerjaId`,pr.programNomor) AS program_id,
	pr.programNomor AS program_kode,
	pr.programNama AS program_nama,
	IFNULL(rkakl_k.`rkaklKegiatanNama`,'-') AS rkakl_kegiatan_nama,
	CONCAT(uk.`unitkerjaId`,pr.programNomor,sp.subprogNomor) AS kegiatan_id,
	sp.subprogNomor AS kegiatan_kode,
	sp.subprogNama AS kegiatan_nama,
	IFNULL(rkakl_sk.`rkaklSubKegiatanNama`,'-') AS rkakl_sub_kegiatan_nama,
	CONCAT(uk.`unitkerjaId`,pr.programNomor,sp.subprogNomor,kr.kegrefNomor) AS sub_kegiatan_id,
	kr.kegrefNomor AS sub_kegiatan_kode,
	kr.kegrefNama AS sub_kegiatan_nama,
	IFNULL(rkakl_o.`rkaklOutputNama`,'-') AS rkakl_output_nama,
	kegdetId AS kegiatan_detil_id,
	CONCAT(uk.`unitkerjaId`,pr.programNomor,sp.subprogNomor,kr.kegrefNomor,pb_p.`paguBasKode`) AS mak_parent_id,
	pb_p.`paguBasKode` AS mak_parent_kode,
	pb_p.`paguBasKeterangan` AS mak_parent_nama,
	CONCAT(uk.`unitkerjaId`,pr.programNomor,sp.subprogNomor,kr.kegrefNomor,pb_p.`paguBasKode`,pb.`paguBasKode` ) AS mak_id,
	pb.`paguBasKode` AS mak_kode,
	pb.`paguBasKeterangan` AS mak_nama,
	concat(rpeng.`rncnpengeluaranKomponenNama`,' - ', pr.`programThanggarId`) AS komponen_nama,
	rpeng.`rncnpengeluaranSatuan` AS volume,
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    ) AS nominal,
	sd.`sumberdanaNama` AS sumber_dana
FROM
rencana_pengeluaran rpeng
 LEFT JOIN komponen komp ON komp.kompKode = rpeng.rncnpengeluaranKomponenKode 
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k ON  kd.kegdetKegId = k.kegId
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
LEFT JOIN kegiatan_ref kr ON kr.kegrefId = kd.kegdetKegrefId
LEFT JOIN sub_program sp ON kr.kegrefSubprogId = sp.subprogId
LEFT JOIN program_ref pr ON sp.subprogProgramId = pr.programId
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN finansi_ref_pagu_bas pb_p ON pb_p.`paguBasId` = pb.`paguBasParentId`
LEFT JOIN finansi_ref_rkakl_kegiatan rkakl_k ON rkakl_k.`rkaklKegiatanId` =  pr.`programRKAKLKegiatanId`
LEFT JOIN finansi_ref_rkakl_subkegiatan rkakl_sk ON rkakl_sk.`rkaklSubKegiatanId` = kr.`kegrefRkaklSubKegiatanId`
LEFT JOIN finansi_ref_rkakl_output rkakl_o ON rkakl_o.`rkaklOutputId` = sp.`subprogRKAKLOutputId`
LEFT JOIN finansi_ref_sumber_dana sd ON sd.`sumberdanaId` = komp.`kompSumberDanaId`
WHERE
 pr.`programThanggarId` = '%s'
 AND
   (uk.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	uk.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)
ORDER BY unit_kode,program_kode,kegiatan_kode,sub_kegiatan_kode
%s
";


$sql['get_total_per_mak']="
SELECT 
	CONCAT(uk.`unitkerjaId`,pr.programNomor,sp.subprogNomor,kr.kegrefNomor,pb_p.`paguBasKode`,pb.`paguBasKode` ) AS mak_id,
SUM(IF( pr.`programThanggarId`=%s,(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )),0)) AS nominal_sebelum,
SUM(IF( pr.`programThanggarId`=%s,(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )),0)) AS nominal_sekarang
FROM
rencana_pengeluaran rpeng
 LEFT JOIN komponen komp ON komp.kompKode = rpeng.rncnpengeluaranKomponenKode 
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k ON  kd.kegdetKegId = k.kegId
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
LEFT JOIN kegiatan_ref kr ON kr.kegrefId = kd.kegdetKegrefId
LEFT JOIN sub_program sp ON kr.kegrefSubprogId = sp.subprogId
LEFT JOIN program_ref pr ON sp.subprogProgramId = pr.programId
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN finansi_ref_pagu_bas pb_p ON pb_p.`paguBasId` = pb.`paguBasParentId`
WHERE
 pr.`programThanggarId` IN( '%s','%s')
  AND
   (uk.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	uk.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)   
GROUP BY mak_id
";

$sql['get_total_per_pagu']="
SELECT 
	CONCAT(uk.`unitkerjaId`,pr.programNomor,sp.subprogNomor,kr.kegrefNomor,pb_p.`paguBasKode`) AS mak_parent_id,
SUM(IF( pr.`programThanggarId`=%s,(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )),0)) AS nominal_sebelum,
SUM(IF( pr.`programThanggarId`=%s,(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )),0)) AS nominal_sekarang
FROM
rencana_pengeluaran rpeng
 LEFT JOIN komponen komp ON komp.kompKode = rpeng.rncnpengeluaranKomponenKode 
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k ON  kd.kegdetKegId = k.kegId
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
LEFT JOIN kegiatan_ref kr ON kr.kegrefId = kd.kegdetKegrefId
LEFT JOIN sub_program sp ON kr.kegrefSubprogId = sp.subprogId
LEFT JOIN program_ref pr ON sp.subprogProgramId = pr.programId
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN finansi_ref_pagu_bas pb_p ON pb_p.`paguBasId` = pb.`paguBasParentId`
WHERE
 pr.`programThanggarId` IN( '%s','%s')
   AND
   (uk.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	uk.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)  
GROUP BY mak_parent_id
";
$sql['get_total_per_sk']="
SELECT 
	CONCAT(uk.`unitkerjaId`,pr.programNomor,sp.subprogNomor,kr.kegrefNomor) AS sk_id,
SUM(IF( pr.`programThanggarId`=%s,(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )),0)) AS nominal_sebelum,
SUM(IF( pr.`programThanggarId`=%s,(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )),0)) AS nominal_sekarang
FROM
rencana_pengeluaran rpeng
 LEFT JOIN komponen komp ON komp.kompKode = rpeng.rncnpengeluaranKomponenKode 
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k ON  kd.kegdetKegId = k.kegId
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
LEFT JOIN kegiatan_ref kr ON kr.kegrefId = kd.kegdetKegrefId
LEFT JOIN sub_program sp ON kr.kegrefSubprogId = sp.subprogId
LEFT JOIN program_ref pr ON sp.subprogProgramId = pr.programId
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN finansi_ref_pagu_bas pb_p ON pb_p.`paguBasId` = pb.`paguBasParentId`
WHERE
 pr.`programThanggarId` IN( '%s','%s')
   AND
   (uk.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	uk.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)  
GROUP BY sk_id
";
$sql['get_total_per_k']="
SELECT 
	CONCAT(uk.`unitkerjaId`,pr.programNomor,sp.subprogNomor) AS k_id,
SUM(IF( pr.`programThanggarId`=%s,(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )),0)) AS nominal_sebelum,
SUM(IF( pr.`programThanggarId`=%s,(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )),0)) AS nominal_sekarang
FROM
rencana_pengeluaran rpeng
 LEFT JOIN komponen komp ON komp.kompKode = rpeng.rncnpengeluaranKomponenKode 
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k ON  kd.kegdetKegId = k.kegId
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
LEFT JOIN kegiatan_ref kr ON kr.kegrefId = kd.kegdetKegrefId
LEFT JOIN sub_program sp ON kr.kegrefSubprogId = sp.subprogId
LEFT JOIN program_ref pr ON sp.subprogProgramId = pr.programId
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN finansi_ref_pagu_bas pb_p ON pb_p.`paguBasId` = pb.`paguBasParentId`
WHERE
 pr.`programThanggarId` IN( '%s','%s')
   AND
   (uk.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	uk.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)  
GROUP BY k_id
";

$sql['get_total_per_p']="
SELECT 
	CONCAT(uk.`unitkerjaId`,pr.programNomor) AS p_id,
SUM(IF( pr.`programThanggarId`=%s,(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )),0)) AS nominal_sebelum,
SUM(IF( pr.`programThanggarId`=%s,(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )),0)) AS nominal_sekarang
FROM
rencana_pengeluaran rpeng
 LEFT JOIN komponen komp ON komp.kompKode = rpeng.rncnpengeluaranKomponenKode 
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k ON  kd.kegdetKegId = k.kegId
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
LEFT JOIN kegiatan_ref kr ON kr.kegrefId = kd.kegdetKegrefId
LEFT JOIN sub_program sp ON kr.kegrefSubprogId = sp.subprogId
LEFT JOIN program_ref pr ON sp.subprogProgramId = pr.programId
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN finansi_ref_pagu_bas pb_p ON pb_p.`paguBasId` = pb.`paguBasParentId`
WHERE
 pr.`programThanggarId` IN( '%s','%s')
   AND
   (uk.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	uk.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)  
GROUP BY p_id
";

$sql['get_total_per_u']="
SELECT 
	uk.`unitkerjaId` AS u_id,
SUM(IF( pr.`programThanggarId`=%s,(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )),0)) AS nominal_sebelum,
SUM(IF( pr.`programThanggarId`=%s,(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )),0)) AS nominal_sekarang
FROM
rencana_pengeluaran rpeng
 LEFT JOIN komponen komp ON komp.kompKode = rpeng.rncnpengeluaranKomponenKode 
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k ON  kd.kegdetKegId = k.kegId
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
LEFT JOIN kegiatan_ref kr ON kr.kegrefId = kd.kegdetKegrefId
LEFT JOIN sub_program sp ON kr.kegrefSubprogId = sp.subprogId
LEFT JOIN program_ref pr ON sp.subprogProgramId = pr.programId
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN finansi_ref_pagu_bas pb_p ON pb_p.`paguBasId` = pb.`paguBasParentId`
WHERE
 pr.`programThanggarId` IN( '%s','%s')
   AND
   (uk.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	uk.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)  
GROUP BY u_id
";

$sql['get_total_all']="
SELECT 
SUM(IF( pr.`programThanggarId`=%s,(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )),0)) AS nominal_sebelum,
SUM(IF( pr.`programThanggarId`=%s,(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )),0)) AS nominal_sekarang
FROM
rencana_pengeluaran rpeng
 LEFT JOIN komponen komp ON komp.kompKode = rpeng.rncnpengeluaranKomponenKode 
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k ON  kd.kegdetKegId = k.kegId
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
LEFT JOIN kegiatan_ref kr ON kr.kegrefId = kd.kegdetKegrefId
LEFT JOIN sub_program sp ON kr.kegrefSubprogId = sp.subprogId
LEFT JOIN program_ref pr ON sp.subprogProgramId = pr.programId
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN finansi_ref_pagu_bas pb_p ON pb_p.`paguBasId` = pb.`paguBasParentId`
WHERE
 pr.`programThanggarId` IN( '%s','%s')
   AND
   (uk.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	uk.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)  
";

//COMBO
$sql['get_combo_tahun_anggaran']="
	SELECT
		thanggarId 		AS id,
		thanggarNama 	AS name
	FROM
		tahun_anggaran
	ORDER BY thanggarNama
";
//aktif
$sql['get_tahun_anggaran_aktif']="
	SELECT
		thanggarId 		AS id,
		thanggarNama 	AS name
	FROM
		tahun_anggaran
	WHERE
		thanggarIsAktif='Y'
";
//aktif
$sql['get_tahun_anggaran']="
SELECT 
  `thanggarId` AS `id`,
  `thanggarNama` AS `name`,
  `thanggarBuka` AS tgl_buka,
  `thanggarTutup` AS tgl_tutup,
  (YEAR(`thanggarBuka`)) AS `tahun_buka`,
  (YEAR(`thanggarTutup`)) AS `tahun_tutup` 
FROM
  `tahun_anggaran`
WHERE
  `thanggarId` = '%s'
";

$sql['get_tahun_anggaran_kemarin']="
SELECT 
  `thanggarId` AS `id`,
  `thanggarNama` AS `name`,
  `thanggarBuka` AS tgl_buka,
  `thanggarTutup` AS tgl_tutup,
  (YEAR(`thanggarBuka`)) AS `tahun_buka`,
  (YEAR(`thanggarTutup`)) AS `tahun_tutup` 
FROM
  `tahun_anggaran`
 WHERE 
 `thanggarBuka` < (
SELECT 
  `thanggarBuka`
FROM
  `tahun_anggaran`
 WHERE 
 thanggarId ='%s'
 )
ORDER BY tgl_buka DESC LIMIT 1
";


$sql['get_sumber_dana'] ="
SELECT
   sd.`sumberdanaNama` AS sumber_dana,
SUM(IF(  k.`kegThanggarId`= %s,(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )),0)) AS nominal_sebelum,
SUM(IF(  k.`kegThanggarId`= %s ,(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )),0)) AS nominal_sekarang    

FROM
rencana_pengeluaran rpeng
LEFT JOIN komponen komp ON komp.kompKode = rpeng.rncnpengeluaranKomponenKode 
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k ON  kd.kegdetKegId = k.kegId
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
LEFT JOIN finansi_ref_sumber_dana sd ON sd.`sumberdanaId` = komp.`kompSumberDanaId`
WHERE
  k.`kegThanggarId` IN('%s','%s')
 AND
   (uk.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	uk.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)
GROUP BY sd.`sumberdanaId` 
";