<?php

//===GET===
$sql['get_count_data'] = "
SELECT
	COUNT(*) as total
FROM kegiatan_detail b
	LEFT JOIN kegiatan a ON b.kegdetKegId = a.kegId
	LEFT JOIN kegiatan_ref c ON b.kegdetKegrefId = c.kegrefId
	LEFT JOIN sub_program d ON c.kegrefSubprogId = d.subprogId
	LEFT JOIN program_ref e ON d.subprogProgramId = e.programId
	LEFT JOIN unit_kerja_ref g ON kegUnitkerjaId = g.unitkerjaId
	/*LEFT JOIN unit_kerja_ref g ON f.unitkerjaParentId = g.unitkerjaId*/
	LEFT JOIN (
		SELECT
			rncnpengeluaranKegdetId,
			sum(rncnpengeluaranKomponenNominal*rncnpengeluaranSatuan*
						IF(kompFormulaHasil > 0,kompFormulaHasil,1)) AS nominalUsulan,
			sum(rncnpengeluaranKomponenNominalAprove*rncnpengeluaranSatuanAprove*
						IF(kompFormulaHasil > 0,kompFormulaHasil,1)) AS nominalSetuju,
			rncnpengeluaranIsAprove
		FROM rencana_pengeluaran
		LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
	GROUP BY rncnpengeluaranKegdetId
) h ON h.rncnpengeluaranKegdetId = b.kegdetId
	LEFT JOIN jenis_kegiatan_ref i ON d.subprogJeniskegId = i.jeniskegId
	WHERE a.kegThanggarId =%s  AND h.rncnpengeluaranIsAprove ='Ya'
      %s
		%s
		%s
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

$sql['get_data'] = "
SELECT
	/*CONCAT(CASE
      WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
		WHEN LENGTH(programNomor) = 2 THEN programNomor END, '.',

      CASE WHEN jeniskegNama='Rutin' THEN '1'
      WHEN jeniskegNama='Pengembangan' THEN '2' END,

      '.00.00')*/programNomor AS kodeProg,

	ifnull(/*CONCAT(CASE
      WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
		WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.',

      CASE WHEN jeniskegNama='Rutin' THEN '1'
      WHEN jeniskegNama='Pengembangan' THEN '2' END,'.',

      CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
		WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.00')*/subprogNomor,'') AS kodeKegiatan,

	ifnull(/*CONCAT(CASE
      WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
		WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.',

      CASE WHEN jeniskegNama='Rutin' THEN '1'
      WHEN jeniskegNama='Pengembangan' THEN '2' END,'.',

      CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
		WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.',

      CASE WHEN LENGTH(kegrefNomor) = 1 THEN CONCAT('0',kegrefNomor)
		WHEN LENGTH(kegrefNomor) = 2 THEN kegrefNomor END)*/kegrefNomor,'') AS kodeSubKegiatan,
   programId as program_id,
   subProgId as kegiatan_id,
	programNama AS namaProgram,
	CONCAT(ifnull(subprogNama,''),' (',IFNULL(jeniskegNama, '-'),')') AS namaKegiatan,
	ifnull(kegrefNama,'') AS namaSubKegiatan,
	/*CASE WHEN g.unitkerjaNama IS NOT NULL THEN CONCAT(g.unitkerjaNama,'-',f.unitkerjaNama)
		WHEN g.unitkerjaNama IS NULL THEN f.unitkerjaNama END*/
		 g.unitkerjaNama as unitName,
	IF(h.nominalUsulan > 0,h.nominalUsulan,0) AS nominalUsulan,
	IF(h.nominalSetuju > 0,h.nominalSetuju,0) AS nominalSetuju
FROM kegiatan_detail b
	LEFT JOIN kegiatan a ON b.kegdetKegId = a.kegId
	LEFT JOIN kegiatan_ref c ON b.kegdetKegrefId = c.kegrefId
	LEFT JOIN sub_program d ON c.kegrefSubprogId = d.subprogId
	LEFT JOIN program_ref e ON d.subprogProgramId = e.programId
	LEFT JOIN unit_kerja_ref g ON kegUnitkerjaId = g.unitkerjaId
	/*LEFT JOIN unit_kerja_ref g ON f.unitkerjaParentId = g.unitkerjaId*/
	LEFT JOIN (
		SELECT
			rncnpengeluaranKegdetId,
			sum(rncnpengeluaranKomponenNominal*rncnpengeluaranSatuan*
					IF(kompFormulaHasil > 0,kompFormulaHasil,1)) AS nominalUsulan,
			sum(rncnpengeluaranKomponenNominalAprove*rncnpengeluaranSatuanAprove*
					IF(kompFormulaHasil > 0,kompFormulaHasil,1)) AS nominalSetuju,
			rncnpengeluaranIsAprove
		FROM rencana_pengeluaran
		LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
	GROUP BY rncnpengeluaranKegdetId
) h ON h.rncnpengeluaranKegdetId = b.kegdetId
	LEFT JOIN jenis_kegiatan_ref i ON d.subprogJeniskegId = i.jeniskegId
	WHERE a.kegThanggarId=%s AND h.rncnpengeluaranIsAprove ='Ya'
		%s
		%s
      %s
ORDER BY kodeProg, jeniskegId, kodeKegiatan, kodeSubKegiatan, g.unitkerjaNama
LIMIT %s, %s
";
$sql['get_cetak_data'] = "
SELECT
	/*CONCAT(CASE
      WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
		WHEN LENGTH(programNomor) = 2 THEN programNomor END, '.',

      CASE WHEN jeniskegNama='Rutin' THEN '1'
      WHEN jeniskegNama='Pengembangan' THEN '2' END,

      '.00.00')*/programNomor AS kodeProg,

	ifnull(/*CONCAT(CASE
      WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
		WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.',

      CASE WHEN jeniskegNama='Rutin' THEN '1'
      WHEN jeniskegNama='Pengembangan' THEN '2' END,'.',

      CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
		WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.00')*/subprogNomor,'') AS kodeKegiatan,

	ifnull(/*CONCAT(CASE
      WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
		WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.',

      CASE WHEN jeniskegNama='Rutin' THEN '1'
      WHEN jeniskegNama='Pengembangan' THEN '2' END,'.',

      CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
		WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.',

      CASE WHEN LENGTH(kegrefNomor) = 1 THEN CONCAT('0',kegrefNomor)
		WHEN LENGTH(kegrefNomor) = 2 THEN kegrefNomor END)*/kegrefNomor,'') AS kodeSubKegiatan,
	programNama AS namaProgram,
	CONCAT(ifnull(subprogNama,''),' (',IFNULL(jeniskegNama, '-'),')') AS namaKegiatan,
	ifnull(kegrefNama,'') AS namaSubKegiatan,
	/*CASE WHEN g.unitkerjaNama IS NOT NULL THEN CONCAT(g.unitkerjaNama,'-',f.unitkerjaNama)
		WHEN g.unitkerjaNama IS NULL THEN f.unitkerjaNama END*/
		 g.unitkerjaNama as unitName,
	IF(h.nominalUsulan > 0,h.nominalUsulan,0) AS nominalUsulan,
	IF(h.nominalSetuju > 0,h.nominalSetuju,0) AS nominalSetuju,
   kegdetDeskripsi AS deskripsi
FROM kegiatan_detail b
	LEFT JOIN kegiatan a ON b.kegdetKegId = a.kegId
	LEFT JOIN kegiatan_ref c ON b.kegdetKegrefId = c.kegrefId
	LEFT JOIN sub_program d ON c.kegrefSubprogId = d.subprogId
	LEFT JOIN program_ref e ON d.subprogProgramId = e.programId
	LEFT JOIN unit_kerja_ref g ON kegUnitkerjaId = g.unitkerjaId
	/*LEFT JOIN unit_kerja_ref g ON f.unitkerjaParentId = g.unitkerjaId*/
	LEFT JOIN (
		SELECT
			rncnpengeluaranKegdetId,
			sum(rncnpengeluaranKomponenNominal*rncnpengeluaranSatuan*
					IF(kompFormulaHasil > 0,kompFormulaHasil,1)) AS nominalUsulan,
			sum(rncnpengeluaranKomponenNominalAprove*rncnpengeluaranSatuanAprove*
					IF(kompFormulaHasil > 0,kompFormulaHasil,1)) AS nominalSetuju
		FROM rencana_pengeluaran
		LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
	GROUP BY rncnpengeluaranKegdetId
) h ON h.rncnpengeluaranKegdetId = b.kegdetId
	LEFT JOIN jenis_kegiatan_ref i ON d.subprogJeniskegId = i.jeniskegId
	WHERE a.kegThanggarId=%s
		%s
		%s
		%s
ORDER BY kodeProg, jeniskegId, kodeKegiatan, kodeSubKegiatan,  g.unitkerjaNama
";
$sql['get_resume'] = "
SELECT
   programId as id,
	/*CONCAT(CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
			WHEN LENGTH(programNomor) = 2 THEN programNomor END, '.',
         CASE WHEN jeniskegNama='Rutin' THEN '1'
         WHEN jeniskegNama='Pengembangan' THEN '2' END,'.00.00')*/programNomor AS kode,
	programNama AS nama,
	IF(SUM(h.nominalUsulan) > 0,SUM(h.nominalUsulan),0) AS nominal_usulan,
	IF(SUM(h.nominalSetuju) > 0,SUM(h.nominalSetuju),0) AS nominal_setuju
FROM kegiatan_detail b
	LEFT JOIN kegiatan a ON b.kegdetKegId = a.kegId
	LEFT JOIN kegiatan_ref c ON b.kegdetKegrefId = c.kegrefId
	LEFT JOIN sub_program d ON c.kegrefSubprogId = d.subprogId
	LEFT JOIN program_ref e ON d.subprogProgramId = e.programId
	LEFT JOIN unit_kerja_ref g ON kegUnitkerjaId = g.unitkerjaId
	/*LEFT JOIN unit_kerja_ref g ON f.unitkerjaParentId = g.unitkerjaId*/
	LEFT JOIN (
		SELECT
			rncnpengeluaranKegdetId,
			sum(rncnpengeluaranKomponenNominal*rncnpengeluaranSatuan*
					IF(kompFormulaHasil > 0,kompFormulaHasil,1)) AS nominalUsulan,
			sum(rncnpengeluaranKomponenNominalAprove*rncnpengeluaranSatuanAprove*
					IF(kompFormulaHasil > 0,kompFormulaHasil,1)) AS nominalSetuju
		FROM rencana_pengeluaran
		LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
	GROUP BY rncnpengeluaranKegdetId
) h ON h.rncnpengeluaranKegdetId = b.kegdetId
	LEFT JOIN jenis_kegiatan_ref i ON d.subprogJeniskegId = i.jeniskegId
	WHERE a.kegThanggarId=%s
		%s
		%s
      %s
   GROUP BY programId, jeniskegId
   ORDER BY kode, jeniskegId
";
$sql['get_resume_kegiatan'] = "
SELECT
   programId AS id,
   programNomor AS kode,
   programNama AS namaKegiatan,
   IF(SUM(h.nominalUsulan) > 0,SUM(h.nominalUsulan),0) AS nominal_usulan,
   IF(SUM(h.nominalSetuju) > 0,SUM(h.nominalSetuju),0) AS nominal_setuju
FROM kegiatan_detail b
   LEFT JOIN kegiatan a ON b.kegdetKegId = a.kegId
   LEFT JOIN kegiatan_ref c ON b.kegdetKegrefId = c.kegrefId
   LEFT JOIN sub_program d ON c.kegrefSubprogId = d.subprogId
   LEFT JOIN program_ref e ON d.subprogProgramId = e.programId
   LEFT JOIN unit_kerja_ref g ON kegUnitkerjaId = g.unitkerjaId
   LEFT JOIN (
      SELECT
         rncnpengeluaranKegdetId,
         SUM(rncnpengeluaranKomponenNominal*rncnpengeluaranSatuan*
                  IF(kompFormulaHasil > 0,kompFormulaHasil,1)) AS nominalUsulan,
         SUM(rncnpengeluaranKomponenNominalAprove*rncnpengeluaranSatuanAprove*
                  IF(kompFormulaHasil > 0,kompFormulaHasil,1)) AS nominalSetuju
      FROM rencana_pengeluaran
      LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
   GROUP BY rncnpengeluaranKegdetId
) h ON h.rncnpengeluaranKegdetId = b.kegdetId
   LEFT JOIN jenis_kegiatan_ref i ON d.subprogJeniskegId = i.jeniskegId
	WHERE a.kegThanggarId=%s
		%s
		%s
      %s
   GROUP BY programId
ORDER BY kode, jeniskegId
";
//COMBO
$sql['get_combo_tahun_anggaran']="
	SELECT
		thanggarId as id,
		thanggarNama as name
	FROM
		tahun_anggaran
	ORDER BY thanggarNama
";
$sql['get_tahun_anggaran_aktif']="
	SELECT
		thanggarId as id,
		thanggarNama as name
	FROM
		tahun_anggaran
	WHERE
		thanggarIsAktif='Y'
";
$sql['get_combo_jenis_kegiatan']="
	SELECT
		jeniskegId as id,
		jeniskegNama as name
	FROM
		jenis_kegiatan_ref
	ORDER BY jeniskegId
";

$sql['get_tahun_anggaran_by_id']="
	SELECT
		thanggarId as id,
		thanggarNama as nama
	FROM
		tahun_anggaran
	WHERE thanggarId=%s
";

$sql['get_program_by_id'] =
   "SELECT
      programId as id,
	  programNomor as kode,
	  programNama as nama
   FROM
      program_ref
   WHERE
   programId=%s
";

   $sql['get_unitkerja_by_id'] = "
   SELECT
		(if(tempUnitId IS NULL,unitkerjaId,unitkerjaId)) AS `id`,
		(if(tempUnitKode IS NULL,unitkerjaKode,unitkerjaKode)) AS `kode`,
		(if(tempUnitNama IS NULL,unitkerjaNama,CONCAT_WS('/ ',tempUnitNama, unitkerjaNama))) AS `nama`
   FROM
      unit_kerja_ref
		LEFT JOIN
			(SELECT
				unitkerjaId AS tempUnitId,
				unitkerjaKode AS tempUnitKode,
				unitkerjaNama AS tempUnitNama,
				unitkerjaParentId AS tempParentId
			FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUnitKerja ON(unitkerjaParentId=tempUnitId)
   WHERE
      unitkerjaId=%s
   ";
$sql['get_jenis_kegiatan_by_id']="
	SELECT
		jeniskegId as id,
		jeniskegNama as nama
	FROM
		jenis_kegiatan_ref
	WHERE jeniskegId=%s
";