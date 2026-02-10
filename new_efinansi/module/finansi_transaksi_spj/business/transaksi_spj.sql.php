<?php
/**
 * @package SQL-FILE
 */
$sql['set_tahun_pembukuan']   = "
SET @tahun_pembukuan  = '';
";

$sql['set_tahun_anggaran']    = "
SET @tahun_anggaran = '';
";

$sql['do_set_tahun_pembukuan']   = "
SELECT
   tppId INTO @tahun_pembukuan
FROM tahun_pembukuan_periode
WHERE 1 = 1
AND tppIsBukaBuku = 'Y'
LIMIT 1
";

$sql['do_set_tahun_anggaran']    = "
SELECT
   thanggarId INTO @tahun_anggaran
FROM tahun_anggaran
WHERE 1 = 1
AND thanggarIsAktif = 'Y'
LIMIT 1
";

$sql['get_date_range']  = "
SELECT
   EXTRACT(YEAR FROM IFNULL(MIN(tppTanggalAwal), DATE_ADD(DATE(NOW()),INTERVAL -DAY(DATE(NOW()))+1 DAY))) AS minYear,
   EXTRACT(YEAR FROM IFNULL(MAX(tppTanggalAkhir), DATE(LAST_DAY(DATE(NOW()))))) AS maxYear,
   IFNULL(MIN(tppTanggalAwal), DATE_ADD(DATE(NOW()),INTERVAL -DAY(DATE(NOW()))+1 DAY)) AS tanggalAwal,
   IFNULL(MAX(tppTanggalAkhir), DATE(LAST_DAY(DATE(NOW())))) AS tanggalAkhir
FROM tahun_pembukuan_periode
";

$sql['get_tipe_transaksi'] = "
SELECT
   ttId AS `id`,
   ttNamaTransaksi AS `name`
FROM transaksi_tipe_ref
WHERE 1 = 1
AND ttId NOT IN (2,4)
ORDER BY ttId
";

$sql['get_jenis_transaksi']   = "
SELECT
   transjenId AS `id`,
   transjenNama AS `name`
FROM
   transaksi_jenis_ref
WHERE transjenNama NOT IN (
      'Payroll',
      'Aset',
      'Registrasi',
      'Penyusutan Aset',
      'Pengadaan',
      'Beban ATK',
      'Pengendalian',
      'Pengelolaan Aset'
   )
ORDER BY transjenNama
";

$sql['get_unit_info']   = "
SELECT
   unitkerjaId AS id,
   unitkerjaKodeSistem AS kodeSistem,
   unitkerjaKode AS kode,
   unitkerjaNama AS nama,
   unitkerjaNamaPimpinan AS namaPimpinan,
   unitkerjaParentId AS parentId
FROM unit_kerja_ref
WHERE 1 = 1
AND unitkerjaId = %s
LIMIT 1
";

$sql['do_insert_transaksi']   = "
INSERT INTO transaksi
SET transTtId = '%s',
   transTransjenId = '%s',
   transUnitkerjaId = '%s',
   transTppId = @tahun_pembukuan,
   transThanggarId = @tahun_anggaran,
   transReferensi = @GENERATE_NUMBER,
   transUserId = '%s',
   transTanggal = '%s',
   transTanggalEntri = '%s',
   transDueDate = '%s',
   transCatatan = '%s',
   transNilai = '%s',
   transPenanggungJawabNama = '%s',
   transIsJurnal = 'T'
";

$sql['do_insert_transaksi_spj']  = "
INSERT INTO transaksi_detail_spj
SET transdtspjTransId = '%s',
   transdtspjKegdetId = '%s'
";

$sql['do_add_transaksi_detil_pengembalian_anggaran'] = "
INSERT INTO transaksi_detail_pengembalian(
   transdtpengembalianTransId,
   transdtpengembalianKegdetId,
   transdtpengembalianPengrealId
) VALUES (%s, %s, %s)
";

$sql['do_add_transaksi_detil_pencairan'] = "
INSERT INTO transaksi_detail_pencairan
   (transdtpencairanTransId, transdtpencairanKegdetId, transdtpencairanPengrealId)
VALUES (%s, %s, %s)
";

$sql['do_add_transaksi_detil_anggaran'] = "
INSERT INTO transaksi_detail_anggaran(
   transdtanggarTransId,
   transdtanggarKegdetId,
   transdtanggarPengrealId
) VALUES (%s,%s,%s)
";

$sql['do_save_attachment_transaksi']   = "
INSERT INTO transaksi_file
SET transfileTransId = '%s',
   transfileNama = '%s',
   transfilePath = '%s'
";

$sql['do_save_invoice_transaksi']   = "
INSERT INTO transaksi_invoice
SET transinvoiceNomor = '%s',
   transinvoiceTransId = '%s'
";

$sql['get_files_transaksi']   = "
SELECT
   transfilePath AS `path`,
   transfileNama AS `fileName`
FROM transaksi_file
WHERE 1 = 1
AND transfileTransId = %s
ORDER BY transfileId
";

$sql['get_data_detail']    = "
SELECT
   transId AS id,
   tppId,
   tppTanggalAwal,
   tppTanggalAkhir,
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   transReferensi AS nomorReferensi,
   transTanggal AS tanggal,
   transTanggalEntri AS tanggalEntri,
   transDueDate AS dueDate,
   transCatatan AS keterangan,
   transPenanggungJawabNama AS penanggungJawab,
   transjenId AS transaksiJenisId,
   transjenKode AS transaksiJenisKode,
   transjenNama AS transaksiJenisNama,
   ttId AS transaksiTipeId,
   ttKodeTransaksi AS transaksiTipeKode,
   ttNamaTransaksi AS transaksiTipeNama,
   kegrefNomor AS kode,
   kegrefNama AS nama,
   SUM(pengrealNominalAprove) AS nominalApprove,
   IFNULL(pencairan.realisasiNominal, 0)-transNilai AS nominalPencairan,
   transNilai AS nominal
FROM
   transaksi
   JOIN transaksi_detail_spj
      ON transdtspjTransId = transId
   JOIN tahun_pembukuan_periode
      ON tppId = transTppId
   JOIN tahun_anggaran
      ON thanggarId = transThanggarId
   JOIN unit_kerja_ref
      ON unitkerjaId = transUnitkerjaId
   JOIN kegiatan_detail
      ON kegdetId = transdtspjKegdetId
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN pengajuan_realisasi
      ON pengrealKegdetId = kegdetId
   LEFT JOIN transaksi_tipe_ref
      ON ttId = transTtId
   LEFT JOIN transaksi_jenis_ref
      ON transjenId = transTransjenId
   LEFT JOIN (SELECT
      realisasi.kegiatanId AS kegId,
      realisasi.realisasiId AS realId,
      SUM(realisasi.nominal) AS realisasiNominal
   FROM (SELECT
      kegdetId AS kegiatanId,
      pengrealId AS realisasiId,
      SUM(IFNULL(transNilai,0)) AS nominal
   FROM transaksi
   JOIN transaksi_detail_anggaran
      ON transdtanggarTransId = transId
   JOIN kegiatan_detail
      ON kegdetId = transdtanggarKegdetId
   JOIN pengajuan_realisasi
      ON pengrealKegdetId = kegdetId
      AND pengrealid = transdtanggarPengrealId
   GROUP BY kegdetId, pengrealId
   UNION
   SELECT
      kegdetId AS kegiatanId,
      pengrealId AS realisasiId,
      SUM(IFNULL(transNilai,0)) AS nominal
   FROM transaksi
   JOIN transaksi_detail_pencairan
      ON transdtpencairanTransId = transid
   JOIN kegiatan_detail
      ON kegdetId = transdtpencairanKegdetId
   JOIN pengajuan_realisasi
      ON pengrealKegdetId = kegdetId
      AND pengrealid = transdtpencairanPengrealId
   GROUP BY kegdetId, pengrealId
   UNION
   SELECT
      kegdetId AS kegiatanId,
      pengrealId AS realisasiId,
      SUM(IFNULL(transNilai,0)) AS nominal
   FROM transaksi
   JOIN transaksi_detail_pengembalian
      ON transdtpengembalianTransId = transId
   JOIN kegiatan_detail
      ON kegdetid = transdtpengembalianKegdetId
   JOIN pengajuan_realisasi
      ON pengrealKegdetId = kegdetId
      AND pengrealid = transdtpengembalianPengrealId
   GROUP BY kegdetId, pengrealId
   UNION
   SELECT
      kegdetId AS kegiatanId,
      pengrealId AS realisasiId,
      SUM(IFNULL(transNilai,0)) AS nominal
   FROM transaksi
   JOIN transaksi_detail_realisasi
      ON transdtrealisasiTransId = transId
   JOIN kegiatan_detail
      ON kegdetid = transdtrealisasiKegdetId
   JOIN pengajuan_realisasi
      ON pengrealKegdetId = kegdetId
   GROUP BY kegdetId, pengrealId
   UNION
   SELECT
      kegdetId AS kegiatanId,
      pengrealId AS realisasiId,
      SUM(IFNULL(transNilai,0)) AS nominal
   FROM transaksi
   JOIN transaksi_detail_spj
      ON transdtspjTransId = transid
   JOIN kegiatan_detail
      ON kegdetId = transdtspjKegdetId
   JOIN pengajuan_realisasi
      ON pengrealKegdetId = kegdetId
   GROUP BY kegdetId, pengrealId) AS realisasi
   GROUP BY kegiatanId, realisasiId) AS pencairan ON pencairan.kegId = kegdetId
   AND pencairan.realId = pengrealId
WHERE transId = %s
LIMIT 1
";

$sql['get_invoice_transaksi'] = "
SELECT
   transinvoiceId AS id,
   transinvoiceNomor AS nomor
FROM transaksi_invoice
WHERE transinvoiceTransId = %s
ORDER BY transinvoiceId
";

$sql['get_files_transaksi']   = "
SELECT
   transfilePath AS `path`,
   transfileNama AS `fileName`
FROM transaksi_file
WHERE 1 = 1
AND transfileTransId = %s
ORDER BY transfileId
";
?>