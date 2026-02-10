<?php
$sql['get_periode_tahun']       = "
SELECT
   thanggarId AS id,
   thanggarNama AS `name`,
   thanggarIsAktif AS statusAktif,
   thanggarIsOpen AS statusOpen
FROM tahun_anggaran
WHERE 1 = 1
AND thanggarId = %s
LIMIT 0, 1
";

$sql['get_program'] = "
SELECT
   programId AS id,
   CONCAT(IFNULL(programNomor, programKodeLabel),' - ',programNama) AS `name`
FROM
   program_ref
WHERE 1 = 1
AND programThanggarId = '%s'
ORDER BY programNomor ASC
";

$sql['get_data_jenis_kegiatan'] = "
SELECT
   jeniskegId AS id,
   jeniskegNama AS `name`
FROM
   jenis_kegiatan_ref
";

$sql['count']        = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']     = "
SELECT
   SQL_CALC_FOUND_ROWS thanggarId AS taId,
   thanggarNama AS taNama,
   program.programId AS programId,
   program.programNomor AS programKode,
   program.programNama AS programNama,
   subprogram.subprogId AS kegiatanId,
   subprogram.subprogNomor AS kegiatanKode,
   subprogram.subprogNama AS kegiatanNama,
   kegiatan.kegrefId AS komponenId,
   kegiatan.kegrefNomor AS komponenKode,
   kegiatan.kegrefNama AS komponenNama,
   unit.unitkerjaId AS unitId,
   unit.unitkerjaKode AS unitKode,
   unit.unitkerjaNama AS unitNama,
   IFNULL(detail.count, 0) AS detail
FROM
   program_ref AS program
   JOIN tahun_anggaran
      ON thanggarId = program.programThanggarId
   JOIN sub_program AS subprogram
      ON subprogram.subprogProgramId = program.programId
   JOIN kegiatan_ref AS kegiatan
      ON kegiatan.kegrefSubprogId = subprogram.subprogId
   LEFT JOIN finansi_pa_kegiatan_ref_unit_kerja AS pa
      ON pa.kegrefId = kegiatan.kegrefId
   LEFT JOIN unit_kerja_ref AS unit
      ON unit.unitkerjaId = pa.unitkerjaId
   LEFT JOIN jenis_kegiatan_ref
      ON jeniskegId = subprogJeniskegId
   LEFT JOIN (SELECT
      kompkegKegrefId AS id,
      COUNT(kompkegKegrefId) AS `count`
   FROM
      komponen_kegiatan
   GROUP BY kompkegKegrefId) detail ON detail.id = kegiatan.`kegrefId`
WHERE 1 = 1
AND programThanggarId = %s
AND (pa.unitkerjaId = %s OR 1 = %s)
AND (IFNULL(subprogJeniskegId, 1) = %s OR 1 = %s)
AND (program.programId = %s OR 1 = %s)
AND kegrefNomor LIKE '%s'
AND kegrefNama LIKE '%s'
ORDER BY program.programId,
subprogram.subprogId,
kegiatan.kegrefId
LIMIT %s, %s
";
?>