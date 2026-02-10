<?php
$sql['get_data_excel_old'] = "
SELECT
   programId,
   subprogId,
   kegrefId,
   programNomor,
   programNama,
   jeniskegId,
   jeniskegNama,
   subprogNomor,
   subprogNama,
   kegrefNomor,
   kegrefNama,
   kompKode,
   kompNama,
   kompNamaSatuan,
   kompFormula,
   kompkegBiaya
FROM
   kegiatan_ref
   JOIN sub_program ON subprogId = kegrefSubprogId
   LEFT JOIN jenis_kegiatan_ref ON jeniskegId = subprogJeniskegId
   JOIN program_ref ON programId = subprogProgramId
   LEFT JOIN komponen_kegiatan ON kompkegKegrefId = kegrefId
   LEFT JOIN komponen ON kompId = kompkegKompId
WHERE
   programThanggarId = %s
ORDER BY
   programNomor,
   subprogJeniskegId,
   subprogNomor,
   kegrefNomor
";

$sql['get_data_excel'] =
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
      jeniskegNama,
      subprogJeniskegId
   FROM   (
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
            b.subprogJeniskegId AS subprogJeniskegId,
            jeniskegNama
      FROM
      program_ref a
      LEFT JOIN sub_program b ON b.subprogProgramId =  a.programId AND
         (subprogId='%s' OR '%s') AND
         (subprogJeniskegId ='%s' OR '%s')
      LEFT JOIN kegiatan_ref c ON b.subprogId = c.kegrefSubprogId
      LEFT JOIN jenis_kegiatan_ref ON jeniskegId = subprogJeniskegId
      LEFT JOIN(
         SELECT kompkegKegrefId ,count(kompkegKegrefId) as jumlah
         FROM komponen_kegiatan
         GROUP BY kompkegKegrefId
      ) d ON d.kompkegKegrefId = c.kegrefId
      WHERE
         programThanggarId = '%s'
      AND
         (programId = '%s'   OR '%s')
      ORDER BY kodeProg,kodeKegiatan,kodeSubKegiatan
   )a
   WHERE
      kodeSubKegiatan like '%s'
   AND
      namaSubKegiatan like '%s'
   ORDER BY
   kodeProg, kodeKegiatan, kodeSubKegiatan";

$sql['kopi_program_ref'] = "
INSERT INTO
   program_ref
   (
      programNomor,
      programNama,
      programThanggarId
   )
SELECT
   programNomor,
   programNama,
   idTahun
FROM
   (
      SELECT *
      FROM program_ref JOIN (SELECT %s AS idTahun) temp2
      WHERE programThanggarId = (SELECT thanggarId FROM tahun_anggaran JOIN program_ref ON thanggarId = programThanggarId WHERE thanggarBuka < (SELECT thanggarBuka FROM tahun_anggaran WHERE thanggarId = idTahun) ORDER BY thanggarBuka DESC LIMIT 1)
   ) temp
";

$sql['kopi_sub_program'] = "
INSERT INTO
   sub_program
   (
      subprogProgramId,
      subprogNomor,
      subprogNama,
      subprogJeniskegId
   )
SELECT
   programId,
   subprogNomor,
   subprogNama,
   subprogJeniskegId
FROM
   (
      SELECT tujuan.*,sub_program.*
      FROM
         program_ref AS tujuan
         JOIN program_ref AS asal USING (programNomor)
         JOIN sub_program ON subprogProgramId = asal.programId
      WHERE
         tujuan.programThanggarId = %s AND
         asal.programThanggarId = (SELECT thanggarId FROM tahun_anggaran JOIN program_ref ON thanggarId = programThanggarId WHERE thanggarBuka < (SELECT thanggarBuka FROM tahun_anggaran WHERE thanggarId = tujuan.programThanggarId) ORDER BY thanggarBuka DESC LIMIT 1)
   ) temp
";

$sql['kopi_kegiatan_ref'] = "
INSERT INTO
   kegiatan_ref
   (
      kegrefNomor,
      kegrefSubprogId,
      kegrefNama
   )
SELECT
   kegrefNomor,
   tujuan.subprogId,
   kegrefNama
FROM
   (SELECT * FROM program_ref JOIN sub_program ON subprogProgramId = programId) AS tujuan
   JOIN (SELECT * FROM program_ref JOIN sub_program ON subprogProgramId = programId) AS asal USING (programNomor, subprogJeniskegId, subprogNomor)
   JOIN kegiatan_ref ON kegrefSubprogId = asal.subprogId
WHERE
   tujuan.programThanggarId = %s AND
   asal.programThanggarId = (SELECT thanggarId FROM tahun_anggaran JOIN program_ref ON thanggarId = programThanggarId WHERE thanggarBuka < (SELECT thanggarBuka FROM tahun_anggaran WHERE thanggarId = tujuan.programThanggarId) ORDER BY thanggarBuka DESC LIMIT 1)
";

$sql['kopi_komponen_kegiatan'] = "
INSERT INTO
   komponen_kegiatan
   (
      kompkegKompId,
      kompkegKegrefId,
      kompkegBiaya
   )
SELECT
   kompkegKompId,
   tujuan.kegrefId,
   kompkegBiaya
FROM
   (SELECT * FROM program_ref JOIN sub_program ON subprogProgramId = programId JOIN kegiatan_ref ON kegrefSubprogId = subprogId) AS tujuan
   JOIN (SELECT * FROM program_ref JOIN sub_program ON subprogProgramId = programId JOIN kegiatan_ref ON kegrefSubprogId = subprogId) AS asal USING (programNomor, subprogJeniskegId, subprogNomor, kegrefNomor)
   JOIN komponen_kegiatan ON kompkegKegrefId = asal.kegrefId
WHERE
   tujuan.programThanggarId = %s AND
   asal.programThanggarId = (SELECT thanggarId FROM tahun_anggaran JOIN program_ref ON thanggarId = programThanggarId WHERE thanggarBuka < (SELECT thanggarBuka FROM tahun_anggaran WHERE thanggarId = tujuan.programThanggarId) ORDER BY thanggarBuka DESC LIMIT 1)
";

$sql['copy_kegref_unitkerja'] = "
INSERT INTO finansi_pa_kegiatan_ref_unit_kerja (kegrefId, unitkerjaId)
(SELECT
   t.kegrefId,
   u.unitkerjaId
FROM
   (SELECT *
   FROM program_ref
      JOIN sub_program
         ON subprogProgramId = programId) AS tujuan
   JOIN
      (SELECT  *
      FROM program_ref
         JOIN sub_program
            ON subprogProgramId = programId
         LEFT JOIN kegiatan_ref
            ON kegrefSubprogId = subprogId) AS asal USING (
         programNomor,
         subprogJeniskegId,
         subprogNomor
      )
   JOIN kegiatan_ref AS a
      ON a.kegrefSubprogId = asal.subprogId
   LEFT JOIN finansi_pa_kegiatan_ref_unit_kerja AS u
      ON a.kegrefId = u.kegrefId
   JOIN kegiatan_ref AS t
      ON t.kegrefSubprogId = tujuan.subprogId
WHERE tujuan.programThanggarId = '%s'
   AND asal.programThanggarId =
   (SELECT
      thanggarId
   FROM
      tahun_anggaran
      JOIN program_ref
         ON thanggarId = programThanggarId
   WHERE thanggarBuka <
      (SELECT
         thanggarBuka
      FROM
         tahun_anggaran
      WHERE thanggarId = tujuan.programThanggarId)
   ORDER BY thanggarBuka DESC
   LIMIT 1))
";
?>
