<?php
#get min-max tahun
$sql['get_minmax_tahun_transaksi'] = "
	SELECT
 		YEAR(MIN(transTanggalEntri)) - 5 AS minTahun,
 		YEAR(MAX(transTanggalEntri)) + 5 AS maxTahun
	FROM
 		transaksi
";
/**
 * new Query
 * untuk menampilkan saldo awal meskipun belum ada transaksi jurnal yang terposting
 * @since 3 Februari 2016
 */
$sql['get_saldo'] =" 
SELECT
  bhis.`bbTanggal` AS tanggal,
  bhis.`bbCoaId` AS coa_id,
  c.`coaKodeAkun` AS coa_kode_akun,
  c.`coaNamaAkun` AS coa_nama_akun,
  SUM(IF(`bbPembukuanRefId` IS NULL AND `bbPdId` IS NULL, IF(c.`coaIsDebetPositif` = 1 , `bbDebet`,`bbKredit`),0)) AS saldo_awal,
  SUM(IF(`bbPembukuanRefId` IS NOT NULL AND `bbPdId` IS NOT NULL,`bbDebet`,0)) AS debet,
  SUM(IF(`bbPembukuanRefId` IS NOT NULL AND `bbPdId` IS NOT NULL,`bbKredit`,0)) AS kredit,
  SUM(`bbSaldo`) AS saldo_akhir
FROM 
	`buku_besar_his` bhis
	JOIN coa c ON c.`coaId` = bhis.`bbCoaId`
	LEFT JOIN pembukuan_referensi pr ON pr.`prId` = bhis.`bbPembukuanRefId` AND pr.`prIsPosting` = 'Y'	
	AND pr.`prTanggal` BETWEEN '%s' AND '%s'
WHERE
 bhis.bbTppId = (SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y')
GROUP BY `bbCoaId` 
ORDER BY c.`coaKodeAkun` ASC        
";

/**
 * non active sejak 3 Februari 2016
 */
/*
$sql['get_saldo'] = "
	SELECT
  		bbTanggal AS bb_tanggal, 
		b.coaId AS coa_id, 
		b.coaKodeAkun AS coa_kode_akun, 
		b.coaNamaAkun AS coa_nama_akun, 
		b.coaIsDebetPositif AS coa_status_debet, 
		a.bbSaldoAwal AS saldo_awal, 
		a.bbSaldoAkhir AS saldo_akhir, 
		a.bbDebet AS debet, 
		a.bbKredit AS kredit
  	FROM
  		buku_besar_his a
  		JOIN coa b ON a.bbCoaId=b.coaId
                JOIN pembukuan_referensi c ON c.prId = a.bbPembukuanRefId   
  	WHERE
		a.bbTanggal BETWEEN '%s' AND '%s' 
		AND a.bbTppId = (SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y')
                AND c.prIsPosting = 'Y'
   ORDER BY
    	b.coaKodeAkun, a.bbHisId asc
";
*/
?>