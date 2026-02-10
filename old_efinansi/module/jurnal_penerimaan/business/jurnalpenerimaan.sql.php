<?php

/**
 * @package SQL-FILE
 */
$sql['get_setting_value'] = "
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

$sql['get_patern_sub_account'] = "
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

$sql['get_sub_account'] = "
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

$sql['get_data_referensi_transaksi'] = "
SELECT
   CASE
      WHEN `transTtId` = '1' THEN transid    
      WHEN `transTtId` = '5' THEN transid    
      WHEN `transTtId` = '10' THEN transid    
   END AS transaksiId,
   transId AS id,
   prId AS pembukuanId,
   transReferensi AS nomorReferensi,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   tppId AS tpId,
   tppTanggalAwal AS tpAwal,
   tppTanggalAkhir AS tpAkhir,
   CONCAT_WS('/', tppTanggalAwal, tppTanggalAkhir) AS tpNama,
   thanggarId AS taId,
   thanggarNama AS taNama,
   ttNamaTransaksi AS tipeNama,
   ttId AS tipeId,
   ttKodeTransaksi AS kodeTransaksi,
   ttKeterangan AS tipeKeterangan,
   ttNamaJurnal AS tipeJurnal,
   transNilai AS nominal,
   prKeterangan AS keterangan,
   prTanggal AS tanggal,
   prIsKas AS isKas,
   prIsApproved AS `status`,
   prBentukTransaksi AS bentukTransaksi,
   transTtId AS transTtId
FROM
   transaksi
   JOIN transaksi_tipe_ref
      ON ttId = transTtId
   JOIN tahun_pembukuan_periode
      ON tppId = transTppId
   JOIN tahun_anggaran
      ON thanggarId = transThanggarId
   JOIN unit_kerja_ref
      ON unitkerjaId = transUnitkerjaId
   JOIN pembukuan_referensi
      ON prTransId = transId
WHERE 1 = 1
AND transId = %s
AND prId = %s
AND transTtId IN (1,5,10)
AND transTransjenId NOT IN(8,9)
HAVING transaksiId IS NOT NULL
LIMIT 1
";

$sql['get_data_jurnal_sub_akun'] = "
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

$sql['get_bentuk_transaksi'] = "
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

$sql['do_insert_pembukuan_referensi'] = "
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

$sql['do_update_pembukuan_referensi'] = "
UPDATE pembukuan_referensi
SET prTransId = '%s',
   prUserId = '%s',
   prTanggal = '%s',
   prKeterangan = '%s',
   prIsKas = IF('%s' = '' OR %s IS NULL, NULL, %s),
   prBentukTransaksi = IF('%s' = '' OR %s IS NULL, NULL, '%s')
WHERE prId = %s
";

$sql['do_delete_pembukuan_referensi'] = "
DELETE FROM pembukuan_referensi WHERE prId = '%s'
";

$sql['do_delete_pembukuan_detail'] = "
DELETE FROM pembukuan_detail WHERE pdPrId = %s
";

$sql['do_insert_pembukuan_detail'] = "
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

$sql['update_status_jurnal'] = "
UPDATE transaksi SET transIsJurnal = '%s' WHERE transId = %s
";

$sql['count'] = "
SELECT FOUND_ROWS() AS `count`
";


$sql['get_data_jurnal_penerimaan'] = "
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
   CONCAT_WS(
      '-',
      TRIM(BOTH FROM subaccPertamaKode),
      TRIM(BOTH FROM subaccKeduaKode),
      TRIM(BOTH FROM subaccKetigaKode),
      TRIM(BOTH FROM subaccKeempatKode),
      TRIM(BOTH FROM subaccKelimaKode),
      TRIM(BOTH FROM subaccKeenamKode),
      TRIM(BOTH FROM subaccKetujuhKode)
   ) AS subAccount
FROM
   pembukuan_detail
   JOIN
      (SELECT
         CASE
            WHEN `transTtId` = '1' THEN transid    
            WHEN `transTtId` = '5' THEN transid       
         END AS transaksiId,
         transId AS id,
         prId AS pembukuanId,
         CONCAT_WS(
            '/',
            tppTanggalAwal,
            tppTanggalAkhir
         ) AS tpNama,
         thanggarId AS taId,
         thanggarNama AS taNama,
         tppId,
         tppTanggalAwal,
         tppTanggalAkhir,
         unitkerjaId AS unitId,
         unitkerjaKode AS unitKode,
         unitkerjaNama AS unitNama,
         transReferensi AS referensi,
         transTtId AS tipeJurnalId,
         ttNamaJurnal AS tipeJurnalNama,
         ttKodeTransaksi AS tipeJurnalKode,
         transTanggalEntri AS tanggal,
         prKeterangan AS catatan,
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
         tmp_pr.jurnal,
         IF(tmp_pr.summary < tmp_pr.jurnal, 'YES', 'NO') AS hasJurnal,
         transTtId AS transTtId
      FROM
         transaksi
         JOIN transaksi_tipe_ref
            ON ttId = transTtId
         JOIN tahun_pembukuan_periode
            ON tppId = transTppId
         JOIN tahun_anggaran
            ON thanggarId = transThanggarId
         JOIN unit_kerja_ref
            ON unitkerjaId = transUnitkerjaId
         JOIN
            (SELECT DISTINCT
               prTransId AS pembukuanTransaksiId,
               MAX(prId) AS pembukuanReferensiId,
               COUNT(prId) AS summary,
               COUNT(IF(prIsPosting = 'T', NULL, prId)) AS jurnal
            FROM
               pembukuan_referensi
            GROUP BY prTransId
            ORDER BY prId DESC) AS tmp_pr
            ON tmp_pr.pembukuanTransaksiId = transId
         JOIN pembukuan_referensi
            ON prId = tmp_pr.pembukuanReferensiId
         JOIN
            (SELECT
               pdPrId AS id
            FROM
               pembukuan_detail
               JOIN coa
                  ON coaId = pdCoaId
            GROUP BY pdPrId) AS detailPembukuan
            ON detailPembukuan.id = prId
      WHERE 1 = 1
         AND transReferensi LIKE '%s'
         AND transTtId IN (1,5,10)
         AND transTransjenId NOT IN(8,9)
         AND (prIsPosting = '%s'
            OR 1 = %s)
         AND transTanggalEntri BETWEEN '%s'
         AND '%s'
      HAVING transaksiId IS NOT NULL
         ORDER BY transReferensi ASC
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
   JOIN
      (SELECT
         unitkerjaId AS id,
         CASE
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$'
            THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$'
            THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$'
            THEN CONCAT(unitkerjaKodeSistem, '.0.0')
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$'
            THEN CONCAT(unitkerjaKodeSistem, '.0')
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$'
            THEN unitkerjaKodeSistem
         END AS kode
      FROM
         unit_kerja_ref) AS tmp_unit
      ON tmp_unit.id = jurnal.unitId
WHERE 1 = 1
ORDER BY 
jurnal.referensi ASC,
pdStatus ASC
";


$sql['count_jurnal_penerimaan'] = "
SELECT
   COUNT(jurnal.transaksiId) AS `count`
FROM
   (SELECT
         CASE
            WHEN `transTtId` = '1' THEN transid    
            WHEN `transTtId` = '5' THEN transid       
            WHEN `transTtId` = '10' THEN transid       
         END AS transaksiId,
         transId AS id,
         prId AS pembukuanId,
         CONCAT_WS(
            '/',
            tppTanggalAwal,
            tppTanggalAkhir
         ) AS tpNama
   FROM
      transaksi
      JOIN transaksi_tipe_ref
         ON ttId = transTtId
      JOIN tahun_pembukuan_periode
         ON tppId = transTppId
      JOIN tahun_anggaran
         ON thanggarId = transThanggarId
      JOIN unit_kerja_ref
         ON unitkerjaId = transUnitkerjaId
      JOIN
         (SELECT DISTINCT
            prTransId AS pembukuanTransaksiId,
            MAX(prId) AS pembukuanReferensiId,
            COUNT(prId) AS summary,
            COUNT(IF(prIsPosting = 'T', NULL, prId)) AS jurnal
         FROM
            pembukuan_referensi
         GROUP BY prTransId
         ORDER BY prId DESC) AS tmp_pr
         ON tmp_pr.pembukuanTransaksiId = transId
      JOIN pembukuan_referensi
         ON prId = tmp_pr.pembukuanReferensiId
      JOIN
         (SELECT
            pdPrId AS id
         FROM
            pembukuan_detail
            JOIN coa
               ON coaId = pdCoaId
         GROUP BY pdPrId) AS detailPembukuan
         ON detailPembukuan.id = prId
   WHERE 1 = 1
      AND transReferensi LIKE '%s'
       AND transTtId IN (1,5,10)
         AND transTransjenId NOT IN(8,9)
      AND (prIsPosting = '%s'
      OR 1 = %s)
      AND transTanggalEntri BETWEEN %s AND %s
   HAVING transaksiId IS NOT NULL) AS jurnal
";

$sql['update_status_jurnal_balik'] = "
UPDATE
  `pembukuan_referensi`
SET
  `prIsJurnalBalik` = '1'
WHERE `prId` = '%s'
";

$sql['do_jurnal_balik_pembukuan_referensi'] = "
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

$sql['do_update_due_date_transaksi'] = "
UPDATE transaksi SET transDueDate = DATE(NOW()) WHERE transId = %s
";

$sql['do_jurnal_balik_pembukuan_detail'] = "
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


$sql['get_coa_kode_in_transaksi'] = "
    SELECT
       c.coaKodeAkun AS coaKode
    FROM
       transaksi
       JOIN realisasi_penerimaan real_pen
          ON real_pen.`realterimaTransId` = transaksi.`transId`
       JOIN rencana_penerimaan ren_pen
          ON ren_pen.`renterimaId` =  real_pen.`realrenterimaId`
             AND ren_pen.`renterimaThanggarId` = transThanggarId
       JOIN `finansi_coa_map` kp_coa
           ON kp_coa.`kodeterimaId` =  ren_pen.`renterimaKodeterimaId`
       JOIN coa c
          ON c.`coaId` =  kp_coa.`coaId`   
    WHERE
    transId = %s
";

?>