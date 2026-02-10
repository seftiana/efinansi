<?php
$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']  = "
SELECT SQL_CALC_FOUND_ROWS
   IFNULL(transid,0) AS id,
   coaKodeSistem,
   tppId,
   tppTanggalAwal,
   tppTanggalAkhir,
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   IFNULL(transTanggalEntri,bbTanggal) AS tanggalJurnalEntri,
   prTanggal AS tanggalJurnal,
   transDueDate AS tanggalDueDate,
   bbTanggal AS tanggalPosting,
   transReferensi AS nomorReferensi,
   transCatatan AS keterangan,
   coaId AS akunId,
   coaKodeAkun AS akunKode,
   coaNamaAkun AS akunNama,
   CONCAT_WS('-', TRIM(BOTH FROM subaccPertamaKode),
   TRIM(BOTH FROM subaccKeduaKode),
   TRIM(BOTH FROM subaccKetigaKode),
   TRIM(BOTH FROM subaccKeempatKode),
   TRIM(BOTH FROM subaccKelimaKode),
   TRIM(BOTH FROM subaccKeenamKode),
   TRIM(BOTH FROM subaccKetujuhKode)) AS subAccount,   
   IFNULL(sa.saldo_awal,0) AS saldoAwal, 
   bbDebet AS debet,
   bbKredit AS kredit,
   bbSaldo AS saldo,
   bbSaldoAkhir AS saldoAkhir
FROM buku_besar_his
LEFT JOIN pembukuan_referensi
   ON prId = bbPembukuanRefId AND prIsJurnalBalik = 0 AND `prIsPosting` = 'Y'
LEFT JOIN pembukuan_detail
   ON pdId = bbPdId
   AND pdPrId = bbPembukuanRefId
LEFT JOIN transaksi
   ON transid = prTransId 
JOIN coa
     ON coaId = bbCoaId
LEFT JOIN tahun_pembukuan_periode
   ON (tppId =  bbTppId OR tppId = transTppId )
   AND pdCoaId = bbCoaId
   AND tppIsBukaBuku = 'Y'
LEFT JOIN tahun_anggaran
   ON thanggarId = transThanggarId
LEFT JOIN unit_kerja_ref
   ON unitkerjaId = transUnitkerjaId
   AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
LEFT JOIN (
   SELECT unitkerjaId AS id,
   CASE
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN unitkerjaKodeSistem
   END AS kode
   FROM unit_kerja_ref
) AS tmp_unit ON tmp_unit.id = unitkerjaId
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
LEFT JOIN (
SELECT 
      `bbTppId` AS tp_id,
      `bbCoaId` AS coa_id,
      SUM(`bbSaldoAwal` + (IF(c.`coaCoaKelompokId` = 1/*Aktiva*/,
      bbDebet - bbKredit,`bbSaldo`))) AS saldo_awal
    FROM 
    buku_besar_his
    JOIN tahun_pembukuan_periode tpp
     ON tpp.tppId =  bbTppId AND tpp.tppIsBukaBuku = 'Y'   
    JOIN coa c ON c.`coaId` = bbCoaId
     WHERE bbPembukuanRefId IS NULL 
      AND bbPdId IS NULL 
GROUP BY bbTppId,bbCoaId
) sa ON sa.coa_id = coaId AND sa.tp_id =bbTppId
WHERE 1 = 1
AND ((transTanggalEntri BETWEEN '%s' AND '%s')  OR( bbPembukuanRefId IS NULL AND bbPdId IS NULL))
AND (coaId = %s OR 1 = %s)
ORDER BY  akunKode,tanggalJurnalEntri,nomorReferensi ASC
";

$sql['get_limit'] = "
LIMIT %s,%s
";
/* 
ORDER BY SUBSTRING_INDEX(tmp_unit.kode, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 5), '.', -1)+0,
prTanggal DESC, SUBSTR(SUBSTRING_INDEX(transReferensi, '/' , 1), 3)+0 DESC, pdStatus ASC, pdId
";
 */

// AND (SUBSTR(coaKodeSistem, 1, (SELECT LENGTH(CONCAT(coaKodeSistem, '.')) FROM coa WHERE coaId = %s)) = (SELECT CONCAT(coaKodeSistem, '.') FROM coa WHERE coaId = %s) OR 1 = %s)
#get min-max tahun

$sql['get_total_saldo'] = "
SELECT 
    IFNULL(transid,0) AS id,
    coaKodeAkun AS akun_kode,
    transTanggalEntri AS tanggal_transaksi,
    transReferensi AS nomor_referensi,
    IFNULL(sa.saldo_awal,0) AS saldo_awal,
    bbDebet AS debet,
    bbKredit AS kredit
FROM
  buku_besar_his 
  left JOIN pembukuan_referensi 
    ON prId = bbPembukuanRefId 
  left JOIN pembukuan_detail 
    ON pdId = bbPdId 
    AND pdPrId = bbPembukuanRefId 
  left JOIN transaksi 
    ON transid = prTransId 
  left JOIN coa 
    ON coaId = pdCoaId 
  left JOIN tahun_pembukuan_periode 
    ON tppId = transTppId 
    AND pdCoaId = bbCoaId 
    AND tppIsBukaBuku = 'Y' 
  left JOIN tahun_anggaran 
    ON thanggarId = transThanggarId 
    AND thanggarIsAktif = 'Y' 
  left JOIN unit_kerja_ref 
    ON unitkerjaId = transUnitkerjaId 
LEFT JOIN (SELECT 
      `bbTppId` as tp_id,
      `bbCoaId` as coa_id,
      SUM(`bbSaldoAwal` + (IF(c.`coaCoaKelompokId` = 1/*Aktiva*/,
      bbDebet - bbKredit,`bbSaldo`))) AS saldo_awal
    FROM `buku_besar_his`
    JOIN coa c ON c.`coaId` = bbCoaId
     WHERE bbPembukuanRefId IS NULL 
      AND bbPdId IS NULL 
GROUP BY bbTppId,bbCoaId) sa ON sa.coa_id = coaId AND sa.tp_id =transTppId
WHERE 1 = 1
AND prIsJurnalBalik = 0
AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
AND transTanggalEntri BETWEEN '%s' AND '%s'
AND (coaId = %s OR 1 = %s)
AND `prIsPosting` = 'Y'
ORDER BY akun_kode,tanggal_transaksi,nomor_referensi ASC

";

$sql['get_minmax_tahun_transaksi'] = "
SELECT
   YEAR(MIN(transTanggalEntri)) - 5 AS minTahun,
   YEAR(MAX(transTanggalEntri)) + 5 AS maxTahun
FROM
   transaksi
";

$sql['get_rekening_coa'] = "
SELECT
   coaId AS id,
   concat(coaKodeAkun,' [', coaNamaAkun,']') AS name
FROM
   coa
";

$sql['get_data_buku_besar'] = "
SELECT 
  a.bbhisId AS bbhis_id,
  a.bbCoaId AS coa_id,
  e.coaKodeAkun AS rekening,
  e.coaNamaAkun AS coa,
  a.bbTanggal AS bb_tanggal,
  SUM(a.bbSaldoAwal) AS saldo_awal,
  SUM(a.bbDebet) AS debet,
  SUM(a.bbKredit) AS kredit,
  SUM(a.bbSaldo) AS saldo,
  SUM(a.bbSaldoAkhir) AS saldo_akhir,
  b.pdKeterangan AS keterangan,
  d.transReferensi AS referensi,
  f.unitkerjaId 
FROM
  buku_besar_his a 
  JOIN pembukuan_referensi c 
    ON a.bbPembukuanRefId = c.prId 
  JOIN pembukuan_detail b 
    ON b.pdPrId = c.prId 
  JOIN transaksi d 
    ON d.transId = c.prTransId 
  JOIN coa e 
    ON a.bbCoaId = e.coaId 
  JOIN unit_kerja_ref f 
    ON e.coaUnitKerjaId = f.unitkerjaId 
  JOIN tahun_anggaran ta 
    ON ta.thanggarId = d.transThanggarId 
    AND ta.thanggarIsAktif = 'Y' 
  JOIN tahun_pembukuan_periode tpp 
    ON tpp.tppId = d.transTppId 
    AND b.pdCoaId = a.bbCoaId 
    AND tpp.tppIsBukaBuku = 'Y' 
WHERE 
   (
    SUBSTR(
      `unitkerjaKodeSistem`,
      1,
      (SELECT 
        LENGTH(
          CONCAT(`unitkerjaKodeSistem`, '.')
        ) 
      FROM
        unit_kerja_ref 
      WHERE `unitkerjaId` = '%s')
    ) = 
    (SELECT 
      CONCAT(`unitkerjaKodeSistem`, '.') 
    FROM
      unit_kerja_ref 
    WHERE `unitkerjaId` = '%s') 
    OR unitkerjaId = '%s'
  ) 
  AND
 (d.transTanggalEntri BETWEEN '%s' AND '%s' )
 AND
 (e.coaId = '%s'  OR 1 = %s) 
GROUP BY a.bbCoaId 
ORDER BY rekening ASC 
";

$sql['get_info_coa'] = "
SELECT
   coaId AS coa_id,
   coaKodeAkun AS no_rekening,
   coaNamaAkun AS rekening
FROM
   coa
WHERE
   coaId = '%s'
";

?>
