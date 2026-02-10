<?php


$sql['get_count_data_laporan'] = "
	SELECT FOUND_ROWS() AS total
";

$sql['get_data_laporan']="
SELECT 
	SQL_CALC_FOUND_ROWS
	sp.`subprogId` AS output_id,
	sp.`subprogNomor` AS output_kode,
	sp.`subprogNama` AS output_nama,
	CONCAT(sp.`subprogId`,kr.`kegrefId`) AS komponen_id,
	kr.`kegrefNomor` AS komponen_kode,
	kr.`kegrefNama` AS komponen_nama,
	CONCAT(sp.`subprogId`,kr.`kegrefId`,rpeng.`rncnpengeluaranId`) AS db_id,
	pb.`paguBasKode` AS mak_kode,
	pb.`paguBasKeterangan` AS mak_nama,
	rpeng.`rncnpengeluaranKomponenKode` AS db_kode,
	rpeng.`rncnpengeluaranKomponenNama` AS db_nama,
	SUM(rpeng.`rncnpengeluaranSatuanAprove`) AS db_volume,
	SUM(rpeng.`rncnpengeluaranKomponenTotalAprove`) AS db_nominal_total,
	GROUP_CONCAT(DISTINCT sd.`sumberdanaNama`) AS sumber_dana_nama
FROM 
	rencana_pengeluaran rpeng
	LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
	LEFT JOIN kegiatan k ON k.`kegId` = kd.`kegdetKegId`
	LEFT JOIN kegiatan_ref kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
	LEFT JOIN sub_program sp ON sp.`subprogId` = kr.`kegrefSubprogId`
	LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
	LEFT JOIN komponen komp ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
	LEFT JOIN finansi_ref_sumber_dana sd ON sd.`sumberdanaId` = rpeng.`rncnpengeluaranSumberDanaId`
	LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
WHERE
  k.`kegThanggarId` = '%s'
  AND
  rpeng.`rncnpengeluaranIsAprove` = 'Ya'
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
	AND
	komp.kompIsPengadaan = 'Y'
GROUP BY output_id,komponen_id,rpeng.`rncnpengeluaranMakId`		
ORDER BY output_id,komponen_id,mak_kode
%s
";


$sql['get_total_per_sk']="
SELECT 
CONCAT(sp.`subprogId`,kr.`kegrefId`) AS komponen_id,
SUM(rpeng.`rncnpengeluaranKomponenTotalAprove`) AS nominal
FROM 
rencana_pengeluaran rpeng
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k ON k.`kegId` = kd.`kegdetKegId`
LEFT JOIN kegiatan_ref kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
LEFT JOIN sub_program sp ON sp.`subprogId` = kr.`kegrefSubprogId`
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN komponen komp ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN finansi_ref_sumber_dana sd ON sd.`sumberdanaId` = rpeng.`rncnpengeluaranSumberDanaId`
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
WHERE
	k.`kegThanggarId` = '%s'
	AND
    rpeng.`rncnpengeluaranIsAprove` = 'Ya'
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
	AND
	komp.kompIsPengadaan = 'Y'	
GROUP BY komponen_id
";

$sql['get_total_per_k']="
SELECT 
sp.`subprogId` AS output_id,
SUM(rpeng.`rncnpengeluaranKomponenTotalAprove`) AS nominal
FROM 
rencana_pengeluaran rpeng
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k ON k.`kegId` = kd.`kegdetKegId`
LEFT JOIN kegiatan_ref kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
LEFT JOIN sub_program sp ON sp.`subprogId` = kr.`kegrefSubprogId`
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN komponen komp ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN finansi_ref_sumber_dana sd ON sd.`sumberdanaId` = rpeng.`rncnpengeluaranSumberDanaId`
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
WHERE
	k.`kegThanggarId` = '%s'
	AND
    rpeng.`rncnpengeluaranIsAprove` = 'Ya'
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
	AND
	komp.kompIsPengadaan = 'Y'	
GROUP BY output_id
";


$sql['get_total_all']="
SELECT 
SUM(rpeng.`rncnpengeluaranKomponenTotalAprove`) AS nominal
FROM 
rencana_pengeluaran rpeng
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k ON k.`kegId` = kd.`kegdetKegId`
LEFT JOIN kegiatan_ref kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
LEFT JOIN sub_program sp ON sp.`subprogId` = kr.`kegrefSubprogId`
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN komponen komp ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN finansi_ref_sumber_dana sd ON sd.`sumberdanaId` = rpeng.`rncnpengeluaranSumberDanaId`
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
WHERE
	k.`kegThanggarId` = '%s'
	AND
    rpeng.`rncnpengeluaranIsAprove` = 'Ya'
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
	AND
	komp.kompIsPengadaan = 'Y'	
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


?>