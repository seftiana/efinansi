<?php
$sql['get_tahun_anggaran']    = "
SELECT
   thanggarId AS id,
   thanggarNama AS `name`
FROM
   tahun_anggaran
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
ORDER BY thanggarNama ASC
";

$sql['get_data_program_kegiatan']   = "
SELECT
   programId AS kegiatanId,
   programNomor AS kegiatanKode,
   programNama AS kegiatanNama,
   subprogId AS outputId,
   subprogNomor AS outputKode,
   subprogNama AS outputNama,
   kegrefId AS komponenId,
   kegrefNomor AS komponenKode,
   kegrefNama AS komponenNama,
   thanggarId,
   thanggarNama,
   IFNULL(tmpKegiatan.count, 0) AS output,
   IFNULL(tmpOutput.count, 0) AS komponen,
   IFNULL(kompKegiatan.count, '-') AS detailBelanja,
   jeniskegId,
   jeniskegNama
FROM program_ref
LEFT JOIN sub_program
   ON subprogProgramId = programId
LEFT JOIN(
   SELECT subprogProgramId AS id,
   COUNT(DISTINCT subprogId) AS `count`
   FROM sub_program
   GROUP BY subprogProgramId
) AS tmpKegiatan ON tmpKegiatan.id = programId
LEFT JOIN kegiatan_ref
   ON kegrefSubprogId = subprogId
LEFT JOIN(
   SELECT kegrefSubprogId AS id,
      COUNT(DISTINCT kegrefId) AS `count`
   FROM kegiatan_ref
   WHERE kegrefId IS NOT NULL
   GROUP BY kegrefSubprogId
) AS tmpOutput ON tmpOutput.id = subprogId
JOIN tahun_anggaran
   ON thanggarId = programThanggarId
LEFT JOIN(
   SELECT
      kompkegKegrefId AS id,
      COUNT(kompkegKompId) AS `count`
   FROM komponen_kegiatan
   GROUP BY kompkegKegrefId
) AS kompKegiatan ON kompKegiatan.id = kegrefId
LEFT JOIN jenis_kegiatan_ref
   ON jeniskegId = subprogJeniskegId
WHERE 1 = 1
AND (programThanggarId = %s OR 1 = %s)
AND (programId = '%s' OR 1 = %s)
AND (subprogId = '%s' OR 1 = %s)
AND IFNULL(kegrefNomor, '') LIKE '%s'
AND IFNULL(kegrefNama, '') LIKE '%s'
ORDER BY thanggarNama,
programId,
SUBSTRING_INDEX(programNomor, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(programNomor, '.', 2)+0, '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(programNomor, '.', 3)+0, '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(programNomor, '.', 4)+0, '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(programNomor, '.', 5)+0, '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(programNomor, '.', 6)+0, '.', -1)+0,
subprogId,
SUBSTRING_INDEX(subprogNomor, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(subprogNomor, '.', 2)+0, '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(subprogNomor, '.', 3)+0, '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(subprogNomor, '.', 4)+0, '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(subprogNomor, '.', 5)+0, '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(subprogNomor, '.', 6)+0, '.', -1)+0,
kegrefId,
SUBSTRING_INDEX(kegrefNomor, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(kegrefNomor, '.', 2)+0, '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(kegrefNomor, '.', 3)+0, '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(kegrefNomor, '.', 4)+0, '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(kegrefNomor, '.', 5)+0, '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(kegrefNomor, '.', 6)+0, '.', -1)+0
LIMIT %s, %s
";

$sql['get_count_program_kegiatan']  = "
SELECT
   COUNT(programId) AS `count`
FROM program_ref
LEFT JOIN sub_program
   ON subprogProgramId = programId
LEFT JOIN(
   SELECT subprogProgramId AS id,
   COUNT(DISTINCT subprogId) AS `count`
   FROM sub_program
   GROUP BY subprogProgramId
) AS tmpKegiatan ON tmpKegiatan.id = programId
LEFT JOIN kegiatan_ref
   ON kegrefSubprogId = subprogId
LEFT JOIN(
   SELECT kegrefSubprogId AS id,
      COUNT(DISTINCT kegrefId) AS `count`
   FROM kegiatan_ref
   WHERE kegrefId IS NOT NULL
   GROUP BY kegrefSubprogId
) AS tmpOutput ON tmpOutput.id = subprogId
JOIN tahun_anggaran
   ON thanggarId = programThanggarId
LEFT JOIN(
   SELECT
      kompkegKegrefId AS id,
      COUNT(kompkegKompId) AS `count`
   FROM komponen_kegiatan
   GROUP BY kompkegKegrefId
) AS kompKegiatan ON kompKegiatan.id = kegrefId
WHERE 1 = 1
AND (programThanggarId = %s OR 1 = %s)
AND (programId = '%s' OR 1 = %s)
AND (subprogId = '%s' OR 1 = %s)
AND IFNULL(kegrefNomor, '') LIKE '%s'
AND IFNULL(kegrefNama, '') LIKE '%s'
";

$sql['get_rkakl_kegiatan_by_kode']     = "
SELECT
   rkaklKegiatanId AS id
FROM finansi_ref_rkakl_kegiatan
WHERE 1 = 1
AND TRIM(BOTH FROM rkaklKegiatanKode) = '%s'
LIMIT 0, 1
";

$sql['check_program_ref_by_kode']   = "
SELECT
   COUNT(DISTINCT programId) AS `count`
FROM program_ref
WHERE 1 = 1
AND programNomor = '%s'
AND (programThanggarId = %s OR 1 = %s)
AND (programId != %s OR 1 = %s)
";

$sql['get_rkakl_program']     = "
SELECT
   rkaklProgramId AS `id`,
   CONCAT(rkaklProgramKode, ' - ', rkaklProgramNama) AS `name`
FROM finansi_ref_rkakl_prog
WHERE 1 = 1
";

$sql['do_insert_rkakl_kegiatan']    = "
INSERT INTO finansi_ref_rkakl_kegiatan
SET rkaklKegiatanRkaklProgramId = %s,
rkaklKegiatanKode = '%s',
rkaklKegiatanNama = '%s'
";

$sql['do_insert_program_ref']    = "
INSERT INTO program_ref
SET programNomor = '%s',
programNama = '%s',
programThanggarId = '%s',
programRKAKLKegiatanId = '%s',
programIndikator = '%s',
programStrategi = '%s',
programKebijakan = '%s',
programKodeLabel = NULL,
programRKAKLProgramId = NULL,
programSasaran = NULL,
programSasaranId = NULL
";

$sql['get_program_ref_by_id']    = "
SELECT
   programId AS id,
   programNomor AS kode,
   programNama AS nama,
   programThanggarId AS taId,
   programKodeLabel AS kodeLabel,
   programRKAKLProgramId AS rkaklProgramId,
   programSasaran AS sasaran,
   programIndikator AS indikator,
   programStrategi AS strategi,
   programKebijakan AS kebijakan,
   programSasaranId AS sasaranId,
   IF(programRKAKLKegiatanId IS NULL OR programRKAKLKegiatanId != rkaklKegiatanId, rkaklKegiatanId, programRKAKLKegiatanId) AS rkaklKegiatanId,
   rkaklKegiatanKode,
   rkaklKegiatanNama,
   thanggarNama
FROM program_ref
JOIN tahun_anggaran
   ON thanggarId = programThanggarId
LEFT JOIN finansi_ref_rkakl_prog
   ON rkaklProgramId = programRKAKLProgramId
LEFT JOIN finansi_ref_rkakl_kegiatan
   ON rkaklKegiatanId = programRKAKLKegiatanId
   OR TRIM(BOTH FROM programNomor) = TRIM(BOTH FROM rkaklKegiatanKode)
WHERE 1 = 1
AND programId = %s
LIMIT 0, 1
";

$sql['do_update_rkakl_kegiatan']    = "
UPDATE finansi_ref_rkakl_kegiatan
SET rkaklKegiatanKode = '%s',
rkaklKegiatanNama = '%s'
WHERE rkaklKegiatanId = '%s'
";

$sql['do_update_program_ref']    = "
UPDATE program_ref
SET programNomor = '%s',
programNama = '%s',
programThanggarId = '%s',
programRKAKLKegiatanId = '%s',
programIndikator = '%s',
programStrategi = '%s',
programKebijakan = '%s',
programKodeLabel = NULL,
programRKAKLProgramId = NULL,
programSasaran = NULL,
programSasaranId = NULL
WHERE programId = %s
";

$sql['delete_related_program_ref']     = "
DELETE e, c, a
FROM program_ref AS a
LEFT JOIN finansi_ref_rkakl_kegiatan AS b
   ON b.rkaklKegiatanId = a.programRKAKLKegiatanId
LEFT JOIN sub_program AS c
   ON c.subprogProgramId = a.programId
LEFT JOIN finansi_ref_rkakl_output AS d
   ON d.rkaklOutputId = c.subprogRKAKLOutputId
LEFT JOIN kegiatan_ref AS e
   ON e.kegrefSubprogId = c.subprogId
LEFT JOIN finansi_ref_rkakl_subkegiatan AS f
   ON f.rkaklSubKegiatanId = e.kegrefRkaklSubKegiatanId
WHERE a.programId = %s
";

$sql['delete_related_rkakl_program_ref']  = "
DELETE f, d, b
FROM program_ref AS a
LEFT JOIN finansi_ref_rkakl_kegiatan AS b
   ON b.rkaklKegiatanId = a.programRKAKLKegiatanId
LEFT JOIN sub_program AS c
   ON c.subprogProgramId = a.programId
LEFT JOIN finansi_ref_rkakl_output AS d
   ON d.rkaklOutputId = c.subprogRKAKLOutputId
LEFT JOIN kegiatan_ref AS e
   ON e.kegrefSubprogId = c.subprogId
LEFT JOIN finansi_ref_rkakl_subkegiatan AS f
   ON f.rkaklSubKegiatanId = e.kegrefRkaklSubKegiatanId
WHERE a.programId = %s
";

$sql['check_subprog_by_kode']   = "
SELECT
   COUNT(subprogId) AS `count`
FROM sub_program
JOIN program_ref
   ON programId = subprogProgramId
WHERE 1 = 1
AND subprogNomor = '%s'
AND programId = %s
AND programThanggarId = %s
AND (subprogId != '%s' OR 1 = %s)
";

$sql['get_rkakl_output_by_kode']    = "
SELECT
   rkaklOutputId AS id
FROM finansi_ref_rkakl_output
WHERE 1 = 1
AND TRIM(BOTH FROM rkaklOutputKode) = '%s'
";

$sql['do_insert_rkakl_output']   = "
INSERT INTO finansi_ref_rkakl_output
SET rkaklOutputKegiatanId = '%s',
rkaklOutputKode = '%s',
rkaklOutputNama = '%s',
rkaklOutputUserId = '%s'
";

$sql['do_insert_sub_program']    = "
INSERT INTO sub_program
SET subprogProgramId = '%s',
subprogNomor = '%s',
subprogNama = '%s',
subprogRKAKLOutputId = '%s',
subprogJeniskegId = NULL,
subprogKodeLabel = NULL,
subprogRKAKLKegiatanId = NULL
";

$sql['get_sub_program_by_id']    = "
SELECT
   subprogId AS id,
   subprogNomor AS kode,
   subprogNama AS nama,
   subprogKodeLabel AS kodeLabel,
   programId AS kegiatanId,
   programNomor AS kegiatanKode,
   programNama AS kegiatanNama,
   rkaklOutputId,
   rkaklOutputKode,
   rkaklOutputNama,
   thanggarId,
   thanggarNama
FROM sub_program
JOIN program_ref
   ON programId = subprogProgramId
JOIN tahun_anggaran
   ON thanggarId = programThanggarId
LEFT JOIN finansi_ref_rkakl_output
   ON rkaklOutputId = subprogRKAKLOutputId
WHERE 1 = 1
AND subprogId = %s
LIMIT 0, 1
";

$sql['do_update_sub_program']    = "
UPDATE sub_program
SET subprogProgramId = '%s',
subprogNomor = '%s',
subprogNama = '%s',
subprogRKAKLOutputId = '%s',
subprogJeniskegId = NULL,
subprogKodeLabel = NULL,
subprogRKAKLKegiatanId = NULL
WHERE subprogId = %s
";

$sql['do_update_rkakl_output']   = "
UPDATE finansi_ref_rkakl_output
SET rkaklOutputKegiatanId = '%s',
rkaklOutputKode = '%s',
rkaklOutputNama = '%s',
rkaklOutputUserId = '%s'
WHERE rkaklOutputId = %s
";

$sql['do_delete_related_sub_program']  = "
DELETE e, c
FROM sub_program AS c
LEFT JOIN finansi_ref_rkakl_output AS d
   ON d.rkaklOutputId = c.subprogRKAKLOutputId
LEFT JOIN kegiatan_ref AS e
   ON e.kegrefSubprogId = c.subprogId
LEFT JOIN finansi_ref_rkakl_subkegiatan AS f
   ON f.rkaklSubKegiatanId = e.kegrefRkaklSubKegiatanId
WHERE c.subprogId = '%s'
";

$sql['do_delete_related_rkakl_output'] = "
DELETE f, d
FROM sub_program AS c
LEFT JOIN finansi_ref_rkakl_output AS d
   ON d.rkaklOutputId = c.subprogRKAKLOutputId
LEFT JOIN kegiatan_ref AS e
   ON e.kegrefSubprogId = c.subprogId
LEFT JOIN finansi_ref_rkakl_subkegiatan AS f
   ON f.rkaklSubKegiatanId = e.kegrefRkaklSubKegiatanId
WHERE c.subprogId = %s
";

$sql['get_unit_kerja_ref']    = "
SELECT
   SQL_CALC_FOUND_ROWS
   unitkerjaId AS id,
   unitkerjaKode AS kode,
   unitkerjaNama AS nama,
   unitkerjaNamaPimpinan AS namaPimpinan,
   unitkerjaKodeSistem AS kodeSistem,
   unitkerjaParentId AS parentId,
   IFNULL(child.count, 0) AS child
FROM
   `unit_kerja_ref`
   JOIN (
      SELECT unitkerjaId AS id,
      CASE
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN unitkerjaKodeSistem
      END AS kode
      FROM unit_kerja_ref
   ) AS tmp ON tmp.id = unitkerjaId
   LEFT JOIN tipe_unit_kerja_ref
      ON tipeunitId = unitkerjaTipeunitId
   LEFT JOIN(
      SELECT
         unitkerjaParentId AS id,
         COUNT(unitkerjaId) AS `count`
      FROM unit_kerja_ref
      GROUP BY unitkerjaParentId
   ) AS child ON child.id = unitkerjaId
WHERE 1 = 1
ORDER BY SUBSTRING_INDEX(tmp.kode, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 5), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 6), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 7), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 8), '.', -1)+0
";

$sql['check_kegiatan_ref']    = "
SELECT
   COUNT(kegrefId) AS `count`
FROM kegiatan_ref
JOIN sub_program
   ON subprogId = kegrefSubprogId
JOIN program_ref
   ON programId = subprogProgramId
WHERE 1 = 1
AND kegrefNomor = '%s'
AND (kegrefId != '%s' OR 1 = %s)
AND (kegrefSubprogId = '%s' OR 1 = %s)
AND programThanggarId = %s
";

$sql['get_rkakl_sub_kegiatan']   = "
SELECT
   rkaklSubKegiatanId AS `id`
FROM finansi_ref_rkakl_subkegiatan
WHERE 1 = 1
AND rkaklSubKegiatanKode = '%s'
";

$sql['insert_rkakl_sub_kegiatan']   = "
INSERT INTO finansi_ref_rkakl_subkegiatan
SET rkaklSubKegiatanKode = '%s',
rkaklSubKegiatanNama = '%s'
";

$sql['do_insert_kegiatan_ref']   = "
INSERT INTO kegiatan_ref
SET kegrefSubprogId = '%s',
kegrefNomor = '%s',
kegrefNama = '%s',
kegrefRkaklSubKegiatanId = '%s',
kegregIkId = NULL,
kegrefLabelKode = NULL
";

$sql['do_insert_keg_ref_unit']   = "
INSERT INTO finansi_pa_kegiatan_ref_unit_kerja
SET kegrefId = '%s',
unitkerjaId = '%s'
";

$sql['get_kegiatan_ref_by_id']   = "
SELECT
   kegrefId AS id,
   kegrefNomor AS kode,
   kegrefNama AS nama,
   rkaklSubKegiatanId,
   rkaklSubKegiatanKode,
   rkaklSubKegiatanNama,
   subprogId AS outputId,
   subprogNomor AS outputKode,
   subprogNama AS outputNama,
   programId AS kegiatanId,
   programNomor AS kegiatanKode,
   programNama AS kegiatanNama,
   thanggarId,
   thanggarNama
FROM kegiatan_ref
LEFT JOIN finansi_ref_rkakl_subkegiatan
   ON rkaklSubKegiatanId = kegrefRkaklSubKegiatanId
JOIN sub_program
   ON subprogId = kegrefSubprogId
JOIN program_ref
   ON programId = subprogProgramId
JOIN tahun_anggaran
   ON thanggarId = programThanggarId
WHERE 1 = 1
AND kegrefId = %s
LIMIT 0, 1
";

$sql['get_keg_ref_unit']   = "
SELECT
   ref.kegrefId,
   ref.unitkerjaId,
   unit.unitkerjaId AS id,
   unit.unitkerjaKode AS kode,
   unit.unitkerjaNama AS nama
FROM finansi_pa_kegiatan_ref_unit_kerja AS ref
JOIN unit_kerja_ref AS unit
   ON unit.unitkerjaId = ref.unitkerjaId
WHERE kegrefId = %s
";

$sql['update_rkakl_sub_kegiatan']   = "
UPDATE finansi_ref_rkakl_subkegiatan
SET rkaklSubKegiatanKode = '%s',
rkaklSubKegiatanNama = '%s'
WHERE rkaklSubKegiatanId = %s
";

$sql['do_delete_keg_ref_unit']   = "
DELETE FROM finansi_pa_kegiatan_ref_unit_kerja WHERE kegrefId = %s
";

$sql['do_update_kegiatan_ref']   = "
UPDATE kegiatan_ref
SET kegrefSubprogId = '%s',
kegrefNomor = '%s',
kegrefNama = '%s',
kegrefRkaklSubKegiatanId = '%s',
kegregIkId = NULL,
kegrefLabelKode = NULL
WHERE kegrefId = %s
";

$sql['do_delete_related_rkakl_subkegiatan']  = "
DELETE FROM finansi_ref_rkakl_subkegiatan WHERE rkaklSubKegiatanId = %s
";

$sql['do_delete_kegiatan_ref']   = "
DELETE FROM kegiatan_ref WHERE kegrefId = %s
";

$sql['get_rkakl_subkegiatan_id'] = "
SELECT b.rkaklSubKegiatanId AS id
FROM kegiatan_ref AS a
LEFT JOIN finansi_ref_rkakl_subkegiatan AS b
   ON b.rkaklSubKegiatanId = a.kegrefRkaklSubKegiatanId
WHERE a.kegrefId = '%s'
";

$sql['get_detail_belanja']    = "
SELECT SQL_CALC_FOUND_ROWS
   kompId AS id,
   kompKode AS kode,
   kompNama AS nama,
   kompNamaSatuan AS satuan,
   kompkegBiaya AS nominal,
   paguBasId AS makId,
   paguBasKode AS makKode,
   paguBasKeterangan AS makNama,
   kegrefId,
   kegrefNomor,
   kegrefNama,
   IFNULL(keg.keg, 0) AS kegiatan
FROM komponen_kegiatan
JOIN kegiatan_ref
   ON kegrefId = kompkegKegrefId
JOIN komponen
   ON kompId = kompkegKompId
LEFT JOIN finansi_ref_pagu_bas
   ON paguBasId = kompMakId
LEFT JOIN (SELECT
   kegdetKegrefId AS id,
   COUNT(kegdetId) AS keg
FROM kegiatan_detail
JOIN kegiatan
   ON kegId = kegdetKegId
GROUP BY kegdetKegrefId) AS keg
   ON keg.id = kegrefId
WHERE kompkegKegrefId = %s
ORDER BY kompKode
LIMIT %s, %s
";

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['do_insert_komponen_kegiatan'] = "
INSERT INTO komponen_kegiatan
SET kompkegKompId = '%s',
   kompkegKegrefId = '%s',
   kompkegBiaya = '%s'
";

$sql['do_insert_komponen_unit_kerja']  = "
INSERT INTO finansi_pa_komponen_unit_kerja
SET kompUnitKompId = '%s',
   kompUnitKegRefId = '%s',
   kompUnitUnitKerjaId = '%s',
   kompUnitNominal = '%s'
";

$sql['get_detail_belanja_detail']   = "
SELECT SQL_CALC_FOUND_ROWS
   kompId AS id,
   kompKode AS kode,
   kompNama AS nama,
   kompNamaSatuan AS satuan,
   kompkegBiaya AS nominal,
   paguBasId AS makId,
   paguBasKode AS makKode,
   paguBasKeterangan AS makNama,
   kegrefId,
   kegrefNomor,
   kegrefNama
FROM komponen_kegiatan
JOIN kegiatan_ref
   ON kegrefId = kompkegKegrefId
JOIN komponen
   ON kompId = kompkegKompId
LEFT JOIN finansi_ref_pagu_bas
   ON paguBasId = kompMakId
WHERE kompkegKegrefId = %s
AND kompkegKompId = %s
LIMIT 0, 1
";

$sql['get_data_komponen_unit']   = "
SELECT SQL_CALC_FOUND_ROWS
   kompUnitKompId AS id,
   kompKode AS kode,
   kompNama AS nama,
   kompUnitKegRefId AS kegrefId,
   kegrefNomor AS kegrefKode,
   kegrefNama,
   kompUnitUnitKerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   kompUnitNominal AS nominal
FROM finansi_pa_komponen_unit_kerja
JOIN kegiatan_ref
   ON kegrefId = kompUnitKegRefId
JOIN komponen
   ON kompId = kompUnitKompId
JOIN unit_kerja_ref
   ON unitkerjaId = kompUnitUnitKerjaId
WHERE 1 = 1
AND kompUnitKegRefId = %s
AND kompUnitKompId = %s
";

$sql['do_update_komponen_kegiatan'] = "
UPDATE komponen_kegiatan
SET kompkegKompId = '%s',
   kompkegKegrefId = '%s',
   kompkegBiaya = '%s'
WHERE kompkegKompId = '%s'
   AND kompkegKegrefId = '%s'
";

$sql['delete_komponen_unit']     = "
DELETE
FROM finansi_pa_komponen_unit_kerja
WHERE kompUnitKegRefId = '%s'
AND kompUnitKompId = '%s'
";

$sql['check_detail_belanja']     = "
SELECT COUNT(kompkegKompId) AS `count`
FROM komponen_kegiatan
WHERE kompkegKompId = '%s'
   AND kompkegKegrefId = '%s'
";

$sql['delete_komponen_kegiatan'] = "
DELETE FROM komponen_kegiatan
WHERE kompkegKompId = '%s'
AND kompkegKegrefId = '%s'
";

$sql['do_bulk_delete_related_komponen_unit'] = "
DELETE
FROM finansi_pa_komponen_unit_kerja
WHERE kompUnitKegRefId = '%s'
";

$sql['check_rkakl_sub_kegiatan']    = "
SELECT COUNT(DISTINCT kegrefId) AS `count` FROM kegiatan_ref WHERE kegrefRkaklSubKegiatanId = '%s'
";
?>