<?php
/**
 * @package SQL-FILE
 */
$sql['get_date_range']  = "
SELECT
   EXTRACT(YEAR FROM IFNULL(MIN(thanggarBuka), DATE_ADD(DATE(NOW()),INTERVAL -DAY(DATE(NOW()))+1 DAY))) AS minYear,
   EXTRACT(YEAR FROM IFNULL(MAX(thanggarTutup), DATE(LAST_DAY(DATE(NOW()))))) AS maxYear,
   IFNULL(MIN(thanggarBuka), DATE_ADD(DATE(NOW()),INTERVAL -DAY(DATE(NOW()))+1 DAY)) AS tanggalAwal,
   IFNULL(MAX(thanggarTutup), DATE(LAST_DAY(DATE(NOW())))) AS tanggalAkhir
FROM tahun_anggaran
WHERE thanggarIsAktif = 'Y'
OR thanggarIsOpen = 'Y'
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

$sql['get_tipe_transaksi'] = "
SELECT
   ttId AS `id`,
   ttNamaTransaksi AS `name`
FROM
   transaksi_tipe_ref
WHERE 1 = 1
ORDER BY ttId
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
   transPenerimaNama =  '%s',
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

$sql['do_insert_transaksi_det_pencairan'] = "
INSERT INTO transaksi_detail_pencairan
SET transdtpencairanTransId = '%s',
   transdtpencairanKegdetId = '%s',
   transdtpencairanPengrealId = '%s'
";

$sql['do_insert_transaksi_det_pencairan_komp_belanja'] = "
INSERT INTO `transaksi_detail_pencairan_komponen_belanja`
SET 
  `transdtpencairanKompBelanjaTransDtPencairanId` = '%s',
  `transdtpencairanKompBelanjaPengrealDetId` = '%s',
  `transdtpencairanKompBelanjaNominal` = '%s'
";

$sql['do_insert_transaksi_det_anggaran']  = "
INSERT INTO transaksi_detail_anggaran
SET transdtanggarTransId = '%s',
   transdtanggarKegdetId = %s,
   transdtanggarPengrealId = %s,
   transdtanggarPenerimaanId = NULL
";

$sql['get_data_detail']       = "
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
   transPenerimaNama AS penerima,
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
   JOIN transaksi_detail_pencairan
      ON transdtpencairanTransId = transId
   JOIN tahun_pembukuan_periode
      ON tppId = transTppId
   JOIN tahun_anggaran
      ON thanggarId = transThanggarId
   JOIN unit_kerja_ref
      ON unitkerjaId = transUnitkerjaId
   JOIN kegiatan_detail
      ON kegdetId = transdtpencairanKegdetId
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN pengajuan_realisasi
      ON pengrealKegdetId = kegdetId
      AND pengrealId = transdtpencairanPengrealId
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

$sql['get_komponen_anggaran_by_trans_id'] ="
SELECT
  peng_real.`pengrealId` AS pId, 
  rpeng.`rncnpengeluaranKegdetId` AS kegdetId,  
  peng_real_det.`pengrealdetId` AS pdId,
  rpeng.`rncnpengeluaranKomponenKode` AS kompKode,
  rpeng.`rncnpengeluaranKomponenNama` AS kompNama,
  peng_real_det.`pengrealdetDeskripsi` AS deskripsi,
  c.`coaId` AS coaId,
  c.`coaKodeAkun` AS coaKode,
 tdp_komp.`transdtpencairanKompBelanjaNominal` AS nominal
FROM 
pengajuan_realisasi_detil peng_real_det 
JOIN rencana_pengeluaran rpeng 
  ON rpeng.`rncnpengeluaranId` = peng_real_det.`pengrealdetRncnpengeluaranId` 
JOIN pengajuan_realisasi peng_real
  ON peng_real.`pengrealId` = peng_real_det.`pengrealdetPengRealId`
JOIN komponen komp 
  ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode` 
LEFT JOIN coa c 
  ON c.`coaId` = komp.`kompCoaId` 
JOIN kegiatan_detail kd
  ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`  
JOIN kegiatan k
  ON k.`kegId` = kd.`kegdetId`  
JOIN `transaksi_detail_pencairan` tdp
  ON tdp.`transdtpencairanKegdetId` = kd.`kegdetId` AND tdp.`transdtpencairanPengrealId` = peng_real.`pengrealId`
JOIN transaksi tr
  ON tr.`transId`  = tdp.`transdtpencairanTransId` AND tr.`transThanggarId` = k.`kegThanggarId`
JOIN `transaksi_detail_pencairan_komponen_belanja` tdp_komp
  ON tdp_komp.`transdtpencairanKompBelanjaTransDtPencairanId` = tdp.`transdtpencairanId`
   AND tdp_komp.`transdtpencairanKompBelanjaPengrealDetId` = peng_real_det.`pengrealdetId`  
WHERE
tr.`transId` = '%s'
ORDER BY peng_real.`pengrealId` ASC
";
?>