<?php

$sql['get_count_jurnal_terposting'] ="
SELECT
  COUNT(pr.`prId`) AS total_rows
FROM
  `pembukuan_referensi` pr
   JOIN transaksi tr ON tr.`transId`= pr.`prTransId`
WHERE
pr.`prIsApproved` = 'Y'
AND
pr.`prIsPosting` = 'Y'
AND
transTppId = (SELECT `tppId` FROM `tahun_pembukuan_periode` WHERE `tppIsBukaBuku` = 'Y')
";

$sql['get_minmax_tahun_transaksi'] = "
SELECT
   YEAR(MIN(transDueDate)) - 5 AS minTahun,
   YEAR(MAX(transDueDate)) + 5 AS maxTahun
FROM
   transaksi
";

$sql['set_tahun_pembukuan']   = "
SET @tahun_pembukuan    = ''
";

$sql['set_tahun_anggaran']    = "
SET @tahun_anggaran     = ''
";

$sql['set_coa_laba_rugi']     = "
SET @coa_laba_rugi   = ''
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

$sql['do_set_coa_laba_rugi']     = "
SELECT coaId INTO @coa_laba_rugi  FROM coa WHERE `coaIsLabaRugiThJln` = '1' ORDER BY coaId ASC 
LIMIT 1
";

$sql['get_set_coa_laba_rugi']    = "
SELECT IF(@coa_laba_rugi = '', NULL, @coa_laba_rugi) AS coa_id
";

$sql['get_last_posting']   = "
SELECT
   MAX(bbTanggal) AS lastPosting
FROM buku_besar_his
JOIN pembukuan_referensi
   ON prId = bbPembukuanRefId
JOIN transaksi
   ON transId = prTransId
WHERE 1 = 1
AND transTppId = @tahun_pembukuan
AND transThanggarId = @tahun_anggaran
";

$sql['get_last_transaksi'] = "
SELECT
   MAX(prTanggal) AS lastTransaksi,
   MIN(prTanggal) AS firstTransaksi
FROM pembukuan_referensi
JOIN transaksi
   ON transId = prTransId
WHERE 1 = 1
AND transTppId = @tahun_pembukuan
AND transThanggarId = @tahun_anggaran
";

$sql['get_data_jurnal_due_date'] = "
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
   transTanggalEntri AS tanggalEntry,
   transDueDate AS tanggal,
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
   ON  tppIsBukaBuku ='Y'
   and  (tppTanggalAwal) <= (transTanggalEntri)
      and  (tppTanggalAkhir) >=  (transTanggalEntri)
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
AND (transTanggalEntri BETWEEN '%s' AND '%s' OR transTanggalEntri < '%s')
 #AND transTppId = @tahun_pembukuan 
AND prIsApproved = 'Y'
AND prIsPosting = 'T'
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
pdStatus ASC
";

$sql['count_jurnal_due_date'] = "
SELECT
   COUNT(transId) AS `count`
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
AND (transTanggalEntri BETWEEN '%s' AND '%s' OR transTanggalEntri < '%s')
AND transTppId = @tahun_pembukuan 
AND prIsApproved = 'Y'
AND prIsPosting = 'T'
";

$sql['get_jurnal_detail']  = "
SELECT
   transId AS id,
   prId AS pembukuanId,
   transReferensi AS referensi,
   transTtId AS tipeJurnalId,
   ttNamaJurnal AS tipeJurnalNama,
   ttKodeTransaksi AS tipeJurnalKode,
   transDueDate AS tanggal,
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
AND prId = %s
LIMIT 1
";

$sql['do_update_pembukuan'] = "
UPDATE pembukuan_referensi SET prTanggal = '%s' WHERE prId = %s
";

$sql['do_update_transaksi']   = "
UPDATE transaksi SET transDueDate = '%s' WHERE transId = %s
";

$sql['get_pembukuan_jurnal_coa']  = "
SELECT
   bbId,
   IFNULL(bbSaldoAwal, 0) AS saldoAwal,
   IFNULL(bbSaldoAkhir, 0) AS saldoAkhir,
   prId AS pembukuanId,
   transId AS transaksiId,
   pdId AS pembukuanDetailId,
   coaId AS akunId,
   coaKodeAkun AS akunKode,
   coaNamaAkun AS akunNama,
   transCatatan AS catatan,
   pdNilai AS nominal,
   IF(UPPER(pdStatus) = 'D', pdNilai, 0) AS nominalDebet,
   IF(UPPER(pdStatus) = 'K', pdNilai, 0) AS nominalKredit,
   pdStatus AS `status`,
   coaCoaKelompokId AS akunKelompokId,
   UPPER(coaKelompokNama) AS akunKelompok,
   coaIsDebetPositif AS statusDebet,
   CONCAT_WS('-', subaccPertamaKode,
   subaccKeduaKode,
   subaccKetigaKode,
   subaccKeempatKode,
   subaccKelimaKode,
   subaccKeenamKode,
   subaccKetujuhKode) AS subAccount
FROM pembukuan_detail
JOIN pembukuan_referensi
   ON prId = pdPrId
JOIN transaksi
   ON transId = prTransId
JOIN tahun_pembukuan_periode
   ON  tppIsBukaBuku ='Y'
   and  (tppTanggalAwal) <= (transTanggalEntri)
      and  (tppTanggalAkhir) >=  (transTanggalEntri)
JOIN coa
   ON coaId = pdCoaId
LEFT JOIN coa_kelompok
   ON coaKelompokId = coaCoaKelompokId
LEFT JOIN buku_besar
   ON bbTppId = transTppId
   AND bbCoaId = pdCoaId
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
AND prIsApproved = 'Y'
AND prIsPosting = 'T'
#AND transTppId = @tahun_pembukuan
/* AND transThanggarId = @tahun_anggaran*/ 
AND (transTanggalEntri BETWEEN '%s' AND '%s' OR transTanggalEntri < '%s')
ORDER BY coaId ASC, prTanggal DESC
";

$sql['get_data_pembukuan_jurnal']   = "
SELECT
   bbId,
   IFNULL(bbSaldoAwal, 0) AS saldoAwal,
   IFNULL(bbSaldoAkhir, 0) AS saldoAkhir,
   prId AS pembukuanId,
   transId AS transaksiId,
   pdId AS pembukuanDetailId,
   coaId AS akunId,
   coaKodeAkun AS akunKode,
   coaNamaAkun AS akunNama,
   transCatatan AS catatan,
   IF(UPPER(pdStatus) = 'D', pdNilai, 0) AS nominalDebet,
   IF(UPPER(pdStatus) = 'K', pdNilai, 0) AS nominalKredit,
   pdStatus AS `status`,
   coaCoaKelompokId AS akunKelompokId,
   UPPER(coaKelompokNama) AS akunKelompok,
   coaIsDebetPositif AS statusDebet,
   CONCAT_WS('-', subaccPertamaKode,
   subaccKeduaKode,
   subaccKetigaKode,
   subaccKeempatKode,
   subaccKelimaKode,
   subaccKeenamKode,
   subaccKetujuhKode) AS subAccount
FROM pembukuan_detail
JOIN pembukuan_referensi
   ON prId = pdPrId
JOIN transaksi
   ON transId = prTransId
JOIN tahun_pembukuan_periode
   ON   tppIsBukaBuku ='Y'
   and  (tppTanggalAwal) <= (transTanggalEntri)
      and  (tppTanggalAkhir) >=  (transTanggalEntri)
JOIN coa
   ON coaId = pdCoaId
LEFT JOIN coa_kelompok
   ON coaKelompokId = coaCoaKelompokId
LEFT JOIN buku_besar
   ON bbTppId = transTppId
   AND bbCoaId = pdCoaId
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
AND prIsApproved = 'Y'
AND prIsPosting = 'T'
#AND transTppId = @tahun_pembukuan
AND transThanggarId = @tahun_anggaran
AND transTanggalEntri BETWEEN '%s' AND '%s'
ORDER BY prTanggal DESC, SUBSTR(SUBSTRING_INDEX(transReferensi, '/' , 1), 3)+0 DESC, prId, pdStatus
";

$sql['get_pembukuan_laba_rugi']     = "
SELECT
   bbId AS bb_id,
   bbSaldoAwal AS saldo_awal,
   bbSaldoAkhir AS saldo_akhir
FROM
   buku_besar
WHERE bbCoaId = @coa_laba_rugi
AND bbTppId = @tahun_pembukuan
GROUP BY bbCoaId
";

$sql['do_insert_buku_besar_sub_account']  = "
INSERT INTO buku_besar
SET bbTppId = @tahun_pembukuan,
   bbTanggal = '%s',
   bbCoaId = '%s',
   bbSubaccPertamaKode = '%s',
   bbSubaccKeduaKode = '%s',
   bbSubaccKetigaKode = '%s',
   bbSubaccKeempatKode = '%s',
   bbSubaccKelimaKode = '%s',
   bbSubaccKeenamKode = '%s',
   bbSubaccKetujuhKode = '%s',
   bbSaldoAwal = IF(%s IS NULL, 0, %s),
   bbDebet = IF(%s IS NULL, 0, %s),
   bbKredit = IF(%s IS NULL, 0, %s),
   bbSaldo = IF(%s IS NULL, 0, %s),
   bbSaldoAkhir = IF(%s IS NULL, 0, %s),
   bbUserId = '%s'
";

$sql['do_update_buku_besar_sub_account']  = "
UPDATE buku_besar
SET bbTppId = @tahun_pembukuan,
   bbTanggal = '%s',
   bbCoaId = '%s',
   bbSubaccPertamaKode = '%s',
   bbSubaccKeduaKode = '%s',
   bbSubaccKetigaKode = '%s',
   bbSubaccKeempatKode = '%s',
   bbSubaccKelimaKode = '%s',
   bbSubaccKeenamKode = '%s',
   bbSubaccKetujuhKode = '%s',
   bbSaldoAwal = IF(%s IS NULL, 0, %s),
   bbDebet = IF(%s IS NULL, 0, %s),
   bbKredit = IF(%s IS NULL, 0, %s),
   bbSaldo = IF(%s IS NULL, 0, %s),
   bbSaldoAkhir = IF(%s IS NULL, 0, %s),
   bbUserId = '%s'
WHERE bbId = '%s'
";

$sql['do_insert_buku_besar_his_sub_account'] = "
INSERT INTO buku_besar_his
SET bbTppId = @tahun_pembukuan,
   bbPembukuanRefId = '%s',
   bbPdId = '%s',
   bbTanggal = '%s',
   bbCoaId = '%s',
   bbhisSubaccPertamaKode = '%s',
   bbhisSubaccKeduaKode = '%s',
   bbhisSubaccKetigaKode = '%s',
   bbhisSubaccKeempatKode = '%s',
   bbhisSubaccKelimaKode = '%s',
   bbhisSubaccKeenamKode = '%s',
   bbhisSubaccKetujuhKode = '%s',
   bbSaldoAwal = IF(%s IS NULL, 0, %s),
   bbDebet = IF(%s IS NULL, 0, %s),
   bbKredit = IF(%s IS NULL, 0, %s),
   bbSaldo = IF(%s IS NULL, 0, %s),
   bbSaldoAkhir = IF(%s IS NULL, 0, %s),
   bbUserId = '%s'
";

$sql['do_insert_buku_besar_lr_sub_account']  = "
INSERT INTO buku_besar
SET bbTppId = @tahun_pembukuan,
   bbTanggal = '%s',
   bbCoaId = @coa_laba_rugi,
   bbSubaccPertamaKode = '%s',
   bbSubaccKeduaKode = '%s',
   bbSubaccKetigaKode = '%s',
   bbSubaccKeempatKode = '%s',
   bbSubaccKelimaKode = '%s',
   bbSubaccKeenamKode = '%s',
   bbSubaccKetujuhKode = '%s',
   bbSaldoAwal = IF(%s IS NULL, 0, %s),
   bbDebet = IF(%s IS NULL, 0, %s),
   bbKredit = IF(%s IS NULL, 0, %s),
   bbSaldo = IF(%s IS NULL, 0, %s),
   bbSaldoAkhir = IF(%s IS NULL, 0, %s),
   bbUserId = '%s'
";

$sql['do_update_buku_besar_lr_sub_account']  = "
UPDATE buku_besar
SET bbTppId = @tahun_pembukuan,
   bbTanggal = '%s',
   bbCoaId = @coa_laba_rugi,
   bbSubaccPertamaKode = '%s',
   bbSubaccKeduaKode = '%s',
   bbSubaccKetigaKode = '%s',
   bbSubaccKeempatKode = '%s',
   bbSubaccKelimaKode = '%s',
   bbSubaccKeenamKode = '%s',
   bbSubaccKetujuhKode = '%s',
   bbSaldoAwal = IF(%s IS NULL, 0, %s),
   bbDebet = IF(%s IS NULL, 0, %s),
   bbKredit = IF(%s IS NULL, 0, %s),
   bbSaldo = IF(%s IS NULL, 0, %s),
   bbSaldoAkhir = IF(%s IS NULL, 0, %s),
   bbUserId = '%s'
WHERE bbId = '%s'
";

$sql['do_insert_buku_besar_his_lr_sub_account'] = "
INSERT INTO buku_besar_his
SET bbTppId = @tahun_pembukuan,
   bbPembukuanRefId = '%s',
   bbPdId = '%s',
   bbTanggal = '%s',
   bbCoaId = @coa_laba_rugi,
   bbhisSubaccPertamaKode = '%s',
   bbhisSubaccKeduaKode = '%s',
   bbhisSubaccKetigaKode = '%s',
   bbhisSubaccKeempatKode = '%s',
   bbhisSubaccKelimaKode = '%s',
   bbhisSubaccKeenamKode = '%s',
   bbhisSubaccKetujuhKode = '%s',
   bbSaldoAwal = IF(%s IS NULL, 0, %s),
   bbDebet = IF(%s IS NULL, 0, %s),
   bbKredit = IF(%s IS NULL, 0, %s),
   bbSaldo = IF(%s IS NULL, 0, %s),
   bbSaldoAkhir = IF(%s IS NULL, 0, %s),
   bbUserId = '%s'
";


$sql['update_status_posting_pembukuan_ref'] = "
UPDATE
   pembukuan_referensi
SET
   prIsPosting = 'Y'
WHERE
   prId = '%s'
";

# ######################################################################################
$sql['do_add_log'] = "
INSERT INTO logger
   (logUserId, logAlamatIp, logUpdateTerakhir, logKeterangan)
VALUES
   ('%s', '%s', NOW(), '%s')
";

$sql['do_add_log_detil'] = "
INSERT INTO logger_detail
   (logId, logAksiQuery)
VALUES
   ('%s', '%s')
";
# ######################################################################################

$sql['get_data_pembukuan'] = "
SELECT
   prId AS pembukuan_ref_id,
   transID AS transaksi_id,
   transTanggalEntri AS transaksi_tanggal,
   transReferensi AS transaksi_referensi,
   transCatatan AS transaksi_catatan,
   pdCoaId AS coa_id,
   coaKodeAkun AS akun_kode,
   coaNamaAkun AS akun_nama,
   pdId AS pembukuan_detail_id,
   pdNilai AS nilai,
   pdStatus AS status_pembukuan,
   coaIsDebetPositif AS coa_status_debet,
   coaCoaKelompokId AS coa_kelompok
FROM
      transaksi
   JOIN pembukuan_referensi ON transId = prTransId
   JOIN pembukuan_detail ON prId = pdPrId
   JOIN coa ON pdCoaId = coaId
   JOIN coa_kelompok ON coaCoaKelompokId = coaKelompokId
   JOIN tahun_pembukuan_periode
      ON   tppIsBukaBuku ='Y'
      and  (tppTanggalAwal) <= (transTanggalEntri)
         and  (tppTanggalAkhir) >=  (transTanggalEntri)
WHERE
   prIsApproved = 'Y'
   AND
   prIsPosting = 'T'
   AND
   transTanggalEntri <= '%s'
";

$sql['cek_coa_is_debet'] = "
   SELECT
      coaIsDebetPositif AS coa_is_debet_positif
   FROM
      coa
   WHERE
      coaId = '%s'
";

$sql['cek_akun_buku_besar'] = "
   SELECT
      bbId AS bb_id,
      bbSaldoAwal AS saldo_awal,
      bbSaldoAkhir AS saldo_akhir
   FROM
      buku_besar
   WHERE
      bbCoaId = '%s'
";

//hanya mengakomodir 1 coa laba rigi saja. kalo lebih dari 1 ??
$sql['cek_akun_laba_rugi_buku_besar'] = "
   SELECT
      bbId AS bb_id,
      bbSaldoAwal AS saldo_awal,
      bbSaldoAkhir AS saldo_akhir
   FROM
      buku_besar
   WHERE
      bbCoaId = (SELECT coaId  FROM coa WHERE `coaIsLabaRugiThJln` = '1')
";

$sql['do_insert_buku_besar'] = "
   INSERT INTO buku_besar
      (bbTppId, bbTanggal, bbCoaId, bbSaldoAwal, bbDebet, bbKredit, bbSaldo, bbSaldoAkhir, bbUserId)
   VALUES ((SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'), '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');
";

$sql['do_insert_laba_rugi_buku_besar'] = "
   INSERT INTO buku_besar
      (bbTppId, bbTanggal, bbCoaId, bbSaldoAwal, bbDebet, bbKredit, bbSaldo, bbSaldoAkhir, bbUserId)
   VALUES ((SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'), '%s', (SELECT coaId  FROM coa WHERE `coaIsLabaRugiThJln` = '1' AND coaUnitkerjaId = (SELECT userunitkerjaUnitkerjaId FROM user_unit_kerja WHERE userunitkerjaUserId = %s)), '%s', '%s', '%s', '%s', '%s', '%s');
";

$sql['do_update_buku_besar'] = "
   UPDATE
      buku_besar
   SET
      bbTppId = (SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'),
      bbTanggal = '%s',
      bbCoaId = '%s',
      bbSaldoAwal = '%s',
      bbDebet = '%s',
      bbKredit = '%s',
      bbSaldo = '%s',
      bbSaldoAkhir = '%s',
      bbUserId = '%s'
   WHERE
      bbId = '%s'
";

$sql['do_update_laba_rugi_buku_besar'] = "
   UPDATE
      buku_besar
   SET
      bbTppId = (SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'),
      bbTanggal = '%s',
      bbCoaId = (SELECT coaId  FROM coa WHERE `coaIsLabaRugiThJln` = '1' AND coaUnitkerjaId = (SELECT userunitkerjaUnitkerjaId FROM user_unit_kerja WHERE userunitkerjaUserId = %s)),
      bbSaldoAwal = '%s',
      bbDebet = '%s',
      bbKredit = '%s',
      bbSaldo = '%s',
      bbSaldoAkhir = '%s',
      bbUserId = '%s'
   WHERE
      bbId = '%s'
";

$sql['do_insert_buku_besar_his'] = "
   INSERT INTO buku_besar_his
      (bbTppId, bbPembukuanRefId, bbPdId, bbTanggal, bbCoaId, bbSaldoAwal, bbDebet, bbKredit, bbSaldo, bbSaldoAkhir, bbUserId)
   VALUES ((SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'), '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');
";

$sql['do_insert_laba_rugi_buku_besar_his'] = "
   INSERT INTO buku_besar_his
      (bbTppId, bbPembukuanRefId, bbPdId, bbTanggal, bbCoaId, bbSaldoAwal, bbDebet, bbKredit, bbSaldo, bbSaldoAkhir, bbUserId)
   VALUES ((SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'), '%s', '%s', '%s', (SELECT coaId  FROM coa WHERE `coaIsLabaRugiThJln` = '1' AND coaUnitkerjaId = (SELECT userunitkerjaUnitkerjaId FROM user_unit_kerja WHERE userunitkerjaUserId = %s)), '%s', '%s', '%s', '%s', '%s', '%s');
";

$sql['get_user_id'] = "
   SELECT
      UserId AS id
   FROM
      gtfw_user
   WHERE
      UserName = '%s'
";

$sql['coa_laba_rugi'] = "
SELECT coaId  FROM coa WHERE `coaIsLabaRugiThJln` = '1'
";

$sql['get_coa_laba_rugi'] = "
SELECT
   coaKelompokId
FROM
   coa_kelompok
WHERE
   coaKelompokNama = 'Pendapatan' OR coaKelompokNama = 'Biaya';
";

$sql['cek_saldo_laba_rugi'] = "
   SELECT
      (
      SELECT
         SUM(bbSaldoAkhir)
      FROM
         buku_besar
         JOIN coa ON coaId = bbCoaId
         JOIN coa_kelompok ON coaCoaKelompokId = coaKelompokId
         JOIN coa_tipe_coa ON coatipecoaCoaId = coaId
         JOIN coa_tipe_ref  ON  coatipecoaCtrId = ctrId
      WHERE
         coaKelompokNama = 'Pendapatan'
      )AS pendapatan,
      (
      SELECT
         SUM(bbSaldoAkhir)
      FROM
         buku_besar
         JOIN coa ON coaId = bbCoaId
         JOIN coa_kelompok ON coaCoaKelompokId = coaKelompokId
         JOIN coa_tipe_coa ON coatipecoaCoaId = coaId
         JOIN coa_tipe_ref  ON  coatipecoaCtrId = ctrId
      WHERE
         coaKelompokNama = 'Biaya'
      ) AS biaya,
      (
      SELECT
         SUM(bbSaldoAkhir)
      FROM
         buku_besar
         JOIN coa ON coaId = bbCoaId
         JOIN coa_kelompok ON coaCoaKelompokId = coaKelompokId
         JOIN coa_tipe_coa ON coatipecoaCoaId = coaId
         JOIN coa_tipe_ref  ON  coatipecoaCtrId = ctrId
      WHERE
         coaKelompokNama = 'Pendapatan'
      )-
      (
      SELECT
         SUM(bbSaldoAkhir)
      FROM
         buku_besar
         JOIN coa ON coaId = bbCoaId
         JOIN coa_kelompok ON coaCoaKelompokId = coaKelompokId
         JOIN coa_tipe_coa ON coatipecoaCoaId = coaId
         JOIN coa_tipe_ref  ON  coatipecoaCtrId = ctrId
      WHERE
         coaKelompokNama = 'Biaya'
      ) AS labarugi
";
?>
