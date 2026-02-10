<?php

$sql['get_data']="
SELECT 
SQL_CALC_FOUND_ROWS
uk.`unitkerjaId` AS unit_kerja_id,
uk.`unitkerjaKode` AS unit_kerja_kode,
uk.`unitkerjaNama` AS unit_kerja_nama,
CONCAT(uk.`unitkerjaId`,komp.`kompIsLangsung`) AS biaya_id,
komp.`kompIsLangsung` AS biaya_langsung,
CONCAT(uk.`unitkerjaId`,komp.`kompIsLangsung`,pb.`paguBasId`) AS mak_id,
pb.`paguBasKode` AS mak_kode,
pb.`paguBasKeterangan` AS mak_nama,
rpeng.`rncnpengeluaranKomponenNama` AS komp_nama,
rpeng.`rncnpengeluaranSatuanAprove` AS komp_volume,
rpeng.`rncnpengeluaranKomponenTotalAprove` AS komp_jumlah,
k.`kegThanggarId` as tahun_anggaran_id
FROM 
rencana_pengeluaran rpeng
LEFT JOIN komponen komp
ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN finansi_ref_pagu_bas pb
ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
/*LEFT JOIN finansi_ref_pagu_bas pb_p
ON pb_p.`paguBasId` = pb.`paguBasParentId`*/
LEFT JOIN kegiatan_detail kd
ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k
ON k.`kegId` = kd.`kegdetKegId`
LEFT JOIN unit_kerja_ref uk
ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
WHERE
k.`kegThanggarId` = '%s'
AND rpeng.`rncnpengeluaranIsAprove` = 'Ya' 
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
ORDER BY unit_kerja_kode, biaya_langsung,mak_nama,komp_nama		
%s
";

$sql['get_count_data']="
SELECT FOUND_ROWS() AS total
";

$sql['get_total_per_mak']="
SELECT 
CONCAT(uk.`unitkerjaId`,komp.`kompIsLangsung`,pb.`paguBasId`) AS mak_id,
SUM(rpeng.`rncnpengeluaranKomponenTotalAprove` ) AS total
FROM 
rencana_pengeluaran rpeng
LEFT JOIN komponen komp
ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN finansi_ref_pagu_bas pb
ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN finansi_ref_pagu_bas pb_p
ON pb_p.`paguBasId` = pb.`paguBasParentId`
LEFT JOIN kegiatan_detail kd
ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k
ON k.`kegId` = kd.`kegdetKegId`
LEFT JOIN unit_kerja_ref uk
ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
WHERE
k.`kegThanggarId` = '%s'
AND 
rpeng.`rncnpengeluaranIsAprove` = 'Ya' 
AND 
CONCAT(uk.`unitkerjaId`,komp.`kompIsLangsung`,pb.`paguBasId`)  = '%s'
AND uk.`unitkerjaId` = '%s'
GROUP BY  mak_id
";
$sql['get_total_per_biaya']="
SELECT 
SUM(rpeng.`rncnpengeluaranKomponenTotalAprove` ) AS total
FROM 
rencana_pengeluaran rpeng
LEFT JOIN komponen komp
ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN finansi_ref_pagu_bas pb
ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN finansi_ref_pagu_bas pb_p
ON pb_p.`paguBasId` = pb.`paguBasParentId`
LEFT JOIN kegiatan_detail kd
ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k
ON k.`kegId` = kd.`kegdetKegId`
LEFT JOIN unit_kerja_ref uk
ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
WHERE
k.`kegThanggarId` = '%s'
AND 
rpeng.`rncnpengeluaranIsAprove` = 'Ya' 
AND 
komp.`kompIsLangsung`='%s'
AND 
uk.`unitkerjaId` = '%s'
";
$sql['get_total_per_unit']="
SELECT 
SUM(rpeng.`rncnpengeluaranKomponenTotalAprove` ) AS total
FROM 
rencana_pengeluaran rpeng
LEFT JOIN komponen komp
ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN finansi_ref_pagu_bas pb
ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN finansi_ref_pagu_bas pb_p
ON pb_p.`paguBasId` = pb.`paguBasParentId`
LEFT JOIN kegiatan_detail kd
ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k
ON k.`kegId` = kd.`kegdetKegId`
LEFT JOIN unit_kerja_ref uk
ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
WHERE
k.`kegThanggarId` = '%s'
AND 
rpeng.`rncnpengeluaranIsAprove` = 'Ya' 
AND 
uk.`unitkerjaId` = '%s'
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
