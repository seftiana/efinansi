<?php
$sql['get_data_output']    = "
SELECT
   programId AS kegiatanId,
   programNomor AS kegiatanKode,
   programNama AS kegiatanNama,
   subprogId AS outputId,
   subprogNomor AS outputKode,
   subprogNama AS outputNama
FROM program_ref
LEFT JOIN sub_program
   ON subprogProgramId = programId
WHERE 1 = 1
AND (programThanggarId = '%s' OR 1 = %s)
AND (programNomor LIKE '%s' OR programNama LIKE '%s')
AND (subprogNomor LIKE '%s' OR subprogNama LIKE '%s')
ORDER BY programId, subprogId
LIMIT %s, %s
";

$sql['get_count_output']   = "
SELECT
   COUNT(DISTINCT subprogId) AS `count`
FROM program_ref
LEFT JOIN sub_program
   ON subprogProgramId = programId
WHERE 1 = 1
AND (programThanggarId = '%s' OR 1 = %s)
AND (programNomor LIKE '%s' OR programNama LIKE '%s')
AND (subprogNomor LIKE '%s' OR subprogNama LIKE '%s')
";

$sql['get_data_program_ref']  = "
SELECT
   programId AS id,
   programNomor AS `kode` ,
   programNama AS `name`,
   thanggarId,
   thanggarNama
FROM program_ref
JOIN tahun_anggaran
   ON thanggarId = programThanggarId
WHERE 1 = 1
AND (programThanggarId = %s OR 1 = %s)
AND (programNomor LIKE '%s' OR programNama LIKE '%s')
ORDER BY programNomor+0 ASC
LIMIT %s, %s
";

$sql['get_count_program_ref'] = "
SELECT
   COUNT(programId) AS `count`
FROM program_ref
JOIN tahun_anggaran
   ON thanggarId = programThanggarId
WHERE 1 = 1
AND (programThanggarId = %s OR 1 = %s)
AND (programNomor LIKE '%s' OR programNama LIKE '%s')
";

$sql['get_detail_belanja']    = "
SELECT SQL_CALC_FOUND_ROWS
   kompId AS id,
   kompKode AS kode,
   kompNama AS nama,
   kompDeskripsi AS deskripsi,
   IFNULL(kompFormula, '-') AS formula,
   kompHargaSatuan AS nominal,
   paguBasId AS makId,
   IFNULL(paguBasKode, '-') AS makKode,
   paguBasKeterangan AS makNama,
   r.id AS referensiId
FROM komponen
LEFT JOIN finansi_ref_pagu_bas
   ON paguBasId = kompMakId
LEFT JOIN (
SELECT
   kompkegKompId AS id,
   kegrefId AS kegiatan_id
FROM komponen_kegiatan
   JOIN kegiatan_ref
      ON kegrefId = kompkegKegrefId
) AS r ON r.id = kompId
   AND r.kegiatan_id = %s
WHERE 1 = 1
AND kompKode LIKE '%s'
AND kompNama LIKE '%s'
ORDER BY kompKode+0
LIMIT %s, %s
";

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";
?>