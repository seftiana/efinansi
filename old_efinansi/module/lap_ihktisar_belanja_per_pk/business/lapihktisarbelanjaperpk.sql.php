<?php

$sql['get_count_data_laporan'] ="
SELECT FOUND_ROWS() as total
";

$sql['get_data_laporan'] ="
SELECT
	SQL_CALC_FOUND_ROWS
	pr.programId AS program_id,
	pr.programNomor AS program_kode,
	pr.programNama AS program_nama,
	IFNULL(rkakl_k.`rkaklKegiatanNama`,'-') AS rkakl_kegiatan_nama,
	sp.subprogId AS kegiatan_id,
	sp.subprogNomor AS kegiatan_kode,
	sp.subprogNama AS kegiatan_nama,
	IFNULL(rkakl_sk.`rkaklSubKegiatanNama`,'-') AS rkakl_sub_kegiatan_nama,
	kr.kegrefId AS sub_kegiatan_id,
	kr.kegrefNomor AS sub_kegiatan_kode,
	kr.kegrefNama AS sub_kegiatan_nama,
	IFNULL(rkakl_o.`rkaklOutputNama`,'-') AS rkakl_output_nama,
	kegdetId AS kegiatan_detil_id,
    GROUP_CONCAT(uk.`unitkerjaNama`) AS unit
FROM
rencana_pengeluaran rpeng
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k ON  kd.kegdetKegId = k.kegId
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
LEFT JOIN kegiatan_ref kr ON kr.kegrefId = kd.kegdetKegrefId
LEFT JOIN sub_program sp ON kr.kegrefSubprogId = sp.subprogId
LEFT JOIN program_ref pr ON sp.subprogProgramId = pr.programId

LEFT JOIN finansi_ref_rkakl_kegiatan rkakl_k ON rkakl_k.`rkaklKegiatanId` =  pr.`programRKAKLKegiatanId`
LEFT JOIN finansi_ref_rkakl_subkegiatan rkakl_sk ON rkakl_sk.`rkaklSubKegiatanId` = kr.`kegrefRkaklSubKegiatanId`
LEFT JOIN finansi_ref_rkakl_output rkakl_o ON rkakl_o.`rkaklOutputId` = sp.`subprogRKAKLOutputId`
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
GROUP BY sub_kegiatan_id
ORDER BY program_kode,kegiatan_kode,sub_kegiatan_kode
%s
";

$sql['get_mak'] ="
SELECT
  pb.`paguBasKode` AS mak_kode,
  pb.`paguBasKeterangan` AS mak_nama
FROM `finansi_pa_pagu_bas_kelompok_laporan_ref` kl
  JOIN `finansi_pa_pagu_bas_kelompok_jenis_laporan_ref` kjl
	ON kjl.`paguBasKelJnsLapRefId` = kl.`paguBasKelLapRefJnsLapRefId`
  JOIN `finansi_pa_pagu_bas_kelompok_laporan_ref_pagu_bas` pbk
	ON pbk.`paguBasKelLapKelLapRefId` = kl.`paguBasKelLapRefId`
  JOIN finansi_ref_pagu_bas  pb
ON pb.`paguBasId` = pbk.`paguBasKelLapPaguBasId`
WHERE kjl.`paguBasKelJnsLapRefId` = '%s'
";

$sql['get_pagu_bas_header'] ="
SELECT
pb_p.`paguBasId` AS id,
pb_p.`paguBasKode` AS kode,
pb_p.`paguBasKeterangan` AS nama
FROM 
rencana_pengeluaran rpeng
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN finansi_ref_pagu_bas pb_p ON pb_p.`paguBasId` = pb.`paguBasParentId`
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k ON k.`kegId` = kd.`kegdetKegId`
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
WHERE
k.`kegThanggarId` = '%s'
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
GROUP BY kode
";

$sql['get_nominal_pengeluaran']="
SELECT 
  kr.`kegrefId` AS sub_keg_id,
  pb_p.`paguBasId` AS pb_id,
  SUM(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )
  ) AS nominal
FROM
  rencana_pengeluaran rpeng
  LEFT JOIN komponen komp 
    ON komp.kompKode = rpeng.rncnpengeluaranKomponenKode 
  LEFT JOIN finansi_ref_pagu_bas pb
     ON  pb.`paguBasId`= rpeng.`rncnpengeluaranMakId`
  LEFT JOIN finansi_ref_pagu_bas pb_p
     ON pb_p.`paguBasId` = pb.`paguBasParentId`   
  LEFT JOIN kegiatan_detail kd
     ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
  LEFT JOIN kegiatan_ref kr
     ON kr.`kegrefId` = kd.`kegdetKegrefId` 
  LEFT JOIN kegiatan k
     ON k.`kegId` = kd.`kegdetKegId`
 LEFT JOIN unit_kerja_ref uk 
	ON uk.`unitkerjaId` = k.`kegUnitkerjaId`     
WHERE 
  k.`kegThanggarId` = '%s'
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
GROUP BY sub_keg_id,pb_id
";


$sql['get_nominal_pengeluaran_per_k'] ="
SELECT 
 sp.`subprogId` AS k_id,
  pb_p.`paguBasId` AS pb_id,
  SUM(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )
  ) AS nominal
FROM
  rencana_pengeluaran rpeng
  LEFT JOIN komponen komp 
    ON komp.kompKode = rpeng.rncnpengeluaranKomponenKode 
  LEFT JOIN finansi_ref_pagu_bas pb
     ON  pb.`paguBasId`= rpeng.`rncnpengeluaranMakId`
  LEFT JOIN finansi_ref_pagu_bas pb_p
     ON pb_p.`paguBasId` = pb.`paguBasParentId`   
  LEFT JOIN kegiatan_detail kd
     ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
  LEFT JOIN kegiatan_ref kr
     ON kr.`kegrefId` = kd.`kegdetKegrefId` 
  LEFT JOIN kegiatan k
     ON k.`kegId` = kd.`kegdetKegId`
LEFT JOIN sub_program sp
  ON sp.`subprogId` = kr.`kegrefSubprogId`     
 LEFT JOIN unit_kerja_ref uk 
	ON uk.`unitkerjaId` = k.`kegUnitkerjaId`     
WHERE 
  k.`kegThanggarId` = '%s'
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
GROUP BY k_id,pb_id
";

$sql['get_nominal_pengeluaran_per_p'] ="
SELECT
 pr.`programId` AS p_id,
  pb_p.`paguBasId` AS pb_id,
  SUM(
    rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    )
  ) AS nominal
FROM
  rencana_pengeluaran rpeng
  LEFT JOIN komponen komp 
    ON komp.kompKode = rpeng.rncnpengeluaranKomponenKode 
  LEFT JOIN finansi_ref_pagu_bas pb
     ON  pb.`paguBasId`= rpeng.`rncnpengeluaranMakId`
  LEFT JOIN finansi_ref_pagu_bas pb_p
     ON pb_p.`paguBasId` = pb.`paguBasParentId`   
  LEFT JOIN kegiatan_detail kd
     ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
  LEFT JOIN kegiatan_ref kr
     ON kr.`kegrefId` = kd.`kegdetKegrefId` 
  LEFT JOIN kegiatan k
     ON k.`kegId` = kd.`kegdetKegId`
LEFT JOIN sub_program sp
  ON sp.`subprogId` = kr.`kegrefSubprogId`     
LEFT JOIN program_ref pr
 ON pr.`programId` = sp.`subprogProgramId`
 LEFT JOIN unit_kerja_ref uk 
	ON uk.`unitkerjaId` = k.`kegUnitkerjaId`     
WHERE 
  k.`kegThanggarId` = '%s'
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
GROUP BY p_id,pb_id
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
