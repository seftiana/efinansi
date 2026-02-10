<?php
/**
 * @package SQL-FILE
 */
$sql['get_data_transaksi'] = "
SELECT 
  SQL_CALC_FOUND_ROWS 
  transaksiBankId AS id,
  transaksiBankNomor AS kode,
  transaksiBankBpkb AS bpkb,
  transaksiBankTanggal AS tanggal,
  transaksiBankPenerima AS namaPenyetor,
  transaksiBankRekeningPenerima AS rekeningPenyetor,
  transaksiBankTujuan AS bankTujuan,
  transaksiBankRekeningTujuan AS rekeningTujuan,
  IFNULL(transaksi.nominal, 0) AS nominal ,
  jurnal.is_jurnal AS isJurnal,
  jurnal.is_jurnal_approve AS isJurnalApprove
FROM finansi_pa_transaksi_bank
JOIN (SELECT
   transaksiBankDetilTransaksiBankId AS id,
   SUM(transaksiBankDetilNominal) AS nominal
FROM finansi_pa_transaksi_bank_detil
GROUP BY transaksiBankDetilTransaksiBankId) AS transaksi
    ON transaksi.id = transaksiBankId 
  LEFT JOIN (
  SELECT 
  tr.`transId` AS tr_id,
  tpbank.`transdtPenerimaanBankTBankId` AS tr_bank_id,
  tr.`transIsJurnal` AS is_jurnal,
  pr.`prIsApproved` AS is_jurnal_approve
FROM
  `transaksi_detail_penerimaan_bank` tpbank 
  JOIN `transaksi` tr 
  ON tr.`transId` = tpbank.`transdtPenerimaanBankTransId`
  JOIN pembukuan_referensi pr
  ON pr.`prTransId` = tr.`transId`
  ) AS jurnal
  ON jurnal.tr_bank_id =  transaksiBankId 
WHERE 1 = 1
AND LOWER(transaksiBankTipe) = 'penerimaan'
AND transaksiBankBpkb LIKE '%s'
AND transaksiBankTanggal BETWEEN '%s' AND '%s'
ORDER BY transaksiBankTanggal DESC,
SUBSTRING_INDEX(transaksiBankNomor, '/', -1)+0 DESC
LIMIT %s, %s
";

$sql['count']        = "
SELECT FOUND_ROWS() AS `count`
";

$sql['set_number']   = "SET @tb_number    = '';";
$sql['do_set_number']   = "
SELECT
   CONCAT_WS(
      '/',
      UPPER('%s'),
      EXTRACT(YEAR FROM DATE(NOW())),
      LPAD(EXTRACT(MONTH FROM DATE(NOW())),2,0),
      LPAD(IFNULL(
         MAX(
            SUBSTRING_INDEX(transaksiBankNomor, '/', - 1)+0
         )+1,
         1
      ), 3, 0)
   ) INTO @tb_number
FROM
   finansi_pa_transaksi_bank
WHERE 1 = 1
   AND EXTRACT(YEAR FROM transaksiBankTglBuat) = EXTRACT(YEAR FROM DATE(NOW()))
   AND EXTRACT(MONTH FROM transaksiBankTglBuat) = EXTRACT(MONTH FROM DATE(NOW()))
   AND UPPER(SUBSTRING_INDEX(transaksiBankNomor, '/', 1)) = UPPER('%s')
";

$sql['do_insert_transaksi_bank']    = "
INSERT INTO finansi_pa_transaksi_bank
SET transaksiBankNomor = @tb_number,
   transaksiBankBpkb = '%s',
   transaksiBankTanggal = '%s',
   transaksiBankCoaIdPenerima = '%s',
   transaksiBankPenerima = '%s',
   transaksiBankRekeningPenerima = '%s',
   transaksiBankCoaIdTujuan = '%s',
   transaksiBankTujuan = '%s',
   transaksiBankRekeningTujuan = '%s',
   transaksiBankNominal = '%s',
   transaksiBankTipe = 'penerimaan',
   transaksiBankUserId = '%s'
";

$sql['do_update_transaksi_bank']       = "
UPDATE finansi_pa_transaksi_bank
SET transaksiBankBpkb = '%s',
   transaksiBankTanggal = '%s',
   transaksiBankCoaIdPenerima = '%s',
   transaksiBankPenerima = '%s',
   transaksiBankRekeningPenerima = '%s',
   transaksiBankCoaIdTujuan = '%s',
   transaksiBankTujuan = '%s',
   transaksiBankRekeningTujuan = '%s',
   transaksiBankNominal = '%s',
   transaksiBankUserId = '%s'
WHERE transaksiBankId = '%s'
";

$sql['do_insert_transaksi_bank_detil'] = "
INSERT INTO finansi_pa_transaksi_bank_detil
SET transaksiBankDetilTransaksiBankId = '%s',
   transaksiBankDetilKompId = '%s',
   transaksiBankDetilNama = '%s',
   transaksiBankDetilTanggal = '%s',
   transaksiBankDetilNominal = '%s',
   transaksiBankDetilUserid = '%s'
";

$sql['do_delete_transaksi_bank_detail_transaksi']  = "
DELETE
FROM finansi_pa_transaksi_bank_detil
WHERE transaksiBankDetilTransaksiBankId = '%s'
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
   transaksiBankNominal AS nominal
FROM finansi_pa_transaksi_bank
WHERE 1 = 1
AND transaksiBankId = %s
LIMIT 1
";

$sql['get_list_transaksi_detil']    = "
SELECT
   transksaiBankDetilId AS id,
   IF(transaksiBankDetilNama = '' AND transaksiBankDetilKompId IS NOT NULL, kompNama, transaksiBankDetilNama) AS nama,
   transaksiBankDetilTanggal AS tanggal,
   transaksiBankDetilNominal AS nominal
FROM finansi_pa_transaksi_bank_detil
LEFT JOIN komponen
   ON kompId = transaksiBankDetilKompId
WHERE 1 = 1
AND transaksiBankDetilTransaksiBankId = %s
";

$sql['do_delete_transaksi_bank']   = "
DELETE
FROM finansi_pa_transaksi_bank
WHERE transaksiBankId = '%s'
";

//tambahan
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
   transIsJurnal = '%s',
   transKelompok = 'none',
   transIsDariAplikasiKeuangan = 'T'
";

$sql['do_delete_transaksi'] ="
DELETE FROM `transaksi` 
WHERE `transReferensi` =  (SELECT `transaksiBankBpkb` FROM `finansi_pa_transaksi_bank` WHERE `transaksiBankId` = '%s')
";

$sql['do_update_transaksi'] ="
UPDATE 
  `transaksi`
SET
  `transTanggal` = DATE(NOW()),
  `transTanggalEntri` = '%s',
  `transDueDate` = '%s',
  `transNilai` = '%s',
  `transUserId` = '%s'
WHERE `transReferensi` =  (SELECT `transaksiBankBpkb` FROM `finansi_pa_transaksi_bank` WHERE `transaksiBankId` = '%s')
";

$sql['do_insert_transaksi_det_penerimaan_bank'] = "
INSERT INTO `transaksi_detail_penerimaan_bank`
SET
  `transdtPenerimaanBankTransId` = '%s',
  `transdtPenerimaanBankTBankId` = '%s'
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



$sql['do_insert_pembukuan_detail'] = "
INSERT INTO `pembukuan_detail`
SET 
 `pdPrId` = '%s',
 `pdCoaId` = '%s',
 `pdNilai` = '%s',
 `pdKeterangan` = '%s',
 `pdStatus` = '%s',
 `pdSubaccPertamaKode` = '%s',
 `pdSubaccKeduaKode` = '%s',
 `pdSubaccKetigaKode` = '%s',
 `pdSubaccKeempatKode` = '%s',
 `pdSubaccKelimaKode` = '%s',
 `pdSubaccKeenamKode` = '%s',
 `pdSubaccKetujuhKode` = '%s'
";

$sql['get_transaksi_id'] = "
SELECT transId AS transaksiId FROM `transaksi` 
WHERE `transReferensi` =  (SELECT `transaksiBankBpkb` FROM `finansi_pa_transaksi_bank` WHERE `transaksiBankId` = '%s')
";

$sql['do_delete_pembukuan_referensi'] = "
DELETE
FROM `pembukuan_referensi`
WHERE `prTransId` = (SELECT transId FROM `transaksi` 
WHERE `transReferensi` =  (SELECT `transaksiBankBpkb` FROM `finansi_pa_transaksi_bank` WHERE `transaksiBankId` = '%s'))
";

?>