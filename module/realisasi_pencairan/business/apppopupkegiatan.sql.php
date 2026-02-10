<?php /*
$sql['get_count_data_subprogram'] =
   "
SELECT COUNT(id) AS total FROM (
SELECT
      sp.subprogId as id,
      sp.subprogNomor as kode,
      sp.subprogNama as nama,
      jk.jeniskegNama as jenis,
      kd.kegdetId as kegiatandetail_id,
      sp.subprogProgramId,
      sp.subprogJeniskegId,
      kd.kegdetKegId,
      kd.kegdetIsAprove


FROM
      kegiatan_detail kd
      JOIN kegiatan_ref kr ON (kd.kegdetKegrefId=kr.kegrefId)
      JOIN sub_program sp ON (kr.kegrefSubprogId = sp.subprogId)
      LEFT JOIN jenis_kegiatan_ref jk ON (jk.jeniskegId = sp.subprogJeniskegId)
GROUP BY
     sp.subprogId

HAVING
     sp.subprogProgramId=%s
     AND sp.subprogNama LIKE %s
     AND sp.subprogNomor LIKE %s
     AND sp.subprogJeniskegId LIKE %s
     AND kd.kegdetKegId = %s
     AND kd.kegdetIsAprove = 'Ya') result

LIMIT 1
"; */

$sql['get_count_data_subprogram'] = "
   SELECT COUNT(id) AS total FROM (
SELECT
      DISTINCT
      sp.subprogId AS id
FROM

      kegiatan_detail kd
      JOIN kegiatan_ref kr ON (kd.kegdetKegrefId=kr.kegrefId)
      JOIN sub_program sp ON (kr.kegrefSubprogId = sp.subprogId)
      LEFT JOIN jenis_kegiatan_ref jk ON (jk.jeniskegId = sp.subprogJeniskegId)
WHERE
    sp.subprogProgramId=%s
     AND sp.subprogNama LIKE %s
     AND sp.subprogNomor LIKE %s
     AND sp.subprogJeniskegId LIKE %s
     AND kd.kegdetKegId = %s
     AND kd.kegdetIsAprove = 'Ya') result

LIMIT 1
";

$sql['get_data_subprogram'] =
"SELECT
      SQL_CALC_FOUND_ROWS
	  DISTINCT
      sp.subprogId as id,
      ifnull(CONCAT(
			CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
			WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.',subprogJeniskegId,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
				WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.00'),'') as kode,
      sp.subprogNama as nama,
      jk.jeniskegNama as jenis
FROM

      kegiatan_detail kd
      JOIN kegiatan_ref kr ON (kd.kegdetKegrefId=kr.kegrefId)
      JOIN sub_program sp ON (kr.kegrefSubprogId = sp.subprogId)
      LEFT JOIN jenis_kegiatan_ref jk ON (jk.jeniskegId = sp.subprogJeniskegId)
      LEFT JOIN program_ref ON subprogProgramId = programId
WHERE
     sp.subprogProgramId=%s
     AND sp.subprogNama LIKE %s
     AND sp.subprogNomor LIKE %s
     AND sp.subprogJeniskegId LIKE %s
     AND kd.kegdetKegId = %s
     AND kd.kegdetIsAprove = 'Ya'

LIMIT %s, %s
";

$sql['get_combo_jenis']="
	SELECT
		jeniskegId as id,
		jeniskegNama as name
	FROM
		jenis_kegiatan_ref
   WHERE jeniskegId < 3
";
?>