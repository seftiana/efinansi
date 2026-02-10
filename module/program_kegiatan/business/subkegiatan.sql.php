<?php

//===GET===

$sql['get_tahun_anggaran_aktif'] = "
	SELECT
		thanggarId
	FROM
		tahun_anggaran
	WHERE
		thanggarIsAktif = 'Y'
";

$sql['get_tahun_anggaran_by_id'] = "
	SELECT
		thanggarId AS id,
		thanggarNama AS name
	FROM
		tahun_anggaran
	WHERE
		thanggarId = '%s'
";


$sql['get_keg_count'] = "
	SELECT
		count(subprogId) as count
	FROM(
		SELECT
			subprogId,
			CONCAT(
			    CASE
			        WHEN LENGTH(a.programNomor) = 1
			        THEN CONCAT('0',a.programNomor)
			        WHEN LENGTH(a.programNomor) = 2
			        THEN a.programNomor
			        ELSE a.programNomor
			    END,
			'.',
			    CASE
			        WHEN LENGTH(b.subprogNomor)= 1
			        THEN CONCAT('0',b.subprogNomor)
			        WHEN LENGTH(b.subprogNomor)= 2
			        THEN b.subprogNomor
			        ELSE b.subprogNomor
			    END,
			'.00'
			) AS kodeKegiatan,
			subprogNama
		FROM
			program_ref a
		LEFT JOIN
			sub_program b ON b.subprogProgramId =  a.programId
		WHERE
			subprogProgramId like '%s'AND
			programThanggarId ='%s'
	) a
	WHERE
		kodeKegiatan like '%s'
	OR
		subProgNama like '%s'
";

$sql['get_program_pop_up'] = "
	SELECT
		subprogId as id,
		kodeKegiatan as kode,
		subprogNama as nama,
		kodeProg AS kodeProgram,
		namaProgram
	FROM(
		SELECT
			subprogId,
            a.programNomor  AS kodeProg,
			b.subprogNomor  AS kodeKegiatan,
			a.programNama AS namaProgram,
			subprogNama
		FROM
			program_ref a
		LEFT JOIN
			sub_program b ON b.subprogProgramId =  a.programId
		WHERE
			subprogProgramId like '%s' AND
			programThanggarId ='%s'
	) a
	WHERE
		kodeKegiatan like '%s'
	OR
		subProgNama like '%s'
	ORDER BY kodeProgram,kode
	LIMIT %d, %d
";

$sql['get_tahun_anggaran'] = "
	SELECT
		thanggarId AS id,
		thanggarNama AS name
	FROM
		tahun_anggaran
	ORDER BY name DESC

";


$sql['get_program'] = "
	SELECT
		programId AS id,
		programNama AS name
	FROM
		program_ref
	WHERE
		programThanggarId = '%s'
";

$sql['get_count_data_where_program_id'] =
   "SELECT
        COUNT(sp.subprogId) AS total
    FROM
        sub_program sp
    INNER JOIN
       (program_ref prog INNER JOIN renstra rens ON prog.programRenstraId=rens.renstraId)
    ON sp.subprogProgramId=prog.programId

    WHERE
      rens.renstraIsAktif = 'Y' AND
      sp.subprogProgramId = %s  AND
      sp.subprogNomor LIKE %s AND
      sp.subprogNama LIKE %s
   ";

$sql['get_count_data_old'] =
   "SELECT
		count(kodeProg) AS total
	FROM	(
		SELECT
			CONCAT(CASE WHEN LENGTH(a.programNomor) = 1 THEN CONCAT('0',a.programNomor)
			WHEN LENGTH(programNomor) = 2 THEN a.programNomor END,'.00.00') AS kodeProg,

			ifnull(CONCAT(
			CASE WHEN LENGTH(a.programNomor) = 1 THEN CONCAT('0',a.programNomor)
			WHEN LENGTH(a.programNomor) = 2 THEN a.programNomor END,'.',CASE WHEN LENGTH(b.subprogNomor)= 1 THEN CONCAT('0',b.subprogNomor)
				WHEN LENGTH(b.subprogNomor)= 2 THEN b.subprogNomor END,'.00'),'') AS kodeKegiatan,
			ifnull(CONCAT(
			CASE WHEN LENGTH(a.programNomor) = 1 THEN CONCAT('0',a.programNomor)
			WHEN LENGTH(a.programNomor) = 2 THEN a.programNomor END,'.',CASE WHEN LENGTH(b.subprogNomor)= 1 THEN CONCAT('0',b.subprogNomor)
				WHEN LENGTH(b.subprogNomor)= 2 THEN b.subprogNomor END,'.',CASE WHEN LENGTH(c.kegrefNomor) = 1 THEN CONCAT('0',c.kegrefNomor)
			WHEN LENGTH(c.kegrefNomor) = 2 THEN c.kegrefNomor END),'') AS kodeSubKegiatan,
				a.programNomor,
				ifnull(b.subprogNomor,'') AS subprogNomor,
				ifnull(c.kegrefNomor,'') AS kegrefNomor,
				a.programNama AS namaProgram,
				ifnull(b.subprogNama,'') AS namaKegiatan,
				ifnull(c.kegrefNama,'') AS namaSubKegiatan
		FROM
		program_ref a
		LEFT JOIN sub_program b ON b.subprogProgramId =  a.programId AND
			subprogId like '%s' AND
			subprogJeniskegId like '%s'
		LEFT JOIN kegiatan_ref c ON b.subprogId = c.kegrefSubprogId
		WHERE
			programThanggarId = '%s'
		AND
			programId like '%s'
		ORDER BY kodeProg
	)a
	WHERE
		kodeSubKegiatan like '%s'
	AND
		namaSubKegiatan like '%s'
   ";
$sql['get_count_data']="SELECT
		count(*) AS total
	FROM	(
		SELECT
			ifnull(d.jumlah,0) as jumlah,
			CONCAT(
			CASE WHEN LENGTH(a.programNomor) = 1 THEN CONCAT('0',a.programNomor)
			WHEN LENGTH(programNomor) = 2 THEN a.programNomor END,'.0.00.00') AS kodeProg,
			ifnull(CONCAT(
			CASE WHEN LENGTH(a.programNomor) = 1 THEN CONCAT('0',a.programNomor)
			WHEN LENGTH(a.programNomor) = 2 THEN a.programNomor END,'.',b.subprogJeniskegId,'.',CASE WHEN LENGTH(b.subprogNomor)= 1 THEN CONCAT('0',b.subprogNomor)
				WHEN LENGTH(b.subprogNomor)= 2 THEN b.subprogNomor END,'.00'),'') AS kodeKegiatan,
			ifnull(CONCAT(
			CASE WHEN LENGTH(a.programNomor) = 1 THEN CONCAT('0',a.programNomor)
			WHEN LENGTH(a.programNomor) = 2 THEN a.programNomor END,'.',b.subprogJeniskegId,'.',CASE WHEN LENGTH(b.subprogNomor)= 1 THEN CONCAT('0',b.subprogNomor)
				WHEN LENGTH(b.subprogNomor)= 2 THEN b.subprogNomor END,'.',CASE WHEN LENGTH(c.kegrefNomor) = 1 THEN CONCAT('0',c.kegrefNomor)
			WHEN LENGTH(c.kegrefNomor) = 2 THEN c.kegrefNomor END),'') AS kodeSubKegiatan,
				a.programId,
				ifnull(b.subprogId,'') AS subprogId,
				ifnull(c.kegrefId,'') AS kegrefId,
				a.programNama AS namaProgram,
				ifnull(b.subprogNama,'') AS namaKegiatan,
				ifnull(c.kegrefNama,'') AS namaSubKegiatan,
				b.subprogJeniskegId AS subprogJeniskegId
		FROM
		program_ref a
		LEFT JOIN sub_program b ON b.subprogProgramId =  a.programId AND
			(subprogId='%s' OR '%s') AND
			(subprogJeniskegId ='%s' OR '%s')
		LEFT JOIN kegiatan_ref c ON b.subprogId = c.kegrefSubprogId
		LEFT JOIN(
			SELECT kompkegKegrefId ,count(kompkegKegrefId) as jumlah
			FROM komponen_kegiatan
			GROUP BY kompkegKegrefId
		) d ON d.kompkegKegrefId = c.kegrefId
		WHERE
			programThanggarId = '%s'
		AND
			(programId = '%s'	OR '%s')
		ORDER BY kodeProg,kodeKegiatan,kodeSubKegiatan
	)a
	WHERE
		kodeSubKegiatan like '%s'
	AND
		namaSubKegiatan like '%s'
	ORDER BY kodeProg, kodeKegiatan, kodeSubKegiatan";

$sql['get_data_kegiatan_count_all'] = "
SELECT
   count(*) as count
FROM
   program_ref
WHERE
   programThanggarId = %s
";

$sql['get_data'] =
   "SELECT
		jumlah,
		kodeProg,
		kodeKegiatan,
		kodeSubKegiatan,
		namaProgram,
		namaKegiatan,
		namaSubKegiatan,
		programId,
		subprogId,
		kegrefId,
		subprogJeniskegId
	FROM	(
		SELECT
			ifnull(d.jumlah,0) as jumlah,
			/*CONCAT(
			CASE WHEN LENGTH(a.programNomor) = 1 THEN CONCAT('0',a.programNomor)
			WHEN LENGTH(programNomor) = 2 THEN a.programNomor END,'.0.00.00')*/a.programNomor AS kodeProg,
			ifnull(/*CONCAT(
			CASE WHEN LENGTH(a.programNomor) = 1 THEN CONCAT('0',a.programNomor)
			WHEN LENGTH(a.programNomor) = 2 THEN a.programNomor END,'.',b.subprogJeniskegId,'.',CASE WHEN LENGTH(b.subprogNomor)= 1 THEN CONCAT('0',b.subprogNomor)
				WHEN LENGTH(b.subprogNomor)= 2 THEN b.subprogNomor END,'.00')*/b.subprogNomor,'') AS kodeKegiatan,
			ifnull(/*CONCAT(
			CASE WHEN LENGTH(a.programNomor) = 1 THEN CONCAT('0',a.programNomor)
			WHEN LENGTH(a.programNomor) = 2 THEN a.programNomor END,'.',b.subprogJeniskegId,'.',CASE WHEN LENGTH(b.subprogNomor)= 1 THEN CONCAT('0',b.subprogNomor)
				WHEN LENGTH(b.subprogNomor)= 2 THEN b.subprogNomor END,'.',CASE WHEN LENGTH(c.kegrefNomor) = 1 THEN CONCAT('0',c.kegrefNomor)
			WHEN LENGTH(c.kegrefNomor) = 2 THEN c.kegrefNomor END)*/c.kegrefNomor,'') AS kodeSubKegiatan,
				a.programId,
				ifnull(b.subprogId,'') AS subprogId,
				ifnull(c.kegrefId,'') AS kegrefId,
				a.programNama AS namaProgram,
				ifnull(b.subprogNama,'') AS namaKegiatan,
				ifnull(c.kegrefNama,'') AS namaSubKegiatan,
				b.subprogJeniskegId AS subprogJeniskegId
		FROM
		program_ref a
		LEFT JOIN sub_program b ON b.subprogProgramId =  a.programId AND
			(subprogId='%s' OR '%s') AND
			(subprogJeniskegId ='%s' OR '%s')
		LEFT JOIN kegiatan_ref c ON b.subprogId = c.kegrefSubprogId
		LEFT JOIN(
			SELECT kompkegKegrefId ,count(kompkegKegrefId) as jumlah
			FROM komponen_kegiatan
			GROUP BY kompkegKegrefId
		) d ON d.kompkegKegrefId = c.kegrefId
		WHERE
			programThanggarId = '%s'
		AND
			(programId = '%s'	OR '%s')
		ORDER BY kodeProg,kodeKegiatan,kodeSubKegiatan
	)a
	WHERE
		kodeSubKegiatan like '%s'
	AND
		namaSubKegiatan like '%s'
	ORDER BY kodeProg, kodeKegiatan, kodeSubKegiatan
	LIMIT %s, %s";



$sql['get_data_by_id'] =
   "SELECT
	   prog.programId AS program_nomor,
	   prog.programNama AS program_nama,
	   prog.programNama AS program_label,
	   sp.subprogId  AS kegiatan_id,
	   sp.subprogNomor AS kegiatan_nomor,
	   sp.subprogNama AS kegiatan_nama,
	   k.kegrefId AS id,
	   k.kegrefNomor AS kode,
	   k.kegrefNama AS nama,
	   k.kegrefLabelKode AS kode_label,
	   k.kegrefRkaklSubKegiatanId AS rkakl_subkegiatan,
	   r.rkaklSubKegiatanNama AS rkakl_subkegiatan_label,
	   sp.subprogJeniskegId AS jeniskegiatan_id,
	   jk.jeniskegNama AS jeniskegiatan_label,
	   i.ikId AS ik_id,
	   i.ikKode AS ik_kode,
	   i.ikNama AS ik_nama,
	   i.ikValue AS ik_value
	FROM
	   kegiatan_ref k INNER JOIN
		 (sub_program sp INNER JOIN program_ref prog ON sp.subprogProgramId=prog.programId)
	   ON k.kegrefSubprogId=sp.subprogId
	   LEFT JOIN jenis_kegiatan_ref jk ON (jk.jeniskegId = sp.subprogJeniskegId)
	   LEFT JOIN finansi_ref_rkakl_subkegiatan r ON r.rkaklSubKegiatanId = k.kegrefRkaklSubKegiatanId
       LEFT JOIN finansi_pa_ref_ik i ON k.kegregIkId = i.ikId
    WHERE
	  k.kegrefId = %s";
/*
            [id] =&gt;
            [kegiatan_nama] =&gt;
            [kegiatan_id] =&gt;
            [program_nama] =&gt;
            [jeniskegiatan_id] =&gt; 1
            [kode] =&gt; 153
            [nama] =&gt;
   */
$sql['get_data_program_by_nama'] =
   "SELECT
       programId AS program_id,
	   programNama AS program_nama
	FROM
	   program_ref p INNER JOIN renstra r ON p.programRenstraId = r.renstraId
	WHERE
	   r.renstraIsAktif = 'Y'
    ORDER BY
       programNama
   ";

$sql['get_data_program_by_nama_where'] =
   "SELECT
       programId AS program_id,
	   programNama AS program_nama
	FROM
	   program_ref p INNER JOIN renstra r ON p.programRenstraId = r.renstraId
	WHERE
	   r.renstraIsAktif = 'Y' AND programNama LIKE %s
	ORDER BY
	   programNama
   ";
$sql['get_count_program_by_nama'] =
   "SELECT
        COUNT(programId) AS total
	FROM
	    program_ref";

$sql['get_count_program_by_nama_where'] =
   "SELECT
        COUNT(programId) AS total
	FROM
	    program_ref
	WHERE
        programNama LIKE %s
	";

$sql['get_max_nomor'] =
   "SELECT
        MAX(subprogNomor) AS maksimum
	FROM
	    sub_program
	WHERE
        subprogProgramId=%s
	";
$sql['get_renstra_program_aktif']=
    "SELECT
	    renstraId AS renstra_id,
		renstraNama As renstra_nama
	 FROM
	    renstra
	 WHERE
	    renstraIsAktif = 'Y'
	 LIMIT 1
	";


$sql['get_data_kegiatan'] =
   "SELECT
      prog.programNama AS program_nama,
      prog.programNomor AS program_nomor,
      sp.subprogId AS kegiatan_id,
      sp.subprogNomor AS kegiatan_kode,
      sp.subprogNama AS kegiatan_nama
    FROM
      sub_program sp INNER JOIN
       (
         program_ref prog INNER JOIN
           (renstra rens INNER JOIN tahun_anggaran ta ON rens.renstraId=ta.thanggarRenstraId)
         ON prog.programThanggarId = ta.thanggarId
       )
     ON sp.subprogProgramId = prog.programId
    WHERE
      rens.renstraIsAktif = 'Y' AND
      sp.subprogNomor LIKE %s AND
      sp.subprogNama LIKE %s
    ORDER BY
      prog.programNomor , sp.subprogNomor ASC
    LIMIT %s, %s";

$sql['get_max_nomor'] = "
    SELECT
	  MAX(kegrefNomor)+1 AS max
	FROM
	  kegiatan_ref
";

//==GET For combo box==
$sql['get_data_jenis_kegiatan']=
     "SELECT
         jeniskegId AS id,
         jeniskegNama AS name
      FROM
	     jenis_kegiatan_ref
      ";

$sql['get_kode_selanjutnya'] =
   "SELECT
        IFNULL(MAX(kegrefNomor)+1, 1) AS nomor
	FROM
	    kegiatan_ref
	WHERE
        kegrefSubprogId=%s
	";

//===DO===
$sql['do_add'] ="
INSERT INTO kegiatan_ref (
        `kegrefNomor`,
        `kegrefSubprogId`,
        `kegrefNama`,
        `kegrefLabelKode`,
        `kegrefRkaklSubKegiatanId`
)
VALUES('%s','%s','%s','%s','%s')
";

$sql['do_update'] ="
UPDATE `kegiatan_ref`
    SET
      `kegrefNomor`=%s ,
      `kegrefSubprogId`=%s,
	  `kegrefNama`=%s,
      `kegrefLabelKode`=%s,
      `kegrefRkaklSubKegiatanId`=%s
   WHERE
      `kegrefId`=%s
";

$sql['do_delete'] =
   "DELETE FROM kegiatan_ref
   WHERE
      kegrefId = %s";
$sql['get_last_kegiatan_ref_id']=
"
	select max(kegrefid) as last_kegrefid
	from  kegiatan_ref
";


//unit kerja
//tabel unit_kerja_ref ,finansi_pa_kegiatan_ref_unit_kerja

$sql['get_count_unit_kerja_ref']=
"
SELECT
	count(kegrefId) as jumlah
FROM finansi_pa_kegiatan_ref_unit_kerja
	where kegrefId = %s
";

$sql['do_input_unit_kerja_ref']=
"
	INSERT INTO finansi_pa_kegiatan_ref_unit_kerja
      ( kegrefId,unitkerjaId )
	VALUES
	  (%s , %s )
";


$sql['do_delete_unit_kerja_ref_by_kegref']="
DELETE FROM finansi_pa_kegiatan_ref_unit_kerja WHERE kegrefId=%s
";

$sql['get_unit_kerja_kegiatan']="
SELECT
        unit_kerja_ref.unitkerjaId AS unitkerja_id,
        unit_kerja_ref.unitkerjaNama AS unitkerja_nama,
        unit_kerja_ref.unitkerjaParentId AS unitkerja_parent,
        COUNT(
                finansi_pa_kegiatan_ref_unit_kerja.kegrefId
        ) AS total
FROM
        unit_kerja_ref
        LEFT JOIN finansi_pa_kegiatan_ref_unit_kerja
                ON finansi_pa_kegiatan_ref_unit_kerja.unitkerjaId = unit_kerja_ref.unitkerjaId
                AND finansi_pa_kegiatan_ref_unit_kerja.kegrefId = %s
GROUP BY unit_kerja_ref.unitkerjaId
ORDER BY unitkerjaKode ASC
";


/**
 * Untuk Olah data Indikator Kegiatan (IK)
 */
$sql['do_input_ik_ref']="
INSERT INTO `finansi_pa_kegiatan_ik`(
                kegiatanIkKegrefId,
                kegiatanIkIkId,
                kegiatanIkTglUbah,
                kegiatanIkUserId
                )
VALUES('%s','%s',NOW(),'%s')
";

$sql['get_data_ik']="
SELECT
   ik.`ikId` AS id,
   ik.`ikKode` AS kode,
   ik.`ikNama` AS nama,
   ik.`ikValue` AS nilai,
   COUNT(kik.`kegiatanIkKegrefId`) AS total
FROM
`finansi_pa_ref_ik` ik
LEFT JOIN `finansi_pa_kegiatan_ik` kik ON ik.`ikId` = kik.`kegiatanIkIkId`
AND kik.`kegiatanIkKegrefId` = '%s'
GROUP BY ik.`ikKode`
ORDER BY ik.`ikKode` ASC
";

$sql['get_count_data_ik']="
SELECT
        COUNT(kegiatanIkId) AS jumlah
FROM
        finansi_pa_kegiatan_ik
WHERE
    kegiatanIkKegrefId = '%s'
";

$sql['do_delete_data_ik_by_kegref']="
DELETE FROM
    finansi_pa_kegiatan_ik
WHERE
    kegiatanIkKegrefId = '%s'
";
/**
 * end
 */
