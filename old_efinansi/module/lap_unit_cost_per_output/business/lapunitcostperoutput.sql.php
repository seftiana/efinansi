<?php

$sql['get_count_data']="
SELECT FOUND_ROWS() AS total
";

$sql['get_data']="
SELECT 
SQL_CALC_FOUND_ROWS
k.`kegThanggarId` AS tahun_anggaran_id,
uk.`unitkerjaId` AS unit_kerja_id,
uk.`unitkerjaKode` AS unit_kerja_kode,
uk.`unitkerjaNama` AS unit_kerja_nama,
CONCAT(uk.`unitkerjaId`,sp.`subprogId`) AS output_id,
sp.`subprogNomor` AS output_kode,
sp.`subprogNama` AS output_nama,
CONCAT(uk.`unitkerjaId`,kr.`kegrefId`,komp.`kompIsLangsung`) AS langsung_id,
komp.`kompIsLangsung` AS langsung_kode,
CONCAT(uk.`unitkerjaId`,kr.`kegrefId`,komp.`kompIsLangsung`,pb.`paguBasId`) AS mak_id,
pb.`paguBasKode` AS mak_kode,
pb.`paguBasKeterangan` AS mak_nama,
SUM(rpeng.`rncnpengeluaranSatuanAprove`) AS volume,
SUM(rpeng.`rncnpengeluaranKomponenTotalAprove`) AS nominal
FROM 
rencana_pengeluaran rpeng
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN komponen komp ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan_ref kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
LEFT JOIN kegiatan k ON k.`kegId` = kd.`kegdetKegId`
LEFT JOIN sub_program sp ON sp.`subprogId` = kr.`kegrefSubprogId`
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
GROUP BY mak_id
ORDER BY unit_kerja_kode,output_kode,langsung_kode,mak_kode
%s
";


$sql['get_total_per_output']="
SELECT 
CONCAT(uk.`unitkerjaId`,sp.`subprogId`) AS output_id,
SUM(rpeng.`rncnpengeluaranKomponenTotalAprove` ) AS total
FROM 
rencana_pengeluaran rpeng
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN komponen komp ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan_ref kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
LEFT JOIN kegiatan k ON k.`kegId` = kd.`kegdetKegId`
LEFT JOIN sub_program sp ON sp.`subprogId` = kr.`kegrefSubprogId`
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
WHERE
k.`kegThanggarId` = '%s'
   AND
   rpeng.`rncnpengeluaranIsAprove` = 'Ya'	
AND 
CONCAT(uk.`unitkerjaId`,sp.`subprogId`)  = '%s'
AND uk.`unitkerjaId` = '%s'
GROUP BY  output_id
";

$sql['get_total_per_biaya']="
SELECT 
CONCAT(uk.`unitkerjaId`,kr.`kegrefId`,komp.`kompIsLangsung`) AS langsung_id,
SUM(rpeng.`rncnpengeluaranKomponenTotalAprove` ) AS total
FROM 
rencana_pengeluaran rpeng
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN komponen komp ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan_ref kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
LEFT JOIN kegiatan k ON k.`kegId` = kd.`kegdetKegId`
LEFT JOIN sub_program sp ON sp.`subprogId` = kr.`kegrefSubprogId`
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
WHERE
k.`kegThanggarId` = '%s'
   AND
   rpeng.`rncnpengeluaranIsAprove` = 'Ya'	
AND 
CONCAT(uk.`unitkerjaId`,kr.`kegrefId`,komp.`kompIsLangsung`) = '%s'
AND
uk.`unitkerjaId` = '%s'
GROUP BY langsung_id
";

$sql['get_total_per_unit']="
SELECT 
SUM(rpeng.`rncnpengeluaranKomponenTotalAprove` ) AS total
FROM 
rencana_pengeluaran rpeng
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN komponen komp ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan_ref kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
LEFT JOIN kegiatan k ON k.`kegId` = kd.`kegdetKegId`
LEFT JOIN sub_program sp ON sp.`subprogId` = kr.`kegrefSubprogId`
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
WHERE
	k.`kegThanggarId` = '%s'
   AND
   rpeng.`rncnpengeluaranIsAprove` = 'Ya'	
	AND uk.`unitkerjaId` = '%s'
";

$sql['get_total_all']="
SELECT 
SUM(rpeng.`rncnpengeluaranKomponenTotalAprove` ) AS total
FROM 
rencana_pengeluaran rpeng
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN komponen komp ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan_ref kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
LEFT JOIN kegiatan k ON k.`kegId` = kd.`kegdetKegId`
LEFT JOIN sub_program sp ON sp.`subprogId` = kr.`kegrefSubprogId`
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

?>