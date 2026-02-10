<?php
$sql['get_min_max_tahun_pencatatan'] = "
SELECT
   YEAR(MIN(transTanggalEntri)) - 5 AS minTahun,
   YEAR(MAX(transTanggalEntri)) + 5 AS maxTahun
FROM
   transaksi
";

$sql['get_combo_tipe_transaksi'] = "
SELECT
   ttId as `id`,
   ttNamaTransaksi as `name`
FROM
   transaksi_tipe_ref
ORDER BY ttNamaTransaksi
";

$sql['get_bentuk_transaksi']        = "
SELECT
   kelJnsId AS id,
   kelJnsNama AS name
FROM
   kelompok_jenis_laporan_ref
WHERE kelJnsPrntId = '2'
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

$sql['get_data_jurnal']       = "
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
AND (transTtId = %s OR 1 = %s)
AND transReferensi LIKE '%s'
AND (prIsApproved = %s OR 1 = %s)
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
ORDER BY  id,pembukuanId,SUBSTRING_INDEX(tmp_unit.kode, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 5), '.', -1)+0,
jurnal.tanggalPembukuan DESC, SUBSTR(SUBSTRING_INDEX(jurnal.referensi, '/' , 1), 3)+0 DESC,
pdStatus ASC
";

$sql['get_count_data_jurnal'] = "
SELECT
   COUNT(prId) AS `count`
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
AND (transTtId = %s OR 1 = %s)
AND transReferensi LIKE '%s'
AND (prIsApproved = %s OR 1 = %s)
AND transTanggalEntri BETWEEN '%s' AND '%s'
";

//===DO===
// $sql['do_add']="
// UPDATE `pembukuan_referensi`
// SET
//    `prIsApproved`='Y',
//    `prIsKas` = '%s',
//    `prBentukTransaksi` = '%s'
// WHERE `prId` = '%s'
// ";

// update periode tahun sesuai dengan tanggal dan tahun pembukuan
$sql['do_add']="
UPDATE transaksi 
   JOIN pembukuan_referensi
      ON prTransId = transId
   join tahun_pembukuan_periode tpp 
   on year(tpp.tppTanggalAwal) = year(transTanggalEntri)
      and year(tpp.tppTanggalAkhir) = year(transTanggalEntri)
SET 
 transTppId = tpp.tppId,
 `prIsApproved`='Y',
 `prIsKas` = '%s',
 `prBentukTransaksi` = '%s' 
where
 `prId` = '%s' 
";

$sql['do_unapprove']="
UPDATE pembukuan_referensi
   SET prIsApproved = 'T'
WHERE prId = %s
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

//===GET===
$sql['get_combo_coa']="
SELECT
 coaId AS id,
 coaNamaAkun AS name
FROM
 coa
WHERE
 coaIsDebetPositif LIKE %s AND
 coaId NOT IN (SELECT DISTINCT(coaParentAkun) FROM coa)
";

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
 t.transTtId ='2'
";

$sql['get_data']="
SELECT
 pr.prId AS id,
 t.transReferensi AS referensi,
 t.transTanggalEntri AS tanggal,
 pd.pdKeterangan AS keterangan,
 c.coaKodeAkun AS rekening_kode,
 c.coaNamaAkun AS rekening_nama,
 pd.pdNilai AS nilai,
 pd.pdStatus AS tipeakun,
 t.transIsJurnal AS is_jurnal,
 pr.prIsPosting AS is_posting,
 pr.prDelIsLocked AS is_locked,
 gu.RealName AS petugas_entri,
 pr.prIsApproved as is_approve,
 pr.prIsKas as is_kas,
 pr.prBentukTransaksi as bentuk_transaksi,
 t.transTtId as trans_tipe
 [SUBACC_VIEW]
FROM
  transaksi t
  JOIN pembukuan_referensi pr ON t.transId=pr.prTransId
  JOIN pembukuan_detail pd ON pr.prId = pd.pdPrId
  JOIN coa c ON pd.pdCoaId = c.coaId
  JOIN gtfw_user gu ON gu.UserId =  pr.prUserId
WHERE
   pr.prId IN ('%s')
   # AND pr.prIsJurnalBalik <> 1
ORDER BY t.transTanggalEntri DESC, pr.prId, pd.pdStatus ASC
";
$sql['get_pembukuan_referensi'] ="
   SELECT
      prId as id
   FROM
      transaksi
      JOIN pembukuan_referensi ON transId=prTransId
      JOIN pembukuan_detail ON prId = pdPrId
   WHERE transTtId LIKE '%s' AND prIsApproved LIKE '%s'
   AND transTanggalEntri BETWEEN '%s' AND '%s'
   # AND prIsJurnalBalik <> 1
   GROUP BY prId
   ORDER BY transTanggalEntri DESC
   LIMIT %s, %s
";
$sql['get_count']="
SELECT
    COUNT(DISTINCT(prId)) as total
FROM
  transaksi t
  JOIN pembukuan_referensi pr ON t.transId=pr.prTransId
  JOIN pembukuan_detail pd ON pr.prId = pd.pdPrId
WHERE
   pr.prId IN (
      SELECT
         prId
      FROM
         transaksi
         JOIN pembukuan_referensi ON transId=prTransId
         JOIN pembukuan_detail ON prId = pdPrId
      WHERE transTtId LIKE '%s' AND prIsApproved LIKE '%s'
      AND transTanggalEntri BETWEEN %s AND %s
   )
   # AND pr.prIsJurnalBalik <> 1
GROUP BY prId
";




$sql['get_data_by_id']="
SELECT
 t.transId AS referensi_id,
 t.transReferensi AS referensi_nama,
 t.transNilai AS referensi_nilai,
 t.transDueDate AS referensi_tanggal,
 pr.prId AS pembukuan_referensi_id,
 pd.pdId AS detail_id,
 pd.pdKeterangan AS detail_keterangan,
 pd.pdNilai AS detail_nilai,
 pd.pdStatus AS detail_status,
 c.coaId AS coa_id,
 c.coaKodeAkun AS coa_kode,
 c.coaNamaAkun AS coa_nama


FROM
 transaksi t
 JOIN pembukuan_referensi pr ON t.transId =pr.prTransId
 JOIN pembukuan_detail pd ON pr.prId = pdPrId
 JOIN coa c ON pd.pdCoaId = c.coaId

WHERE
 pr.prId = %s

ORDER BY pr.prId,pd.pdId,pd.pdStatus
";








$sql['get_data_kode_akun'] = "
SELECT
  `subaccPertamaKode` AS id,`subaccPertamaNama` AS nama, '1' AS akun

FROM finansi_keu_ref_subacc_1

UNION

SELECT
  subaccKeduaKode,`subaccKeduaNama` AS nama, '2'

FROM finansi_keu_ref_subacc_2

UNION

SELECT
  subaccKetigaKode,`subaccKetigaNama` AS nama, '3'

FROM finansi_keu_ref_subacc_3

UNION

SELECT
  subaccKeempatKode, `subaccKeempatNama` AS nama,'4'

FROM finansi_keu_ref_subacc_4

UNION

SELECT
  subaccKelimaKode,`subaccKelimaNama` AS nama, '5'

FROM finansi_keu_ref_subacc_5

UNION

SELECT
  subaccKeenamKode, `subaccKeenamNama` AS nama,'6'

FROM finansi_keu_ref_subacc_6
UNION

SELECT
  subaccKetujuhKode, `subaccKetujuhNama` AS nama,'7'

FROM finansi_keu_ref_subacc_7";


?>
