<?php
$sql['get_setting_value']        = "
SELECT
   settingValue AS 'setting'
FROM setting
WHERE UPPER(settingName) = '%s'
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
SET transTtId = '3',
   transTransjenId = '1',
   transUnitkerjaId = '%s',
   transTppId = @tahun_Pembukuan,
   transThanggarId = @tahun_anggaran,
   transReferensi = @REFERENCE_NUMBER,
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
   transReferensi = @REFERENCE_NUMBER,
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
AND transTtId = 3
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
ORDER BY SUBSTRING_INDEX(tmp_unit.kode, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 5), '.', -1)+0,
jurnal.tanggalPembukuan DESC, SUBSTR(SUBSTRING_INDEX(jurnal.referensi, '/' , 1), 3)+0 DESC,
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
AND transTtId = 3
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
ORDER BY pdStatus
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

$sql['do_update_due_date_transaksi']   = "
UPDATE transaksi SET transDueDate = DATE(NOW()) WHERE transId = %s
";
// ------------------------------------------------------------------------------------------------ //
//===GET===
$sql['get_referensi_transaksi']="
SELECT
   t.transId AS transaksi_id,
   t.transDueDate AS transaksi_tanggal,
   t.transReferensi AS transaksi_referensi,
   t.transCatatan AS transaksi_catatan,
   t.transNilai AS transaksi_nilai

FROM
 transaksi t
 JOIN tahun_anggaran ta ON t.transThanggarId = ta.thanggarId

WHERE
 ta.thanggarIsAktif ='Y' AND
 t.transIsJurnal ='T' AND
 t.transTtId IN (3,6)
";

//===DO===
$sql['do_add_pembukuan_referensi']="
INSERT INTO `pembukuan_referensi`
   (`prTransId`, `prUserId`, `prTanggal`, `prKeterangan`, `prIsPosting`, `prDelIsLocked`, `prIsApproved` )
VALUES
   (%s,  %s,  %s,  %s,  'T',  'T',  'T' )
";

$sql['do_add_pembukuan_detail']="
INSERT INTO `pembukuan_detail`
SET
   `pdPrId`=%s,
   `pdCoaId`=%s,
   `pdNilai`=%s,
   `pdKeterangan`=%s,
   `pdKeteranganTambahan`=%s,
   `pdStatus`=%s
   [INSERT_SUB_ACC]
";

$sql['do_approve']="
UPDATE `pembukuan_referensi`
SET
 `prIsApproved`='Y',
 `prIsKas` = '%s',
 `prBentukTransaksi` = '%s'
WHERE `prId` = '%s'
";

$sql['do_update_pembukuan_detail']="
UPDATE
  `pembukuan_detail`
SET
  `pdCoaId`=%s,
  `pdNilai`=%s,
  `pdKeterangan`=%s,
  `pdKeteranganTambahan`=%s
  [UPDATE_SUB_ACC]
where
  `pdId`=%s
";

/**
 * untuk mendapatkan id transaksi berdasarkan prId
 * yang akan digunakan untuk proses hapus data transaksi
 */

$sql['get_trans_id']="
SELECT prTransId as trans_id FROM pembukuan_referensi WHERE prId = %s
";
/**
 * end
 */

// untuk proses balik jurnal
$sql['update_status_jurnal'] = "
UPDATE
   transaksi
SET
   transIsJurnal = 'Y'
WHERE
   transId = %s
";

$sql['get_max_pembukuan_referensi_id'] = "
SELECT
   MAX(prId) AS max_id
FROM
   pembukuan_referensi
";

$sql['update_status_posting_saat_jurnal_balik'] = "
UPDATE
   pembukuan_referensi
SET
   prIsPosting = 'T',
   prIsApproved = 'T'
WHERE
   prId = '%s'
";

$sql['update_status_is_jurnal'] = "
UPDATE
   transaksi
SET
   transIsJurnal = 'Y'
WHERE
   transId = %s
";

//posting setelah jurnal balik
$sql['get_data_jurnal_balik'] = "
   SELECT
      prId AS pembukuan_ref_id,
      transID AS transaksi_id,
      transTanggalEntri AS transaksi_tanggal,
      transReferensi AS transaksi_referensi,
      transCatatan AS transaksi_catatan,
      pdCoaId AS coa_id,
      coaKodeAkun AS akun_kode,
      coaNamaAkun AS akun_nama,
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
   WHERE
      prId = '%s'
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
      bbCoaId = (SELECT coaId FROM coa WHERE `coaIsLabaRugiThJln` = '1')
";

$sql['get_coa_laba_rugi'] = "
   SELECT
      coaKelompokId
   FROM
      coa_kelompok
   WHERE
      coaKelompokNama = 'Pendapatan' OR coaKelompokNama = 'Biaya';
";

$sql['do_insert_buku_besar'] = "
   INSERT INTO buku_besar
      (bbTppId, bbTanggal, bbCoaId, bbSaldoAwal, bbDebet, bbKredit, bbSaldo, bbSaldoAkhir, bbUserId)
   VALUES ((SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'), now(), '%s', '%s', '%s', '%s', '%s', '%s', '%s');
";

$sql['do_insert_laba_rugi_buku_besar'] = "
   INSERT INTO buku_besar
      (bbTppId, bbTanggal, bbCoaId, bbSaldoAwal, bbDebet, bbKredit, bbSaldo, bbSaldoAkhir, bbUserId)
   VALUES ((SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'), now(), (SELECT coaId FROM coa WHERE `coaIsLabaRugiThJln` = '1' AND coaUnitkerjaId = (SELECT userunitkerjaUnitkerjaId FROM user_unit_kerja WHERE userunitkerjaUserId = %s)), '%s', '%s', '%s', '%s', '%s', '%s');
";

$sql['do_update_buku_besar'] = "
   UPDATE
      buku_besar
   SET
      bbTppId = (SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'),
      bbTanggal = now(),
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
      bbTanggal = now(),
      bbCoaId = (SELECT coaId FROM coa WHERE `coaIsLabaRugiThJln` = '1' AND coaUnitkerjaId = (SELECT userunitkerjaUnitkerjaId FROM user_unit_kerja WHERE userunitkerjaUserId = %s)),
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
      (bbTppId, bbPembukuanRefId, bbTanggal, bbCoaId, bbSaldoAwal, bbDebet, bbKredit, bbSaldo, bbSaldoAkhir, bbUserId)
   VALUES ((SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'), '%s', now(), '%s', '%s', '%s', '%s', '%s', '%s', '%s');
";

$sql['do_insert_laba_rugi_buku_besar_his'] = "
   INSERT INTO buku_besar_his
      (bbTppId, bbPembukuanRefId, bbTanggal, bbCoaId, bbSaldoAwal, bbDebet, bbKredit, bbSaldo, bbSaldoAkhir, bbUserId)
   VALUES ((SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'), '%s', now(), (SELECT coaId FROM coa WHERE `coaIsLabaRugiThJln` = '1' AND coaUnitkerjaId = (SELECT userunitkerjaUnitkerjaId FROM user_unit_kerja WHERE userunitkerjaUserId = %s)), '%s', '%s', '%s', '%s', '%s', '%s');
";

$sql['update_status_posting_balik_pembukuan_ref'] = "
   UPDATE
      pembukuan_referensi
   SET
      prIsPosting = 'Y',
      prIsJurnalBalik = 1
   WHERE
      prId = '%s'
";

$sql['update_status_is_jurnal_ketika_delete'] = "
   UPDATE
      transaksi
   SET
      transIsJurnal = '%s'
   WHERE
      transId = (SELECT prTransId FROM pembukuan_referensi WHERE prId = %s)
";

$sql['do_add_transaksi'] = "
   INSERT INTO transaksi(
      transTtId,
      transTransjenId,
      transUnitkerjaId,
      transTppId,
      transThanggarId,
      transReferensi,
      transUserId,
      transTanggal,
      transTanggalEntri,
      transDueDate,
      transCatatan,
      transNilai,
      transPenanggungJawabNama,
      transIsJurnal
   ) VALUES (
      '3',/*transTtId*/
      '1',/*transTransjenId*/
      (SELECT userunitkerjaUnitkerjaId FROM user_unit_kerja WHERE userunitkerjaUserId = '%s'),/*transUnitkerjaId*/
      (SELECT `tppId` FROM `tahun_pembukuan_periode` WHERE tppIsBukaBuku = 'Y' LIMIT 1),
      (SELECT thanggarId FROM tahun_anggaran WHERE thanggarIsAktif = 'Y' LIMIT 1),
      '%s',/*transReferensi*/
      '%s',/*transUserId*/
      NOW(),/*transTanggal*/
      %s,/*transTanggalEntri*/
      %s,/*transDueDate*/
      'Auto Generate transaksi dari jurnal umum',/*transCatatan*/
      '%s',/*transNilai*/
      (SELECT RealName FROM gtfw_user WHERE userId='%s'),/*transPenanggungJawabNama*/
      'T'/*transIsJurnal*/
   )
";

$sql['do_update_transaksi']="
UPDATE transaksi
SET
   transUnitkerjaId = (SELECT userunitkerjaUnitkerjaId FROM user_unit_kerja WHERE userunitkerjaUserId = '%s'),
   transTppId = (SELECT `tppId` FROM `tahun_pembukuan_periode` WHERE tppIsBukaBuku = 'Y' LIMIT 1),
   transThanggarId = (SELECT thanggarId FROM tahun_anggaran WHERE thanggarIsAktif = 'Y' LIMIT 1),
   transUserId = %s,
   transTanggal = NOW(),
   transTanggalEntri = %s,
   transDueDate = %s,
   transNilai = '%s',
   transPenanggungJawabNama = (SELECT RealName FROM gtfw_user WHERE userId='%s')
WHERE transId = %s
";

$sql['count_bukti'] = "
   SELECT
      transReferensi
   FROM
      transaksi
   WHERE
      transReferensi LIKE %s
      # AND EXTRACT(MONTH FROM transTanggalEntri) = %%s
      AND EXTRACT(YEAR FROM transTanggalEntri) = %s
      AND transUnitkerjaId = (SELECT userunitkerjaUnitkerjaId FROM user_unit_kerja WHERE userunitkerjaUserId = '%s')
";

$sql['get_last_kuitansi_id'] = "
   SELECT MAX(transId) AS transId FROM transaksi
";

$sql['get_unit_kode']="
   SELECT unitkerjaKode
FROM unit_kerja_ref
JOIN user_unit_kerja ON userunitkerjaUnitkerjaId = unitkerjaId
WHERE userunitkerjaUserId = '%s'
";

$sql['set_jurnal_balik_status']="
   UPDATE buku_besar_his SET bbIsJurnalBalik = 'Y' WHERE bbPembukuanRefId='%s'
";

#------------------------------- Balik Jurnal ------------------------------------#
$sql['insert_transaksi_untuk_jurnal_balik']="
INSERT INTO `pembukuan_referensi`
            (`prTransId`,
             `prUserId`,
             `prTanggal`,
             `prKeterangan`,
             `prIsPosting`,
             `prIsFinalPosting`,
             `prDelIsLocked`,
             `prIsApproved`,
             `prIsKas`,
             `prBentukTransaksi`,
             `prIsJurnalBalik`)
(SELECT
  `prTransId`,
  `prUserId`,
   NOW(),
  `prKeterangan`,
  'T' AS prIsPosting,
  `prIsFinalPosting`,
  `prDelIsLocked`,
  '%s' AS `prIsApproved`,
  NULL,
  NULL,
  '%s' AS `prIsJurnalBalik`
FROM `pembukuan_referensi`
WHERE prId = '%s'
)
";

$sql['get_data_pembukuan_detail']="
SELECT
  `pdPrId`,
  `pdCoaId`,
  `pdNilai`,
  `pdKeterangan`,
  `pdKeteranganTambahan`,
   pdStatus,
  `pdSubaccPertamaKode`,
  `pdSubaccKeduaKode`,
  `pdSubaccKetigaKode`,
  `pdSubaccKeempatKode`,
  `pdSubaccKelimaKode`,
  `pdSubaccKeenamKode`,
  `pdSubaccKetujuhKode`
FROM `pembukuan_detail`
WHERE pdPrId = '%s'
";

$sql['insert_data_pembukuan_detail']="
INSERT INTO `pembukuan_detail`
            (`pdPrId`,
             `pdCoaId`,
             `pdNilai`,
             `pdKeterangan`,
             `pdKeteranganTambahan`,
             `pdStatus`,
             `pdSubaccPertamaKode`,
             `pdSubaccKeduaKode`,
             `pdSubaccKetigaKode`,
             `pdSubaccKeempatKode`,
             `pdSubaccKelimaKode`,
             `pdSubaccKeenamKode`,
             `pdSubaccKetujuhKode`)
      (SELECT
      (SELECT MAX(prId) FROM pembukuan_referensi),#pdPrId
        '%s',#pdCoaId
        '%s',#pdNilai
        '%s',#pdKeterangan
        '%s',#pdKeteranganTambahan
        '%s',#pdStatus
        '%s',#pdSubaccPertamaKode
        '%s',#pdSubaccKeduaKode
        '%s',#pdSubaccKetigaKode
        '%s',#pdSubaccKeempatKode
        '%s',#pdSubaccKelimaKode
        '%s',#pdSubaccKeenamKode
        '%s')#pdSubaccKetujuhKode
      ";


$sql['get_data_pembukuan_detail_jurnal_balik']="
SELECT
  `pdPrId`,
  `pdCoaId`,
  `pdNilai`,
  `pdKeterangan`,
  `pdKeteranganTambahan`,
  IF(pdStatus = 'D','K','D') AS pdStatus,
  `pdSubaccPertamaKode`,
  `pdSubaccKeduaKode`,
  `pdSubaccKetigaKode`,
  `pdSubaccKeempatKode`,
  `pdSubaccKelimaKode`,
  `pdSubaccKeenamKode`,
  `pdSubaccKetujuhKode`
FROM `pembukuan_detail`
WHERE pdPrId = '%s'
";
#------------------------------- Balik Jurnal ------------------------------------#


#------------------------------- Popup History Jurnal ------------------------------------#
$sql['get_data_history_jurnal_by_pr_id']="
SELECT
  prId,
  transReferensi,
  prTransId,
  prTanggal,
  prKeterangan,
  coaNamaAkun,
  coaKodeAkun,
  pdStatus,
  pdNilai,
  transPenanggungJawabNama ,
  prIsJurnalBalik
  [SUBACC_VIEW]
FROM
  `pembukuan_referensi`
  JOIN pembukuan_detail
    ON prId = pdPrId
  JOIN transaksi
    ON transId = prTransId
  JOIN coa
    ON pdCoaId = coaId
WHERE prTransId =
  (SELECT
    `prTransId`
  FROM
    `pembukuan_referensi`
  WHERE prId = '%s')
ORDER BY prId, pdStatus
";
#------------------------------- Popup History Jurnal ------------------------------------#
?>