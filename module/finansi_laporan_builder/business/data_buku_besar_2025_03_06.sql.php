<?php

/**
 * catatan :
 * akun penyusutan memiliki saldo normal kredit,
 * oleh karena berada dalam kelompok aktiva maka 
 * di perlakukan kebalikannya (mengurangi)
 */
$sql['get_data_buku_besar_saldo_akhir_tahun_lalu'] ="
SELECT 
  bhis.`bbTppId` AS tpp_id,
  bhis.`bbCoaId` AS coa_id,
  c.`coaNamaAkun` AS coa_nama,
  c.`coaIsDebetPositif` AS saldo_normal,
  SUM(IF(UPPER(`coaKelompokNama`) ='AKTIVA',
	IF(c.`coaIsDebetPositif` = 0,(`bbSaldo` * -1),`bbSaldo`),
	`bbSaldo`
  )) saldo_awal,
  CONCAT_WS('-',
    IFNULL(bhis.`bbhisSubaccPertamaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeduaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKetigaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeempatKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKelimaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeenamKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKetujuhKode`,'00')) AS sub_acc
FROM `buku_besar_his` bhis
JOIN coa c ON c.`coaId` = bhis.`bbCoaId`
JOIN coa_kelompok ck ON ck.`coaKelompokId` = c.`coaCoaKelompokId`
WHERE bbTppId < %s 
GROUP BY bbCoaId,sub_acc 
HAVING (sub_acc LIKE '%s' OR 1 = %s)
";

$sql['get_data_buku_besar_saldo_awal_tahun'] ="
SELECT
  bhis.`bbTppId` AS tpp_id,
  bhis.`bbCoaId` AS coa_id,
  c.`coaNamaAkun` AS coa_nama,
  c.`coaIsDebetPositif` AS saldo_normal,
  bhis.`bbDebet` AS debet,
  bhis.`bbKredit` AS kredit,
  bhis.`bbSaldo` AS saldo_mutasi,
  IF(UPPER(`coaKelompokNama`) ='AKTIVA',
	IF(c.`coaIsDebetPositif` = 0,(bhis.`bbSaldoAkhir` * -1),bhis.`bbSaldoAkhir`),
	bhis.`bbSaldoAkhir`
  ) AS saldo_awal,
  CONCAT_WS('-',
    IFNULL(bhis.`bbhisSubaccPertamaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeduaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKetigaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeempatKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKelimaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeenamKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKetujuhKode`,'00')) AS sub_acc
FROM `buku_besar_his` bhis
    JOIN coa c ON c.`coaId` = bhis.`bbCoaId`
    JOIN coa_kelompok ck ON ck.`coaKelompokId` = c.`coaCoaKelompokId`
WHERE
    bhis.`bbTppId` = '%s'
    AND
    bhis.`bbPembukuanRefId` IS NULL
    AND
    bhis.`bbPdId` IS NULL
    AND
    bhis.`bbIsJurnalBalik` = 'T'
HAVING (sub_acc LIKE '%s' OR 1 = %s)
";

$sql['get_data_buku_besar_saldo_mutasi'] ="
SELECT
  bhis.`bbTppId` AS tpp_id,
  bhis.`bbCoaId` AS coa_id,
  c.`coaNamaAkun` AS coa_nama,
  c.`coaIsDebetPositif` AS saldo_normal,
  SUM(bhis.`bbDebet`) AS saldo_mutasi_d,
  SUM(bhis.`bbKredit`) AS saldo_mutasi_k,
  SUM(IF(UPPER(`coaKelompokNama`) ='AKTIVA',
	IF(c.`coaIsDebetPositif` = 0,(bhis.`bbSaldo` * -1),bhis.`bbSaldo`),
	bhis.`bbSaldo`
  )) AS saldo_mutasi_dk,
  CONCAT_WS('-',
    IFNULL(bhis.`bbhisSubaccPertamaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeduaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKetigaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeempatKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKelimaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeenamKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKetujuhKode`,'00')) AS sub_acc
FROM `buku_besar_his` bhis
JOIN coa c ON c.`coaId` = bhis.`bbCoaId`
JOIN coa_kelompok ck ON ck.`coaKelompokId` = c.`coaCoaKelompokId`
JOIN pembukuan_referensi ON prId = bbPembukuanRefId
JOIN transaksi ON prTransId = transId
WHERE
    bhis.`bbTppId` = '%s'
    AND `transTanggalEntri` BETWEEN '%s' AND '%s'
    AND bhis.`bbPembukuanRefId` IS NOT NULL
    AND bhis.`bbPdId` IS NOT NULL
    AND bhis.`bbIsJurnalBalik` = 'T'
GROUP BY c.`coaId`,sub_acc
HAVING (sub_acc LIKE '%s' OR 1 = %s)
";

$sql['get_data_buku_besar_saldo_awal_tahun_lalu'] ="
SELECT
  bhis.`bbTppId` AS tpp_id,
  bhis.`bbCoaId` AS coa_id,
  c.`coaNamaAkun` AS coa_nama,
  c.`coaIsDebetPositif` AS saldo_normal,
  bhis.`bbDebet` AS debet,
  bhis.`bbKredit` AS kredit,
  bhis.`bbSaldo` AS saldo_mutasi,
  IF(UPPER(`coaKelompokNama`) ='AKTIVA',
	IF(c.`coaIsDebetPositif` = 0,(bhis.`bbSaldoAkhir` * -1),bhis.`bbSaldoAkhir`),
	bhis.`bbSaldoAkhir`
  ) AS saldo_awal,
  CONCAT_WS('-',
    IFNULL(bhis.`bbhisSubaccPertamaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeduaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKetigaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeempatKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKelimaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeenamKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKetujuhKode`,'00')) AS sub_acc
FROM `buku_besar_his` bhis
    JOIN coa c ON c.`coaId` = bhis.`bbCoaId`
    JOIN coa_kelompok ck ON ck.`coaKelompokId` = c.`coaCoaKelompokId`
WHERE
    bhis.`bbTppId` = '%s'
    AND
    bhis.`bbPembukuanRefId` IS NULL
    AND
    bhis.`bbPdId` IS NULL
    AND
    bhis.`bbIsJurnalBalik` = 'T'
    HAVING  (sub_acc LIKE '%s' OR 1 = %s)
";

$sql['get_data_buku_besar_saldo_mutasi_tahun_lalu'] ="
SELECT
  bhis.`bbTppId` AS tpp_id,
  bhis.`bbCoaId` AS coa_id,
  c.`coaNamaAkun` AS coa_nama,
  c.`coaIsDebetPositif` AS saldo_normal,
  SUM(bhis.`bbDebet`) AS saldo_mutasi_d,
  SUM(bhis.`bbKredit`) AS saldo_mutasi_k,
  SUM(IF(UPPER(`coaKelompokNama`) ='AKTIVA',
	IF(c.`coaIsDebetPositif` = 0,(bhis.`bbSaldo` * -1),bhis.`bbSaldo`),
	bhis.`bbSaldo`
  )) AS saldo_mutasi_dk,
  CONCAT_WS('-',
    IFNULL(bhis.`bbhisSubaccPertamaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeduaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKetigaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeempatKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKelimaKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKeenamKode`,'00'),
    IFNULL(bhis.`bbhisSubaccKetujuhKode`,'00')) AS sub_acc
FROM `buku_besar_his` bhis
JOIN coa c ON c.`coaId` = bhis.`bbCoaId`
JOIN coa_kelompok ck ON ck.`coaKelompokId` = c.`coaCoaKelompokId`
WHERE
    bhis.`bbTppId` = '%s'
    AND
    bhis.`bbPembukuanRefId` IS NOT NULL
    AND
    bhis.`bbPdId` IS NOT NULL
    AND
    bhis.`bbIsJurnalBalik` = 'T'
GROUP BY c.`coaId`,sub_acc
HAVING (sub_acc LIKE '%s' OR 1 = %s)
";

?>