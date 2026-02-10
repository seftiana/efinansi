<?php

//===GET===

$sql['get_tahun_anggaran_by_id'] = "
	SELECT 
		thanggarId AS id,
		thanggarNama AS name
	FROM 
		tahun_anggaran
	WHERE
		thanggarId = '%s'
";

$sql['get_count_data_program'] = 
   "SELECT 
      count(programId) AS total
   FROM 
      program_ref
   WHERE
      programNomor LIKE %s AND programNama LIKE %s AND programThanggarId = %s";

$sql['get_data_program'] = 
   "SELECT 
      programId as program_id,
	  programNomor as program_nomor,
	  programNama as program_nama,
	  programThanggarId as ta_id
   FROM 
      program_ref
   WHERE
      programNomor LIKE %s AND programNama LIKE %s AND programThanggarId = %s
   ORDER BY 
      programNama
   LIMIT %s, %s";

/**
 * maping baru dengan kode rkakl kegiatan
 * since 16 November 2012
 */
$sql['get_data_program_by_id'] = "
SELECT 
  programId AS id,
  programNomor AS nomor,
  programNama AS nama,
  programThanggarId AS ta_id,
  programKodeLabel AS label,
  programRKAKLKegiatanId AS rkakl_kegiatan_id,
  rkakl_keg.`rkaklKegiatanNama` AS rkakl_kegiatan_nama,
  programSasaran AS sasaran,
  programIndikator AS indikator,
  programStrategi AS strategi,
  programKebijakan AS kebijakan 
FROM
  program_ref prog 
  /*LEFT JOIN tahun_anggaran ta

			ON prog.programThanggarId=ta.thanggarId*/
  LEFT JOIN `finansi_ref_rkakl_kegiatan` rkakl_keg 
    ON rkakl_keg.`rkaklKegiatanId` = prog.`programRKAKLKegiatanId`
   WHERE
      programId= %s 
";
//for combobox


$sql['get_data_ta'] =
   "SELECT
      thanggarId AS id,
	  thanggarNama AS name
	FROM
	  tahun_anggaran	
	ORDER BY
	  thanggarNama DESC
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

   
$sql['get_kode_selanjutnya'] = 
   "SELECT 
      IF(programNomor IS NOT NULL,MAX(programNomor)+1, 1) AS nomor      
   FROM 
      program_ref
   WHERE
      programThanggarId=%s";


$sql['get_kode_rkakl'] = "
	SELECT 
		rkaklProgramId AS id,
		rkaklProgramNama AS name
	FROM 
		finansi_ref_rkakl_prog
";

//for popup at module kegiatan
$sql['get_data_program_popup'] =
    "SELECT       
       prog.programNama AS program_nama,
       sp.subprogId AS kegiatan_id,   
       sp.subprogNomor AS kegiatan_nomor,
       sp.subprogNama AS kegiatan_nama   
   
     FROM
       sub_program sp INNER JOIN
          (program_ref prog INNER JOIN  tahun_anggaran ta ON prog.programThanggarId=ta.thanggarId)  
       ON sp.subprogProgramId = prog.programId
     WHERE        
       ta.thanggarId =%s AND
       sp.subprogNama LIKE %s
     ";

//===DO===

/**
 * maping baru ke kode rkakl kegiatan sebelumnya ke rkakl program
 * since 16 november 2012
 */  
$sql['do_add_program'] = 
   "INSERT INTO program_ref
      (`programId`,
       `programNomor`,
       `programNama`,
       `programThanggarId`,
       `programKodeLabel`,
       `programRKAKLKegiatanId`,
       `programSasaran`,
       `programIndikator`,
       `programStrategi`,
       `programKebijakan`,
       `programSasaranId`
       )
   VALUES 
      (NULL,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)";

/**
 * maping baru ke kode rkakl kegiatan sebelumnya ke rkakl program
 * since 16 november 2012
 */  
$sql['do_update_program'] = 
   "UPDATE program_ref
   SET 
      programNomor=%s,
	  programNama=%s,
	  programThanggarId=%s,
	  programKodeLabel=%s,
	  programRKAKLKegiatanId=%s,
	  programSasaran=%s,
	  programIndikator=%s,
	  programStrategi=%s,
	  programKebijakan=%s,
	  programSasaranId = %s
   WHERE 
      programId='%s'";

$sql['do_delete_program'] = 
   "DELETE from program_ref
   WHERE 
      programId='%s'";
	  
$sql['is_duplicate_nomor']=
    "SELECT programNomor as program_nomor
	 FROM program_ref
	 WHERE programNomor=%s
	 AND programThanggarId='%s'
	 ";

$sql['is_duplicate_nomor_where']=
    "SELECT programNomor as program_nomor
	 FROM program_ref
	 WHERE programNomor=%s AND programId <> '%s'
	 AND programThanggarId='%s'
	 ";
?>