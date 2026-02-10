<?php

$sql['get_count_detail_belanja'] ="
SELECT 
COUNT(rpeng.`rncnpengeluaranId`) AS total
FROM rencana_pengeluaran rpeng
WHERE
rpeng.`rncnpengeluaranKegdetId` = %s
";

$sql['set_date']           = "
SELECT
   MIN(thanggarBuka) AS startDate,
   MAX(thanggarTutup) AS endDate
FROM tahun_anggaran
WHERE 1 = 1
AND thanggarIsAktif = 'Y'
OR thanggarIsOpen = 'Y'
";

$sql['get_periode_tahun']  = "
SELECT
   thanggarId AS `id`,
   thanggarNama AS `name`,
   thanggarBuka AS startDate,
   thanggarTutup AS endDate
FROM tahun_anggaran
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
ORDER BY thanggarNama DESC
";

//COMBO
$sql['get_combo_jenis_kegiatan']    = "
SELECT
   `jeniskegId` AS id,
   `jeniskegNama` AS `name`
FROM `jenis_kegiatan_ref`
ORDER BY jeniskegNama ASC
";

$sql['get_combo_prioritas']      = "
SELECT
   prioritasId  as id,
   prioritasNama as name
FROM
   prioritas_ref
ORDER BY prioritasId ASC
";

// Combo tahun anggaran aktif atau open
$sql['get_combo_tahun_anggaran_input']  = "
SELECT
   thanggarId AS id,
   thanggarNama AS `name`,
   thanggarBuka AS tabuka,
   thanggarTutup AS tatutup
FROM
   tahun_anggaran
WHERE thanggarIsAktif = 'Y'
   OR thanggarIsOpen = 'Y'
ORDER BY thanggarNama DESC
";


$sql['get_tahun_anggaran_by_id']  = "
SELECT
    thanggarId AS id,
    thanggarNama AS `name`,
    thanggarBuka AS tabuka,
    thanggarTutup AS tatutup
FROM
   tahun_anggaran
WHERE
thanggarId = '%s'
";

$sql['count']              = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']           = "
SELECT
   SQL_CALC_FOUND_ROWS kegdetId AS id,
   kegId,
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   kegProgramId AS programId,
   programNomor AS programKode,
   programNama AS programNama,
   subprogId AS kegiatanId,
   subprogNomor AS kegiatanKode,
   subprogNama AS kegiatanNama,
   kegrefId AS subKegiatanId,
   kegrefNomor AS subKegiatanKode,
   kegrefNama AS subKegiatanNama,
   UPPER(IFNULL(jeniskegNama, 'rutin')) AS jenisKegiatan,
   UPPER(kegdetIsAprove) AS `approval`,
   kegdetPrioritasId AS prioritasId,
   prioritasNama AS prioritas,
   kegLatarBelakang AS latarBelakang,
   thanggarIsAktif AS taAktif,
   thanggarIsOpen AS taOpen,
   IF(kegdetDeskripsi IS NULL OR kegdetDeskripsi = '','-',kegdetDeskripsi) AS deskripsi,
   CONVERT(IFNULL(rp.nominal, 0), DECIMAL (20, 2)) AS nominal,
   IF(rp.id IS NULL, 'NO', 'YES') AS rkat,
   MONTH(kegdetWaktuMulaiPelaksanaan) AS bulan
FROM
   kegiatan_detail
   JOIN kegiatan
      ON kegId = kegdetKegId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   JOIN unit_kerja_ref
      ON unitkerjaId = kegUnitkerjaId
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
   ) AS tmp_unit ON tmp_unit.id = unitkerjaId
   JOIN program_ref
      ON programId = kegProgramId
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN sub_program
      ON subprogId = kegrefSubProgId
   LEFT JOIN jenis_kegiatan_ref
      ON jeniskegId = subprogJeniskegId
   LEFT JOIN prioritas_ref
      ON kegdetPrioritasId = prioritasId
   LEFT JOIN
      (SELECT
         rncnpengeluaranKegdetId AS id,
         IF(rncnpengeluaranIsAprove = 'Ya', SUM(rncnpengeluaranKomponenTotalAprove * IF(kompFormulaHasil = '0' OR kompFormulaHasil IS NULL, 1, kompFormulaHasil)), SUM(rncnpengeluaranSatuan * rncnpengeluaranKomponenNominal * IF(kompFormulaHasil = '0' OR kompFormulaHasil IS NULL, 1, kompFormulaHasil))) AS nominal
      FROM
         rencana_pengeluaran
         LEFT JOIN komponen
            ON kompKode = rncnpengeluaranKomponenKode
      GROUP BY rncnpengeluaranKegdetId) AS rp
      ON rp.id = kegdetId
WHERE 1 = 1
   AND kegrefNomor LIKE '%s'
   AND kegrefNama LIKE '%s'
   AND kegThanggarId = %s
   AND (programId = %s OR 1 = %s)
   AND (IFNULL(subprogJeniskegId, 1) = %s OR 1 = %s)
   AND (kegdetPrioritasId = %s OR 1 = %s)
   AND kegUnitkerjaId IN
   (SELECT
      unitkerjaId
   FROM
      unit_kerja_ref
   WHERE 1 = 1
      AND SUBSTR(`unitkerjaKodeSistem`,1,
         (SELECT
            LENGTH(CONCAT(`unitkerjaKodeSistem`, '.'))
         FROM
            unit_kerja_ref
         WHERE `unitkerjaId` = '%s')
      ) =
      (SELECT
         CONCAT(`unitkerjaKodeSistem`, '.')
      FROM
         unit_kerja_ref
      WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
     AND
     (MONTH(kegdetWaktuMulaiPelaksanaan) = %s OR %s ) 
 ORDER BY bulan ASC,programId, subprogId, kegrefId
 LIMIT %s, %s
";

$sql['get_data_kegiatan_detail'] = "
SELECT
   kegdetId AS id,
   kegId,
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   kegProgramId AS programId,
   programNomor AS programKode,
   programNama AS programNama,
   subprogId AS kegiatanId,
   subprogNomor AS kegiatanKode,
   subprogNama AS kegiatanNama,
   kegrefId AS subKegiatanId,
   kegrefNomor AS subKegiatanKode,
   kegrefNama AS subKegiatanNama,
   UPPER(IFNULL(jeniskegNama, 'rutin')) AS jenisKegiatan,
   kegdetPrioritasId AS prioritasId,
   prioritasNama AS prioritas,
   kegLatarBelakang AS latarBelakang,
   IF(kegdetDeskripsi IS NULL OR kegdetDeskripsi = '','-',kegdetDeskripsi) AS deskripsi,
   kegLatarBelakang AS latarBelakang,
   kegIndikator AS indikator,
   kegBaseline AS baseLine,
   kegFinal AS final,
   kegPIC AS namePic,
   kegdetCatatan AS catatan,
   kegdetOutPut AS output,
   kegdetTupoksiId AS tupoksiId,
   tupoksiNama,
   kegdetWaktuMulaiPelaksanaan AS startDate,
   kegdetWaktuSelesaiPelaksanaan AS endDate,
   kegdetPrioritasId AS prioritasId,
   prioritasNama,
   kegdetMasTUK AS masTuk,
   kegdetMasTk AS masTk,
   kegdetKelTUK AS kelTuk,
   kegdetKelTk AS kelTk,
   kegdetIkkId AS ikkId,
   ikkNama,
   kegdetIkuId AS ikuId,
   ikuNama
FROM
   kegiatan_detail
   JOIN kegiatan
      ON kegId = kegdetKegId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   JOIN unit_kerja_ref
      ON unitkerjaId = kegUnitkerjaId
   JOIN program_ref
      ON programId = kegProgramId
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN sub_program
      ON subprogId = kegrefSubProgId
   LEFT JOIN jenis_kegiatan_ref
      ON jeniskegId = subprogJeniskegId
   LEFT JOIN prioritas_ref
      ON kegdetPrioritasId = prioritasId
   LEFT JOIN finansi_pa_ref_ikk
      ON ikkId = kegdetIkkId
   LEFT JOIN finansi_pa_ref_iku
      ON ikuId = kegdetIkuId
   LEFT JOIN finansi_pa_ref_tupoksi
      ON tupoksiId = kegdetTupoksiId
WHERE 1 = 1
   AND kegdetId = '%s'
 ORDER BY programId, subprogId, kegrefId
 LIMIT 0, 1
";

$sql['do_insert_kegiatan']    = "
INSERT INTO kegiatan
SET kegUnitkerjaId = '%s',
   kegProgramRkaklId = NULL,
   kegProgramId = '%s',
   kegLatarBelakang = '%s',
   kegIndikator = '%s',
   kegBaseline = '%s',
   kegFinal = '%s',
   kegThanggarId = '%s',
   kegPimpinanSatuanKerja = NULL,
   kegPimpinanUnitKerja = NULL,
   kegPIC = '%s',
   kegUserId = '%s'
";

$sql['do_insert_kegiatan_detail']   = "
INSERT INTO kegiatan_detail
SET kegdetKegId = '%s',
   kegdetKegrefId = '%s',
   kegdetProgramRkaklId = NULL,
   kegdetDeskripsi = '%s',
   kegdetIsAprove = 'Belum',
   kegdetCatatan = '%s',
   kegdetOutPut = '%s',
   kegdetTupoksiId = IF('%s' = '', NULL, '%s'),
   kegdetWaktuMulaiPelaksanaan = '%s',
   kegdetWaktuSelesaiPelaksanaan = '%s',
   kegdetPrioritasId = '%s',
   kegdetMasTUK = '%s',
   kegdetMasTk = '%s',
   kegdetKelTUK = '%s',
   kegdetKelTk = '%s',
   kegdetRABFile = NULL,
   kegdetIkkId = IF('%s' = '', NULL, '%s'),
   kegdetIkuId = IF('%s' = '', NULL, '%s'),
   kegdetRkaklOutputId = NULL,
   kegdetUserId = '%s'
";

$sql['do_update_kegiatan']    = "
UPDATE kegiatan
SET kegUnitkerjaId = '%s',
   kegProgramRkaklId = NULL,
   kegProgramId = '%s',
   kegLatarBelakang = '%s',
   kegIndikator = '%s',
   kegBaseline = '%s',
   kegFinal = '%s',
   kegThanggarId = '%s',
   kegPimpinanSatuanKerja = NULL,
   kegPimpinanUnitKerja = NULL,
   kegPIC = '%s',
   kegUserId = '%s'
WHERE kegId = %s
";

$sql['do_update_kegiatan_detail']   = "
UPDATE kegiatan_detail
SET kegdetKegId = '%s',
   kegdetKegrefId = '%s',
   kegdetProgramRkaklId = NULL,
   kegdetDeskripsi = '%s',
   kegdetIsAprove = 'Belum',
   kegdetCatatan = '%s',
   kegdetOutPut = '%s',
   kegdetTupoksiId = IF('%s' = '', NULL, '%s'),
   kegdetWaktuMulaiPelaksanaan = '%s',
   kegdetWaktuSelesaiPelaksanaan = '%s',
   kegdetPrioritasId = '%s',
   kegdetMasTUK = '%s',
   kegdetMasTk = '%s',
   kegdetKelTUK = '%s',
   kegdetKelTk = '%s',
   kegdetRABFile = NULL,
   kegdetIkkId = IF('%s' = '', NULL, '%s'),
   kegdetIkuId = IF('%s' = '', NULL, '%s'),
   kegdetRkaklOutputId = NULL,
   kegdetUserId = '%s'
WHERE kegdetId = %s
AND kegdetKegId = %s
";

$sql['delete_kegiatan'] = "
DELETE
FROM `kegiatan`
WHERE `kegId` = '%s'
";

$sql['delete_kegiatan_detil']   = "
DELETE
FROM `kegiatan_detail`
WHERE `kegdetId` = '%s' AND kegdetKegId = '%s'
";

// --------------------------------------------------------------------------------------- //
$sql['get_unit']    = "
SELECT ukr.unitkerjaId AS id,
ukr.unitkerjaNama AS nama,
ukr.unitkerjaKode AS kode,
ukr.unitkerjaKodeSistem AS kodeSistem,
ukr.`unitKerjaJenisId` AS jenis,
ukr.`unitkerjaParentId` AS parent,
ukr.unitkerjaNamaPimpinan AS pimpinan,
(SELECT COUNT(DISTINCT `unitkerjaId`) FROM `unit_kerja_ref` WHERE unitkerjaParentId = ukr.unitkerjaId)
AS child,
usr.`RealName` AS namaUser
FROM user_unit_kerja AS uk
JOIN unit_kerja_ref AS ukr ON uk.userunitkerjaUnitkerjaId = ukr.unitkerjaId
JOIN gtfw_user AS usr ON uk.`userunitkerjaUserId` = usr.`UserId`

WHERE uk.userunitkerjaUserId = %s
";


/**
* untuk mengecek tahun anggaran yang aktif
* */
$sql['check_tahun_anggaran'] = "
   SELECT
      `thanggarId` AS id,
      `thanggarNama` AS nama,
      `thanggarIsAktif` AS is_aktif,
      `thanggarIsOpen` AS is_open,
      `thanggarBuka` AS buka,
      `thanggarTutup` AS tutup
   FROM `tahun_anggaran`
   WHERE `thanggarId` = '%s'
";

$sql['get_combo_tahun_anggaran_all']="
   SELECT
      thanggarId as id,
      thanggarNama as name
   FROM
      tahun_anggaran
   ORDER BY thanggarNama DESC
";

//aktif
$sql['get_tahun_anggaran_aktif']="
   SELECT
      thanggarId as id,
      thanggarNama as name,
      YEAR(thanggarBuka) AS thn_buka,
      (YEAR(thanggarTutup)+1) AS thn_tutup
   FROM
      tahun_anggaran
   WHERE
      thanggarIsAktif='Y'
";

// insert into kegiatan
$sql['insert_into_kegiatan']    = "
INSERT INTO `kegiatan`
         (`kegUnitkerjaId`,
          `kegProgramId`,
          `kegLatarBelakang`,
          `kegIndikator`,
          `kegBaseline`,
          `kegFinal`,
          `kegThanggarId`,
          `kegPimpinanSatuanKerja`,
          `kegPimpinanUnitKerja`,
          `kegPIC`,
          `kegUserId`,
          `kegTglBuat`)
VALUES ('%s',
      '%s',
      '%s',
      '%s',
      '%s',
      '%s',
      '%s',
      '%s',
      '%s',
      '%s',
      '%s',
      NOW())
";

// get last insert id kegiatan
$sql['get_last_id']   = "
SELECT MAX(kegId) AS last_id FROM kegiatan
";

// add detil kegiatan
$sql['do_add_detil_usulan_kegiatan']    = "
INSERT INTO kegiatan_detail SET kegdetKegId = '%s',
kegdetKegrefId = '%s',
kegdetDeskripsi = '%s',
kegdetCatatan = '%s',
kegdetOutPut = '%s',
kegdetWaktuMulaiPelaksanaan = '%s',
kegdetWaktuSelesaiPelaksanaan = '%s',
kegdetPrioritasId = '%s',
kegdetMasTUK = '%s',
kegdetMasTk = '%s',
kegdetKelTUK = '%s',
kegdetKelTk = '%s',
kegdetIkkId = NULLIF('%s', ''),
kegdetIkuId = NULLIF('%s', ''),
kegdetTupoksiId = NULLIF('%s', ''),
kegdetUserId = '%s'
";

$sql['update_kegiatan'] = "
UPDATE `kegiatan`
SET `kegUnitkerjaId` = '%s',
`kegProgramId` = '%s',
`kegLatarBelakang` = '%s',
`kegIndikator` = '%s',
`kegBaseline` = '%s',
`kegFinal` = '%s',
`kegThanggarId` = '%s',
`kegPimpinanSatuanKerja` = '%s',
`kegPimpinanUnitKerja` = '%s',
`kegPIC` = '%s',
`kegUserId` = '%s'
WHERE `kegId` = '%s'
";

$sql['update_kegiatan_detil'] = "
UPDATE kegiatan_detail SET kegdetKegId = '%s',
kegdetKegrefId = '%s',
kegdetDeskripsi = '%s',
kegdetCatatan = '%s',
kegdetOutPut = '%s',
kegdetWaktuMulaiPelaksanaan = '%s',
kegdetWaktuSelesaiPelaksanaan = '%s',
kegdetPrioritasId = '%s',
kegdetMasTUK = '%s',
kegdetMasTk = '%s',
kegdetKelTUK = '%s',
kegdetKelTk = '%s',
kegdetIkkId = NULLIF('%s', ''),
kegdetIkuId = NULLIF('%s', ''),
kegdetTupoksiId = NULLIF('%s', ''),
kegdetUserId = '%s'
WHERE `kegdetId` = '%s'
";

// get data rencana kinerja tahunan kegiatan
/*$sql['get_data']    = "
SELECT
k.kegProgramId AS program_id,
p.programNomor AS kodeprogram,
p.programNama AS program,
kd.kegdetKegId AS kegiatan_id,
sp.`subprogNomor` AS kegiatan_nomor,
sp.subprogNama AS kegiatan,
kd.kegdetKegRefId AS subkegiatan_id,
kr.`kegrefNomor` AS subkegiatan_nomor,
kr.kegrefNama AS subkegiatan,
kd.kegdetId AS id,
sp.subprogId,
kr.kegrefId,
jk.jeniskegNama AS jenis,
kd.kegdetIsAprove AS `approval`,
kd.kegdetPrioritasId AS prioritas_id,
pr.prioritasNama AS prioritas,
ukr.unitkerjaId AS idunit,
ukr.unitkerjaKode AS kodeunit,
ukr.unitkerjaNama AS unit,
k.kegLatarBelakang AS latarbelakang,
ukr.unitkerjaNama AS subUnitNama,
ukr.unitkerjaParentId AS paretnId,
ta.`thanggarIsAktif` AS ta_aktif,
ta.`thanggarIsOpen` AS ta_open,
jk.`jeniskegNama` AS jenis_kegiatan,
kd.kegdetDeskripsi AS deskripsi,
(
      SELECT
         sum(rp.rncnpengeluaranSatuan * rp.rncnpengeluaranKomponenNominal *
            IF(komp.kompFormulaHasil = '0' ,1,IFNULL( komp.kompFormulaHasil,1 ) ))  AS is_approve
      FROM
         rencana_pengeluaran rp
      LEFT JOIN
            komponen komp ON komp.kompKode = rp.rncnpengeluaranKomponenKode
      WHERE
         rp.rncnpengeluaranKegdetId = kd.kegdetId
   ) as nilai
FROM
   kegiatan_detail AS kd
   JOIN kegiatan AS k
      ON (k.kegId = kd.kegdetKegId)
   JOIN unit_kerja_ref AS ukr
      ON ukr.`unitkerjaId` = k.`kegUnitkerjaId`
   JOIN program_ref p
      ON (p.programId = k.kegProgramId)
   JOIN kegiatan_ref kr
      ON (kr.kegRefId = kd.kegdetKegRefId)
   JOIN sub_program AS sp
      ON (sp.subprogId = kr.kegrefSubProgId)
   LEFT JOIN jenis_kegiatan_ref AS jk
      ON (jk.jeniskegId = sp.subprogJeniskegId)
   LEFT JOIN prioritas_ref AS pr
      ON (kd.kegdetPrioritasId = pr.prioritasId)
   LEFT JOIN tahun_anggaran AS ta
      ON ta.`thanggarId` = k.`kegThanggarId`
WHERE ( kr.`kegrefNomor`  LIKE '%s') AND ( kr.kegrefNama LIKE '%s')
   AND p.programThanggarId = '%s'
   AND SUBSTR(ukr.`unitkerjaKodeSistem`,1,LENGTH('%s')) = '%s'
   AND (kd.kegdetPrioritasId  = '%s' OR 1 = '%s')
ORDER BY kodeprogram,kegiatan_nomor,subkegiatan_nomor
LIMIT %s, %s
";*/

/*$sql['count_data']  = "
SELECT
   COUNT(kd.kegdetId) AS total
FROM
   kegiatan_detail AS kd
   JOIN kegiatan AS k
      ON (k.kegId = kd.kegdetKegId)
   JOIN unit_kerja_ref AS ukr
      ON ukr.`unitkerjaId` = k.`kegUnitkerjaId`
   JOIN program_ref p
      ON (p.programId = k.kegProgramId)
   JOIN kegiatan_ref kr
      ON (kr.kegRefId = kd.kegdetKegRefId)
   JOIN sub_program AS sp
      ON (sp.subprogId = kr.kegrefSubProgId)
   LEFT JOIN jenis_kegiatan_ref AS jk
      ON (jk.jeniskegId = sp.subprogJeniskegId)
   LEFT JOIN prioritas_ref AS pr
      ON (kd.kegdetPrioritasId = pr.prioritasId)
   LEFT JOIN tahun_anggaran AS ta
      ON ta.`thanggarId` = k.`kegThanggarId`
WHERE ( kr.`kegrefNomor`  LIKE '%s') AND ( kr.kegrefNama LIKE '%s')
   AND p.programThanggarId = '%s'
   AND SUBSTR(ukr.`unitkerjaKodeSistem`,1,LENGTH('%s')) = '%s'
   AND (kd.kegdetPrioritasId  = '%s' OR 1 = '%s')
";*/

$sql['get_data_by_id']  = "
SELECT
   kd.kegdetId AS id_detil,
   kd.kegdetKegId AS data_id,
   k.kegThanggarId AS tahun_anggaran,
   ta.`thanggarNama` AS tahun_anggaran_label,
   ukr.`unitkerjaKodeSistem` AS kode_sistem,
   k.`kegUnitkerjaId` AS id_unit,
   ukr.`unitkerjaNama` AS nama_unit,
   k.`kegProgramId` AS program,
   p.programNama AS program_nama,
   sp.subprogId AS kegiatan,
   sp.subprogNama AS kegiatan_nama,
   kd.kegdetKegrefId AS sub_kegiatan,
   kr.kegrefNama AS sub_kegiatan_nama,
   kd.`kegdetIkkId` AS ikk_id,
   fikk.`ikkNama` AS ikk,
   kd.`kegdetIkuId` AS iku_id,
   fiku.`ikuNama` AS iku,
   kd.`kegdetTupoksiId` AS tupoksi_id,
   rt.`tupoksiNama` AS tupoksi,
   k.`kegLatarBelakang` AS latar_belakang,
   k.`kegIndikator` AS indikator,
   k.`kegBaseline` AS baseline,
   k.`kegFinal` AS final,
   kd.`kegdetDeskripsi` AS deskripsi,
   kd.`kegdetCatatan` AS catatan,
   kd.`kegdetOutPut` AS output,
   kd.`kegdetMasTUK` AS mastuk,
   kd.`kegdetMasTUK` AS mastk,
   kd.`kegdetMasTUK` AS keltuk,
   kd.`kegdetKelTk` AS keltk,
   k.`kegPimpinanSatuanKerja` AS satker_pimpinan,
   (SELECT
      unitkerjaNama
   FROM
      unit_kerja_ref
   WHERE unitkerjaId = k.`kegPimpinanSatuanKerja`) AS satker_pimpinan_label,
   (SELECT
      unitkerjaNama
   FROM
      unit_kerja_ref
   WHERE unitkerjaId = k.`kegPimpinanUnitKerja`) AS unitkerja_pimpinan_label,
   k.`kegPimpinanUnitKerja` AS unitkerja_pimpinan,
   kd.`kegdetWaktuMulaiPelaksanaan` AS waktu_mulai_pelaksanaan,
   kd.`kegdetWaktuSelesaiPelaksanaan` AS waktu_mulai_seleseai,
   k.`kegPIC` AS nama_pic,
   kd.`kegdetPrioritasId` AS prioritas  ,
   kd.`kegdetUserId` AS user_id
FROM
   kegiatan_detail AS kd
   JOIN kegiatan AS k
      ON (k.kegId = kd.kegdetKegId)
   JOIN unit_kerja_ref AS ukr
      ON ukr.`unitkerjaId` = k.`kegUnitkerjaId`
   JOIN program_ref p
      ON (p.programId = k.kegProgramId)
   JOIN kegiatan_ref kr
      ON (kr.kegRefId = kd.kegdetKegRefId)
   JOIN sub_program AS sp
      ON (sp.subprogId = kr.kegrefSubProgId)
   LEFT JOIN jenis_kegiatan_ref AS jk
      ON (jk.jeniskegId = sp.subprogJeniskegId)
   LEFT JOIN prioritas_ref AS pr
      ON (kd.kegdetPrioritasId = pr.prioritasId)
   LEFT JOIN tahun_anggaran AS ta
      ON ta.`thanggarId` = k.`kegThanggarId`
   LEFT JOIN `finansi_pa_ref_ikk` AS fikk
      ON fikk.`ikkId` = kd.`kegdetIkkId`
   LEFT JOIN `finansi_pa_ref_iku` AS fiku
      ON fiku.`ikuId` = kd.`kegdetIkuId`
   LEFT JOIN `finansi_pa_ref_tupoksi` AS rt
      ON rt.`tupoksiId` = kd.`kegdetTupoksiId`
WHERE
   kd.`kegdetId` = '%s' AND kd.`kegdetKegId` = '%s'
";

$sql['cek_kegiatan'] ="
SELECT
   COUNT(kegId) AS total
FROM kegiatan
WHERE
   kegUnitkerjaId = '%s'
   AND
   kegProgramId = '%s'
   AND
   kegThanggarId = '%s'
";

$sql['get_kegiatan_id'] ="
SELECT
   kegId as keg_id
FROM kegiatan
WHERE
   kegUnitkerjaId = '%s'
   AND
   kegProgramId = '%s'
   AND
   kegThanggarId = '%s'
";
?>
