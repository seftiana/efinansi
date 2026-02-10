<?php

//===GET===
$sql['get_count_data'] = "
SELECT COUNT(*) as total 
FROM(
	SELECT
		unitkerjaId AS id_unit,
		unitkerjaKode AS kode_unit,
		finansi_ref_rkakl_kode_penerimaan.`rkaklKodePenerimaanKode` AS kode,
		IF(kode_penerimaan_ref.`kodeterimaSumberdanaId` = 4,1,2) AS sd
	FROM rencana_penerimaan
	LEFT JOIN unit_kerja_ref ON renterimaUnitkerjaId = unitkerjaId AND renterimaThanggarId ='%s'
	LEFT JOIN realisasi_penerimaan ON realrenterimaId = renterimaId
	LEFT JOIN kode_penerimaan_ref ON renterimaKodeterimaId = kodeterimaId
	LEFT JOIN finansi_ref_rkakl_kode_penerimaan 
		ON finansi_ref_rkakl_kode_penerimaan.`rkaklKodePenerimaanId` = kode_penerimaan_ref.`kodeterimaRKAKLKodePenerimaanId`
	LEFT JOIN finansi_ref_sumber_dana 
		ON finansi_ref_sumber_dana.`sumberdanaId` = kode_penerimaan_ref.`kodeterimaSumberdanaId`
	LEFT JOIN (SELECT renterimaUnitkerjaId AS totalUnitkerjaId, SUM(renterimaTotalTerima) AS totalTotalTerima, 
	SUM(renterimaJumlah) AS totalterima FROM rencana_penerimaan WHERE renterimaThanggarId ='%s' GROUP BY totalUnitkerjaId) AS total ON totalUnitkerjaId=unitkerjaId
	WHERE
	finansi_ref_rkakl_kode_penerimaan.`rkaklKodePenerimaanKode` <> 'NULL' AND
	unit_kerja_ref.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'%s') 
	OR 
	unit_kerja_ref.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')	
GROUP BY kode_unit/*,sd, kode */
ORDER BY unitkerjaId,sd,kodeterimaKode 
) lrpu
";

$sql['get_data_rincian_pendapatan_per_unit'] = "
SELECT 
uk.`unitkerjaId` as unit_kerja_id,
uk.`unitkerjaKode` as unit_kerja_kode,
uk.`unitkerjaNama` as unit_kerja_nama,
sd.`sumberdanaId` AS sd_id,
sd.`sumberdanaNama` AS sd_nama,
pb.`paguBasKode` AS mak_kode,
pb.`paguBasKeterangan` AS  mak_nama,
SUM(IF( rpen.`renterimaThanggarId` = %s,rpen.`renterimaTotalTerima`,0)) AS target_sebelum,
SUM(IF( rpen.`renterimaThanggarId` = %s,realpen.`realterimaTotalTerima`,0)) AS realisasi_sebelum,
SUM(IF( rpen.`renterimaThanggarId` = %s,rpen.`renterimaTotalTerima`,0)) AS target_sekarang
FROM rencana_penerimaan rpen
LEFT JOIN  kode_penerimaan_ref kp
ON kp.`kodeterimaId` = rpen.`renterimaKodeterimaId`
LEFT JOIN finansi_ref_pagu_bas pb
ON pb.`paguBasId` = kp.`kodeterimaPaguBasId`
LEFT JOIN unit_kerja_ref uk
ON uk.`unitkerjaId` = rpen.`renterimaUnitkerjaId`
LEFT JOIN finansi_ref_sumber_dana sd
ON sd.`sumberdanaId` = rpen.`renterimaSumberDanaId`
LEFT JOIN realisasi_penerimaan realpen
ON realpen.`realrenterimaId` = rpen.`renterimaId`
WHERE
 rpen.`renterimaThanggarId` IN('%s','%s')
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

 AND pb.`paguBasKode`IS NOT NULL
 AND rpen.`renterimaSumberDanaId` IS NOT NULL
GROUP BY sd_id,mak_kode
%s
";

$sql['get_total_rincian_pendapatan_per_sd']="
SELECT 
sd.`sumberdanaId` AS sd_id,
SUM(IF( rpen.`renterimaThanggarId` = %s,rpen.`renterimaTotalTerima`,0)) AS target_sebelum,
SUM(IF( rpen.`renterimaThanggarId` = %s,realpen.`realterimaTotalTerima`,0)) AS realisasi_sebelum,
SUM(IF( rpen.`renterimaThanggarId` = %s,rpen.`renterimaTotalTerima`,0)) AS target_sekarang
FROM rencana_penerimaan rpen
LEFT JOIN  kode_penerimaan_ref kp
ON kp.`kodeterimaId` = rpen.`renterimaKodeterimaId`
LEFT JOIN finansi_ref_pagu_bas pb
ON pb.`paguBasId` = kp.`kodeterimaPaguBasId`
LEFT JOIN unit_kerja_ref uk
ON uk.`unitkerjaId` = rpen.`renterimaUnitkerjaId`
LEFT JOIN finansi_ref_sumber_dana sd
ON sd.`sumberdanaId` = rpen.`renterimaSumberDanaId`
LEFT JOIN realisasi_penerimaan realpen
ON realpen.`realrenterimaId` = rpen.`renterimaId`
WHERE
 rpen.`renterimaThanggarId` IN('%s','%s')
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

 AND pb.`paguBasKode`IS NOT NULL
GROUP BY sd_id
";

$sql['get_total_rincian_pendapatan_all']="
SELECT 
SUM(IF( rpen.`renterimaThanggarId` = %s,rpen.`renterimaTotalTerima`,0)) AS target_sebelum,
SUM(IF( rpen.`renterimaThanggarId` = %s,realpen.`realterimaTotalTerima`,0)) AS realisasi_sebelum,
SUM(IF( rpen.`renterimaThanggarId` = %s,rpen.`renterimaTotalTerima`,0)) AS target_sekarang
FROM rencana_penerimaan rpen
LEFT JOIN  kode_penerimaan_ref kp
ON kp.`kodeterimaId` = rpen.`renterimaKodeterimaId`
LEFT JOIN finansi_ref_pagu_bas pb
ON pb.`paguBasId` = kp.`kodeterimaPaguBasId`
LEFT JOIN unit_kerja_ref uk
ON uk.`unitkerjaId` = rpen.`renterimaUnitkerjaId`
LEFT JOIN finansi_ref_sumber_dana sd
ON sd.`sumberdanaId` = rpen.`renterimaSumberDanaId`
LEFT JOIN realisasi_penerimaan realpen
ON realpen.`realrenterimaId` = rpen.`renterimaId`
WHERE
 rpen.`renterimaThanggarId` IN('%s','%s')
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

 AND pb.`paguBasKode`IS NOT NULL
";
$sql['get_data_rincian_pendapatan_per_unit_old'] = "
	SELECT
		unitkerjaId AS id_unit,
		unitkerjaKode AS kode_unit,
		unitkerjaNama AS nama_unit,
		finansi_ref_rkakl_kode_penerimaan.`rkaklKodePenerimaanKode` AS kode,
		finansi_ref_rkakl_kode_penerimaan.`rkaklKodePenerimaanNama` AS nama,
		SUM(IF (renterimaTotalTerima IS NULL,0,renterimaTotalTerima)) 	AS target_pnbp,
		SUM(realterimaJmlJan) + SUM(realterimaJmlFeb) + SUM(realterimaJmlMar) +
		SUM(realterimaJmlApr) + SUM(realterimaJmlMei) + SUM(realterimaJmlJun) +
		SUM(realterimaJmlJul) + SUM(realterimaJmlAgt) + SUM(realterimaJmlSep) +
		SUM(realterimaJmlOkt) + SUM(realterimaJmlNov) + SUM(realterimaJmlDes) AS total_real,
		SUM(realterimaTotalTerima) AS total_realisasi,
		IF(kode_penerimaan_ref.`kodeterimaSumberdanaId` = 4,1,2) AS sd,/* 1 = PNBP 2 = selain PNBP */
		CONCAT(unitkerjaId,IF(kode_penerimaan_ref.`kodeterimaSumberdanaId` = 4,1,2)) AS unit_sd_id
	FROM rencana_penerimaan
	LEFT JOIN unit_kerja_ref ON renterimaUnitkerjaId = unitkerjaId AND renterimaThanggarId ='%s'
	LEFT JOIN realisasi_penerimaan ON realrenterimaId = renterimaId
	LEFT JOIN kode_penerimaan_ref ON renterimaKodeterimaId = kodeterimaId
	LEFT JOIN finansi_ref_rkakl_kode_penerimaan 
		ON finansi_ref_rkakl_kode_penerimaan.`rkaklKodePenerimaanId` = kode_penerimaan_ref.`kodeterimaRKAKLKodePenerimaanId`
	LEFT JOIN finansi_ref_sumber_dana 
		ON finansi_ref_sumber_dana.`sumberdanaId` = kode_penerimaan_ref.`kodeterimaSumberdanaId`
	LEFT JOIN (SELECT renterimaUnitkerjaId AS totalUnitkerjaId, SUM(renterimaTotalTerima) AS totalTotalTerima, 
	SUM(renterimaJumlah) AS totalterima FROM rencana_penerimaan WHERE renterimaThanggarId ='%s' GROUP BY totalUnitkerjaId) AS total ON totalUnitkerjaId=unitkerjaId
	WHERE
	/*finansi_ref_rkakl_kode_penerimaan.`rkaklKodePenerimaanKode` <> 'NULL' AND*/
	unit_kerja_ref.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'%s') 
	OR 
	unit_kerja_ref.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')	
GROUP BY kode_unit,sd, kode 
ORDER BY unitkerjaId,sd,kodeterimaKode 
%s
";

$sql['get_total_pendapatan_penerimaan']="
SELECT 
	SUM(IF(sd = 1 ,IF(kode IS NULL ,0,target_pnbp),0 )) AS t_target_pnbp,	
	SUM(IF(sd = 2 ,IF(kode IS NULL ,0,target_pnbp),0 )) AS t_target_non_pnbp,
	SUM(IF(sd = 1 ,IF(kode IS NULL ,0,total_real),0 )) AS t_realisasi_pnbp,	
	SUM(IF(sd = 2 ,IF(kode IS NULL ,0,total_real),0 )) AS t_realisasi_non_pnbp
FROM (
	SELECT
		unitkerjaId AS id_unit,
		unitkerjaKode AS kode_unit,
		unitkerjaNama AS nama_unit,
		finansi_ref_rkakl_kode_penerimaan.`rkaklKodePenerimaanKode` AS kode,
		finansi_ref_rkakl_kode_penerimaan.`rkaklKodePenerimaanNama` AS nama,
		SUM(IF (renterimaTotalTerima IS NULL,0,renterimaTotalTerima)) 	AS target_pnbp,
		SUM(realterimaJmlJan) + SUM(realterimaJmlFeb) + SUM(realterimaJmlMar) +
		SUM(realterimaJmlApr) + SUM(realterimaJmlMei) + SUM(realterimaJmlJun) +
		SUM(realterimaJmlJul) + SUM(realterimaJmlAgt) + SUM(realterimaJmlSep) +
		SUM(realterimaJmlOkt) + SUM(realterimaJmlNov) + SUM(realterimaJmlDes) AS total_real,
		SUM(realterimaTotalTerima) AS total_realisasi,
		IF(kode_penerimaan_ref.`kodeterimaSumberdanaId` = 4,1,2) AS sd,/* 1 = PNBP 2 = selain PNBP */
		CONCAT(unitkerjaId,IF(kode_penerimaan_ref.`kodeterimaSumberdanaId` = 4,1,2)) AS unit_sd_id
	FROM rencana_penerimaan
	LEFT JOIN unit_kerja_ref ON renterimaUnitkerjaId = unitkerjaId AND renterimaThanggarId ='%s'
	LEFT JOIN realisasi_penerimaan ON realrenterimaId = renterimaId
	LEFT JOIN kode_penerimaan_ref ON renterimaKodeterimaId = kodeterimaId
	LEFT JOIN finansi_ref_rkakl_kode_penerimaan 
		ON finansi_ref_rkakl_kode_penerimaan.`rkaklKodePenerimaanId` = kode_penerimaan_ref.`kodeterimaRKAKLKodePenerimaanId`
	LEFT JOIN finansi_ref_sumber_dana 
		ON finansi_ref_sumber_dana.`sumberdanaId` = kode_penerimaan_ref.`kodeterimaSumberdanaId`
	LEFT JOIN (SELECT renterimaUnitkerjaId AS totalUnitkerjaId, SUM(renterimaTotalTerima) AS totalTotalTerima, 
	SUM(renterimaJumlah) AS totalterima FROM rencana_penerimaan WHERE renterimaThanggarId ='%s' GROUP BY totalUnitkerjaId) AS total ON totalUnitkerjaId=unitkerjaId
	WHERE
	unit_kerja_ref.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'%s') 
	OR 
	unit_kerja_ref.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')	
GROUP BY kode_unit,sd, kode 
ORDER BY unitkerjaId,sd,kodeterimaKode ) g
";
$sql['get_nilai_proyeksi']="
SELECT 
	settingValue AS nilai
FROM 
	setting 
WHERE 
	settingName = 'nilai_proyeksi'
LIMIT 0,1	
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

