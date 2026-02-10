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
CONCAT(uk.`unitkerjaId`,pr.`programId`) AS kegiatan_id,
pr.`programNomor` AS kegiatan_kode,
pr.`programNama` AS kegiatan_nama,
CONCAT(uk.`unitkerjaId`,pr.`programId`,sp.`subprogId`) AS output_id,
sp.`subprogNomor` AS output_kode,
sp.`subprogNama` AS output_nama,
CONCAT(uk.`unitkerjaId`,pr.`programId`,sp.`subprogId`,kr.`kegrefId`) AS komponen_id,
kr.`kegrefNomor` AS komponen_kode,
kr.`kegrefNama` AS komponen_nama,
SUM(rpeng.`rncnpengeluaranKomponenTotalAprove`) AS total_pagu_def,
SUM(0) AS total_realisasi
FROM 
rencana_pengeluaran rpeng
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN komponen komp ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan_ref kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
LEFT JOIN kegiatan k ON k.`kegId` = kd.`kegdetKegId`
LEFT JOIN sub_program sp ON sp.`subprogId` = kr.`kegrefSubprogId`
LEFT JOIN program_ref pr ON pr.`programId` = k.`kegProgramId`
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
GROUP BY komponen_id
ORDER BY unit_kerja_kode,kegiatan_kode,output_kode,komponen_kode
%s
";


$sql['get_total_per_output']="
SELECT 
CONCAT(uk.`unitkerjaId`,pr.`programId`,sp.`subprogId`) AS output_id,
SUM(rpeng.`rncnpengeluaranKomponenTotalAprove` ) AS total
FROM 
rencana_pengeluaran rpeng
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN komponen komp ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan_ref kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
LEFT JOIN kegiatan k ON k.`kegId` = kd.`kegdetKegId`
LEFT JOIN sub_program sp ON sp.`subprogId` = kr.`kegrefSubprogId`
LEFT JOIN program_ref pr ON pr.`programId` = k.`kegProgramId`
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
WHERE
k.`kegThanggarId` = '%s'
   AND
   rpeng.`rncnpengeluaranIsAprove` = 'Ya'	
AND 
CONCAT(uk.`unitkerjaId`,pr.`programId`,sp.`subprogId`)  = '%s'
AND uk.`unitkerjaId` = '%s'
GROUP BY output_id
";

$sql['get_total_per_kegiatan']="
SELECT 
CONCAT(uk.`unitkerjaId`,pr.`programId`) AS kegiatan_id,
SUM(rpeng.`rncnpengeluaranKomponenTotalAprove` ) AS total
FROM 
rencana_pengeluaran rpeng
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN komponen komp ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan_ref kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
LEFT JOIN kegiatan k ON k.`kegId` = kd.`kegdetKegId`
LEFT JOIN sub_program sp ON sp.`subprogId` = kr.`kegrefSubprogId`
LEFT JOIN program_ref pr ON pr.`programId` = k.`kegProgramId`
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
WHERE
k.`kegThanggarId` = '%s'
   AND
   rpeng.`rncnpengeluaranIsAprove` = 'Ya'	
AND 
CONCAT(uk.`unitkerjaId`,pr.`programId`)  = '%s'
AND
uk.`unitkerjaId` = '%s'
GROUP BY kegiatan_id
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
LEFT JOIN program_ref pr ON pr.`programId` = k.`kegProgramId`
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
LEFT JOIN program_ref pr ON pr.`programId` = k.`kegProgramId`
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