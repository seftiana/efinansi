<?php

$sql['get_count']="
SELECT FOUND_ROWS() AS total
";
$sql['get_data']="
SELECT
SQL_CALC_FOUND_ROWS
/*CASE WHEN g.unitkerjaNama IS NOT NULL THEN CONCAT(g.unitkerjaNama,'-',f.unitkerjaNama)
WHEN g.unitkerjaNama IS NULL THEN f.unitkerjaNama END*/ uk.unitkerjaNama as unitName,
/*CONCAT(CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.0.00.00')*/programNomor AS kodeProg,
ifnull(/*CONCAT(
CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.','',d.subprogJeniskegId,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
	WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.00')*/subprogNomor,'') AS kodeKegiatan,
ifnull(/*CONCAT(
CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.','',d.subprogJeniskegId,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
	WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.',CASE WHEN LENGTH(kegrefNomor) = 1 THEN CONCAT('0',kegrefNomor)
WHEN LENGTH(kegrefNomor) = 2 THEN kegrefNomor END)*/kegrefNomor,'') AS kodeSubKegiatan,
programNama AS namaProgram,
kegdetId AS kegiatandetail_id,
kegdetDeskripsi as deskripsi,
ifnull(subprogNama,'') AS namaKegiatan,
ifnull(kegrefNama,'') AS namaSubKegiatan,
ifnull(h.nominalSetuju,0) AS nominalSetuju

FROM kegiatan_detail b
LEFT JOIN kegiatan a ON b.kegdetKegId = a.kegId
LEFT JOIN kegiatan_ref c ON b.kegdetKegrefId = c.kegrefId
LEFT JOIN sub_program d ON c.kegrefSubprogId = d.subprogId
LEFT JOIN program_ref e ON d.subprogProgramId = e.programId
LEFT JOIN unit_kerja_ref uk ON a.kegUnitkerjaId = uk.unitkerjaId
/*LEFT JOIN unit_kerja_ref g ON f.unitkerjaParentId = g.unitkerjaId*/
LEFT JOIN (
	SELECT
	rpeng.rncnpengeluaranKegdetId,
	SUM(rpeng.rncnpengeluaranKomponenNominalAprove * rpeng.rncnpengeluaranSatuanAprove * 
	(IF(komp.kompFormulaHasil = '0',1,IFNULL(komp.kompFormulaHasil, 1))))AS nominalSetuju
	FROM rencana_pengeluaran rpeng
	LEFT JOIN komponen komp ON komp.kompKode = rpeng.rncnpengeluaranKomponenKode
	GROUP BY rpeng.rncnpengeluaranKegdetId
) h ON h.rncnpengeluaranKegdetId = b.kegdetId
LEFT JOIN jenis_kegiatan_ref i ON d.subprogJeniskegId = i.jeniskegId

WHERE a.kegThanggarId = %s
AND (
    uk.unitkerjaKodeSistem LIKE CONCAT(
      (SELECT 
        unitkerjaKodeSistem 
      FROM
        unit_kerja_ref 
      WHERE unitkerjaId = '%s'),
      '.',
      '%s'
    ) 
    OR uk.unitkerjaKodeSistem LIKE CONCAT(
      (SELECT 
        unitkerjaKodeSistem 
      FROM
        unit_kerja_ref 
      WHERE unitkerjaId = '%s')
    )
  ) 
AND e.programId LIKE %s AND d.subprogId LIKE %s AND c.kegrefId LIKE %s
AND kegdetIsAprove = 'Ya'
AND kegdetId NOT IN(SELECT pengrealKegdetId FROM pengajuan_realisasi)
AND (programNomor = '%s' OR
	  subprogNomor = '%s' OR
	  kegrefNomor = '%s' OR
	  programNama LIKE '%s' OR
	  subprogNama LIKE '%s' OR
	  kegrefNama LIKE '%s'
	  )
ORDER BY uk.unitKerjaKode, kodeProg, kodeKegiatan
LIMIT %s, %s
";


/*
$sql['get_data']="
SELECT
CASE WHEN g.unitkerjaNama IS NOT NULL THEN CONCAT(g.unitkerjaNama,'-',f.unitkerjaNama)
WHEN g.unitkerjaNama IS NULL THEN f.unitkerjaNama END as unitName,
ifnull(CONCAT(
CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.','',d.subprogJeniskegId,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
	WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.00'),'') AS kodeKegiatan,
ifnull(CONCAT(
CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.','',d.subprogJeniskegId,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
	WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.',CASE WHEN LENGTH(kegrefNomor) = 1 THEN CONCAT('0',kegrefNomor)
WHEN LENGTH(kegrefNomor) = 2 THEN kegrefNomor END),'') AS kodeSubKegiatan,
CONCAT(ifnull(subprogNama,''),' (',jeniskegNama,')') AS namaKegiatan,
ifnull(kegrefNama,'') AS namaSubKegiatan,
h.rncnpengeluaranKomponenKode AS kodeKomponen,
h.rncnpengeluaranKomponenNama AS namaKomponen,
ifnull(h.rncnpengeluaranKomponenNominalAprove*h.rncnpengeluaranSatuanAprove,0) AS nominalSetuju

FROM kegiatan_detail b
LEFT JOIN kegiatan a ON b.kegdetKegId = a.kegId
LEFT JOIN kegiatan_ref c ON b.kegdetKegrefId = c.kegrefId
LEFT JOIN sub_program d ON c.kegrefSubprogId = d.subprogId
LEFT JOIN program_ref e ON d.subprogProgramId = e.programId
LEFT JOIN unit_kerja_ref f ON kegUnitkerjaId = unitkerjaId
LEFT JOIN unit_kerja_ref g ON f.unitkerjaParentId = g.unitkerjaId
LEFT JOIN rencana_pengeluaran h ON h.rncnpengeluaranKegdetId = b.kegdetId
LEFT JOIN jenis_kegiatan_ref i ON d.subprogJeniskegId = i.jeniskegId

WHERE a.kegThanggarId = %s
AND ((g.unitkerjaId LIKE  %s OR f.unitkerjaId LIKE %s)
OR (g.unitkerjaParentId LIKE %s  OR f.unitkerjaParentId LIKE %s))
AND e.programId LIKE %s AND d.subprogId LIKE %s AND c.kegrefId LIKE %s
AND kegdetIsAprove = 'Ya'
ORDER BY kodeKegiatan
LIMIT %s, %s
";
*/

$sql['get_count_old']="
SELECT
 COUNT(a.kegId) AS total
FROM kegiatan_detail b
LEFT JOIN kegiatan a ON b.kegdetKegId = a.kegId
LEFT JOIN kegiatan_ref c ON b.kegdetKegrefId = c.kegrefId
LEFT JOIN sub_program d ON c.kegrefSubprogId = d.subprogId
LEFT JOIN program_ref e ON d.subprogProgramId = e.programId
LEFT JOIN unit_kerja_ref f ON kegUnitkerjaId = unitkerjaId
LEFT JOIN unit_kerja_ref g ON f.unitkerjaParentId = g.unitkerjaId
LEFT JOIN (
	SELECT
	rncnpengeluaranKegdetId,

	sum(rncnpengeluaranKomponenNominalAprove*rncnpengeluaranSatuanAprove*IFNULL(kompFormulaHasil,1)) AS nominalSetuju
	FROM rencana_pengeluaran
	LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
	GROUP BY rncnpengeluaranKegdetId
) h ON h.rncnpengeluaranKegdetId = b.kegdetId
LEFT JOIN jenis_kegiatan_ref i ON d.subprogJeniskegId = i.jeniskegId
WHERE a.kegThanggarId = %s
AND ((g.unitkerjaId LIKE  %s OR f.unitkerjaId LIKE %s)
OR (g.unitkerjaParentId LIKE %s  OR f.unitkerjaParentId LIKE %s))
AND e.programId LIKE %s AND d.subprogId LIKE %s AND c.kegrefId LIKE %s
AND kegdetIsAprove = 'Ya'
AND kegdetId NOT IN(SELECT pengrealKegdetId FROM pengajuan_realisasi)
AND (programNomor = '%s' OR
	  subprogNomor = '%s' OR
	  kegrefNomor = '%s' OR
	  programNama LIKE '%s' OR
	  subprogNama LIKE '%s' OR
	  kegrefNama LIKE '%s'
	  )
LIMIT 1
";


$sql['get_resume_unit_kerja']="
SELECT
f.unitkerjaId,
CASE WHEN g.unitkerjaNama IS NOT NULL THEN CONCAT(g.unitkerjaNama,'-',f.unitkerjaNama)
WHEN g.unitkerjaNama IS NULL THEN f.unitkerjaNama END as unitName,
CONCAT(CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.00.00.00') AS kodeProg,
ifnull(CONCAT(
CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.','0',d.subprogJeniskegId,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
	WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.00'),'') AS kodeKegiatan,
ifnull(CONCAT(
CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.','0',d.subprogJeniskegId,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
	WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.',CASE WHEN LENGTH(kegrefNomor) = 1 THEN CONCAT('0',kegrefNomor)
WHEN LENGTH(kegrefNomor) = 2 THEN kegrefNomor END),'') AS kodeSubKegiatan,
programNama AS namaProgram,
CONCAT(ifnull(subprogNama,''),' (',jeniskegNama,')') AS namaKegiatan,
ifnull(kegrefNama,'') AS namaSubKegiatan,
SUM(ifnull(h.nominalUsulan,0)) AS nominalUsulan,
SUM(ifnull(h.nominalSetuju,0)) AS nominalSetuju
FROM kegiatan_detail b
LEFT JOIN kegiatan a ON b.kegdetKegId = a.kegId
LEFT JOIN kegiatan_ref c ON b.kegdetKegrefId = c.kegrefId
LEFT JOIN sub_program d ON c.kegrefSubprogId = d.subprogId
LEFT JOIN program_ref e ON d.subprogProgramId = e.programId
LEFT JOIN unit_kerja_ref f ON kegUnitkerjaId = unitkerjaId
LEFT JOIN unit_kerja_ref g ON f.unitkerjaParentId = g.unitkerjaId
LEFT JOIN (
	SELECT
	rncnpengeluaranKegdetId,
	sum(rncnpengeluaranKomponenNominal*rncnpengeluaranSatuan*IFNULL(kompFormulaHasil,1)) AS nominalUsulan,
	sum(rncnpengeluaranKomponenNominalAprove*rncnpengeluaranSatuanAprove*IFNULL(kompFormulaHasil,1)) AS nominalSetuju
	FROM rencana_pengeluaran
	LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
	GROUP BY rncnpengeluaranKegdetId
) h ON h.rncnpengeluaranKegdetId = b.kegdetId
LEFT JOIN jenis_kegiatan_ref i ON d.subprogJeniskegId = i.jeniskegId

WHERE a.kegThanggarId = %s
AND ((g.unitkerjaId LIKE  %s OR f.unitkerjaId LIKE %s)
OR (g.unitkerjaParentId LIKE %s  OR f.unitkerjaParentId LIKE %s))
AND e.programId LIKE %s AND d.subprogJeniskegId LIKE %s

GROUP BY f.unitkerjaId

ORDER BY unitName, kodeProg, kodeKegiatan


";

$sql['get_resume_program']="
SELECT
f.unitkerjaId,
CASE WHEN g.unitkerjaNama IS NOT NULL THEN CONCAT(g.unitkerjaNama,'-',f.unitkerjaNama)
WHEN g.unitkerjaNama IS NULL THEN f.unitkerjaNama END as unitName,
CONCAT(CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.00.00.00') AS kodeProg,
ifnull(CONCAT(
CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.','0',d.subprogJeniskegId,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
	WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.00'),'') AS kodeKegiatan,
ifnull(CONCAT(
CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
	WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.',CASE WHEN LENGTH(kegrefNomor) = 1 THEN CONCAT('0',kegrefNomor)
WHEN LENGTH(kegrefNomor) = 2 THEN kegrefNomor END),'') AS kodeSubKegiatan,
programNama AS namaProgram,
CONCAT(ifnull(subprogNama,''),' (',jeniskegNama,')') AS namaKegiatan,
ifnull(kegrefNama,'') AS namaSubKegiatan,
SUM(ifnull(h.nominalUsulan,0)) AS nominalUsulan,
SUM(ifnull(h.nominalSetuju,0)) AS nominalSetuju
FROM kegiatan_detail b
LEFT JOIN kegiatan a ON b.kegdetKegId = a.kegId
LEFT JOIN kegiatan_ref c ON b.kegdetKegrefId = c.kegrefId
LEFT JOIN sub_program d ON c.kegrefSubprogId = d.subprogId
LEFT JOIN program_ref e ON d.subprogProgramId = e.programId
LEFT JOIN unit_kerja_ref f ON kegUnitkerjaId = unitkerjaId
LEFT JOIN unit_kerja_ref g ON f.unitkerjaParentId = g.unitkerjaId
LEFT JOIN (
	SELECT
	rncnpengeluaranKegdetId,
	sum(rncnpengeluaranKomponenNominal*rncnpengeluaranSatuan*IFNULL(kompFormulaHasil,1)) AS nominalUsulan,
	sum(rncnpengeluaranKomponenNominalAprove*rncnpengeluaranSatuanAprove*IFNULL(kompFormulaHasil,1)) AS nominalSetuju
	LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
	FROM rencana_pengeluaran
	GROUP BY rncnpengeluaranKegdetId
) h ON h.rncnpengeluaranKegdetId = b.kegdetId
LEFT JOIN jenis_kegiatan_ref i ON d.subprogJeniskegId = i.jeniskegId

WHERE a.kegThanggarId = %s
AND ((g.unitkerjaId LIKE  %s OR f.unitkerjaId LIKE %s)
OR (g.unitkerjaParentId LIKE %s  OR f.unitkerjaParentId LIKE %s))
AND e.programId LIKE %s AND d.subprogJeniskegId LIKE %s

GROUP BY f.unitkerjaId , kodeProg

ORDER BY f.unitkerjaId , kodeProg


";

$sql['get_resume_kegiatan']="
SELECT
f.unitkerjaId,
CASE WHEN g.unitkerjaNama IS NOT NULL THEN CONCAT(g.unitkerjaNama,'-',f.unitkerjaNama)
WHEN g.unitkerjaNama IS NULL THEN f.unitkerjaNama END as unitName,
CONCAT(CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.00.00.00') AS kodeProg,
ifnull(CONCAT(
CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.','0',d.subprogJeniskegId,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
	WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.00'),'') AS kodeKegiatan,
ifnull(CONCAT(
CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
	WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.',CASE WHEN LENGTH(kegrefNomor) = 1 THEN CONCAT('0',kegrefNomor)
WHEN LENGTH(kegrefNomor) = 2 THEN kegrefNomor END),'') AS kodeSubKegiatan,
programNama AS namaProgram,
CONCAT(ifnull(subprogNama,''),' (',jeniskegNama,')') AS namaKegiatan,
ifnull(kegrefNama,'') AS namaSubKegiatan,
SUM(ifnull(h.nominalUsulan,0)) AS nominalUsulan,
SUM(ifnull(h.nominalSetuju,0)) AS nominalSetuju
FROM kegiatan_detail b
LEFT JOIN kegiatan a ON b.kegdetKegId = a.kegId
LEFT JOIN kegiatan_ref c ON b.kegdetKegrefId = c.kegrefId
LEFT JOIN sub_program d ON c.kegrefSubprogId = d.subprogId
LEFT JOIN program_ref e ON d.subprogProgramId = e.programId
LEFT JOIN unit_kerja_ref f ON kegUnitkerjaId = unitkerjaId
LEFT JOIN unit_kerja_ref g ON f.unitkerjaParentId = g.unitkerjaId
LEFT JOIN (
	SELECT
	rncnpengeluaranKegdetId,
	sum(rncnpengeluaranKomponenNominal*rncnpengeluaranSatuan*IFNULL(kompFormulaHasil,1)) AS nominalUsulan,
	sum(rncnpengeluaranKomponenNominalAprove*rncnpengeluaranSatuanAprove*IFNULL(kompFormulaHasil,1)) AS nominalSetuju
	FROM rencana_pengeluaran
	LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
	GROUP BY rncnpengeluaranKegdetId
) h ON h.rncnpengeluaranKegdetId = b.kegdetId
LEFT JOIN jenis_kegiatan_ref i ON d.subprogJeniskegId = i.jeniskegId

WHERE a.kegThanggarId = %s
AND ((g.unitkerjaId LIKE  %s OR f.unitkerjaId LIKE %s)
OR (g.unitkerjaParentId LIKE %s  OR f.unitkerjaParentId LIKE %s))
AND e.programId LIKE %s AND d.subprogJeniskegId LIKE %s

GROUP BY f.unitkerjaId , kodeProg , kodeKegiatan

ORDER BY f.unitkerjaId , kodeProg , kodeKegiatan


";


//untuk popup
$sql['get_unit_kerja']=
   "SELECT
      unitkerjaId AS unitkerja_id,
      unitkerjaKode AS unitkerja_kode,
      unitkerjaNama AS unitkerja_nama, 
      unitkerjaParentId AS unitkerja_parentid 
	FROM
	  unit_kerja_ref
	WHERE
	(unit_kerja_ref.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'%s') OR 
	unit_kerja_ref.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)	
	AND
	  unitkerjaNama LIKE %s
	ORDER BY
	  unitkerjaKodeSistem ASC
	LIMIT %s, %s
   ";

$sql['get_count_unit_kerja']=
   "SELECT
      COUNT(unitkerjaId) AS total
	FROM
	  unit_kerja_ref
	WHERE
	(unit_kerja_ref.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'%s') OR 
	unit_kerja_ref.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)		AND
	  unitkerjaNama LIKE %s
	ORDER BY
	  unitkerjaKodeSistem ASC
	LIMIT 1
   ";

$sql['get_unit_kerja_id'] = "
   SELECT
      unitkerjaNama,
      unitkerjaNamaPimpinan
   FROM
      unit_kerja_ref
   WHERE
      unitkerjaId = '%s'
";

//===untuk combo box
$sql['get_data_ta'] =
   "SELECT
      thanggarId AS id,
	  thanggarNama AS name
	FROM
	  tahun_anggaran
	ORDER BY
	  thanggarNama ASC
   ";

$sql['get_ta_aktif']=
   "SELECT
      thanggarId AS id,
	  thanggarNama AS nama
	FROM
	  tahun_anggaran
	WHERE
	  thanggarIsAktif='Y'
	LIMIT 1
   ";
$sql['get_combo_jenis_kegiatan']="
	SELECT
		jeniskegId as id,
		jeniskegNama as name
	FROM
		jenis_kegiatan_ref
   WHERE jeniskegId != 3
	ORDER BY jeniskegId
";

$sql['get_count_data_realisasi'] = "
SELECT
    COUNT(DISTINCT k_det.kegdetId) AS total_data 
FROM
	pengajuan_realisasi AS pengreal
LEFT JOIN kegiatan_detail AS k_det
ON (k_det.kegdetId = pengreal.pengrealKegdetId) 
WHERE 
	pengreal.pengrealKegdetId = '%s';
";

/**
 * add 
 * @since 3 Januari 2012
 */
$sql['get_total_sub_unit_kerja']="
SELECT 
	count(unitkerjaId) as total
FROM unit_kerja_ref
WHERE unitkerjaParentId = %s
";

/**
 * end
 */

?>
