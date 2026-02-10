<?php
$sql['get_periode_tahun']     = "
SELECT
   thanggarId AS `id`,
   thanggarNama AS `name`
FROM tahun_anggaran
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
ORDER BY thanggarNama
";

$sql['get_data']              = "
SELECT SQL_CALC_FOUND_ROWS
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   programId,
   programNomor AS programKode,
   programNama AS programNama,
   subprogId AS kegiatanId,
   IFNULL(subprogNomor, '') AS kegiatanKode,
   CONCAT(IFNULL(subprogNama, ''),' (',IFNULL(jeniskegNama, '-'),')') AS kegiatanNama,
   kegrefId AS subKegiatanId,
   IFNULL(kegrefNomor, '') AS subKegiatanKode,
   IFNULL(kegrefNama, '') AS subKegiatanNama,
   IF(rencana_pengeluaran.nominalUsulan > 0,rencana_pengeluaran.nominalUsulan, 0) AS nominalUsulan,
   IF(rencana_pengeluaran.nominalSetuju > 0,rencana_pengeluaran.nominalSetuju,0) AS nominalSetuju
FROM
   kegiatan_detail
   LEFT JOIN kegiatan
      ON kegdetKegId = kegId
   LEFT JOIN kegiatan_ref
      ON kegdetKegrefId = kegrefId
   LEFT JOIN sub_program
      ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref
      ON subprogProgramId = programId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
   JOIN (
      SELECT unitkerjaId AS id,
      CASE
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN unitkerjaKodeSistem
      END AS kode
      FROM unit_kerja_ref
   ) AS tmp_unit ON tmp_unit.id = unitkerjaId
   LEFT JOIN
      (SELECT
         rncnpengeluaranKegdetId,
         SUM(rncnpengeluaranKomponenNominal * rncnpengeluaranSatuan * IF(kompFormulaHasil > 0, kompFormulaHasil, 1)) AS nominalUsulan,
         SUM(
            CASE
               WHEN rncnpengeluaranIsAprove = 'Ya'
               THEN (
                  rncnpengeluaranKomponenNominalAprove * rncnpengeluaranSatuanAprove * IF(
                     kompFormulaHasil > 0,
                     kompFormulaHasil,
                     1
                  )
               )
               WHEN rncnpengeluaranIsAprove <> 'Ya'
               THEN 0
            END
         ) AS nominalSetuju
      FROM
         rencana_pengeluaran
         LEFT JOIN komponen
            ON kompKode = rncnpengeluaranKomponenKode
      GROUP BY rncnpengeluaranKegdetId) AS rencana_pengeluaran
      ON rencana_pengeluaran.rncnpengeluaranKegdetId = kegdetId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
WHERE 1 = 1
   AND kegThanggarId = %s
   AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
   AND (programId = '%s' OR 1 = %s)
   AND (jeniskegId = '%s' OR 1 = %s)
ORDER BY SUBSTRING_INDEX(tmp_unit.kode, '.', 1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 2), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 3), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 4), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 5), '.', -1)+0,
   programId,
   subprogId,
   kegrefId
LIMIT %s, %s
";


$sql['get_count']="
SELECT FOUND_ROWS() AS `count`
";


$sql['get_resume_unit_kerja']="
SELECT SQL_CALC_FOUND_ROWS
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   SUM(IF(rencana_pengeluaran.nominalUsulan > 0,rencana_pengeluaran.nominalUsulan, 0)) AS nominalUsulan,
   SUM(IF(rencana_pengeluaran.nominalSetuju > 0,rencana_pengeluaran.nominalSetuju,0)) AS nominalSetuju
FROM
   kegiatan_detail
   LEFT JOIN kegiatan
      ON kegdetKegId = kegId
   LEFT JOIN kegiatan_ref
      ON kegdetKegrefId = kegrefId
   LEFT JOIN sub_program
      ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref
      ON subprogProgramId = programId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
   JOIN (
      SELECT unitkerjaId AS id,
      CASE
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN unitkerjaKodeSistem
      END AS kode
      FROM unit_kerja_ref
   ) AS tmp_unit ON tmp_unit.id = unitkerjaId
   LEFT JOIN
      (SELECT
         rncnpengeluaranKegdetId,
         SUM(rncnpengeluaranKomponenNominal * rncnpengeluaranSatuan * IF(kompFormulaHasil > 0, kompFormulaHasil, 1)) AS nominalUsulan,
         SUM(
            CASE
               WHEN rncnpengeluaranIsAprove = 'Ya'
               THEN (
                  rncnpengeluaranKomponenNominalAprove * rncnpengeluaranSatuanAprove * IF(
                     kompFormulaHasil > 0,
                     kompFormulaHasil,
                     1
                  )
               )
               WHEN rncnpengeluaranIsAprove <> 'Ya'
               THEN 0
            END
         ) AS nominalSetuju
      FROM
         rencana_pengeluaran
         LEFT JOIN komponen
            ON kompKode = rncnpengeluaranKomponenKode
      GROUP BY rncnpengeluaranKegdetId) AS rencana_pengeluaran
      ON rencana_pengeluaran.rncnpengeluaranKegdetId = kegdetId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
WHERE 1 = 1
   AND kegThanggarId = %s
   AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
   AND (programId = '%s' OR 1 = %s)
   AND (jeniskegId = '%s' OR 1 = %s)
GROUP BY unitkerjaId
ORDER BY SUBSTRING_INDEX(tmp_unit.kode, '.', 1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 2), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 3), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 4), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 5), '.', -1)+0
";

$sql['get_resume_program']="
SELECT
g.unitkerjaId,
/*CASE WHEN g.unitkerjaNama IS NOT NULL THEN CONCAT(g.unitkerjaNama,'-',f.unitkerjaNama)
WHEN g.unitkerjaNama IS NULL THEN f.unitkerjaNama END*/
g.unitkerjaNama as unitName,
CONCAT(CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.0.00.00') AS kodeProg,
ifnull(CONCAT(
CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.',d.subprogJeniskegId,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
   WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.00'),'') AS kodeKegiatan,
ifnull(CONCAT(
CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
   WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.',CASE WHEN LENGTH(kegrefNomor) = 1 THEN CONCAT('0',kegrefNomor)
WHEN LENGTH(kegrefNomor) = 2 THEN kegrefNomor END),'') AS kodeSubKegiatan,
programNama AS namaProgram,
CONCAT(ifnull(subprogNama,''),' (',jeniskegNama,')') AS namaKegiatan,
ifnull(kegrefNama,'') AS namaSubKegiatan,
IF(SUM(h.nominalUsulan) > 0,SUM(h.nominalUsulan),0) AS nominalUsulan,
IF(SUM(h.nominalSetuju) > 0,SUM(h.nominalSetuju),0) AS nominalSetuju
FROM kegiatan_detail b
LEFT JOIN kegiatan a ON b.kegdetKegId = a.kegId
LEFT JOIN kegiatan_ref c ON b.kegdetKegrefId = c.kegrefId
LEFT JOIN sub_program d ON c.kegrefSubprogId = d.subprogId
LEFT JOIN program_ref e ON d.subprogProgramId = e.programId
LEFT JOIN unit_kerja_ref g ON kegUnitkerjaId = g.unitkerjaId

LEFT JOIN (
   SELECT
   rncnpengeluaranKegdetId,
   sum(rncnpengeluaranKomponenNominal * rncnpengeluaranSatuan *
      IF(kompFormulaHasil > 0,kompFormulaHasil,1)) AS nominalUsulan,
   sum(CASE WHEN rncnpengeluaranIsAprove='Ya' THEN (rncnpengeluaranKomponenNominalAprove *
         rncnpengeluaranSatuanAprove * IF(kompFormulaHasil > 0,kompFormulaHasil,1))
       WHEN rncnpengeluaranIsAprove<>'Ya' THEN  0 END ) AS nominalSetuju
   FROM rencana_pengeluaran
   LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
   GROUP BY rncnpengeluaranKegdetId
) h ON h.rncnpengeluaranKegdetId = b.kegdetId
LEFT JOIN jenis_kegiatan_ref i ON d.subprogJeniskegId = i.jeniskegId

WHERE a.kegThanggarId = %s
AND
   (
        g.unitkerjaKodeSistem LIKE
      CONCAT((
            SELECT
               unitkerjaKodeSistem
            FROM
               unit_kerja_ref
            WHERE
               unit_kerja_ref.unitkerjaId='%s'),'.','%s')
      OR
      g.unitkerjaKodeSistem LIKE
      CONCAT((
            SELECT
               unitkerjaKodeSistem
            FROM
               unit_kerja_ref
            WHERE
               unit_kerja_ref.unitkerjaId='%s'))
    )AND e.programId LIKE %s AND d.subprogJeniskegId LIKE %s

GROUP BY g.unitkerjaId , kodeProg

ORDER BY g.unitkerjaKodeSistem , kodeProg


";

$sql['get_resume_kegiatan']="
SELECT
g.unitkerjaId,
/*CASE WHEN g.unitkerjaNama IS NOT NULL THEN CONCAT(g.unitkerjaNama,'-',f.unitkerjaNama)
WHEN g.unitkerjaNama IS NULL THEN f.unitkerjaNama END*/
g.unitkerjaNama as unitName,
CONCAT(CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.00.00.00') AS kodeProg,
ifnull(CONCAT(
CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.','0',d.subprogJeniskegId,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
   WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.00'),'') AS kodeKegiatan,
ifnull(CONCAT(
CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.',CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
   WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.',CASE WHEN LENGTH(kegrefNomor) = 1 THEN CONCAT('0',kegrefNomor)
WHEN LENGTH(kegrefNomor) = 2 THEN kegrefNomor END),'') AS kodeSubKegiatan,
programNama AS namaProgram,
CONCAT(ifnull(subprogNama,''),' (',jeniskegNama,')') AS namaKegiatan,
ifnull(kegrefNama,'') AS namaSubKegiatan,
IF(SUM(h.nominalUsulan) > 0,SUM(h.nominalUsulan),0) AS nominalUsulan,
IF(SUM(h.nominalSetuju) > 0,SUM(h.nominalSetuju),0) AS nominalSetuju
FROM kegiatan_detail b
LEFT JOIN kegiatan a ON b.kegdetKegId = a.kegId
LEFT JOIN kegiatan_ref c ON b.kegdetKegrefId = c.kegrefId
LEFT JOIN sub_program d ON c.kegrefSubprogId = d.subprogId
LEFT JOIN program_ref e ON d.subprogProgramId = e.programId
LEFT JOIN unit_kerja_ref g ON kegUnitkerjaId = g.unitkerjaId

LEFT JOIN (
   SELECT
   rncnpengeluaranKegdetId,
   sum(rncnpengeluaranKomponenNominal * rncnpengeluaranSatuan *
         IF(kompFormulaHasil > 0,kompFormulaHasil,1)) AS nominalUsulan,
   sum(CASE WHEN rncnpengeluaranIsAprove='Ya' THEN (rncnpengeluaranKomponenNominalAprove *
            rncnpengeluaranSatuanAprove * IF(kompFormulaHasil > 0,kompFormulaHasil,1))
       WHEN rncnpengeluaranIsAprove<>'Ya' THEN  0 END ) AS nominalSetuju
   FROM rencana_pengeluaran
   LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
   GROUP BY rncnpengeluaranKegdetId
) h ON h.rncnpengeluaranKegdetId = b.kegdetId
LEFT JOIN jenis_kegiatan_ref i ON d.subprogJeniskegId = i.jeniskegId

WHERE a.kegThanggarId = %s
AND
   (
        g.unitkerjaKodeSistem LIKE
      CONCAT((
            SELECT
               unitkerjaKodeSistem
            FROM
               unit_kerja_ref
            WHERE
               unit_kerja_ref.unitkerjaId='%s'),'.','%s')
      OR
      g.unitkerjaKodeSistem LIKE
      CONCAT((
            SELECT
               unitkerjaKodeSistem
            FROM
               unit_kerja_ref
            WHERE
               unit_kerja_ref.unitkerjaId='%s'))
    )
AND e.programId LIKE %s AND d.subprogJeniskegId LIKE %s

GROUP BY g.unitkerjaId , kodeProg , kodeKegiatan

ORDER BY g.unitkerjaKodeSistem , kodeProg , kodeKegiatan


";


//untuk popup
/**
$sql['get_unit_kerja']=
   "SELECT
      unitkerjaId AS unitkerja_id,
      unitkerjaKode AS unitkerja_kode,
      unitkerjaNama AS unitkerja_nama
   FROM
     unit_kerja_ref
   WHERE
     unitkerjaParentId LIKE %s AND
     unitkerjaNama LIKE %s
   ORDER BY
     unitkerjaKode, UnitkerjaNama ASC
   LIMIT %s, %s
   ";

$sql['get_count_unit_kerja']=
   "SELECT
      COUNT(unitkerjaId) AS total
   FROM
     unit_kerja_ref
   WHERE
     unitkerjaParentId LIKE %s AND
     unitkerjaNama LIKE %s
   ORDER BY
     unitkerjaKode, UnitkerjaNama ASC
   LIMIT 1
   ";
*/
$sql['get_unit_kerja_id'] = "
   SELECT
      unitkerjaNama,
      unitkerjaNamaPimpinan
   FROM
      unit_kerja_ref
   WHERE
      unitkerjaId = '%s'
";

//===untuk combo box
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
      thanggarId AS id,
     thanggarNama AS nama
   FROM
     tahun_anggaran
   WHERE
     thanggarIsAktif='Y'
   LIMIT 1
   ";
$sql['get_combo_jenis_kegiatan']="
   SELECT
      jeniskegId as id,
      jeniskegNama as name
   FROM
      jenis_kegiatan_ref
   WHERE jeniskegId < 3
   ORDER BY jeniskegId
";
?>