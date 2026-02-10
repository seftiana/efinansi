<?php
// sifat pembayaran
$sql['sifat_pembayaran'] = "
SELECT
   sifatPembayaranId AS id,
   sifatPembayaranKode AS kode,
   sifatPembayaranNama AS nama,
   CONCAT(
      sifatPembayaranKode,
      ' - ',
      sifatPembayaranNama
   ) AS `name`
FROM
   finansi_pa_ref_sifat_pembayaran
ORDER BY sifatPembayaranNama ASC
";

// jenis pembayaran
$sql['jenis_pembayaran'] = "
SELECT
   jenisPembayaranId AS id,
   jenisPembayaranKode AS kode,
   jenisPembayaranNama AS nama,
   CONCAT(
      jenisPembayaranKode,
      ' - ',
      jenisPembayaranNama
   ) AS `name`
FROM
   finansi_pa_ref_jenis_pembayaran
ORDER BY jenisPembayaranNama ASC
";


$sql['get_periode_tahun']     = "
SELECT
   thanggarId AS `id`,
   thanggarNama AS `name`,
   renstraTanggalAwal AS `start`,
   renstraTanggalAkhir AS `end`
FROM tahun_anggaran
JOIN renstra
   ON renstraId = thanggarRenstraId
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
";

$sql['get_dipa'] = "
SELECT
  `dipaId` AS id,
  `dipaNomor` AS dipa_nama,
  `dipaTanggal` AS dipa_tanggal,
  `dipaNominal` AS dipa_nominal
FROM `finansi_pa_dipa`
WHERE dipaIsAktif = 'Y'
LIMIT 0,1
";

$sql['get_data_pengajuan_realisasi']   = "
SELECT
   pengrealId AS id,
   pengrealNomorPengajuan AS nomor,
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   programId,
   programNomor AS programKode,
   programNama,
   subprogId AS kegiatanId,
   subprogNomor AS kegiatanKode,
   subprogNama AS kegiatanNama,
   kegrefId AS subKegiatanId,
   kegrefNomor AS subKegiatanKode,
   kegrefNama AS subKegiatanNama
FROM
   pengajuan_realisasi
   LEFT JOIN kegiatan_detail
      ON pengrealKegdetId = kegdetId
   LEFT JOIN kegiatan_ref
      ON kegdetKegrefId = kegrefId
   LEFT JOIN sub_program
      ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref
      ON subprogProgramId = programId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   LEFT JOIN kegiatan
      ON kegdetKegId = kegId
   LEFT JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
WHERE 1 = 1
   AND pengrealId = %s
LIMIT 0, 1
";

$sql['get_data_realisasi_detail']      = "
SELECT
   SQL_CALC_FOUND_ROWS pengrealdetId AS id,
   rncnpengeluaranId AS paguId,
   pengrealId AS realisasi_id,
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   programId AS progId,
   programNomor AS programKode,
   programNama AS programNama,
   subprogId AS kegiatan_id,
   subprogNomor AS kegiatanKode,
   subprogNama AS kegiatanNama,
   kegrefId AS subKegiatanId,
   kegrefNomor AS subKegiatanKode,
   kegrefNama AS subKegiatanNama,
   paguBasId AS mak,
   paguBasKode AS makKode,
   paguBasKeterangan AS makNama,
   pengrealdetTanggal AS tanggal,
   IFNULL(rp.nominalPagu, 0) AS nominalPagu,
   IFNULL(
      (SELECT
         SUM(pengrealdetNominalPencairan)
      FROM
         pengajuan_realisasi_detil
         JOIN rencana_pengeluaran
            ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
         LEFT JOIN pengajuan_realisasi
            ON pengrealId = pengrealdetPengRealId
         LEFT JOIN kegiatan_detail
            ON pengrealKegdetId = kegdetId
         LEFT JOIN kegiatan_ref
            ON kegdetKegrefId = kegrefId
         LEFT JOIN sub_program
            ON kegrefSubprogId = subprogId
         LEFT JOIN program_ref
            ON subprogProgramId = programId
         LEFT JOIN jenis_kegiatan_ref
            ON subprogJeniskegId = jeniskegId
         LEFT JOIN kegiatan
            ON kegdetKegId = kegId
         LEFT JOIN unit_kerja_ref
            ON kegUnitkerjaId = unitkerjaId
      WHERE 1 = 1
         AND pengrealdetPengRealId IN
         (SELECT
            sppDetRealdetId
         FROM
            finansi_pa_spp_det)
         AND pengrealId != realisasi_id
         AND pengrealdetTanggal <= tanggal
         AND kegThanggarId = taId
         AND unitkerjaId = unitId
         AND programId = progId
         AND subprogId = kegiatan_id
         AND kegrefId = subKegiatanId
         AND rncnpengeluaranMakId = mak),
      0
   ) AS sppLalu,
   pengrealdetNominalPencairan AS nominal
FROM
   pengajuan_realisasi_detil
   JOIN rencana_pengeluaran
      ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
   LEFT JOIN pengajuan_realisasi
      ON pengrealId = pengrealdetPengRealId
   LEFT JOIN kegiatan_detail
      ON pengrealKegdetId = kegdetId
   LEFT JOIN kegiatan_ref
      ON kegdetKegrefId = kegrefId
   LEFT JOIN sub_program
      ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref
      ON subprogProgramId = programId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   LEFT JOIN kegiatan
      ON kegdetKegId = kegId
   LEFT JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
   LEFT JOIN finansi_ref_pagu_bas
      ON paguBasId = rncnpengeluaranMakId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   LEFT JOIN
      (SELECT
         rncnpengeluaranId AS id,
         rncnpengeluaranMakId AS makId,
         rncnpengeluaranKegdetId AS kegiatanDetId,
         IF(
            rncnpengeluaranIsAprove IS NULL
            OR UPPER(rncnpengeluaranIsAprove) != 'YA',
            0,
            rncnpengeluaranSatuanAprove
         ) * rncnpengeluaranKomponenNominalAprove * IF(
            kompFormulaHasil = 0,
            1,
            kompFormulaHasil
         ) AS nominalPagu
      FROM
         rencana_pengeluaran
         LEFT JOIN komponen
            ON kompKode = rncnpengeluaranKomponenKode) AS rp
      ON rp.id = pengrealdetRncnpengeluaranId
WHERE 1 = 1
AND pengrealdetPengRealId = %s
ORDER BY rncnpengeluaranId
";
/**
 * Insert Data SPP
 */
$sql['do_insert_spp']      = "
INSERT INTO finansi_pa_spp SET sppThanggarId = '%s',
sppUnitkerjaId = '%s',
sppNomor =
(SELECT
   CONCAT(LPAD(IFNULL(MAX(SUBSTRING_INDEX(tmp.sppNomor, '/', 1) + 0) + 1, 1), 7, 0),
      '/H46.PPK/SPP/',
      LPAD(MONTH(%s), 2, 0),
      '/',
      YEAR(%s))
FROM
   finansi_pa_spp AS tmp
WHERE 1 = 1
   AND YEAR(tmp.sppTgl) = YEAR(%s)
   AND MONTH(tmp.sppTgl) = MONTH(%s)),
sppTgl = '%s',
sppDipaId = '%s',
sppSifatPembayaran = '%s',
sppJenisPembayaran = '%s',
sppKeperluan = '%s',
sppJenisBelanja = '%s',
sppAtasNama = '%s',
sppAlamat = '%s',
sppRekening = '%s',
sppNilaiSpk = '%s',
sppSpkNomor = '%s',
sppSpkTgl = '%s',
sppNpwp = '%s',
sppTotal = '%s',
sppUserId = '%s'
";

/**
 * Delete Detail SPP
 */
$sql['delete_spp_det']        = "
DELETE FROM finansi_pa_spp_det WHERE sppDetSppId = '%s'
";

/**
 * Insert SPP Detail
 */
$sql['insert_spp_det']  = "
INSERT INTO
   finansi_pa_spp_det (sppDetSppId,sppDetRealdetId,sppDetNominal,sppDetUserId)
   VALUES
   ('%s','%s','%s','%s')
";

/**
 * Get Detil SPP
 */
$sql['get_data_spp']    = "
SELECT
   sppId AS id,
   sppThanggarId AS taId,
   thanggarNama AS taNama,
   sppUnitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   sppNomor AS nomor,
   sppTgl AS tanggal,
   sppDipaId AS dipaId,
   dipaNomor,
   dipaTanggal,
   dipaNominal,
   sppSifatPembayaran AS sifatPembayaranId,
   sifatPembayaranKode,
   sifatPembayaranNama,
   sppJenisPembayaran AS jenisPembayaranId,
   jenisPembayaranKode,
   jenisPembayaranNama,
   sppKeperluan AS keperluan,
   sppJenisBelanja AS jenisBelanja,
   sppAtasNama AS nama,
   sppAlamat AS alamat,
   sppRekening AS rekening,
   sppNilaiSpk AS spkNominal,
   sppSpkNomor AS spkNomor,
   sppSpkTgl AS spkTanggal,
   sppNpwp AS npwp,
   det.realisasiId,
   IFNULL(det.nominal, 0) AS nominal
FROM finansi_pa_spp
JOIN (
SELECT
   sppDetSppId AS id,
   SUM(sppDetNominal) AS nominal,
   pengrealId AS realisasiId
FROM finansi_pa_spp_det
JOIN pengajuan_realisasi_detil
   ON pengrealdetId = sppDetRealdetId
JOIN pengajuan_realisasi
   ON pengrealId = pengrealDetPengrealId
GROUP BY sppDetSppId
) AS det ON det.id = sppId
LEFT JOIN tahun_anggaran
   ON thanggarId = sppThanggarId
LEFT JOIN unit_kerja_ref
   ON unitkerjaId = sppUnitkerjaId
LEFT JOIN finansi_pa_ref_sifat_pembayaran
   ON sifatPembayaranId = sppSifatPembayaran
LEFT JOIN finansi_pa_ref_jenis_pembayaran
   ON jenisPembayaranId = sppJenisPembayaran
LEFT JOIN finansi_pa_dipa
   ON dipaId = sppDipaId
WHERE 1 = 1
AND sppId = %s
LIMIT 0, 1
";

/**
 * Do Update SPP
 */
$sql['do_update_data_spp']    = "
UPDATE finansi_pa_spp
SET sppThanggarId = '%s',
   sppUnitkerjaId = '%s',
   sppSifatPembayaran = '%s',
   sppJenisPembayaran = '%s',
   sppKeperluan = '%s',
   sppJenisBelanja = '%s',
   sppAtasNama = '%s',
   sppAlamat = '%s',
   sppRekening = '%s',
   sppNilaiSpk = '%s',
   sppSpkNomor = '%s',
   sppSpkTgl = '%s',
   sppNpwp = '%s',
   sppTotal = '%s',
   sppUserId = '%s'
WHERE sppId = '%s'
";
// ----------------------------------------------------------------------------------------- //

// getdata
$sql['get_data_by_id'] = "
SELECT SQL_CALC_FOUND_ROWS
   pengrealdetPengRealId AS id,
   pengrealdetPengRealId AS realisasi_id,
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   programId AS progId,
   programNomor AS programId,
   programNama AS programNama,
   subprogId AS kegiatan_id,
   subprogNomor AS kegiatanKode,
   subprogNama AS kegiatanNama,
   kegrefId AS subKegiatanId,
   kegrefNomor AS subKegiatanKode,
   kegrefNama AS subKegiatanNama,
   paguBasId AS mak,
   paguBasKode AS makKode,
   paguBasKeterangan AS makNama,
   pengrealdetTanggal AS tanggal,
   IFNULL(rp.nominalPagu, 0) AS nominalPagu,
      IFNULL((SELECT
      SUM(pengrealdetNominalPencairan)
   FROM
      pengajuan_realisasi_detil
      JOIN rencana_pengeluaran
         ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
      LEFT JOIN pengajuan_realisasi
         ON pengrealId = pengrealdetPengRealId
      LEFT JOIN kegiatan_detail
         ON pengrealKegdetId = kegdetId
      LEFT JOIN kegiatan_ref
         ON kegdetKegrefId = kegrefId
      LEFT JOIN sub_program
         ON kegrefSubprogId = subprogId
      LEFT JOIN program_ref
         ON subprogProgramId = programId
      LEFT JOIN jenis_kegiatan_ref
         ON subprogJeniskegId = jeniskegId
      LEFT JOIN kegiatan
         ON kegdetKegId = kegId
      LEFT JOIN unit_kerja_ref
         ON kegUnitkerjaId = unitkerjaId
   WHERE 1 = 1
   AND pengrealdetPengRealId IN (SELECT sppDetRealdetId FROM finansi_pa_spp_det)
   AND pengrealId != realisasi_id
   AND pengrealdetTanggal <= tanggal
   AND kegThanggarId = taId
   AND unitkerjaId = unitId
   AND programId = progId
   AND subprogId = kegiatan_id
   AND kegrefId = subKegiatanId
   AND rncnpengeluaranMakId = mak
   ), 0) AS sppLalu,
   pengrealdetNominalPencairan AS nominal
FROM
   pengajuan_realisasi_detil
   JOIN rencana_pengeluaran
      ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
   LEFT JOIN pengajuan_realisasi
      ON pengrealId = pengrealdetPengRealId
   LEFT JOIN kegiatan_detail
      ON pengrealKegdetId = kegdetId
   LEFT JOIN kegiatan_ref
      ON kegdetKegrefId = kegrefId
   LEFT JOIN sub_program
      ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref
      ON subprogProgramId = programId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   LEFT JOIN kegiatan
      ON kegdetKegId = kegId
   LEFT JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
   LEFT JOIN finansi_ref_pagu_bas
      ON paguBasId = rncnpengeluaranMakId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   LEFT JOIN (
      SELECT
         rncnpengeluaranId AS id,
         rncnpengeluaranMakId AS makId,
         rncnpengeluaranKegdetId AS kegiatanDetId,
         IF(
            rncnpengeluaranIsAprove IS NULL
            OR UPPER(rncnpengeluaranIsAprove) != 'YA',
            0,
            rncnpengeluaranSatuanAprove
         ) * rncnpengeluaranKomponenNominalAprove * IF(
            kompFormulaHasil = 0,
            1,
            kompFormulaHasil
         ) AS nominalPagu
      FROM
         rencana_pengeluaran
         LEFT JOIN komponen
            ON kompKode = rncnpengeluaranKomponenKode
   ) AS rp ON rp.id = pengrealdetRncnpengeluaranId
WHERE pengrealdetPengRealId = '10'
";


// get detail spp
$sql['get_detil_spp'] ="
SELECT SQL_CALC_FOUND_ROWS
   sppId,
   thanggarId AS taId,
   pengrealdetTanggal AS tanggal,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   pengrealdetPengRealId AS realisasi_id,
   paguBasId AS mak,
   paguBasKode AS makKode,
   paguBasKeterangan AS makNama,
   programId AS progId,
   programNomor AS programKode,
   programNama AS programNama,
   subprogId AS kegiatan_id,
   IFNULL(subprogNomor, '') AS kegiatanKode,
   CONCAT(IFNULL(subprogNama, ''),' (',jeniskegNama,')') AS kegiatanNama,
   kegrefId AS subKegiatanId,
   IFNULL(kegrefNomor, '') AS subKegiatanKode,
   kegrefNama AS subKegiatanNama,
   IFNULL(rp.nominalPagu, 0) AS nominalPagu,
   IFNULL((SELECT
      SUM(pengrealdetNominalPencairan)
   FROM
      pengajuan_realisasi_detil
      JOIN rencana_pengeluaran
         ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
      LEFT JOIN pengajuan_realisasi
         ON pengrealId = pengrealdetPengRealId
      LEFT JOIN kegiatan_detail
         ON pengrealKegdetId = kegdetId
      LEFT JOIN kegiatan_ref
         ON kegdetKegrefId = kegrefId
      LEFT JOIN sub_program
         ON kegrefSubprogId = subprogId
      LEFT JOIN program_ref
         ON subprogProgramId = programId
      LEFT JOIN jenis_kegiatan_ref
         ON subprogJeniskegId = jeniskegId
      LEFT JOIN kegiatan
         ON kegdetKegId = kegId
      LEFT JOIN unit_kerja_ref
         ON kegUnitkerjaId = unitkerjaId
   WHERE 1 = 1
   AND pengrealdetPengRealId IN (SELECT sppDetRealdetId FROM finansi_pa_spp_det)
   AND pengrealId != realisasi_id
   AND pengrealdetTanggal <= tanggal
   AND kegThanggarId = taId
   AND unitkerjaId = unitId
   AND programId = progId
   AND subprogId = kegiatan_id
   AND kegrefId = subKegiatanId
   AND rncnpengeluaranMakId = mak
   ), 0) AS sppLalu,
   sppDetNominal AS sppIni
FROM
   finansi_pa_spp
   JOIN finansi_pa_spp_det
      ON sppDetSppId = sppId
   JOIN pengajuan_realisasi_detil
      ON pengrealdetId = sppDetRealdetId
   LEFT JOIN (
      SELECT
         rncnpengeluaranId AS id,
         rncnpengeluaranMakId AS makId,
         rncnpengeluaranKegdetId AS kegiatanDetId,
         IF(
            rncnpengeluaranIsAprove IS NULL
            OR UPPER(rncnpengeluaranIsAprove) != 'YA',
            0,
            rncnpengeluaranSatuanAprove
         ) * rncnpengeluaranKomponenNominalAprove * IF(
            kompFormulaHasil = 0,
            1,
            kompFormulaHasil
         ) AS nominalPagu
      FROM
         rencana_pengeluaran
         LEFT JOIN komponen
            ON kompKode = rncnpengeluaranKomponenKode
   ) AS rp ON rp.id = pengrealdetRncnpengeluaranId
   JOIN finansi_ref_pagu_bas
      ON paguBasId = rp.makId
   LEFT JOIN kegiatan_detail
      ON kegdetId = rp.kegiatanDetId
   LEFT JOIN kegiatan_ref
      ON kegdetKegrefId = kegrefId
   LEFT JOIN sub_program
      ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref
      ON subprogProgramId = programId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   LEFT JOIN kegiatan
      ON kegdetKegId = kegId
   LEFT JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
WHERE 1 = 1
   AND sppId = %s
";

$sql['delete_spp']   = "
DELETE FROM finansi_pa_spp WHERE sppId = '%s'
";
?>