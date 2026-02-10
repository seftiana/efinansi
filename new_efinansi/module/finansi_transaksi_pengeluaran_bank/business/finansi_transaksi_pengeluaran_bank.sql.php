<?php
/**
 * @package SQL-FILE
 */

$sql['get_range_tahun_periode_anggaran'] ="
SELECT
  YEAR(MIN(`thanggarBuka`)) AS tahun_awal,
  YEAR(MAX(`thanggarTutup`)) AS tahun_akhir
FROM
  `tahun_anggaran`
WHERE
`thanggarIsAktif`  = 'Y' OR `thanggarIsOpen` = 'Y'
";

//set tahun pembukuan dan anggaran
$sql['do_set_tahun_pembukuan']   = "
SET @tahun_Pembukuan = ''
";

$sql['do_set_tahun_anggaran']    = "
SET @tahun_anggaran  = ''
";

$sql['get_set_tahun_pembukuan']  = "
SELECT
   `tppId` INTO @tahun_pembukuan
FROM
   `tahun_pembukuan_periode`
WHERE tppIsBukaBuku = 'Y'
LIMIT 1
";

$sql['get_tahun_pembukuan_periode']  = "
SELECT
   tppId AS `id`,
   tppTanggalAwal AS `awal`,
   tppTanggalAkhir AS `akhir`,
   tppIsBukaBuku AS `open`
FROM tahun_pembukuan_periode
WHERE 1 = 1
AND (tppIsBukaBuku = 'Y' OR 1 = %s)
";

$sql['get_set_tahun_anggaran']   = "
SELECT
   thanggarId INTO @tahun_anggaran
FROM
   tahun_anggaran
WHERE thanggarIsAktif = 'Y'
LIMIT 1
";

// end
// set real name 
// 
$sql['do_set_realname_user']  = "
SELECT RealName INTO @real_name FROM gtfw_user WHERE UserId = %s
";

//
$sql['get_setting_value']        = "
SELECT
   settingValue AS 'setting'
FROM setting
WHERE UPPER(settingName) = '%s'
LIMIT 1
";

$sql['get_min_max_tahun_pencatatan'] = "
SELECT
   YEAR(MIN(transTanggalEntri)) - 5 AS minTahun,
   YEAR(MAX(transTanggalEntri)) + 5 AS maxTahun
FROM
   transaksi
";
// untuk trasaksi

$sql['do_save_transaksi']     = "
INSERT INTO transaksi
SET transTtId = '2',
   transTransjenId = '10',
   transUnitkerjaId = '%s',
   transTppId = @tahun_Pembukuan,
   transThanggarId = @tahun_anggaran,
   transReferensi = '%s',
   transUserId = '%s',
   transTanggal = DATE(NOW()),
   transTanggalEntri = '%s',
   transDueDate = '%s',
   transCatatan = '%s',
   transNilai = '%s',
   transPenanggungJawabNama = @real_name,
   transIsJurnal = 'Y'
";
// unutk transaki detial pengeluran
$sql['do_insert_transaksi_det_pengeluaran_bank'] = "
    INSERT INTO `transaksi_detail_pengeluaran_bank`
    SET
      `transdtPengeluaranBankTransId` = '%s',
      `transdtPengeluaranBankTBankId` = '%s'
";
// end
//untuk tabel finansi_pa_transaksi_bank

$sql['get_transaksi_detail']     = "
SELECT
   ptb.transaksiBankId AS id,
   ptb.transaksiBankNomor AS nomor,
   ptb.transaksiBankBpkb AS bpkb,
   ptb.transaksiBankTanggal AS tanggal,
   ptb.transaksiBankCoaIdPenerima AS coaIdPenyetor,
   ptb.transaksiBankPenerima AS namaPenyetor,
   ptb.transaksiBankRekeningPenerima AS rekeningPenyetor,
   ptb.transaksiBankCoaIdTujuan AS coaIdPenerima,
   ptb.transaksiBankTujuan AS bankPenerima,
   ptb.transaksiBankRekeningTujuan AS rekeningPenerima,
   ptb.transaksiBankNominal AS nominal,
   tr.`transCatatan` AS keterangan
FROM finansi_pa_transaksi_bank ptb
JOIN transaksi_detail_penerimaan_bank tdpb ON tdpb.`transdtPenerimaanBankTBankId` = ptb.`transaksiBankId`
JOIN transaksi tr ON tr.`transId` = tdpb.`transdtPenerimaanBankTransId` 
WHERE 1 = 1
AND 
tr.`transId` = '%s'
LIMIT 1
";


$sql['get_list_transaksi_detil']    = "
SELECT
   transksaiBankDetilId AS id,
   transaksiBankDetilNama AS nama,
   transaksiBankDetilTanggal AS tanggal,
   transaksiBankDetilNominal AS nominal
FROM finansi_pa_transaksi_bank_detil
JOIN finansi_pa_transaksi_bank ON 
transaksiBankDetilTransaksiBankId  = transaksiBankId
WHERE 1 = 1
AND transaksiBankDetilTransaksiBankId = (SELECT `transdtPenerimaanBankTBankId` 
FROM `transaksi_detail_penerimaan_bank` WHERE `transdtPenerimaanBankTransId` = %s)
";

$sql['do_insert_transaksi_bank']    = "
INSERT INTO finansi_pa_transaksi_bank
SET 
   transaksiBankNomor = '%s',
   transaksiBankBpkb = '%s',
   transaksiBankTanggal = '%s',
   transaksiBankPenerima = '%s',
   transaksiBankTujuan = '%s',
   transaksiBankNominal = '%s',
   transaksiBankTipe = 'pengeluaran',
   transaksiBankUserId = '%s',
   transaksiBankSppuId = '%s',
   transaksiBankTipeTransaksi = 'pengeluaran'
";
/*
$sql['do_insert_transaksi_bank_detil'] = "
INSERT INTO finansi_pa_transaksi_bank_detil
SET 
   transaksiBankDetilTransaksiBankId = '%s',
   transaksiBankDetilNama = '%s',
   transaksiBankDetilTanggal = '%s',
   transaksiBankDetilNominal = '%s',
   transaksiBankDetilUserid = '%s'
";
*/
$sql['do_insert_transaksi_bank_detail_from_sppu'] ="
INSERT INTO finansi_pa_transaksi_bank_detil (
    transaksiBankDetilTransaksiBankId ,
    transaksiBankDetilTanggal,
    transaksiBankDetilNama,
    transaksiBankDetilNominal,
    transaksiBankDetilUserid
)
SELECT 
  '%s' AS transBankId,
  '%s' AS tanggal,
  IFNULL(rpeng.`rncnpengeluaranKomponenNama`,'-') AS deskripsi,
  sppu_det.`sppuDetNominal` AS nominal ,
  '%s' AS userId
FROM
  `finansi_pa_sppu` sppu 
  JOIN finansi_pa_sppu_det sppu_det 
    ON sppu_det.`sppuDetSppuId` = sppu.`sppuId` 
  JOIN pengajuan_realisasi_detil peng_real_d 
    ON peng_real_d.`pengrealdetId` = sppu_det.`sppuDetPengrealDetId`
  JOIN rencana_pengeluaran rpeng 
    ON rpeng.`rncnpengeluaranId` = peng_real_d.`pengrealdetRncnpengeluaranId` 
WHERE 
sppu.`sppuId` = '%s'
ORDER BY deskripsi    
";

//update transaksi
//
$sql['do_update_transaksi']  = "
UPDATE transaksi
SET
   transReferensi = '%s',
   transUserId = '%s',
   transTanggalEntri = '%s',
   transDueDate = '%s',
   transCatatan = '%s',
   transNilai = '%s',
   transPenanggungJawabNama = @real_name
WHERE transId = %s
";

$sql['do_update_pembukuan_referensi']  = "
UPDATE pembukuan_referensi
SET prTransId = '%s',
   prUserId = '%s',
   prTanggal = '%s',
   prKeterangan = '%s',
   prIsKas = IF('%s' = '' OR %s IS NULL, NULL, %s),
   prBentukTransaksi = IF('%s' = '' OR %s IS NULL, NULL, '%s')
WHERE prId = %s
";

$sql['do_update_sppu_transaksi_bank']       = "
UPDATE finansi_pa_transaksi_bank
SET 
   transaksiBankTanggal = '%s',
   transaksiBankPenerima = '%s',
   transaksiBankTujuan = '%s',
   transaksiBankNominal = '%s',
   transaksiBankUserId = '%s'
WHERE transaksiBankSppuId =  %s
";

$sql['get_sppu_transaksi_bank'] ="
SELECT
   transaksiBankId AS id,
   transaksiBankNomor AS nomorBank,
   transaksiBankBpkb AS bpkbBank,
   transaksiBankSppuId AS sppuId
FROM finansi_pa_transaksi_bank
JOIN finansi_pa_sppu
ON sppuId = transaksiBankSppuId
WHERE transaksiBankSppuId = '%s'
AND transaksiBankBpkb = '%s'
";

//update transaksi pengeluran bank
$sql['do_update_transaksi_bank']       = "
UPDATE finansi_pa_transaksi_bank
SET 
   transaksiBankNomor = '%s',
   transaksiBankBpkb = '%s',
   transaksiBankTanggal = '%s',
   transaksiBankPenerima = '%s',
   transaksiBankTujuan = '%s',
   transaksiBankNominal = '%s',
   transaksiBankUserId = '%s'
WHERE transaksiBankId =  (SELECT `transdtPengeluaranBankTBankId` 
FROM `transaksi_detail_pengeluaran_bank` WHERE `transdtPengeluaranBankTransId`  = %s)
";

$sql['do_update_transaksi_bank_detil'] = "
INSERT INTO finansi_pa_transaksi_bank_detil
SET 
   transaksiBankDetilTransaksiBankId =  (SELECT `transdtPengeluaranBankTBankId` 
FROM `transaksi_detail_pengeluaran_bank` WHERE `transdtPengeluaranBankTransId`  = %s),
   transaksiBankDetilNama = '%s',
   transaksiBankDetilTanggal = '%s',
   transaksiBankDetilNominal = '%s',
   transaksiBankDetilUserid = '%s'
";

//delete
$sql['do_delete_transaksi_bank_detail_transaksi']  = "
DELETE
FROM finansi_pa_transaksi_bank_detil
WHERE transaksiBankDetilTransaksiBankId = (SELECT `transdtPengeluaranBankTBankId` 
FROM `transaksi_detail_pengeluaran_bank` WHERE `transdtPengeluaranBankTransId`= %s)
";

$sql['do_delete_transaksi_bank']   = "
DELETE
FROM finansi_pa_transaksi_bank
WHERE transaksiBankId = (SELECT `transdtPengeluaranBankTBankId` 
FROM `transaksi_detail_pengeluaran_bank` WHERE `transdtPengeluaranBankTransId` = %s)
";

$sql['do_update_sppu']   = "
UPDATE finansi_pa_sppu
SET sppuIsTransaksiKas = 'Belum'
WHERE sppuId = (SELECT `transaksiBankSppuId` 
FROM `transaksi_detail_pengeluaran_bank` AS tdBank 
JOIN finansi_pa_transaksi_bank AS tBank 
ON tBank.`transaksiBankId` = tdBank.`transdtPengeluaranBankTBankId` 
WHERE `transdtPengeluaranBankTransId` = %s)
";

$sql['do_update_status_trans_sppu']   = "
UPDATE finansi_pa_sppu
SET sppuIsTransaksiKas = 'Belum'
WHERE sppuId = %s
";

$sql['do_delete_transaksi_det_pengeluaran_bank'] = "
DELETE FROM `transaksi_detail_pengeluaran_bank`
WHERE `transdtPengeluaranBankId` = '%s'
";

$sql['do_delete_transaksi']="
DELETE FROM transaksi WHERE transId = %s;
";


// end delete

$sql['get_cek_noref'] ="
SELECT
  COUNT(`transReferensi`) AS total
FROM `transaksi`
WHERE `transReferensi` ='%s';
";

$sql['get_cek_noref_sppu'] ="
SELECT
  COUNT(sppuId)  AS total
FROM `finansi_pa_sppu`
WHERE
`sppuBPKBCr`  ='%s'
OR
 `sppuBPKBBp`  ='%s'
";

//end tabel pa transaksi bank


$sql['get_patern_sub_account']   = "
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

$sql['get_sub_account']    = "
SELECT
   CONCAT_WS('-', TRIM(BOTH FROM subaccPertamaKode),
   TRIM(BOTH FROM subaccKeduaKode),
   TRIM(BOTH FROM subaccKetigaKode),
   TRIM(BOTH FROM subaccKeempatKode),
   TRIM(BOTH FROM subaccKelimaKode),
   TRIM(BOTH FROM subaccKeenamKode),
   TRIM(BOTH FROM subaccKetujuhKode)) AS subAkun
FROM finansi_keu_ref_subacc_1
LEFT JOIN finansi_keu_ref_subacc_2
   ON subaccKeduaKode = %s
LEFT JOIN finansi_keu_ref_subacc_3
   ON subaccKetigaKode = %s
LEFT JOIN finansi_keu_ref_subacc_4
  ON subaccKeempatKode = %s
LEFT JOIN finansi_keu_ref_subacc_5
   ON subaccKelimaKode =  %s
LEFT JOIN finansi_keu_ref_subacc_6
   ON subaccKeenamKode = %s
LEFT JOIN finansi_keu_ref_subacc_7
   ON subaccKetujuhKode = %s
WHERE 1 = 1
AND subaccPertamaKode = %s
LIMIT 1
";

$sql['get_transaksi_bank_by_id']   = "
SELECT
   tr.transId AS id,
   pr.prId AS pembukuanId,
   tr.transReferensi AS nomorReferensi,
   IFNULL(tbank.`transaksiBankSppuId`,0) AS sppuId,
   uk.unitkerjaId AS unitId,
   uk.unitkerjaKode AS unitKode,
   uk.unitkerjaNama AS unitNama,
   tr.transNilai AS nominal,
   tbank.transaksiBankPenerima  AS namaPenyetor,
   tbank.transaksiBankTujuan AS namaPenerima,
   tr.`transCatatan` AS keterangan,
   tr.`transTanggalEntri` AS tanggal
FROM
   transaksi tr
   LEFT JOIN `transaksi_detail_pengeluaran_bank` trd_b
      ON trd_b.`transdtPengeluaranBankTransId` = tr.`transId`
   LEFT JOIN `finansi_pa_transaksi_bank` tbank
      ON tbank.`transaksiBankId` = trd_b.`transdtPengeluaranBankTBankId` AND tbank.`transaksiBankTipe` =  'pengeluaran'
   JOIN transaksi_tipe_ref trtipe
      ON trtipe.ttId = tr.transTtId
   JOIN tahun_pembukuan_periode tp
      ON tp.tppId = tr.transTppId
   JOIN tahun_anggaran ta
      ON ta.thanggarId = tr.transThanggarId
   JOIN unit_kerja_ref uk
      ON uk.unitkerjaId = tr.transUnitkerjaId
   JOIN pembukuan_referensi pr
      ON pr.prTransId = tr.transId
WHERE 1 = 1
AND tr.transId = %s
AND pr.prId = %s
LIMIT 1
";

$sql['get_data_jurnal_sub_akun']    = "
SELECT
   coaId AS id,
   coaKodeAkun AS kode,
   coaNamaAkun AS nama,
   pdStatus AS `status`,
   pdNilai AS nominal,
   pdKeterangan AS keterangan,
   pdKeteranganTambahan AS referensi,
   CONCAT_WS('-', TRIM(BOTH FROM subaccPertamaKode),
   TRIM(BOTH FROM subaccKeduaKode),
   TRIM(BOTH FROM subaccKetigaKode),
   TRIM(BOTH FROM subaccKeempatKode),
   TRIM(BOTH FROM subaccKelimaKode),
   TRIM(BOTH FROM subaccKeenamKode),
   TRIM(BOTH FROM subaccKetujuhKode)) AS subAccount
FROM pembukuan_detail
JOIN pembukuan_referensi
   ON prId = pdPrId
JOIN transaksi
   ON transId = prTransId
JOIN coa
   ON coaId = pdCoaId
LEFT JOIN finansi_keu_ref_subacc_1
   ON subaccPertamaKode = pdSubaccPertamaKode
LEFT JOIN finansi_keu_ref_subacc_2
   ON subaccKeduaKode = pdSubaccKeduaKode
LEFT JOIN finansi_keu_ref_subacc_3
   ON subaccKetigaKode = pdSubaccKetigaKode
LEFT JOIN finansi_keu_ref_subacc_4
   ON subaccKeempatKode = pdSubaccKeempatKode
LEFT JOIN finansi_keu_ref_subacc_5
   ON subaccKelimaKode = pdSubaccKelimaKode
LEFT JOIN finansi_keu_ref_subacc_6
   ON subaccKeenamKode = pdSubaccKeenamKode
LEFT JOIN finansi_keu_ref_subacc_7
   ON subaccKetujuhKode = pdSubaccKetujuhKode
WHERE 1 = 1
AND transId = %s
AND prId = %s
ORDER BY pdStatus
";

$sql['get_bentuk_transaksi']        = "
SELECT
   kelJnsId AS id,
   kelJnsNama AS name
FROM
   kelompok_jenis_laporan_ref
WHERE kelJnsPrntId = '2'
";

$sql['do_add_log'] = "
INSERT INTO logger(logUserId, logAlamatIp, logUpdateTerakhir, logKeterangan)
VALUES ('%s', '%s', NOW(), '%s')
";

$sql['do_add_log_detil'] = "
INSERT INTO logger_detail(logId, logAksiQuery)
VALUES ('%s', '%s')
";

$sql['do_insert_pembukuan_referensi']  = "
INSERT INTO pembukuan_referensi
SET prTransId = '%s',
   prUserId = '%s',
   prTanggal = '%s',
   prKeterangan = '%s',
   prIsPosting = 'T',
   prDelIsLocked = 'T',
   prIsApproved = '%s',
   prIsKas = IF('%s' = '' OR %s IS NULL, NULL, %s),
   prBentukTransaksi = IF('%s' = '' OR %s IS NULL, NULL, '%s')
";

$sql['do_update_pembukuan_referensi']  = "
UPDATE pembukuan_referensi
SET prTransId = '%s',
   prUserId = '%s',
   prTanggal = '%s',
   prKeterangan = '%s',
   prIsKas = IF('%s' = '' OR %s IS NULL, NULL, %s),
   prBentukTransaksi = IF('%s' = '' OR %s IS NULL, NULL, '%s')
WHERE prId = %s
";

$sql['do_delete_pembukuan_referensi']  = "
DELETE FROM pembukuan_referensi WHERE prId = '%s'
";

$sql['do_delete_pembukuan_detail']  = "
DELETE FROM pembukuan_detail WHERE pdPrId = %s
";

$sql['do_insert_pembukuan_detail']  = "
INSERT INTO pembukuan_detail
SET pdPrId = '%s',
   pdCoaId = '%s',
   pdNilai = '%s',
   pdKeterangan = '%s',
   pdKeteranganTambahan = '%s', -- nomor referensi
   pdStatus = '%s',
   pdSubaccPertamaKode = %s,
   pdSubaccKeduaKode = %s,
   pdSubaccKetigaKode = %s,
   pdSubaccKeempatKode = %s,
   pdSubaccKelimaKode = %s,
   pdSubaccKeenamKode = %s,
   pdSubaccKetujuhKode = %s
";

$sql['update_status_jurnal']     = "
UPDATE transaksi SET transIsJurnal = '%s' WHERE transId = %s
";

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";



$sql['get_data_jurnal_pengeluaran'] = "
SELECT
   jurnal.*,
   coaId,
   coaKodeAkun,
   coaNamaAkun,
   pdStatus,
   pdNilai,
   IF(UPPER(pdStatus) = 'D', pdNilai, 0) AS nominalDebet,
   IF(UPPER(pdStatus) = 'K', pdNilai, 0) AS nominalKredit,
   pdKeterangan,
   CONCAT_WS('-', TRIM(BOTH FROM subaccPertamaKode),
   TRIM(BOTH FROM subaccKeduaKode),
   TRIM(BOTH FROM subaccKetigaKode),
   TRIM(BOTH FROM subaccKeempatKode),
   TRIM(BOTH FROM subaccKelimaKode),
   TRIM(BOTH FROM subaccKeenamKode),
   TRIM(BOTH FROM subaccKetujuhKode)) AS subAccount
FROM pembukuan_detail
JOIN (SELECT
   tppId,
   tppTanggalAwal,
   tppTanggalAkhir,
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   transId AS id,
   prId AS pembukuanId,
   transReferensi AS referensi,
   transTtId AS tipeJurnalId,
   ttNamaJurnal AS tipeJurnalNama,
   ttKodeTransaksi AS tipeJurnalKode,
   transTanggalEntri AS tanggal,
   transCatatan AS catatan,
   prKeterangan AS keterangan,
   transNilai AS nominal,
   transPenanggungJawabNama AS penanggungJawab,
   transIsJurnal AS statusJurnal,
   prTanggal AS tanggalPembukuan,
   prIsPosting AS statusPosting,
   prIsFinalPosting AS statusFinalPosting,
   prDelIsLocked AS lockDelete,
   prIsApproved AS statusApprove,
   prIsKas AS statusKas,
   prIsJurnalBalik AS jurnalBalik,
   kelJnsId,
   kelJnsNama,
   tmp_pr.jurnal,
   IF(tmp_pr.summary < tmp_pr.jurnal, 'YES', 'NO') AS hasJurnal,
   tbank.`transaksiBankPenerima` AS namaPenyetor,
   tbank.`transaksiBankTujuan` AS namaPenerima,
   IFNULL(tbank.`transaksiBankSppuId`,0) AS sppuId
FROM transaksi
LEFT JOIN `transaksi_detail_pengeluaran_bank` trd_b
  ON trd_b.`transdtPengeluaranBankTransId` = `transId`
LEFT JOIN `finansi_pa_transaksi_bank` tbank
  ON tbank.`transaksiBankId` = trd_b.`transdtPengeluaranBankTBankId` AND tbank.`transaksiBankTipe` =  'pengeluaran'
JOIN unit_kerja_ref
   ON unitkerjaId = transUnitkerjaId
JOIN tahun_anggaran
   ON thanggarId = transThanggarId
JOIN tahun_pembukuan_periode
   ON tppId = transTppId
JOIN (SELECT DISTINCT
   prTransId AS pembukuanTransaksiId,
   MAX(prId) AS pembukuanReferensiId,
   COUNT(prId) AS summary,
   COUNT(IF(prIsPosting = 'T', NULL, prId)) AS jurnal
FROM pembukuan_referensi
GROUP BY prTransId
ORDER BY prId DESC) AS tmp_pr
   ON tmp_pr.pembukuanTransaksiId = transId
JOIN pembukuan_referensi
   ON prId = tmp_pr.pembukuanReferensiId
JOIN (SELECT
   pdPrId AS id
FROM pembukuan_detail
JOIN coa
   ON coaId = pdCoaId
GROUP BY pdPrId
) AS detailPembukuan ON detailPembukuan.id = prId
LEFT JOIN transaksi_tipe_ref
   ON ttId = transTtId
LEFT JOIN kelompok_jenis_laporan_ref
   ON kelJnsId = prBentukTransaksi
WHERE 1 = 1
AND transTtId = 2
AND transTransjenId = 10
AND transReferensi LIKE '%s'
AND (prIsPosting = %s OR 1 = %s)
AND transTanggalEntri BETWEEN '%s' AND '%s'
ORDER BY prTanggal DESC, SUBSTR(SUBSTRING_INDEX(transReferensi, '/' , 1), 3)+0 DESC
LIMIT %s, %s) AS jurnal
   ON jurnal.pembukuanId = pdPrId
JOIN coa
   ON coaId = pdCoaId
LEFT JOIN finansi_keu_ref_subacc_1
   ON subaccPertamaKode = pdSubaccPertamaKode
LEFT JOIN finansi_keu_ref_subacc_2
   ON subaccKeduaKode = pdSubaccKeduaKode
LEFT JOIN finansi_keu_ref_subacc_3
   ON subaccKetigaKode = pdSubaccKetigaKode
LEFT JOIN finansi_keu_ref_subacc_4
   ON subaccKeempatKode = pdSubaccKeempatKode
LEFT JOIN finansi_keu_ref_subacc_5
   ON subaccKelimaKode = pdSubaccKelimaKode
LEFT JOIN finansi_keu_ref_subacc_6
   ON subaccKeenamKode = pdSubaccKeenamKode
LEFT JOIN finansi_keu_ref_subacc_7
   ON subaccKetujuhKode = pdSubaccKetujuhKode
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
) AS tmp_unit ON tmp_unit.id = jurnal.unitId
ORDER BY jurnal.referensi ASC,
pdStatus ASC
";

$sql['count_jurnal_pengeluaran'] = "
SELECT
   COUNT(transId) AS `count`
FROM transaksi
JOIN unit_kerja_ref
   ON unitkerjaId = transUnitkerjaId
JOIN tahun_anggaran
   ON thanggarId = transThanggarId
JOIN tahun_pembukuan_periode
   ON tppId = transTppId
JOIN (SELECT DISTINCT
   prTransId AS pembukuanTransaksiId,
   MAX(prId) AS pembukuanReferensiId,
   COUNT(prId) AS summary,
   COUNT(IF(prIsPosting = 'T', NULL, prId)) AS jurnal
FROM pembukuan_referensi
GROUP BY prTransId
ORDER BY prId DESC) AS tmp_pr
   ON tmp_pr.pembukuanTransaksiId = transId
JOIN pembukuan_referensi
   ON prId = tmp_pr.pembukuanReferensiId
JOIN (SELECT
   pdPrId AS id
FROM pembukuan_detail
JOIN coa
   ON coaId = pdCoaId
GROUP BY pdPrId
) AS detailPembukuan ON detailPembukuan.id = prId
LEFT JOIN transaksi_tipe_ref
   ON ttId = transTtId
LEFT JOIN kelompok_jenis_laporan_ref
   ON kelJnsId = prBentukTransaksi
WHERE 1 = 1
AND transTtId = 2
AND transTransjenId = 10
AND transReferensi LIKE '%s'
AND (prIsPosting = %s OR 1 = %s)
AND transTanggalEntri BETWEEN '%s' AND '%s'
";


$sql['update_status_jurnal_balik']="
UPDATE
  `pembukuan_referensi`
SET
  `prIsJurnalBalik` = '1'
WHERE `prId` = '%s'
";

$sql['do_jurnal_balik_pembukuan_referensi']  = "
INSERT INTO `pembukuan_referensi` (
   `prTransId`,
   `prUserId`,
   `prTanggal`,
   `prKeterangan`,
   `prIsPosting`,
   `prIsFinalPosting`,
   `prDelIsLocked`,
   `prIsApproved`,
   `prIsKas`,
   `prBentukTransaksi`,
   `prIsJurnalBalik`
)
SELECT
   prTransId,
   '%s',
   DATE(NOW()),
   prKeterangan,
   'T',
   prIsFinalPosting,
   prDelIsLocked,
   '%s',
   prIsKas,
   prBentukTransaksi,
   '0'
FROM
   pembukuan_referensi
WHERE prId = %s
";

$sql['do_update_due_date_transaksi']   = "
UPDATE transaksi SET transDueDate = DATE(NOW()) WHERE transId = %s
";

$sql['do_jurnal_balik_pembukuan_detail']  = "
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
(SELECT
   %s,
   pdCoaId,
   pdNilai,
   pdKeterangan,
   pdKeteranganTambahan,
   IF(pdStatus = 'D', 'K', 'D'),
   pdSubaccPertamaKode,
   pdSubaccKeduaKode,
   pdSubaccKetigaKode,
   pdSubaccKeempatKode,
   pdSubaccKelimaKode,
   pdSubaccKeenamKode,
   pdSubaccKetujuhKode
FROM
   pembukuan_detail
WHERE pdPrId = %s
ORDER BY pdId DESC)
";

$sql['get_coa_kode_in_transaksi'] ="
SELECT 
  c.`coaKodeAkun` AS coaKode
FROM
  `finansi_pa_sppu` sppu 
  JOIN finansi_pa_sppu_det sppu_det 
    ON sppu_det.`sppuDetSppuId` = sppu.`sppuId` 
  JOIN pengajuan_realisasi_detil peng_real_d 
    ON peng_real_d.`pengrealdetId` = sppu_det.`sppuDetPengrealDetId`
  JOIN rencana_pengeluaran rpeng 
    ON rpeng.`rncnpengeluaranId` = peng_real_d.`pengrealdetRncnpengeluaranId` 
  JOIN komponen komp 
    ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode` 
  JOIN coa c 
    ON c.`coaId` = komp.`kompCoaId` 
WHERE 
`sppuIsTransaksiKas` = 'Belum' 
AND
 sppu.`sppuId`  = %s
GROUP BY c.`coaId`
";

$sql['update_status_sppu'] = "
UPDATE `finansi_pa_sppu`
SET `sppuIsTransaksiKas` = '%s'
WHERE `sppuId` = '%s'
";

$sql['get_list_transaksi_detil']    = "
SELECT
   transksaiBankDetilId AS id,
   transaksiBankDetilNama  AS nama,
   transaksiBankDetilTanggal AS tanggal,
   transaksiBankDetilNominal AS nominal
FROM finansi_pa_transaksi_bank_detil
WHERE 1 = 1
AND transaksiBankDetilTransaksiBankId = (SELECT `transdtPengeluaranBankTBankId` 
FROM `transaksi_detail_pengeluaran_bank` WHERE `transdtPengeluaranBankTransId` = %s)
";


$sql['get_transaksi_detail']     = "
SELECT
   transaksiBankId AS id,
   transaksiBankNomor AS nomor,
   transaksiBankBpkb AS bpkb,
   transaksiBankTanggal AS tanggal,
   transaksiBankCoaIdPenerima AS coaIdPenyetor,
   transaksiBankPenerima AS namaPenyetor,
   transaksiBankRekeningPenerima AS rekeningPenyetor,
   transaksiBankCoaIdTujuan AS coaIdPenerima,
   transaksiBankTujuan AS bankPenerima,
   transaksiBankRekeningTujuan AS rekeningPenerima,
   transaksiBankNominal AS nominal,
   tr.`transCatatan` AS keterangan
FROM finansi_pa_transaksi_bank
JOIN transaksi_detail_pengeluaran_bank tdpb ON tdpb.`transdtPengeluaranBankTBankId` = finansi_pa_transaksi_bank.`transaksiBankId`
JOIN transaksi tr ON tr.`transId` = tdpb.`transdtPengeluaranBankTransId` 
WHERE 1 = 1
AND transaksiBankId = (SELECT `transdtPengeluaranBankTBankId` 
FROM `transaksi_detail_pengeluaran_bank` WHERE `transdtPengeluaranBankTransId` = %s)
LIMIT 1
";

?>