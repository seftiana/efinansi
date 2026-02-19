<?php

//untuk tabel pa_transaksi_bank

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
    pd.`pdId` AS id,
    c.`coaNamaAkun` AS nama,
    pd.`pdKeterangan` AS keterangan,
    pd.`pdKeteranganTambahan` AS keterangan_tambahan,
    pr.`prTanggal` AS tanggal,
    pd.`pdNilai` AS nominal
FROM pembukuan_detail pd
    JOIN pembukuan_referensi pr ON pr.`prId` = pd.`pdPrId`
    JOIN coa c ON c.`coaId`  = pd.`pdCoaId`
WHERE	
    pr.`prTransId` =  '%s'
    AND
    pd.`pdStatus` = 'K'
";

$sql['do_insert_transaksi_bank']    = "
INSERT INTO finansi_pa_transaksi_bank
SET transaksiBankNomor = '%s',
   transaksiBankBpkb = '%s',
   transaksiBankTanggal = '%s',
   transaksiBankPenerima = '%s',
   transaksiBankTujuan = '%s',
   transaksiBankNominal = '%s',
   transaksiBankTipe = 'penerimaan',
   transaksiBankUserId = '%s',
  `transaksiBankUnitId` = '%s',
  `transaksiBankPenerimaanId` = IFNULL('%s',NULL),
  `transaksiBankPenerimaanNominal` = '%s',  
  `transaksiBankSkenarioId` = IFNULL('%s',NULL),
  `transaksiBankTipeTransaksi` =  '%s',
  `transaksiBankPembayaranMhs` = '%s',
  `transaksiBankIsAutoNomor` = '%s',
  `transaksiBankLppaId` =  IFNULL('%s',NULL)
";
$sql['do_insert_transaksi_bank_detil'] = "
INSERT INTO finansi_pa_transaksi_bank_detil
SET 
   transaksiBankDetilTransaksiBankId = '%s',
   transaksiBankDetilNama = '%s',
   transaksiBankDetilTanggal = '%s',
   transaksiBankDetilNominal = '%s',
   transaksiBankDetilUserid = '%s'
";

$sql['do_insert_transaksi_det_penerimaan_bank'] = "
INSERT INTO `transaksi_detail_penerimaan_bank`
SET
  `transdtPenerimaanBankTransId` = '%s',
  `transdtPenerimaanBankTBankId` = '%s'
";

//update
$sql['do_update_transaksi_bank']       = "
UPDATE finansi_pa_transaksi_bank
SET
   transaksiBankNomor = '%s',
   transaksiBankBpkb = '%s',
  `transaksiBankTanggal` = '%s',
  `transaksiBankPenerima` = '%s',
  `transaksiBankTujuan` = '%s',
  `transaksiBankNominal` = '%s',
  `transaksiBankUserId` = '%s',
  `transaksiBankUnitId` = '%s',
  `transaksiBankPenerimaanId` =  IFNULL('%s',NULL),
  `transaksiBankPenerimaanNominal` = '%s',
  `transaksiBankSkenarioId` = IFNULL('%s',NULL),
  `transaksiBankTipeTransaksi` =  '%s',
  `transaksiBankPembayaranMhs` = '%s',
  `transaksiBankIsAutoNomor` = '%s',
  `transaksiBankLppaId` =  IFNULL('%s',NULL)
WHERE `transaksiBankId` =  (SELECT `transdtPenerimaanBankTBankId` 
FROM `transaksi_detail_penerimaan_bank` WHERE `transdtPenerimaanBankTransId` = %s)
";

$sql['do_update_transaksi_bank_detil'] = "
INSERT INTO finansi_pa_transaksi_bank_detil
SET 
   transaksiBankDetilTransaksiBankId =  (SELECT `transdtPenerimaanBankTBankId` 
FROM `transaksi_detail_penerimaan_bank` WHERE `transdtPenerimaanBankTransId` = %s),
   transaksiBankDetilNama = '%s',
   transaksiBankDetilTanggal = '%s',
   transaksiBankDetilNominal = '%s',
   transaksiBankDetilUserid = '%s'
";

//delete
$sql['do_delete_transaksi_bank_detail_transaksi']  = "
DELETE
FROM finansi_pa_transaksi_bank_detil
WHERE transaksiBankDetilTransaksiBankId = (SELECT `transdtPenerimaanBankTBankId` 
FROM `transaksi_detail_penerimaan_bank` WHERE `transdtPenerimaanBankTransId` = %s)
";

$sql['do_delete_transaksi_bank']   = "
DELETE
FROM finansi_pa_transaksi_bank
WHERE transaksiBankId = (SELECT `transdtPenerimaanBankTBankId` 
FROM `transaksi_detail_penerimaan_bank` WHERE `transdtPenerimaanBankTransId` = %s)
";

$sql['do_delete_transaksi_det_penerimaan_bank'] = "
DELETE FROM `transaksi_detail_penerimaan_bank`
WHERE `transdtPenerimaanBankTransId` = '%s'
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

//end tabel pa transaksi bank

$sql['get_cek_noref'] ="
SELECT
  COUNT(`transReferensi`) AS total
FROM `transaksi`
WHERE `transReferensi` ='%s';
";
    
$sql['get_setting_value']        = "
SELECT
   settingValue AS 'setting'
FROM setting
WHERE UPPER(settingName) = '%s'
LIMIT 1
";

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
AND UPPER(subaccPertamaNama) = 'DEFAULT' OR subaccPertamaKode = '00'
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

$sql['get_sub_account_combobox'] = "
SELECT
   CONCAT_WS('-', TRIM(BOTH FROM subaccPertamaKode),
   TRIM(BOTH FROM subaccKeduaKode),
   TRIM(BOTH FROM subaccKetigaKode),
   TRIM(BOTH FROM subaccKeempatKode),
   TRIM(BOTH FROM subaccKelimaKode),
   TRIM(BOTH FROM subaccKeenamKode),
   TRIM(BOTH FROM subaccKetujuhKode)) AS kode,
   CONCAT(TRIM(BOTH FROM subaccPertamaKode), ' - ',subaccPertamaNama) AS nama
FROM finansi_keu_ref_subacc_1
LEFT JOIN finansi_keu_ref_subacc_2
   ON subaccKeduaKode = 00
LEFT JOIN finansi_keu_ref_subacc_3
   ON subaccKetigaKode = 00
LEFT JOIN finansi_keu_ref_subacc_4
  ON subaccKeempatKode = 00
LEFT JOIN finansi_keu_ref_subacc_5
   ON subaccKelimaKode =  00
LEFT JOIN finansi_keu_ref_subacc_6
   ON subaccKeenamKode = 00
LEFT JOIN finansi_keu_ref_subacc_7
   ON subaccKetujuhKode = 00
";

$sql['get_min_max_tahun_pencatatan'] = "
SELECT
   YEAR(MIN(transTanggalEntri)) - 5 AS minTahun,
   YEAR(MAX(transTanggalEntri)) + 5 AS maxTahun
FROM
   transaksi
";

$sql['get_bentuk_transaksi']        = "
   SELECT
      kelJnsId AS id,
      kelJnsNama AS name
   FROM
      kelompok_jenis_laporan_ref
   WHERE kelJnsPrntId = '2'
";

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

$sql['get_set_tahun_anggaran']   = "
SELECT
   thanggarId INTO @tahun_anggaran
FROM
   tahun_anggaran
WHERE thanggarIsAktif = 'Y'
LIMIT 1
";

$sql['get_set_reference_number'] = "
SELECT
   CONCAT('BM', IFNULL(MAX(SUBSTRING(SUBSTRING_INDEX(generate_number.transReferensi, '/', 1), 3)+0)+1,1), '/', tmp_unit.unitkerjaKode, '/', LPAD(EXTRACT(MONTH FROM '%s'), 2, 0), '/', EXTRACT(YEAR FROM '%s')) INTO @REFERENCE_NUMBER
FROM transaksi AS generate_number
JOIN unit_kerja_ref AS tmp_unit
   ON tmp_unit.unitkerjaId = %s
WHERE 1 = 1
AND generate_number.transUnitkerjaId = '%s'
AND UPPER(SUBSTRING(SUBSTRING_INDEX(generate_number.transReferensi, '/', 1), 1, 2)) = 'BM'
AND EXTRACT(MONTH FROM generate_number.transTanggalEntri) = EXTRACT(MONTH FROM '%s')
AND EXTRACT(YEAR FROM generate_number.transTanggalEntri) = EXTRACT(YEAR FROM '%s')
";

$sql['do_set_realname_user']  = "
SELECT RealName INTO @real_name FROM gtfw_user WHERE UserId = %s
";

$sql['do_save_transaksi']     = "
INSERT INTO transaksi
SET transTtId = '1',
   transTransjenId = '8',
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

//LOGGER LOGGER LOGGER

$sql['do_add_log'] = "
   INSERT INTO logger(logUserId, logAlamatIp, logUpdateTerakhir, logKeterangan)
   VALUES ('%s', '%s', NOW(), '%s')
";

$sql['do_add_log_detil'] = "
   INSERT INTO logger_detail(logId, logAksiQuery)
   VALUES ('%s', '%s')
";

$sql['do_update_transaksi_jurnal']  = "
UPDATE transaksi
SET 
   transUnitkerjaId = '%s',
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

$sql['get_data_jurnal']    = "
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
   IF(tmp_pr.summary < tmp_pr.jurnal, 'YES', 'NO') AS hasJurnal
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
AND transTtId = 1
AND transTransjenId = 8
AND transReferensi LIKE '%s'
AND (prIsPosting = %s OR 1 = %s)
AND transTanggalEntri BETWEEN '%s' AND '%s'
ORDER BY SUBSTR(SUBSTRING_INDEX(transReferensi, '/' , 1), 3)+0 ASC
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

$sql['get_count_jurnal']   = "
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
AND transTtId = 1
AND transTransjenId = 8
AND transReferensi LIKE '%s'
AND (prIsPosting = %s OR 1 = %s)
AND transTanggalEntri BETWEEN '%s' AND '%s'
";

$sql['get_data_jurnal_detail']      = "
SELECT
   tppId,
   tppTanggalAwal,
   tppTanggalAkhir,
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitKerjaId,
   unitkerjaKode AS unitKerjaKode,
   unitkerjaNama AS unitKerjaNama,
   IF(
      UPPER(instituteNama) IN ('PERBANAS INSTITUTE', 'UNIVERSITAS') OR
	   UPPER(yayasanNama) IN ('PERBANAS INSTITUTE', 'UNIVERSITAS')
   ,'Y','T') AS isInstitute,
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
   pb.`transaksiBankId` AS tbankId,
   pb.`transaksiBankPenerima` AS namaPenyetor,
   pb.`transaksiBankTujuan` AS namaPenerima,
   # referensi transaksi
   if(pb.transaksiBankTipeTransaksi ='lppa_sisa',
   	pb.transaksiBankLppaId , pb.`transaksiBankPenerimaanId`) AS rpenId,
   if(pb.transaksiBankTipeTransaksi ='lppa_sisa',lppa.noref,kpr.`kodeterimaNama`) AS rpenNama,
   pb.`transaksiBankPenerimaanNominal` AS rpenNominal,
   # end ref 
   pb.`transaksiBankSkenarioId` AS skenarioId,
   jk.`jurkodeNama` AS skenario_nama,
   jk.`jurkodeIdJenisBiaya` AS jenis_biaya_id,
   jk.`jurkodeNamaJenisBiaya` AS jenis_biaya_nama,
   jk.`jurkodeMetodeCatat` AS tipe_bayar_skenario,
   pb.`transaksiBankTipeTransaksi`AS tipeTransaksi,
   pb.`transaksiBankPembayaranMhs` AS pembNominal,
   pb.`transaksiBankIsAutoNomor` AS isAutoNumber,
   IFNULL(tr_pemb.`pembProdiId`,tr_pemb_dep_masuk.pembProdiId) AS pemb_prodi_id,
   IFNULL(tr_pemb.`pembProdiNama`,tr_pemb_dep_masuk.pembProdiNama) AS pemb_prodi_nama,
   IFNULL(tr_pemb.`pembJenisBiayaNama`,tr_pemb_dep_masuk.pembTipeBayar) AS pemb_jenis_biaya,
   SUM(tr_pemb.`pembNominal`) AS pemb_nominal,
   SUM(tr_pemb.`pembPotongan`) AS pemb_potongan,
   SUM(tr_pemb.`pembDeposit`) AS pemb_deposit,
   tr_pemb_dep_masuk.`pembDepositMasuk` AS pemb_deposit_masuk,
   tr_pemb_dep_masuk.`pembKeterangan` AS pemb_keterangan,
   tr_pemb_dep_masuk.`pembPenanggungJawab` AS pemb_penanggung_jawab,
   tr_pemb_dep_masuk.`pembIdsDetail` AS pemb_id_detail,
   tr_pemb.`pembIdsDetailTagihan` AS pemb_id_detil,
   tr_pemb.`pembTipeBayar` AS pemb_tipe_pembayaran
FROM transaksi
JOIN unit_kerja_ref
   ON unitkerjaId = transUnitkerjaId
JOIN tahun_anggaran
   ON thanggarId = transThanggarId
JOIN tahun_pembukuan_periode
   ON tppId = transTppId
JOIN pembukuan_referensi
   ON prTransId = transId
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
JOIN transaksi_detail_penerimaan_bank tpb
     ON tpb.`transdtPenerimaanBankTransId` = transId
JOIN finansi_pa_transaksi_bank pb
     ON pb.`transaksiBankId` = tpb.`transdtPenerimaanBankTBankId`
     AND pb.`transaksiBankBpkb` = `transReferensi`   
LEFT JOIN rencana_penerimaan rpen
	ON rpen.`renterimaId` = pb.`transaksiBankPenerimaanId`
	AND rpen.`renterimaUnitkerjaId` = unitkerjaId
	AND rpen.`renterimaUnitkerjaId` = pb.`transaksiBankUnitId`
LEFT JOIN kode_penerimaan_ref kpr
	ON kpr.kodeterimaId = rpen.renterimaKodeterimaId	
LEFT JOIN jurnal_kode jk
   ON jk.`jurkodeId` = pb.`transaksiBankSkenarioId`  
LEFT JOIN `finansi_pa_transaksi_pembayaran` tr_pemb
   ON tr_pemb.`pembTBankId` = pb.`transaksiBankId`
LEFT JOIN `finansi_pa_transaksi_pembayaran_deposit_masuk` tr_pemb_dep_masuk
   ON tr_pemb_dep_masuk.`pembTBankId` = pb.`transaksiBankId`
left join (
	select 
		lppa.lapLppaId as id,
		pr.pengrealNomorPengajuan as noref
	from finansi_pa_lap_lppa lppa join pengajuan_realisasi pr 
	on pr.pengrealId = lppa.lapLppaRealisasiId 
) lppa on lppa.id = pb.transaksiBankLppaId 
LEFT JOIN (
   SELECT
      unitkerjaKodeSistem AS yayasanKode,
      unitkerjaNama AS yayasanNama
   FROM unit_kerja_ref
) AS yayasan ON SUBSTRING_INDEX(unitkerjaKodeSistem,'.',1) = yayasanKode
LEFT JOIN (
   SELECT
      unitkerjaKodeSistem AS instituteKode,
      unitkerjaNama AS instituteNama
   FROM unit_kerja_ref
) AS institute ON SUBSTRING_INDEX(unitkerjaKodeSistem,'.',2) = instituteKode
WHERE 1 = 1
AND transId = %s
AND prId = %s
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
ORDER BY kode,pdStatus
";

$sql['get_history_jurnal']    = "
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
   kelJnsNama
FROM transaksi
JOIN unit_kerja_ref
   ON unitkerjaId = transUnitkerjaId
JOIN tahun_anggaran
   ON thanggarId = transThanggarId
JOIN tahun_pembukuan_periode
   ON tppId = transTppId
JOIN pembukuan_referensi
   ON prTransId = transId
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
AND transId = %s
ORDER BY prTanggal DESC, SUBSTR(SUBSTRING_INDEX(transReferensi, '/' , 1), 3)+0 DESC
) AS jurnal
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
ORDER BY SUBSTRING_INDEX(tmp_unit.kode, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 5), '.', -1)+0,
jurnal.tanggalPembukuan DESC, SUBSTR(SUBSTRING_INDEX(jurnal.referensi, '/' , 1), 3)+0 DESC,
jurnal.pembukuanId, pdStatus ASC
";

$sql['do_delete_pembukuan_referensi']="
DELETE FROM pembukuan_referensi WHERE prId = %s;
";

$sql['do_delete_transaksi']="
DELETE FROM transaksi WHERE transId = %s;
";


// tambahan
// tabel finansi_
$sql['do_insert_transaksi_pembayaran'] ="
INSERT INTO `finansi_pa_transaksi_pembayaran`(
    `pembTBankId`,
    `pembProdiId`,
    `pembProdiNama`,
    `pembJenisBiayaNama`,
    `pembNominal`,
    `pembPotongan`,
    `pembDeposit`,
    `pembTipeBayar`,
    `pembIdsDetailTagihan`)
VALUES (
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s'
)
";

$sql['do_update_transaksi_pembayaran'] ="
UPDATE `finansi_pa_transaksi_pembayaran`
SET 
  `pembProdiId` = '%s',
  `pembProdiNama` = '%s',
  `pembJenisBiayaNama` = '%s',
  `pembNominal` = '%s',
  `pembPotongan` = '%s',
  `pembDeposit` =  '%s',
  `pembTipeBayar` = '%s',
  `pembIdsDetailTagihan` = '%s'
WHERE `pembTBankId` = (SELECT `transdtPenerimaanBankTBankId` 
FROM `transaksi_detail_penerimaan_bank` WHERE `transdtPenerimaanBankTransId` = %s)";

$sql['delete_transaksi_pembayaran'] ="
DELETE FROM
  `finansi_pa_transaksi_pembayaran` 
WHERE `pembTBankId` = (SELECT `transdtPenerimaanBankTBankId` 
FROM `transaksi_detail_penerimaan_bank` WHERE `transdtPenerimaanBankTransId` = %s)";      

$sql['get_transaksi_pembayaran_id_tagihan_detil'] = "
SELECT
  `pembIdsDetailTagihan` AS id_detil,
  `pembTipeBayar` AS tipe_pembayaran
FROM `finansi_pa_transaksi_pembayaran`
WHERE
  `pembTBankId` = (SELECT `transdtPenerimaanBankTBankId` 
FROM `transaksi_detail_penerimaan_bank` WHERE `transdtPenerimaanBankTransId` = %s)";


$sql['get_tr_bank_id']="
SELECT 
    `transdtPenerimaanBankTBankId` AS bank_id
FROM `transaksi_detail_penerimaan_bank` 
WHERE `transdtPenerimaanBankTransId` = %s";


//insert ke table pembayaran

$sql['do_insert_trans_pembayaran']="
INSERT INTO `finansi_pa_transaksi_pembayaran`
SET            
  `pembTBankId` = '%s',
  `pembProdiId` = '%s',
  `pembProdiNama` = '%s',
  `pembJenisBiayaId` = '%s',
  `pembJenisBiayaNama` = '%s',
  `pembNominal` = '%s',
  `pembPotongan` = '%s',
  `pembDeposit` = '%s',
  `pembKeterangan` = '%s',
  `pembIdsDetailTagihan` = '%s',
  `pembTipeBayar` = '%s'
";

$sql['do_insert_trans_pembayaran_dep_masuk']="
INSERT INTO `finansi_pa_transaksi_pembayaran_deposit_masuk`
SET
   `pembTBankId` = '%s',
   `pembProdiId` = '%s',
   `pembProdiNama` = '%s',
   `pembDepositMasuk` = '%s',
   `pembTipeBayar` = '%s',
   `pembKeterangan` = '%s',
   `pembPenanggungJawab` = '%s',
   `pembIdsDetail` = '%s'
";


$sql['do_delete_trans_pembayaran'] = "
DELETE FROM `finansi_pa_transaksi_pembayaran`
WHERE `pembTBankId` = (SELECT `transdtPenerimaanBankTBankId` 
FROM `transaksi_detail_penerimaan_bank` WHERE `transdtPenerimaanBankTransId` = %s)
";

$sql['do_delete_trans_pembayaran_dep_masuk'] ="
DELETE FROM `finansi_pa_transaksi_pembayaran_deposit_masuk`
WHERE `pembTBankId` =  (SELECT `transdtPenerimaanBankTBankId` 
FROM `transaksi_detail_penerimaan_bank` WHERE `transdtPenerimaanBankTransId` = %s)
";

$sql['get_data_pembayaran_by_trans_id'] = "
SELECT
  `pembProdiId` AS prodi_id,
  `pembProdiNama` AS prodi_nama,
  `pembJenisBiayaId` AS jenis_biaya_id,
  `pembJenisBiayaNama` AS jenis_biaya_nama,
  `pembNominal` AS nominal,
  `pembPotongan` AS potongan,
  `pembDeposit` AS deposit,
  `pembKeterangan` AS keterangan,
  `pembIdsDetailTagihan` AS id_detail,
  `pembTipeBayar` AS tipe
FROM `finansi_pa_transaksi_pembayaran`
WHERE
`pembTBankId` = (SELECT `transdtPenerimaanBankTBankId` 
    FROM `transaksi_detail_penerimaan_bank` WHERE `transdtPenerimaanBankTransId` = %s)
";

//end

$sql['get_id_detail_tagihan'] ="
SELECT
  `pembIdsDetailTagihan` AS id_detail_tagihan
FROM `finansi_pa_transaksi_pembayaran`
WHERE
`pembTBankId` = (SELECT `transdtPenerimaanBankTBankId` 
    FROM `transaksi_detail_penerimaan_bank` WHERE `transdtPenerimaanBankTransId` = %s)
";

$sql['get_id_detail_tagihan_deposit_masuk'] ="
SELECT
  `pembIdsDetail` AS id_detail_tagihan
FROM `finansi_pa_transaksi_pembayaran_deposit_masuk`
WHERE
`pembTBankId` = (SELECT `transdtPenerimaanBankTBankId` 
    FROM `transaksi_detail_penerimaan_bank` WHERE `transdtPenerimaanBankTransId` = %s)
";

$sql['get_tipe_transaksi_pemb'] ="
SELECT 
    `transaksiBankTipeTransaksi` AS pemb_tipe_transaksi
FROM
    `finansi_pa_transaksi_bank`
WHERE
`transaksiBankId` = (
    SELECT `transdtPenerimaanBankTBankId`
        FROM `transaksi_detail_penerimaan_bank`
    WHERE `transdtPenerimaanBankTransId` = %s
)
";


$sql['get_id_transaksi_pembayaran'] ="
SELECT
  `pembId` AS id
FROM `finansi_pa_transaksi_pembayaran`
WHERE
`pembTBankId` = (SELECT `transdtPenerimaanBankTBankId` 
    FROM `transaksi_detail_penerimaan_bank` WHERE `transdtPenerimaanBankTransId` = %s)
";

$sql['get_id_transaksi_pembayaran_deposit_masuk'] ="
SELECT
  `pembDepMasukId` AS id_detail_tagihan
FROM `finansi_pa_transaksi_pembayaran_deposit_masuk`
WHERE
`pembTBankId` = (SELECT `transdtPenerimaanBankTBankId` 
    FROM `transaksi_detail_penerimaan_bank` WHERE `transdtPenerimaanBankTransId` = %s)
";


$sql['get_transaksi_bank_id'] ="
SELECT `transdtPenerimaanBankTBankId` as bankId
FROM `transaksi_detail_penerimaan_bank` WHERE `transdtPenerimaanBankTransId` = %s
LIMIT 1
"

?>