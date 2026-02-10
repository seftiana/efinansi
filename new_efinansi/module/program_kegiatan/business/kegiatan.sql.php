<?php

//===GET===
$sql['get_jenis_kegiatan'] = "
	SELECT
		jeniskegId AS id,
		jeniskegNama AS name
	FROM jenis_kegiatan_ref
";
$sql['get_data_program'] = "
	SELECT
		programId AS id,
		programNama AS name
	FROM
		program_ref
	WHERE
		programThanggarId='%s'

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

$sql['get_count_data_where_program_id'] =
   "SELECT
      COUNT(sp.subprogId) AS total
    FROM
      sub_program sp INNER JOIN
       ( program_ref prog INNER JOIN tahun_anggaran ta ON prog.programThanggarId = ta.thanggarId)
      ON sp.subprogProgramId = prog.programId

    WHERE
      sp.subprogProgramId = %s  AND
      sp.subprogNomor LIKE %s AND
      sp.subprogNama LIKE %s
   ";

$sql['get_count_data'] =
   "SELECT
      COUNT(sp.subprogId) AS total
    FROM
      sub_program sp INNER JOIN
       ( program_ref prog INNER JOIN tahun_anggaran ta ON prog.programThanggarId = ta.thanggarId)
      ON sp.subprogProgramId = prog.programId

    WHERE
      sp.subprogNomor LIKE %s AND
      sp.subprogNama LIKE %s
   ";

$sql['get_data'] =
   "SELECT
      prog.programNama AS program_nama,
      prog.programNomor AS program_nomor,
      sp.subprogId AS kegiatan_id,
      sp.subprogNomor AS kegiatan_kode,
      sp.subprogNama AS kegiatan_nama,
      sp.subprogKodeLabel AS kode_label,
      sp.subprogRKAKLKegiatanId AS rkakl_kegiatan
    FROM
      sub_program sp INNER JOIN
       ( program_ref prog INNER JOIN tahun_anggaran ta ON prog.programThanggarId = ta.thanggarId)
      ON sp.subprogProgramId = prog.programId
    WHERE
      sp.subprogNomor LIKE %s AND
      sp.subprogNama LIKE %s
    ORDER BY
      prog.programNomor , sp.subprogNomor ASC
    LIMIT %s, %s";

$sql['get_data_where_program_id'] =
   "SELECT
      prog.programNama AS program_nama,
      prog.programNomor AS program_nomor,
      sp.subprogId AS kegiatan_id,
      sp.subprogNomor AS kegiatan_kode,
      sp.subprogNama AS kegiatan_nama,
      sp.subprogKodeLabel AS kode_label,
      sp.subprogRKAKLKegiatanId AS rkakl_kegiatan
    FROM
      sub_program sp INNER JOIN
       ( program_ref prog INNER JOIN tahun_anggaran ta ON prog.programThanggarId = ta.thanggarId)
      ON sp.subprogProgramId = prog.programId
    WHERE
      sp.subprogProgramId = %s  AND
      sp.subprogNomor LIKE %s AND
      sp.subprogNama LIKE %s
   ORDER BY
      prog.programNomor , sp.subprogNomor ASC
   LIMIT %s, %s";

$sql['get_data_where_ta'] =
   "SELECT
      prog.programNama AS program_nama,
      prog.programNomor AS program_nomor,
      sp.subprogId AS kegiatan_id,
      sp.subprogNomor AS kegiatan_kode,
      sp.subprogNama AS kegiatan_nama,
      sp.subprogKodeLabel AS kode_label,
      sp.subprogRKAKLKegiatanId AS rkakl_kegiatan
    FROM
      sub_program sp INNER JOIN
       ( program_ref prog INNER JOIN tahun_anggaran ta ON prog.programThanggarId = ta.thanggarId)
      ON sp.subprogProgramId = prog.programId
    WHERE
      prog.programThanggarId = %s AND
      sp.subprogNama LIKE %s
    ORDER BY
      sp.subprogNama ASC
    LIMIT %s, %s";

$sql['get_count_data_where_ta'] =
   "SELECT
      COUNT(sp.subprogId) AS total
    FROM
      sub_program sp INNER JOIN
       ( program_ref prog INNER JOIN tahun_anggaran ta ON prog.programThanggarId = ta.thanggarId)
      ON sp.subprogProgramId = prog.programId
    WHERE
      prog.programThanggarId = %s AND
      sp.subprogNama LIKE %s";

$sql['get_data_by_id'] ="
SELECT 
  prog.programId AS program_id,
  prog.programNama AS program_nama,
  prog.programNomor AS program_nomor,
  sp.subprogId AS id,
  sp.subprogNomor AS kode,
  sp.subprogNama AS nama,
  subprogProgramId AS program,
  subprogJeniskegId AS jenisId,
  sp.subprogKodeLabel AS kode_label,
  sp.`subprogRKAKLOutputId` AS rkakl_output_id,
  rk_o.`rkaklOutputNama` AS rkakl_output_nama
FROM
  sub_program sp 
  LEFT JOIN (
      program_ref prog 
      LEFT JOIN tahun_anggaran ta 
        ON prog.programThanggarId = ta.thanggarId
    ) 
    ON sp.subprogProgramId = prog.programId 
  LEFT JOIN finansi_ref_rkakl_output rk_o 
    ON rk_o.`rkaklOutputId` =  sp.`subprogRKAKLOutputId`
WHERE
	  sp.subprogId = %s
LIMIT 1";



$sql['get_kode_selanjutnya'] =
   "SELECT
      IFNULL(MAX(subprogNomor)+1, 1) AS nomor
	FROM
	   sub_program
	WHERE
      subprogProgramId=%s
   AND
      subprogJeniskegId='%s'
	";

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
      thanggarId AS id
	FROM
	  tahun_anggaran
	WHERE
	  thanggarIsAktif='Y'
	LIMIT 1
   ";


//===DO===
/**
 * maping baru rkaklkegiatan dengan rkakloutput
 */

$sql['do_add'] =
   "INSERT INTO sub_program
      (`subprogProgramId`,`subprogNomor`,`subprogNama`, `subprogJeniskegId`,`subprogKodeLabel`,`subprogRKAKLOutputId`)
   VALUES
      (%s,%s,%s,%s,%s,%s)";

/**
 * maping baru rkaklkegiatan dengan rkakloutput
 */
$sql['do_update'] =
   "UPDATE sub_program
   SET
      subprogProgramId=%s,
	  subprogNomor=%s,
	  subprogNama=%s,
	  subprogJeniskegId = %s,
	  subprogKodeLabel = %s,
     `subprogRKAKLOutputId` =  NULLIF(%s,NULL)
   WHERE
      subprogId=%s";

$sql['do_delete'] =
   "DELETE FROM sub_program
   WHERE
      subprogId IN (%s)";
?>