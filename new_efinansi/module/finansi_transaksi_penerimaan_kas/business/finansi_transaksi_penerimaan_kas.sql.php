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

$sql['get_data_transaksi'] = "
SELECT 
  SQL_CALC_FOUND_ROWS 
  transaksiKasId AS id,
  transaksiKasNomor AS kode,
  transaksiKasBpkb AS bpkb,
  transaksiKasTanggal AS tanggal,
  transaksiKasPenerima AS namaPenyetor,
  transaksiKasRekeningPenerima AS rekeningPenyetor,
  transaksiKasTujuan AS kasTujuan,
  transaksiKasRekeningTujuan AS rekeningTujuan,
  IFNULL(transaksi.nominal, 0) AS nominal,
  jurnal.tp_id AS tpId,
  jurnal.is_jurnal AS isJurnal,
  jurnal.is_jurnal_approve AS isJurnalApprove
FROM finansi_pa_transaksi_kas
JOIN (SELECT
   transaksiKasDetilTransaksiKasId AS id,
   SUM(transaksiKasDetilNominal) AS nominal
FROM finansi_pa_transaksi_kas_detil
GROUP BY transaksiKasDetilTransaksiKasId) AS transaksi
    ON transaksi.id = transaksiKasId 
  LEFT JOIN (
  SELECT 
  tr.`transId` AS tr_id,
  tpkas.`transdtPenerimaanKasTKasId` AS tr_kas_id,
  tr.`transTppId` AS tp_id,
  tr.`transIsJurnal` AS is_jurnal,
  pr.`prIsApproved` AS is_jurnal_approve
FROM
  `transaksi_detail_penerimaan_kas` tpkas 
  JOIN `transaksi` tr 
  ON tr.`transId` = tpkas.`transdtPenerimaanKasTransId`
  JOIN pembukuan_referensi pr
  ON pr.`prTransId` = tr.`transId`
  ) AS jurnal
  ON jurnal.tr_kas_id =  transaksiKasId 
WHERE 1 = 1
AND transaksiKasBpkb LIKE '%s'
AND transaksiKasTanggal BETWEEN '%s' AND '%s'
ORDER BY transaksiKasTanggal DESC,
SUBSTRING_INDEX(transaksiKasNomor, '/', -1)+0 DESC
LIMIT %s, %s
";

$sql['count']        = "
SELECT FOUND_ROWS() AS `count`
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
            SUBSTRING_INDEX(transaksiKasNomor, '/', - 1)+0
         )+1,
         1
      ), 3, 0)
   ) INTO @tb_number
FROM
   finansi_pa_transaksi_kas
WHERE 1 = 1
   AND EXTRACT(YEAR FROM transaksiKasTglBuat) = EXTRACT(YEAR FROM DATE(NOW()))
   AND EXTRACT(MONTH FROM transaksiKasTglBuat) = EXTRACT(MONTH FROM DATE(NOW()))
   AND UPPER(SUBSTRING_INDEX(transaksiKasNomor, '/', 1)) = UPPER('%s')
";

$sql['do_insert_transaksi_kas']    = "
INSERT INTO finansi_pa_transaksi_kas
SET transaksiKasNomor = @tb_number,
   transaksiKasBpkb = '%s',
   transaksiKasTanggal = '%s',
   transaksiKasCoaIdPenerima = '%s',
   transaksiKasPenerima = '%s',
   transaksiKasRekeningPenerima = '%s',
   transaksiKasCoaIdTujuan = '%s',
   transaksiKasTujuan = '%s',
   transaksiKasRekeningTujuan = '%s',
   transaksiKasNominal = '%s',
   transaksiKasUserId = '%s'
";

$sql['do_update_transaksi_kas']       = "
UPDATE finansi_pa_transaksi_kas
SET transaksiKasBpkb = '%s',
   transaksiKasTanggal = '%s',
   transaksiKasCoaIdPenerima = '%s',
   transaksiKasPenerima = '%s',
   transaksiKasRekeningPenerima = '%s',
   transaksiKasCoaIdTujuan = '%s',
   transaksiKasTujuan = '%s',
   transaksiKasRekeningTujuan = '%s',
   transaksiKasNominal = '%s',
   transaksiKasUserId = '%s'
WHERE transaksiKasId = '%s'
";

$sql['do_insert_transaksi_kas_detil'] = "
INSERT INTO finansi_pa_transaksi_kas_detil
SET transaksiKasDetilTransaksiKasId = '%s',
   transaksiKasDetilSppuDetId = '%s',
   transaksiKasDetilNama = '%s',
   transaksiKasDetilTanggal = '%s',
   transaksiKasDetilNominal = '%s',
   transaksiKasDetilUserid = '%s'
";

$sql['do_delete_transaksi_kas_detail_transaksi']  = "
DELETE
FROM finansi_pa_transaksi_kas_detil
WHERE transaksiKasDetilTransaksiKasId = '%s'
";

$sql['get_transaksi_detail']     = "
SELECT
   transaksiKasId AS id,
   transaksiKasNomor AS nomor,
   transaksiKasBpkb AS bpkb,
   transaksiKasTanggal AS tanggal,
   transaksiKasCoaIdPenerima AS coaIdPenyetor,
   transaksiKasPenerima AS namaPenyetor,
   transaksiKasRekeningPenerima AS rekeningPenyetor,
   transaksiKasKeterangan AS keterangan,
   transaksiKasCoaIdTujuan AS coaIdPenerima,
   transaksiKasTujuan AS kasPenerima,
   transaksiKasRekeningTujuan AS rekeningPenerima,
   transaksiKasNominal AS nominal
FROM finansi_pa_transaksi_kas
WHERE 1 = 1
AND transaksiKasId = %s
LIMIT 1
";

$sql['get_list_transaksi_detil']    = "
SELECT
   transaksiKasDetilSppuDetId AS id,
   sppuDetSppuId AS pid,
   transaksiKasDetilNama AS nama,
   transaksiKasDetilTanggal AS tanggal,
   transaksiKasDetilNama AS keterangan,
   transaksiKasDetilNominal AS nominal
FROM finansi_pa_transaksi_kas_detil
 JOIN  `finansi_pa_sppu_det`
   ON sppuDetId = transaksiKasDetilSppuDetId
 JOIN finansi_pa_transaksi_kas
   ON transaksiKasId = transaksiKasDetilTransaksiKasId
WHERE 1 = 1
AND transaksiKasId = %s
";

$sql['do_delete_transaksi_kas']   = "
DELETE
FROM finansi_pa_transaksi_kas
WHERE transaksiKasId = '%s'
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
WHERE `transReferensi` =  (SELECT `transaksiKasBpkb` FROM `finansi_pa_transaksi_kas` WHERE `transaksiKasId` = '%s')
";

$sql['do_update_transaksi'] ="
UPDATE 
  `transaksi`
SET
   `transReferensi` ='%s',
  `transTanggal` = DATE(NOW()),
  `transTanggalEntri` = '%s',
  `transDueDate` = '%s',
  `transNilai` = '%s',
  `transUserId` = '%s'
WHERE `transId` =  (
SELECT 
  `transdtPenerimaanKasTransId`
FROM
 `transaksi_detail_penerimaan_kas`
WHERE `transdtPenerimaanKasTKasId`= '%s')
";

$sql['do_insert_transaksi_det_penerimaan_kas'] = "
INSERT INTO `transaksi_detail_penerimaan_kas`
SET
  `transdtPenerimaanKasTransId` = '%s',
  `transdtPenerimaanKasTKasId` = '%s'
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
SELECT 
  `transdtPenerimaanKasTransId` AS transaksiId 
FROM
 `transaksi_detail_penerimaan_kas`
WHERE `transdtPenerimaanKasTKasId`= '%s'
";

$sql['do_delete_pembukuan_detail'] = "
DELETE
FROM `pembukuan_detail`
WHERE `pdPrId` =  (SELECT
  `prId`
FROM `pembukuan_referensi`
JOIN  `transaksi_detail_penerimaan_kas` 
ON  `transdtPenerimaanKasTransId`= `prTransId`
WHERE `transdtPenerimaanKasTKasId`= '%s')
";


$sql['do_delete_pembukuan_referensi'] = "
DELETE
FROM `pembukuan_referensi`
WHERE `prTransId` =  (
SELECT 
  `transdtPenerimaanKasTransId`
FROM
 `transaksi_detail_penerimaan_kas`
WHERE `transdtPenerimaanKasTKasId`= '%s')
";

$sql['do_update_sppu_status'] = "
UPDATE `finansi_pa_sppu`
SET `sppuIsTransaksiKas` = '%s'
WHERE `sppuId` = '%s'
";

$sql['update_nomor_cr'] = "
UPDATE `finansi_pa_sppu`
SET `sppuBPKBCr` = '%s'
WHERE `sppuId` = '%s'
";

$sql['do_update_hapus_nomor_cr'] = "
UPDATE `finansi_pa_sppu`
SET `sppuBPKBCr` = '%s'
WHERE `sppuId` IN (
SELECT
  `sppuDetSppuId` AS sppuId
FROM `finansi_pa_sppu_det`
WHERE
`sppuDetId` IN (
SELECT 
  tkd.`transaksiKasDetilSppuDetId` 
FROM
  `finansi_pa_transaksi_kas_detil` tkd 
  JOIN `finansi_pa_transaksi_kas` tk 
    ON tk.`transaksiKasId` = tkd.`transaksiKasDetilTransaksiKasId`
  JOIN `transaksi_detail_penerimaan_kas` tdpk
    ON tdpk.`transdtPenerimaanKasTKasId` = tk.`transaksiKasId`
WHERE
 tdpk.`transdtPenerimaanKasTKasId`= '%s'
 )
GROUP BY  `sppuDetSppuId`)
";

$sql['do_update_sppu_status_transaksi_by_kas_id'] = "
UPDATE `finansi_pa_sppu`
SET `sppuIsTransaksiKas` = '%s'
WHERE `sppuId` IN (
SELECT
  `sppuDetSppuId` AS sppuId
FROM `finansi_pa_sppu_det`
WHERE
`sppuDetId` IN (
SELECT 
  tkd.`transaksiKasDetilSppuDetId` 
FROM
  `finansi_pa_transaksi_kas_detil` tkd 
  JOIN `finansi_pa_transaksi_kas` tk 
    ON tk.`transaksiKasId` = tkd.`transaksiKasDetilTransaksiKasId`
  JOIN `transaksi_detail_penerimaan_kas` tdpk
    ON tdpk.`transdtPenerimaanKasTKasId` = tk.`transaksiKasId`
WHERE
 tdpk.`transdtPenerimaanKasTKasId`= '%s'
 )
GROUP BY  `sppuDetSppuId`)
";

?>