<?php
$sql['get_data_pagu'] = "
SELECT
   paguAnggUnitId AS id,
   (IF(tempUnitId IS NULL,unitkerjaId,tempUnitId)) AS idsatker,
   (IF(tempUnitKode IS NULL,unitkerjaKode,tempUnitKode)) AS kodesatker,
   (IF(tempUnitNama IS NULL,unitkerjaNama,tempUnitNama)) AS satker,
   (IF(tempUnitId IS NULL,'-',unitkerjaId)) AS idunit,
   (IF(tempUnitKode IS NULL,'-',unitkerjaKode)) AS kodeunit,
   (IF(tempUnitNama IS NULL,'-',unitkerjaNama)) AS unit,
   unitkerjaNama AS subUnitNama,
   unitkerjaParentId AS parentId,
   paguAnggUnitNominal AS nominal,
   paguAnggUnitBintang AS bintang,
   paguBasKode AS mak_kode,
   paguBasKeterangan AS mak_nama,
   sumberdanaNama AS sumber_dana,
   paguAnggUnitNominalTersedia AS nominal_tersedia,
   rkaklProgramId AS program_id,
   rkaklProgramKode AS program_kode,
   rkaklProgramNama AS program_nama,
   rkaklKegiatanId AS kegiatan_id,
   rkaklKegiatanKode AS kegiatan_kode,
   rkaklKegiatanNama AS kegiatan_nama,
   rkaklOutputId AS output_id,
   rkaklOutputKode AS output_kode,
   rkaklOutputNama AS output_nama,
   rkaklSubKegiatanId AS sub_keg_id,
   rkaklSubKegiatanKode AS sub_keg_kode,
   rkaklSubKegiatanNama AS sub_keg_nama,
   CONCAT(rkaklProgramKode,' - ',rkaklKegiatanKode,' - ',rkaklOutputKode,' - ',rkaklSubKegiatanKode) AS kode_pagu
FROM unit_kerja_ref
   LEFT JOIN
   (SELECT
      unitkerjaId AS tempUnitId,
      unitkerjaKode AS tempUnitKode,
      unitkerjaNama AS tempUnitNama,
      unitkerjaParentId AS tempParetnId
      FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUniKerja
      ON(unitkerjaParentId=tempUnitId)
JOIN finansi_pagu_anggaran_unit
   ON (unitkerjaId = paguAnggUnitUnitKerjaId)
JOIN finansi_ref_pagu_bas
   ON (paguAnggMakId = paguBasId)
LEFT JOIN finansi_ref_sumber_dana
   ON (paguSumberDana = sumberdanaId)
LEFT JOIN finansi_ref_rkakl_prog
   ON paguAnggUnitProgramId = rkaklProgramId
LEFT JOIN finansi_ref_rkakl_kegiatan
   ON paguAnggUnitKegiatanId = rkaklKegiatanId
LEFT JOIN finansi_ref_rkakl_output
   ON paguAnggUnitOutputId = rkaklOutputId
LEFT JOIN finansi_ref_rkakl_subkegiatan
   ON paguAnggUnitSubKegiatanId = rkaklSubKegiatanId
WHERE 1=1
AND finansi_pagu_anggaran_unit.paguAnggMakId IS NOT NULL
AND (paguAnggUnitThAnggaranId = '%s' OR 1 = %s)
AND ((unitkerjaId = '%s' OR tempUnitId = '%s') OR 1 = %s)
ORDER BY satker,unit, rkaklProgramKode, rkaklKegiatanKode, rkaklOutputKode, rkaklSubKegiatanKode, paguBasKode
LIMIT %s, %s
";

$sql['get_count_data_pagu'] = "
SELECT
   COUNT(paguAnggUnitId) AS total
FROM unit_kerja_ref
LEFT JOIN
(SELECT
   unitkerjaId AS tempUnitId,
   unitkerjaKode AS tempUnitKode,
   unitkerjaNama AS tempUnitNama,
   unitkerjaParentId AS tempParetnId
   FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUniKerja
   ON(unitkerjaParentId=tempUnitId)
JOIN finansi_pagu_anggaran_unit ON (unitkerjaId = paguAnggUnitUnitKerjaId)
JOIN finansi_ref_pagu_bas ON (paguAnggMakId = paguBasId)
LEFT JOIN finansi_ref_sumber_dana ON (paguSumberDana = sumberdanaId)
LEFT JOIN finansi_ref_rkakl_prog ON paguAnggUnitProgramId = rkaklProgramId
LEFT JOIN finansi_ref_rkakl_kegiatan ON paguAnggUnitKegiatanId = rkaklKegiatanId
LEFT JOIN finansi_ref_rkakl_output ON paguAnggUnitOutputId = rkaklOutputId
LEFT JOIN finansi_ref_rkakl_subkegiatan ON paguAnggUnitSubKegiatanId = rkaklSubKegiatanId
WHERE 1=1
AND finansi_pagu_anggaran_unit.paguAnggMakId IS NOT NULL
AND (paguAnggUnitThAnggaranId = '%s' OR 1 = %s)
AND ((unitkerjaId = '%s' OR tempUnitId = '%s') OR 1 = %s)
";

$sql['get_data_pagu_by_id']="
SELECT
   paguAnggUnitId AS id,
   paguAnggUnitUnitKerjaId AS unitpagu_id,
   paguAnggUnitThAnggaranId AS tahun_anggaran,
   paguBasId AS mak_id,
   paguBasKeterangan AS mak_label,
   unitkerjaId AS unitkerja,
   unitkerjaKode AS unitkerjaKode,
   unitkerjaNama AS unitkerja_label,
   unitkerjaNama AS subUnitNama,
   unitkerjaParentId AS parentId,
   paguAnggUnitNominal AS nominal,
   thanggarNama AS tahun_anggaran_label,
   paguSumberDana AS sumber_dana,
   sumberdanaNama AS sumber_dana_label,
   paguAnggUnitBintang AS bintang,
   paguAnggUnitNominalTersedia AS nominal_tersedia,
   rkaklProgramId AS program_id,
   rkaklProgramKode AS program_kode,
   rkaklProgramNama AS program_nama,
   rkaklKegiatanId AS kegiatan_id,
   rkaklKegiatanKode AS kegiatan_kode,
   rkaklKegiatanNama AS kegiatan_nama,
   rkaklOutputId AS output_id,
   rkaklOutputKode AS output_kode,
   rkaklOutputNama AS output_nama,
   rkaklSubKegiatanId AS sub_keg_id,
   rkaklSubKegiatanKode AS sub_keg_kode,
   rkaklSubKegiatanNama AS sub_keg_nama
FROM
   unit_kerja_ref
JOIN finansi_pagu_anggaran_unit ON (unitkerjaId = paguAnggUnitUnitKerjaId)
JOIN finansi_ref_pagu_bas ON (paguAnggMakId = paguBasId)
JOIN tahun_anggaran ON (thanggarId = paguAnggUnitThAnggaranId)
LEFT JOIN finansi_ref_sumber_dana ON paguSumberDana = sumberdanaId
LEFT JOIN finansi_ref_rkakl_prog ON paguAnggUnitProgramId = rkaklProgramId
LEFT JOIN finansi_ref_rkakl_kegiatan ON paguAnggUnitKegiatanId = rkaklKegiatanId
LEFT JOIN finansi_ref_rkakl_output ON paguAnggUnitOutputId = rkaklOutputId
LEFT JOIN finansi_ref_rkakl_subkegiatan ON paguAnggUnitSubKegiatanId = rkaklSubKegiatanId
WHERE paguAnggUnitId = %s
";

$sql['do_add_pagu']="
INSERT INTO finansi_pagu_anggaran_unit(
   paguAnggUnitUnitKerjaId,
   paguAnggUnitThAnggaranId,
   paguAnggUnitPaguBasId,
   paguAnggUnitNominal,
   paguSumberDana,
   paguAnggMakId,
   paguAnggUnitProgramId,
   paguAnggUnitKegiatanId,
   paguAnggUnitOutputId,
   paguAnggUnitSubKegiatanId,
   paguAnggUnitBintang)
VALUES(%s, %s,NULL, %s, %s, %s, %s, %s, %s, %s, %s)
";
$sql['do_update_pagu']="
UPDATE
   finansi_pagu_anggaran_unit
SET
   paguAnggUnitUnitKerjaId=%s,
   paguAnggUnitThAnggaranId=%s,
   paguAnggMakId=%s,
   paguAnggUnitNominal=%s,
   paguSumberDana=%s,
   paguAnggUnitProgramId = %s ,
   paguAnggUnitKegiatanId = %s,
   paguAnggUnitOutputId = %s,
   paguAnggUnitSubKegiatanId = %s,
   paguAnggUnitBintang = %s
WHERE
   paguAnggUnitId=%s
";
//COMBO
$sql['get_combo_tahun_anggaran']="
SELECT
   thanggarId AS id,
   thanggarNama AS `name`
FROM
   tahun_anggaran
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
ORDER BY thanggarNama DESC
";

$sql['get_combo_bas']="
SELECT
   paguBasId AS id,
   paguBasKeterangan AS `name`
FROM
   finansi_ref_pagu_bas
WHERE paguBasParentId = 0
ORDER BY paguBasKode DESC
";

//aktif
$sql['get_tahun_anggaran_aktif']="
   SELECT
      thanggarId as id,
      thanggarNama as name
   FROM
      tahun_anggaran
   WHERE
      thanggarIsAktif='Y'
";

$sql['do_delete_pagu_by_id']="
   DELETE from finansi_pagu_anggaran_unit
   WHERE
      paguAnggUnitId='%s'
";

$sql['do_delete_pagu_by_array_id']="
   DELETE from finansi_pagu_anggaran_unit
   WHERE
      paguAnggUnitId IN ('%s')
";

$sql['do_copy_pagu_naik']="
   INSERT INTO
      finansi_pagu_anggaran_unit (
         paguAnggUnitThAnggaranId,
         paguAnggUnitUnitKerjaId,
         paguAnggUnitPaguBasId,
         paguAnggUnitNominal
      )
   SELECT
      (SELECT thanggarId FROM tahun_anggaran WHERE thanggarId='%s'),
      paguAnggUnitUnitKerjaId,
      paguAnggUnitPaguBasId,
      (SELECT paguAnggUnitNominal + ((paguAnggUnitNominal*%d)/100))
   FROM
      finansi_pagu_anggaran_unit
   WHERE
      paguAnggUnitThAnggaranId = '%s' AND
      paguAnggUnitUnitKerjaId='%s'
";

$sql['do_copy_pagu_turun']="
   INSERT INTO
      finansi_pagu_anggaran_unit (
         paguAnggUnitThAnggaranId,
         paguAnggUnitUnitKerjaId,
         paguAnggUnitPaguBasId,
         paguAnggUnitNominal
      )
   SELECT
      (SELECT thanggarId FROM tahun_anggaran WHERE thanggarId='%s'),
      paguAnggUnitUnitKerjaId,
      paguAnggUnitPaguBasId,
      (SELECT paguAnggUnitNominal - ((paguAnggUnitNominal*%d)/100))
   FROM
      finansi_pagu_anggaran_unit
   WHERE
      paguAnggUnitThAnggaranId = '%s' AND
      paguAnggUnitUnitKerjaId='%s'
";

// cek data pagu by mak
$sql['cek_mak']   = "
SELECT COUNT(DISTINCT paguAnggUnitId) AS total_data FROM finansi_pagu_anggaran_unit
WHERE paguAnggUnitUnitKerjaId = '%s' AND paguAnggUnitThAnggaranId = '%s' AND paguAnggMakId = '%s'
    AND paguAnggUnitProgramId = '%s'
    AND paguAnggUnitKegiatanId = '%s'
    AND paguAnggUnitOutputId = '%s'
    AND paguAnggUnitSubKegiatanId = '%s'
";

// get referensi program kegiatan
$sql['get_program_kegiatan']    = "
SELECT
    rkaklProgramId AS id,
    CONCAT(rkaklProgramKode, ' - ', rkaklProgramNama) AS `name`
FROM finansi_ref_rkakl_prog
ORDER BY id ASC
";

// check visibility
$sql['check_availabelity']  = "
SELECT
    COUNT(DISTINCT `paguAnggUnitId`) `count`
FROM `finansi_pagu_anggaran_unit`
WHERE
    paguAnggUnitThAnggaranId = '%s'
    AND paguAnggUnitProgramId = '%s'
    AND paguAnggUnitKegiatanId = '%s'
    AND paguAnggUnitOutputId = '%s'
    AND paguAnggUnitSubKegiatanId = '%s'
    AND paguAnggUnitUnitKerjaId = '%s'
";

$sql['check_pagu_anggaran']   = "
SELECT (paguAnggNominal - (SELECT IFNULL(SUM(paguAnggUnitNominal), 0)
FROM finansi_pagu_anggaran_unit
WHERE paguAnggUnitUnitKerjaId = %s AND (paguAnggUnitId != '%s' OR 1 = %s))) AS nominal
FROM finansi_pa_pagu_anggaran
WHERE paguAnggUnitKerjaId = %s
";

$sql['count']     = "
SELECT FOUND_ROWS() AS `total`
";

$sql['delete_history_pagu']   = "
DELETE
FROM `finansi_pagu_anggaran_unit_hist`
WHERE paguAnggUnitPaguAnggUnitId = '%s'
";

$sql['get_nominal_tup']    = "
SELECT
   sp2dNominal
FROM
   `finansi_pa_sp2d`
WHERE sp2dSpmId IN
   (SELECT
      `spmId`
   FROM
      `finansi_pa_spm`
      LEFT JOIN finansi_pa_ref_jenis_pembayaran
         ON spmJenisBayarId = jenisPembayaranId
      LEFT JOIN finansi_pa_ref_sifat_pembayaran
         ON spmSifatBayarId = sifatPembayaranId
   WHERE 1 = 1
      AND (LOWER(sifatPembayaranNama) IN ('tambahan uang persediaan (tu)') OR 1 = 0)
      AND (LOWER(jenisPembayaranNama) IN ('pengeluaran transito') OR 1 = 0)
   ) AND sp2dThanggarId = (SELECT
   `thanggarId`
FROM `tahun_anggaran`
WHERE thanggarIsAktif = 'Y'
LIMIT 0, 1)
LIMIT 0, 1
";

$sql['get_mak_tup']     = "
SELECT
   paguBasId AS `makId`
FROM `finansi_ref_pagu_bas`
WHERE paguBasKode = '825113'
LIMIT 0, 1
";

$sql['get_data']        = "
SELECT SQL_CALC_FOUND_ROWS
   paguAnggUnitId,
   paguAnggUnitUnitKerjaId,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN ref.unitkerjaId
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN satker.unitkerjaId
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN fakultas.id
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN jurusan.id
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN prodi.id
   END AS satkerId,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN ref.unitkerjaKode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN satker.unitkerjaKode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN fakultas.kode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN jurusan.kode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN prodi.kode
   END AS satkerKode,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN ref.unitkerjaNama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN satker.unitkerjaNama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN fakultas.nama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN jurusan.nama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN prodi.nama
   END AS satkerNama,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN ref.unitkerjaId
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN fakultas.unitId
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN jurusan.unitId
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN prodi.unitId
   END AS unitId,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN ref.unitkerjaKode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN fakultas.unitKode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN jurusan.unitKode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN prodi.unitKode
   END AS unitKode,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN ref.unitkerjaNama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN fakultas.unitNama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN jurusan.unitNama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN prodi.unitNama
   END AS unitNama,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN fakultas.fakultasId
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN jurusan.fakultasId
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN prodi.fakultasId
   END AS fakultasId,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN fakultas.fakultasKode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN jurusan.fakultasKode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN prodi.fakultasKode
   END AS fakultasKode,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN fakultas.fakultasNama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN jurusan.fakultasNama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN prodi.fakultasNama
   END AS fakultasNama,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN jurusan.jurusanId
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN prodi.jurusanId
   END AS jurusanId,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN jurusan.jurusanKode
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN prodi.jurusanKode
   END AS jurusanKode,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN jurusan.jurusanNama
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN prodi.jurusanNama
   END AS jurusanNama,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN prodi.prodiId
   END AS prodiId,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN prodi.prodiId
   END AS prodiId,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN prodi.prodiKode
   END AS prodiKode,
   CASE
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN NULL
      WHEN ref.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN prodi.prodiNama
   END AS prodiNama,
   rkaklProgramId AS programId,
   rkaklProgramKode AS programKode,
   rkaklProgramNama AS programNama,
   rkaklKegiatanId AS kegiatanId,
   rkaklKegiatanKode AS kegiatanKode,
   rkaklKegiatanNama AS kegiatanNama,
   rkaklOutputId AS outputId,
   rkaklOutputKode AS outputKode,
   rkaklOutputNama AS outputNama,
   rkaklSubKegiatanId AS subKegiatanId,
   rkaklSubKegiatanKode AS subKegiatanKode,
   rkaklSubKegiatanNama AS subKegiatanNama,
   paguBasId AS makId,
   paguBasKode AS makKode,
   paguBasKeterangan AS makNama,
   thanggarId,
   thanggarNama,
   sumberdanaNama,
   paguAnggUnitNominal AS nominal,
   paguAnggUnitNominalTersedia AS nominalTersedia,
   CONVERT(IFNULL(pok.nominal, 0), DECIMAL(20,2)) AS nominalPok,
   IF(pok.id IS NULL, TRUE, FALSE) AS pok,
   CONCAT(rkaklProgramKode, '-', rkaklKegiatanKode, '-', rkaklOutputKode, '-', rkaklSubKegiatanKode) AS paguKode,
   paguAnggUnitBintang AS `status`
FROM finansi_pagu_anggaran_unit
LEFT JOIN unit_kerja_ref AS ref
   ON ref.unitkerjaId = paguAnggUnitUnitKerjaId
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
   ) AS tmp_unit ON tmp_unit.id = ref.unitkerjaId
LEFT JOIN unit_kerja_ref AS satker
   ON satker.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$'
   AND satker.unitkerjaId = ref.unitkerjaParentId
LEFT JOIN (
   SELECT
      satker.unitkerjaId AS id,
      satker.unitkerjaKodeSistem AS kodeSistem,
      satker.unitkerjaKode AS kode,
      satker.unitkerjaNama AS nama,
      unit.unitkerjaId AS unitId,
      unit.unitkerjaKodeSistem AS unitKodeSistem,
      unit.unitkerjaKode AS unitKode,
      unit.unitkerjaNama AS unitNama,
      fakultas.unitkerjaId AS fakultasId,
      fakultas.unitkerjaKodeSistem AS fakultasKodeSistem,
      fakultas.unitkerjaKode AS fakultasKode,
      fakultas.unitkerjaNama AS fakultasNama
   FROM unit_kerja_ref AS satker
      JOIN unit_kerja_ref AS unit
         ON unit.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$'
         AND unit.unitkerjaParentId = satker.unitkerjaId
      LEFT JOIN unit_kerja_ref AS fakultas
         ON fakultas.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$'
         AND fakultas.unitkerjaParentId = unit.unitkerjaId
   WHERE satker.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$'
) AS fakultas ON fakultas.fakultasId = paguAnggUnitUnitKerjaId
LEFT JOIN(
SELECT
   satker.unitkerjaId AS id,
   satker.unitkerjaKodeSistem AS kodeSistem,
   satker.unitkerjaKode AS kode,
   satker.unitkerjaNama AS nama,
   unit.unitkerjaId AS unitId,
   unit.unitkerjaKodeSistem AS unitKodeSistem,
   unit.unitkerjaKode AS unitKode,
   unit.unitkerjaNama AS unitNama,
   fakultas.unitkerjaId AS fakultasId,
   fakultas.unitkerjaKodeSistem AS fakultasKodeSistem,
   fakultas.unitkerjaKode AS fakultasKode,
   fakultas.unitkerjaNama AS fakultasNama,
   jurusan.unitkerjaId AS jurusanId,
   jurusan.unitkerjaKodeSistem AS jurusanKodeSistem,
   jurusan.unitkerjaKode AS jurusanKode,
   jurusan.unitkerjaNama AS jurusanNama
FROM unit_kerja_ref AS satker
   JOIN unit_kerja_ref AS unit
      ON unit.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$'
      AND unit.unitkerjaParentId = satker.unitkerjaId
   LEFT JOIN unit_kerja_ref AS fakultas
      ON fakultas.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$'
      AND fakultas.unitkerjaParentId = unit.unitkerjaId
   LEFT JOIN unit_kerja_ref AS jurusan
      ON jurusan.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$'
      AND jurusan.unitkerjaParentId = fakultas.unitkerjaId
WHERE satker.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$'
) AS jurusan ON jurusan.jurusanId = paguAnggUnitUnitKerjaId
LEFT JOIN(
SELECT
   satker.unitkerjaId AS id,
   satker.unitkerjaKodeSistem AS kodeSistem,
   satker.unitkerjaKode AS kode,
   satker.unitkerjaNama AS nama,
   unit.unitkerjaId AS unitId,
   unit.unitkerjaKodeSistem AS unitKodeSistem,
   unit.unitkerjaKode AS unitKode,
   unit.unitkerjaNama AS unitNama,
   fakultas.unitkerjaId AS fakultasId,
   fakultas.unitkerjaKodeSistem AS fakultasKodeSistem,
   fakultas.unitkerjaKode AS fakultasKode,
   fakultas.unitkerjaNama AS fakultasNama,
   jurusan.unitkerjaId AS jurusanId,
   jurusan.unitkerjaKodeSistem AS jurusanKodeSistem,
   jurusan.unitkerjaKode AS jurusanKode,
   jurusan.unitkerjaNama AS jurusanNama,
   prodi.unitkerjaId AS prodiId,
   prodi.unitkerjaKodeSistem AS prodiKodeSistem,
   prodi.unitkerjaKode AS prodiKode,
   prodi.unitkerjaNama AS prodiNama
FROM unit_kerja_ref AS satker
   JOIN unit_kerja_ref AS unit
      ON unit.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$'
      AND unit.unitkerjaParentId = satker.unitkerjaId
   LEFT JOIN unit_kerja_ref AS fakultas
      ON fakultas.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$'
      AND fakultas.unitkerjaParentId = unit.unitkerjaId
   LEFT JOIN unit_kerja_ref AS jurusan
      ON jurusan.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$'
      AND jurusan.unitkerjaParentId = fakultas.unitkerjaId
   LEFT JOIN unit_kerja_ref AS prodi
      ON prodi.unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$'
      AND prodi.unitkerjaParentId = jurusan.unitkerjaId
WHERE satker.unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$'
) AS prodi ON prodi.prodiId = paguAnggUnitUnitKerjaId
JOIN finansi_ref_rkakl_prog
   ON rkaklProgramId = paguAnggUnitProgramId
JOIN finansi_ref_rkakl_kegiatan
   ON rkaklKegiatanId = paguAnggUnitKegiatanId
JOIN finansi_ref_rkakl_output
   ON rkaklOutputId = paguAnggUnitOutputId
JOIN finansi_ref_rkakl_subkegiatan
   ON rkaklSubKegiatanId = paguAnggUnitSubKegiatanId
JOIN finansi_ref_pagu_bas
   ON paguBasId = paguAnggMakId
JOIN tahun_anggaran
   ON thanggarId = paguAnggUnitThAnggaranId
LEFT JOIN finansi_ref_sumber_dana
   ON sumberdanaId = paguSumberDana
LEFT JOIN(
SELECT paguAnggUnitPaguAnggUnitId AS id,
   SUM(paguAnggUnitNominal) AS nominal
FROM finansi_pagu_anggaran_unit_hist
GROUP BY paguAnggUnitPaguAnggUnitId
) AS pok ON pok.id = paguAnggUnitId
WHERE 1 = 1
AND paguAnggMakId IS NOT NULL
AND (paguAnggUnitThAnggaranId = '%s' OR 1 = %s)
AND (paguAnggUnitProgramId = '%s' OR 1 = %s)
AND (paguAnggUnitKegiatanId = '%s' OR 1 = %s)
AND (paguAnggUnitOutputId = '%s' OR 1 = %s)
AND (paguAnggUnitSubKegiatanId = '%s' OR 1 = %s)
AND (paguAnggMakId = '%s' OR 1 = %s)
AND paguAnggUnitUnitKerjaId IN (
   SELECT unitkerjaId
   FROM unit_kerja_ref
   WHERE 1 = 1
   AND CASE (SELECT `roleName` AS role_name
      FROM `gtfw_role`
         JOIN user_unit_kerja
            ON (user_unit_kerja.`userunitkerjaRoleId` = gtfw_role.`roleId`)
      WHERE user_unit_kerja.`userunitkerjaUserId` = %s)
         WHEN 'Administrator' THEN (1 = 1 AND (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s))
      WHEN 'OperatorUnit' THEN (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
      WHEN 'OperatorSubUnit' THEN  unitkerjaId = %s
   END
)
ORDER BY SUBSTRING_INDEX(tmp_unit.kode, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 5), '.', -1)+0,
rkaklProgramId, rkaklKegiatanId, rkaklOutputId, rkaklSubKegiatanId, paguBasId, thanggarNama
LIMIT %s, %s
";

$sql['check_availabelity_pagu']     = "
SELECT
   COUNT(paguAnggUnitId) AS `count`,
   paguAnggUnitId AS id
FROM finansi_pagu_anggaran_unit
WHERE 1 = 1
AND (paguAnggUnitId != '%s' OR 1 = %s)
AND (paguAnggUnitThAnggaranId = '%s' OR 1 = %s)
AND (paguAnggUnitUnitKerjaId = '%s' OR 1 = %s)
AND (paguAnggUnitProgramId = '%s' OR 1 = %s)
AND (paguAnggUnitKegiatanId = '%s' OR 1 = %s)
AND (paguAnggUnitOutputId = '%s' OR 1 = %s)
AND (paguAnggUnitSubKegiatanId = '%s' OR 1 = %s)
AND (paguAnggMakId = '%s' OR 1 = %s)
";

$sql['check_pagu_anggaran_unit']    = "
SELECT
   COUNT(paguAnggId) AS `count`,
   paguAnggNominal AS nominal,
   paguAnggNominal - SUM(IFNULL(pagu.nominal,0)) AS budget,
   SUM(IFNULL(pagu.nominal,0)) AS nominalPagu
FROM finansi_pa_pagu_anggaran
LEFT JOIN(
   SELECT
      paguAnggUnitThAnggaranId AS taId,
      paguAnggUnitUnitKerjaId AS unitId,
      SUM(paguAnggUnitNominal) AS nominal
   FROM finansi_pagu_anggaran_unit
   WHERE 1 = 1
   AND (paguAnggUnitId != %s OR 1 = %s)
   GROUP BY paguAnggUnitThAnggaranId, paguAnggUnitUnitKerjaId
) AS pagu ON pagu.taId = paguAnggThAnggaranId
AND pagu.unitId = paguAnggUnitKerjaId
WHERE 1 = 1
AND (paguAnggUnitKerjaId = '%s' OR 1 = %s)
AND (paguAnggThAnggaranId = '%s' OR 1 = %s)
GROUP BY paguAnggUnitKerjaId
";

$sql['get_data_tahun_anggaran']     = "
SELECT
   thanggarId AS `id`,
   thanggarNama AS `name`,
   thanggarIsAktif AS `status`,
   thanggarBuka AS startDate,
   thanggarTutup AS endDate
FROM tahun_anggaran
WHERE 1 = 1
ORDER BY thanggarIsAktif, thanggarId
";

$sql['get_list_pagu_anggaran']   = "
SELECT
   paguAnggUnitId AS id,
   paguAnggUnitThAnggaranId AS taId,
   thanggarNama AS taNama,
   paguAnggUnitUnitKerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   paguAnggUnitProgramId AS programId,
   rkaklProgramKode AS programKode,
   rkaklProgramNama AS programNama,
   paguAnggUnitKegiatanId AS kegiatanId,
   rkaklKegiatanKode AS kegiatanKode,
   rkaklKegiatanNama AS kegiatanNama,
   paguAnggUnitOutputId AS outputId,
   rkaklOutputKode AS outputKode,
   rkaklOutputNama AS outputNama,
   paguAnggUnitSubKegiatanId AS komponenId,
   rkaklSubKegiatanKode AS komponenKode,
   rkaklSubKegiatanNama AS komponenNama,
   paguBasId AS makId,
   paguBasKode AS makKode,
   paguBasKeterangan AS makNama,
   paguSumberDana AS sumberDana,
   paguAnggUnitNominal AS nominal
FROM finansi_pagu_anggaran_unit
JOIN unit_kerja_ref
   ON unitkerjaId = paguAnggUnitUnitKerjaId
JOIN finansi_ref_rkakl_prog
   ON rkaklProgramId = paguAnggUnitProgramId
JOIN finansi_ref_rkakl_kegiatan
   ON rkaklKegiatanId = paguAnggUnitKegiatanId
JOIN finansi_ref_rkakl_output
   ON rkaklOutputId = paguAnggUnitOutputId
JOIN finansi_ref_rkakl_subkegiatan
   ON rkaklSubKegiatanId = paguAnggUnitSubKegiatanId
JOIN finansi_ref_pagu_bas
   ON paguBasId = paguAnggMakId
JOIN tahun_anggaran
   ON thanggarId = paguAnggUnitThAnggaranId
JOIN (SELECT unitkerjaId AS id,
CASE
   WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
   WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
   WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
   WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0')
   WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN unitkerjaKodeSistem
END AS `code`
FROM unit_kerja_ref) AS tmp
   ON tmp.id = unitkerjaId
WHERE 1 = 1
AND (paguAnggUnitUnitKerjaId = %s OR 1 = %s)
AND (paguAnggUnitThAnggaranId = %s OR 1 = %s)
ORDER BY SUBSTRING_INDEX(tmp.code, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.code, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.code, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.code, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.code, '.', 5), '.', -1)+0
LIMIT 0, 300000
";

$sql['delete_detail_on_copy']    = "
DELETE a FROM finansi_pagu_anggaran_unit_hist AS a
JOIN finansi_pagu_anggaran_unit AS b
ON paguAnggUnitPaguAnggUnitId = paguAnggUnitId
WHERE paguAnggUnitThAnggaranId = %s
AND paguAnggUnitUnitKerjaId = %s
AND paguAnggUnitProgramId = %s
AND paguAnggUnitKegiatanId = %s
AND paguAnggUnitOutputId = %s
AND paguAnggUnitSubKegiatanId = %s
AND paguAnggMakId = %s
";

$sql['update_pagu_on_copy']   = "
UPDATE finansi_pagu_anggaran_unit
SET paguAnggUnitNominal = %s,
paguAnggUnitNominalTersedia = 0
WHERE paguAnggUnitThAnggaranId = %s
AND paguAnggUnitUnitKerjaId = %s
AND paguAnggUnitProgramId = %s
AND paguAnggUnitKegiatanId = %s
AND paguAnggUnitOutputId = %s
AND paguAnggUnitSubKegiatanId = %s
AND paguAnggMakId = %s
";

$sql['get_periode_tahun_pagu_anggaran']   = "
SELECT
   thanggarId AS `id`,
   thanggarNama AS `name`
FROM tahun_anggaran
WHERE 1 =1
AND (thanggarId NOT IN (SELECT paguAnggUnitThAnggaranId
FROM finansi_pagu_anggaran_unit
JOIN(
SELECT paguAnggUnitPaguAnggUnitId AS id
FROM finansi_pagu_anggaran_unit_hist
GROUP BY paguAnggUnitPaguAnggUnitId
) AS tmpPagu ON tmpPagu.id = paguAnggUnitId) OR 1 = %s)
AND (thanggarId IN (SELECT paguAnggUnitThAnggaranId
FROM finansi_pagu_anggaran_unit
JOIN(
SELECT paguAnggUnitPaguAnggUnitId AS id
FROM finansi_pagu_anggaran_unit_hist
GROUP BY paguAnggUnitPaguAnggUnitId
) AS tmpPagu ON tmpPagu.id = paguAnggUnitId) OR 1 = %s)
ORDER BY thanggarNama ASC
";

/**
* @description rest service
*/
$sql['get_referensi_unit_kerja']    = "
SELECT
   unitkerjaId,
   unitkerjaKode,
   unitkerjaNama
FROM unit_kerja_ref
WHERE 1 = 1
AND unitkerjaKode = '%s'
LIMIT 0, 1
";

$sql['get_referensi_tahun_anggaran']   = "
SELECT
   thanggarId AS id,
   thanggarNama AS `name`
FROM tahun_anggaran
WHERE 1 = 1
AND thanggarNama = '%s'
AND thanggarIsAktif = 'Y'
LIMIT 0, 1
";

$sql['get_referensi_program']    = "
SELECT
   rkaklProgramId AS id,
   rkaklProgramKode AS kode,
   rkaklProgramNama AS `name`
FROM finansi_ref_rkakl_prog
WHERE 1 = 1
AND rkaklProgramKode = '%s'
LIMIT 0, 1
";

$sql['get_referensi_output']     = "
SELECT
   rkaklOutputId AS id,
   rkaklOutputKode AS kode,
   rkaklOutputNama AS `name`,
   rkaklKegiatanId AS kegiatanId,
   rkaklKegiatanKode AS kegiatanKode,
   rkaklKegiatanNama AS kegiatanNama
FROM finansi_ref_rkakl_output
JOIN finansi_ref_rkakl_kegiatan
   ON rkaklKegiatanId = rkaklOutputKegiatanId
WHERE 1 = 1
AND rkaklOutputKode = '%s'
AND rkaklKegiatanKode = '%s'
LIMIT 0, 1
";

$sql['get_referensi_komponen']   = "
SELECT
   rkaklSubKegiatanId AS id,
   rkaklSubKegiatanKode AS kode,
   rkaklSubKegiatanNama AS `name`
FROM finansi_ref_rkakl_subkegiatan
WHERE 1 = 1
AND rkaklSubKegiatanKode = '%s'
LIMIT 0, 1
";

$sql['get_referensi_mak']     = "
SELECT
   paguBasId AS id,
   paguBasKode AS `kode`,
   paguBasKeterangan AS `name`
FROM finansi_ref_pagu_bas
WHERE 1 = 1
AND paguBasKode = '%s'
LIMIT 0, 1
";

$sql['get_referensi_sumber_dana']   = "
SELECT
   sumberdanaId AS id,
   sumberdanaNama AS `name`
FROM finansi_ref_sumber_dana
WHERE 1 = 1
AND sumberdanaNama = '%s'
AND isAktif = 'Y'
LIMIT 0, 1
";

$sql['do_update_pagu_usulan'] = "
UPDATE finansi_pagu_anggaran_unit SET paguAnggUnitNominal = paguAnggUnitNominal+%s WHERE paguAnggUnitId = %s
";
?>