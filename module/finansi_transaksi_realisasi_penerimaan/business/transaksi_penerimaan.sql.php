<?php
$sql['get_periode_tahun']  = "
SELECT
   thanggarId AS `id`,
   thanggarNama AS `name`,
   thanggarBuka AS tanggalAwal,
   thanggarTutup AS tanggalAkhir
FROM tahun_anggaran
JOIN renstra
   ON renstraId = thanggarRenstraId
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
";

$sql['get_combo_jenis_transaksi'] = "
SELECT
   transjenId as `id`,
   transjenNama as `name`
FROM
   transaksi_jenis_ref
WHERE
   transjenNama NOT IN ('Payroll','Aset','Registrasi','Penyusutan Aset','Pengadaan','Beban ATK','Pengendalian', 'Pengelolaan Aset')
ORDER BY transjenNama
";

$sql['get_combo_tipe_transaksi'] = "
SELECT
   ttId AS `id`,
   ttNamaTransaksi AS `name`
FROM
   transaksi_tipe_ref
WHERE 1 = 1
AND ttKodeTransaksi IN ('%s')
ORDER BY ttId ASC
";

$sql['set_tahun_anggaran']    = "
SET @tahun_anggaran = ''
";

$sql['set_tahun_pembukuan']   = "
SET @tahun_pembukuan = ''
";

$sql['do_set_tahun_anggaran'] = "
SELECT
   thanggarId INTO @tahun_anggaran
FROM
   tahun_anggaran
WHERE 1 = 1
AND thanggarIsAktif = 'Y'
LIMIT 1
";

$sql['do_set_tahun_pembukuan']   = "
SELECT
   tppId INTO @tahun_pembukuan
FROM tahun_pembukuan_periode
WHERE 1 = 1
AND tppIsBukaBuku = 'Y'
";

$sql['do_insert_transaksi']      = "
INSERT INTO transaksi
SET transTtId = '%s',
   transTransjenId = '%s',
   transUnitkerjaId = '%s',
   transTppId = @tahun_pembukuan,
   transThanggarId = @tahun_anggaran,
   transReferensi = '%s',
   transUserId = '%s',
   transTanggal = DATE(NOW()),
   transTanggalEntri = '%s',
   transDueDate = '%s',
   transCatatan = '%s',
   transNilai = '%s',
   transPenanggungJawabNama = '%s',
   transIsJurnal = '%s',
   transKelompok = 'none',
   transIsDariAplikasiKeuangan = 'T'
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

$sql['do_save_realisasi_penerimaan']   = "
INSERT INTO realisasi_penerimaan
SET realterimaTotalTerima = '%s',
   realterimaDeskripsi = '%s',
   realrenterimaId = '%s',
   realterimaJmlJan = '%s',
   realterimaJmlFeb = '%s',
   realterimaJmlMar = '%s',
   realterimaJmlApr = '%s',
   realterimaJmlMei = '%s',
   realterimaJmlJun = '%s',
   realterimaJmlJul = '%s',
   realterimaJmlAgt = '%s',
   realterimaJmlSep = '%s',
   realterimaJmlOkt = '%s',
   realterimaJmlNov = '%s',
   realterimaJmlDes = '%s',
   realterimaTransId = '%s'
";

$sql['do_insert_pembukuan_referensi']  = "
INSERT INTO pembukuan_referensi
SET prTransId = '%s',
   prUserId = '%s',
   prTanggal = '%s',
   prKeterangan = '%s',
   prIsPosting = 'T',
   prIsFinalPosting = 'T',
   prDelIsLocked = 'T',
   prIsApproved = 'T'
";

$sql['do_insert_pembukuan_detail']     = "
INSERT INTO pembukuan_detail (
   pdPrId,
   pdCoaId,
   pdNilai,
   pdKeterangan,
   pdKeteranganTambahan,
   pdStatus,
   pdSubaccPertamaKode,
   pdSubaccKeduaKode,
   pdSubaccKetigaKode,
   pdSubaccKeempatKode,
   pdSubaccKelimaKode,
   pdSubaccKeenamKode,
   pdSubaccKetujuhKode
)
SELECT
   '%s' AS pdId,
   IF(sknrdKreditCoaId IS NULL, coaDebet.coaId, coaKredit.coaId) AS akunId,
   CONVERT((sknrdProsen * %s)/100, DECIMAL(20,2)) AS nominal,
   '' AS keterangan,
   '' AS keteranganTambahan,
   IF(sknrdKreditCoaId IS NULL, 'D', 'K') AS `status`,
   '%s' AS subAccount1,
   '%s' AS subAccount2,
   '%s' AS subAccount3,
   '%s' AS subAccount4,
   '%s' AS subAccount5,
   '%s' AS subAccount6,
   '%s' AS subAccount7
FROM
   sekenario_detail
   JOIN sekenario
      ON sknrId = sknrdSknrId
   LEFT JOIN coa AS coaKredit
      ON coaKredit.coaId = sknrdKreditCoaId
   LEFT JOIN coa AS coaDebet
      ON coaDebet.coaId = sknrdDebetCoaId
WHERE 1 = 1
   AND sknrdSknrId IN ('%s')
ORDER BY IFNULL(sknrdDebetCoaId, 'Z')
";

$sql['do_insert_pembukuan_detail_debet']  = "
INSERT INTO pembukuan_detail (
   pdPrId,
   pdCoaId,
   pdNilai,
   pdKeterangan,
   pdKeteranganTambahan,
   pdStatus,
   pdSubaccPertamaKode,
   pdSubaccKeduaKode,
   pdSubaccKetigaKode,
   pdSubaccKeempatKode,
   pdSubaccKelimaKode,
   pdSubaccKeenamKode,
   pdSubaccKetujuhKode
)
SELECT
   %s,
   sknrdDebetCoaId,
   (sknrdProsen * %s) / 100,
   '',
   '',
   'D',
   %s,
   %s,
   %s,
   %s,
   %s,
   %s,
   %s
FROM
   sekenario_detail
   JOIN sekenario
      ON sknrId = sknrdSknrId
WHERE 1 = 1
   AND sknrdSknrId IN ('%s')
   AND sknrdKreditCoaId IS NULL
";

$sql['do_insert_pembukuan_detail_kredit']    = "
INSERT INTO pembukuan_detail (
   pdPrId,
   pdCoaId,
   pdNilai,
   pdKeterangan,
   pdKeteranganTambahan,
   pdStatus,
   pdSubaccPertamaKode,
   pdSubaccKeduaKode,
   pdSubaccKetigaKode,
   pdSubaccKeempatKode,
   pdSubaccKelimaKode,
   pdSubaccKeenamKode,
   pdSubaccKetujuhKode
)
SELECT
   %s,
   sknrdKreditCoaId,
   (sknrdProsen * %s) / 100,
   '',
   '',
   'K',
   %s,
   %s,
   %s,
   %s,
   %s,
   %s,
   %s
FROM
   sekenario_detail
   JOIN sekenario
      ON sknrId = sknrdSknrId
WHERE 1 = 1
   AND sknrdSknrId IN ('%s')
   AND sknrdDebetCoaId IS NULL
";

$sql['do_insert_transaksi_detail_anggaran_penerimaan']   = "
INSERT INTO transaksi_detail_anggaran
SET transdtanggarTransId = '%s',
   transdtanggarKegdetId = NULL,
   transdtanggarPengrealId = NULL,
   transdtanggarPenerimaanId = '%s'
";

$sql['get_sub_account_default']  = "
SELECT
   CONCAT_WS('-', REPEAT('*', LENGTH(TRIM(BOTH FROM subaccPertamaKode))),
   REPEAT('*', LENGTH(TRIM(BOTH FROM subaccKeduaKode))),
   REPEAT('*', LENGTH(TRIM(BOTH FROM subaccKetigaKode))),
   REPEAT('*', LENGTH(TRIM(BOTH FROM subaccKeempatKode))),
   REPEAT('*', LENGTH(TRIM(BOTH FROM subaccKelimaKode))),
   REPEAT('*', LENGTH(TRIM(BOTH FROM subaccKeenamKode))),
   REPEAT('*', LENGTH(TRIM(BOTH FROM subaccKetujuhKode)))) AS patern,
   CONCAT_WS('-', CONCAT('([a-zA-Z0-9]{1,',LENGTH(TRIM(BOTH FROM subaccPertamaKode)),'})'),
   CONCAT('([a-zA-Z0-9]{1,',LENGTH(TRIM(BOTH FROM subaccKeduaKode)),'})'),
   CONCAT('([a-zA-Z0-9]{1,',LENGTH(TRIM(BOTH FROM subaccKetigaKode)),'})'),
   CONCAT('([a-zA-Z0-9]{1,',LENGTH(TRIM(BOTH FROM subaccKeempatKode)),'})'),
   CONCAT('([a-zA-Z0-9]{1,',LENGTH(TRIM(BOTH FROM subaccKelimaKode)),'})'),
   CONCAT('([a-zA-Z0-9]{1,',LENGTH(TRIM(BOTH FROM subaccKeenamKode)),'})'),
   CONCAT('([a-zA-Z0-9]{1,',LENGTH(TRIM(BOTH FROM subaccKetujuhKode)),'})')) AS regex,
   CONCAT_WS('-', TRIM(BOTH FROM subaccPertamaKode),
   TRIM(BOTH FROM subaccKeduaKode),
   TRIM(BOTH FROM subaccKetigaKode),
   TRIM(BOTH FROM subaccKeempatKode),
   TRIM(BOTH FROM subaccKelimaKode),
   TRIM(BOTH FROM subaccKeenamKode),
   TRIM(BOTH FROM subaccKetujuhKode)) AS `default`
FROM finansi_keu_ref_subacc_1
JOIN finansi_keu_ref_subacc_2
   ON UPPER(subaccKeduaNama) = 'DEFAULT'
JOIN finansi_keu_ref_subacc_3
   ON subaccKetigaNama = 'DEFAULT'
LEFT JOIN finansi_keu_ref_subacc_4
   ON UPPER(subaccKeempatNama) = 'DEFAULT'
LEFT JOIN finansi_keu_ref_subacc_5
   ON UPPER(subaccKelimaNama) = 'DEFAULT'
LEFT JOIN finansi_keu_ref_subacc_6
   ON UPPER(subaccKeenamNama) = 'DEFAULT'
LEFT JOIN finansi_keu_ref_subacc_7
   ON UPPER(subaccKetujuhNama) = 'DEFAULT'
WHERE 1 = 1
AND UPPER(subaccPertamaNama) = 'DEFAULT'
LIMIT 1
";

$sql['get_data_transaksi_detail']   = "
SELECT
   transId AS id,
   tppId,
   tppTanggalAwal,
   tppTanggalAkhir,
   thanggarId,
   thanggarNama,
   unitkerjaId,
   unitkerjaKode,
   unitkerjaNama,
   transReferensi AS nomorReferensi,
   transTanggal AS tanggal,
   transTanggalEntri AS tanggalEntri,
   transDueDate AS dueDate,
   transCatatan AS keterangan,
   renterimaTotalTerima AS nominalApprove,
   IFNULL(realisasi.nominal, 0)-IFNULL(transNilai, 0) AS nominalRealisasi,
   transNilai AS nominal,
   transPenanggungJawabNama AS penanggungJawab,
   transjenId AS transaksiJenisId,
   transjenKode AS transaksiJenisKode,
   transjenNama AS transaksiJenisNama,
   ttId AS transaksiTipeId,
   ttKodeTransaksi AS transaksiTipeKode,
   ttNamaTransaksi AS transaksiTipeNama,
   kodeterimaKode AS mapKode,
   kodeterimaNama AS mapNama
FROM transaksi
JOIN tahun_pembukuan_periode
   ON tppId = transTppId
JOIN tahun_anggaran
   ON thanggarId = transThanggarId
JOIN unit_kerja_ref
   ON unitkerjaId = transUnitkerjaId
JOIN realisasi_penerimaan
   ON realterimaTransId = transId
JOIN rencana_penerimaan
   ON renterimaId = realrenterimaId
JOIN kode_penerimaan_ref
   ON kodeterimaId = renterimaKodeterimaId
LEFT JOIN transaksi_tipe_ref
   ON ttId = transTtId
LEFT JOIN transaksi_jenis_ref
   ON transjenId = transTransjenId
LEFT JOIN (SELECT realrenterimaId AS id,
      SUM(realterimaTotalTerima) AS nominal
   FROM realisasi_penerimaan
   GROUP BY realrenterimaId
   ) AS realisasi ON realisasi.id = renterimaId
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

$sql['get_skenario_jurnal']   = "
SELECT
   IF(sknrdKreditCoaId IS NULL, coaDebet.coaId, coaKredit.coaId) AS akunId,
   IF(sknrdKreditCoaId IS NULL, coaDebet.coaKodeAkun, coaKredit.coaKodeAkun) AS akunKode,
   IF(sknrdKreditCoaId IS NULL, coaDebet.coaNamaAkun, coaKredit.coaNamaAkun) AS akunNama,
   CONVERT((sknrdProsen * %s)/100, DECIMAL(20,2)) AS nominal,
   IF(sknrdKreditCoaId IS NULL, 'D', 'K') AS `status`
FROM
   sekenario_detail
   JOIN sekenario
      ON sknrId = sknrdSknrId
   LEFT JOIN coa AS coaKredit
      ON coaKredit.coaId = sknrdKreditCoaId
   LEFT JOIN coa AS coaDebet
      ON coaDebet.coaId = sknrdDebetCoaId
WHERE 1 = 1
   AND sknrdSknrId IN ('%s')
ORDER BY IFNULL(sknrdDebetCoaId, 'Z')
";
?>