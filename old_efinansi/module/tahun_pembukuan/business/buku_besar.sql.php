<?php

$sql['insert_buku_besar'] = "
   INSERT buku_besar
   SET 
   	bbTanggal='%s',
   	bbCoaId='%s',
   	bbSaldoAwal='0',
   	bbDebet='%s',
   	bbKredit='%s',
   	bbSaldo='%s',
   	bbSaldoAkhir='%s',
   	bbUserId='%s'
   	[INSERT_SUBACC]
";

$sql['update_buku_besar_where_coa'] = "
   UPDATE buku_besar SET
      bbTanggal = '%s',
      bbSaldoAwal = '0',
      bbDebet = '%s',
      bbKredit = '%s',
      bbSaldo = '%s',
      bbSaldoAkhir = '%s',
      bbUserId = '%s'
   WHERE 
   bbCoaId = '%s'
   [FILTER_SUBACC]
";

$sql['insert_buku_besar_history'] = "
INSERT buku_besar_his
SET
   	bbTanggal='%s',
   	bbCoaId='%s',
   	bbSaldoAwal='0',
   	bbDebet='%s',
   	bbKredit='%s',
   	bbSaldo='%s',
   	bbSaldoAkhir='%s',
   	bbUserId='%s'
  	[INSERT_SUBACC]
";

$sql['update_buku_besar_hist_where_coa'] = "
   UPDATE buku_besar_his SET
      bbTanggal = '%s',
      bbSaldoAwal = '0',
      bbDebet = '%s',
      bbKredit = '%s',
      bbSaldo = '%s',
      bbSaldoAkhir = '%s',
      bbUserId = '%s'
   WHERE 
   bbCoaId = '%s'
   [FILTER_SUBACC]
";

$sql['get_buku_besar_histori_akhir_from_coa'] = "
SELECT
   bbhisId,
   bbTppId,
   bbTanggal,
   bbCoaId,
   bbSaldoAwal,
   bbDebet,
   bbKredit,
   bbSaldo,
   bbSaldoAkhir,
   bbIsJurnalBalik,
   bbUserId
FROM buku_besar_his
WHERE bbhisId = (SELECT MAX(bbhisId) FROM buku_besar_his WHERE  bbCoaId = '%s' [FILTER_SUBACC])
";

$sql['get_buku_besar_from_coa'] = "
 SELECT
   bbId,
   bbTppId,
   bbTanggal,
   bbCoaId,
   bbSaldoAwal,
   bbDebet,
   bbKredit,
   bbSaldo,
   bbSaldoAkhir,
   bbUserId
FROM buku_besar
WHERE bbCoaId = '%s' [FILTER_SUBACC]
";

$sql['update_tahun_periode_buku_besar'] = "
   UPDATE buku_besar
   SET bbTppId = '%s'
";

$sql['update_tahun_periode_buku_besar_histori_is_null'] = "
   UPDATE buku_besar_his
      SET bbTppId = '%s'
   WHERE bbTppId IS NULL 
";

$sql['delete_buku_besar_by_coa_sub_account']="
DELETE FROM buku_besar
WHERE 
	`bbCoaId` = '%s' AND bbTppId IS NULL
	[FILTER_SUBACC]
";

$sql['delete_buku_besar_history_by_coa_sub_account']="
DELETE FROM buku_besar_his
WHERE 
	`bbCoaId` = '%s' AND bbTppId IS NULL
	[FILTER_SUBACC]
";

$sql['coa_pengali'] = "
   SELECT IF(coaIsDebetPositif,1,-1) AS pengaliDebet, IF(!coaIsDebetPositif,1,-1) AS pengaliKredit FROM coa WHERE coaId = '%s'
";

$sql['get_tanggal']="
SELECT bbTanggal AS tanggal
FROM buku_besar
WHERE
	bbCoaId ='%s'
    [FILTER_SUBACC]
";
?>
