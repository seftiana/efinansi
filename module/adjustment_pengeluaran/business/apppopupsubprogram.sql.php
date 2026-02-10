<?php
$sql['get_count_data_subprogram'] =
   "SELECT
      count(*) AS total
   FROM
      sub_program
	  LEFT JOIN jenis_kegiatan_ref ON (jeniskegId = subprogJeniskegId)
   WHERE
	subprogProgramId=%s
    AND subprogNama LIKE '%s'
	AND subprogNomor LIKE '%s'
	AND subprogJeniskegId LIKE '%s'
   AND subprogJeniskegId IN (1,2)
";

$sql['get_data_subprogram'] =
   "SELECT
      subprogId as id,
	  /*CONCAT(
			CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
			WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.',subprogJeniskegId,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
			WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.00')*/subprogNomor as kode,
	  subprogNama as nama,
	  jeniskegNama as jenis
   FROM
      sub_program
	  LEFT JOIN jenis_kegiatan_ref ON (jeniskegId = subprogJeniskegId)
     LEFT JOIN program_ref ON programId = subprogProgramId
   WHERE
	subprogProgramId=%s
	AND subprogNama LIKE '%s'
	AND subprogNomor LIKE '%s'
	AND subprogJeniskegId LIKE '%s'
   AND subprogJeniskegId IN (1,2)
	LIMIT %s, %s
";

$sql['get_combo_jenis']="
	SELECT
		jeniskegId as id,
		jeniskegNama as name
	FROM
		jenis_kegiatan_ref
   WHERE jeniskegId IN (1,2)
";
?>