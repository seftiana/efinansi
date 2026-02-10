<?php
$sql['get_count_data_kegiatanref'] =
   "SELECT
      count(kegrefId) AS total
   FROM
      kegiatan_ref
   WHERE
	kegrefSubprogId=%s
    AND kegrefNama LIKE '%s'
";

$sql['get_data_kegiatanref'] =
   "SELECT
      kegrefId as id,
	  /*CONCAT(
			CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
			WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.','0',subprogJeniskegId,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
				WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.',CASE WHEN LENGTH(kegrefNomor) = 1 THEN CONCAT('0',kegrefNomor)
			WHEN LENGTH(kegrefNomor) = 2 THEN kegrefNomor END)*/kegrefNomor as kode,
	  kegrefNama as nama
   FROM
      kegiatan_ref
   LEFT JOIN sub_program ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref ON subprogProgramId = programId
   WHERE
	kegrefSubprogId='%s'
	AND kegrefNama LIKE '%s'
	LIMIT %s, %s
";
?>