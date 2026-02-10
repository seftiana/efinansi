<?php

$sql['get_data_laporan'] ="
SELECT 
pb.`paguBasKode` AS kode,
pb.`paguBasKeterangan` AS  nama,
SUM(rpen.`renterimaTotalTerima`) AS target
FROM rencana_penerimaan rpen
LEFT JOIN  kode_penerimaan_ref kp
ON kp.`kodeterimaId` = rpen.`renterimaKodeterimaId`
LEFT JOIN finansi_ref_pagu_bas pb
ON pb.`paguBasId` = kp.`kodeterimaPaguBasId`
LEFT JOIN unit_kerja_ref uk
ON uk.`unitkerjaId` = rpen.`renterimaUnitkerjaId`
WHERE
 rpen.`renterimaThanggarId` = '%s'
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
GROUP BY kode

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
