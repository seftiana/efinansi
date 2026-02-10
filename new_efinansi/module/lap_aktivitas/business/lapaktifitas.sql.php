<?php
$sql['get_minmax_tahun_transaksi'] = "
   SELECT
      YEAR(MIN(transTanggalEntri)) - 5 AS minTahun,
      YEAR(MAX(transTanggalEntri)) + 5 AS maxTahun
   FROM
      transaksi
";
$sql['get_laporan_all'] ="
SELECT 
   klr.`kellapOrderBy`,
   kjlr.`kelJnsOrderBy`,
   klr.kellapId,
   klr.kellapJnsId,
   kjlr.kelJnsNama,
   klr.`kellapNama` AS nama_kel_lap,
   klr.`kellapIsTambah` AS `status`,
   cklr.`coakellapDK` AS  saldo_normal,
   SUM(IFNULL(sa.saldo_awal,0) + IFNULL(bbTrans.saldo,0)) AS nilai
FROM
  kelompok_laporan_ref klr
  LEFT JOIN kelompok_jenis_laporan_ref kjlr
    ON klr.kellapJnsId = kjlr.kelJnsId 
  LEFT JOIN coa_kelompok_laporan_ref cklr 
    ON cklr.coakellapIdKellap = klr.kellapId 
  LEFT JOIN coa cl
    ON cl.coaId = cklr.coakellapCoaId 
  LEFT JOIN (
    SELECT
        bhis.`bbCoaId` AS coa_id,
        SUM(
          IF(c.`coaCoaKelompokId` = 1/*aktiva*/,
              bhis.`bbDebet` - bhis.`bbKredit`
          ,bhis.bbSaldo)
        ) AS saldo 
	FROM
        buku_besar_his bhis 
        JOIN coa c ON c.coaId = bhis.bbCoaId 
        JOIN (
            SELECT 
              pr.`prId` AS prId,
              pd.`pdId` AS pdId,
              pd.`pdCoaId` AS coaId
            FROM
              pembukuan_referensi pr 
              JOIN pembukuan_detail pd ON pd.`pdPrId` = pr.`prId`
              JOIN transaksi tr ON tr.`transId` = pr.`prTransId`
            WHERE
              tr.transTanggalEntri BETWEEN '%s' AND '%s'
              #AND prIsJurnalBalik = 0  
              AND `prIsPosting` = 'Y' 
              GROUP BY prId,pdId,coaId
        ) AS jurnal ON jurnal.prId = bhis.`bbPembukuanRefId` 
            AND jurnal.pdId = bhis.`bbPdId` 
            AND jurnal.coaId = `bhis`.`bbCoaId`
	GROUP BY bhis.bbCoaId
  ) bbTrans ON bbTrans.coa_id = coakellapCoaId 
LEFT JOIN (
SELECT 
      `bbCoaId` AS coa_id,
      SUM(`bbSaldoAwal` + `bbSaldo`) AS saldo_awal
    FROM 
    `buku_besar_his` bhis
    JOIN tahun_pembukuan_periode tpp
	   ON tpp.tppId =  bhis.bbTppId AND tpp.tppIsBukaBuku = 'Y'    
     WHERE bbPembukuanRefId IS NULL 
      AND bbPdId IS NULL 
GROUP BY bbTppId,bbCoaId
) sa ON sa.coa_id = coakellapCoaId 
WHERE
  kjlr.kelJnsPrntId = '6' 
GROUP BY kellapId
 %s
ORDER BY  kelJnsOrderBy ASC,kellapOrderBy ASC

";

$sql['get_laporan_all_detil'] ="
SELECT 
   klr.`kellapOrderBy`,
   kjlr.`kelJnsOrderBy`,
   klr.kellapId,
   klr.kellapJnsId,
   kjlr.kelJnsNama,
   cl.`coaKodeAkun` AS kode_coa,
   cl.`coaNamaAkun` AS nama_coa,
   klr.`kellapNama` AS nama_kel_lap,
   klr.`kellapIsTambah` AS `status`,
   cklr.`coakellapDK` AS  saldo_normal,
   SUM(IFNULL(sa.saldo_awal,0) + IFNULL(bbTrans.saldo,0)) AS nilai
FROM
  kelompok_laporan_ref klr
  JOIN kelompok_jenis_laporan_ref kjlr
    ON klr.kellapJnsId = kjlr.kelJnsId 
  JOIN coa_kelompok_laporan_ref cklr 
    ON cklr.coakellapIdKellap = klr.kellapId 
  JOIN coa cl
    ON cl.coaId = cklr.coakellapCoaId 
  LEFT JOIN (
    SELECT
        bhis.`bbCoaId` AS coa_id,
        SUM(
          IF(c.`coaCoaKelompokId` = 1/*aktiva*/,
              bhis.`bbDebet` - bhis.`bbKredit`
          ,bhis.bbSaldo)
        ) AS saldo 
	FROM
        buku_besar_his bhis 
        JOIN coa c ON c.coaId = bhis.bbCoaId 
        JOIN (
            SELECT 
              pr.`prId` AS prId,
              pd.`pdId` AS pdId,
              pd.`pdCoaId` AS coaId
            FROM
              pembukuan_referensi pr 
              JOIN pembukuan_detail pd ON pd.`pdPrId` = pr.`prId`
              JOIN transaksi tr ON tr.`transId` = pr.`prTransId`
            WHERE
              tr.transTanggalEntri BETWEEN '%s' AND '%s'
              #AND prIsJurnalBalik = 0  
              AND `prIsPosting` = 'Y' 
              GROUP BY prId,pdId,coaId
        ) AS jurnal ON jurnal.prId = bhis.`bbPembukuanRefId` 
            AND jurnal.pdId = bhis.`bbPdId` 
            AND jurnal.coaId = `bhis`.`bbCoaId`
	GROUP BY bhis.bbCoaId
  ) bbTrans ON bbTrans.coa_id = coakellapCoaId 
LEFT JOIN (
SELECT 
      `bbCoaId` AS coa_id,
      SUM(`bbSaldoAwal` + `bbSaldo`) AS saldo_awal
    FROM 
    `buku_besar_his` bhis
    JOIN tahun_pembukuan_periode tpp
	   ON tpp.tppId =  bhis.bbTppId AND tpp.tppIsBukaBuku = 'Y'    
     WHERE bbPembukuanRefId IS NULL 
      AND bbPdId IS NULL 
GROUP BY bbTppId,bbCoaId
) sa ON sa.coa_id = coakellapCoaId 
WHERE
  kjlr.kelJnsPrntId = '6' 
GROUP BY cl.`coaId`
 %s
ORDER BY kelJnsOrderBy ASC,kellapOrderBy ASC,
substring_index(replace(replace(cl.coaKodeAkun,'.','*'),'-','*'),'*',1)+0, 
substring_index(substring_index(concat(replace(replace(cl.coaKodeAkun,'.','*'),'-','*'),'*0*0*0*0*0*0*0*0'),'*',2),'*',-1)+0,
substring_index(substring_index(concat(replace(replace(cl.coaKodeAkun,'.','*'),'-','*'),'*0*0*0*0*0*0*0*0'),'*',3),'*',-1)+0,
substring_index(substring_index(concat(replace(replace(cl.coaKodeAkun,'.','*'),'-','*'),'*0*0*0*0*0*0*0*0'),'*',4),'*',-1)+0,
substring_index(substring_index(concat(replace(replace(cl.coaKodeAkun,'.','*'),'-','*'),'*0*0*0*0*0*0*0*0'),'*',5),'*',-1)+0,
substring_index(substring_index(concat(replace(replace(cl.coaKodeAkun,'.','*'),'-','*'),'*0*0*0*0*0*0*0*0'),'*',6),'*',-1)+0,
substring_index(substring_index(concat(replace(replace(cl.coaKodeAkun,'.','*'),'-','*'),'*0*0*0*0*0*0*0*0'),'*',7),'*',-1)+0,
substring_index(substring_index(concat(replace(replace(cl.coaKodeAkun,'.','*'),'-','*'),'*0*0*0*0*0*0*0*0'),'*',8),'*',-1)+0  ASC
";
/**
$sql['get_laporan_all_old'] = "
   SELECT
      kellapOrderBy,
      kelJnsOrderBy,
      kellapId,
      kellapJnsId,
      kelJnsNama,
      nama_kel_lap,
      `status`,
       saldo_normal,
     IFNULL(SUM(nilai),0) AS nilai
   FROM(
   SELECT
         kellapOrderBy,
         kelJnsOrderBy,
         kellapId,
         kellapJnsId,
         kelJnsNama,
         kellapNama AS nama_kel_lap,
         kellapIsTambah AS `status`,
         coakellapDK AS saldo_normal,
      (SELECT bbSaldoAkhir FROM buku_besar_his WHERE bbCoaId = coakellapCoaId AND bbTanggal BETWEEN '%s' AND '%s' ORDER BY bbhisId DESC LIMIT 0,1) AS nilai
      FROM kelompok_laporan_ref
      LEFT JOIN kelompok_jenis_laporan_ref ON kellapJnsId = kelJnsId
      LEFT JOIN coa_kelompok_laporan_ref ON coakellapIdKellap = kellapId
            WHERE
            kelJnsPrntId = '6'
      ORDER BY   kellapJnsId,kelJnsOrderBy,kellapOrderBy asc
   ) a %s
   GROUP BY kellapId
   ORDER BY  kellapJnsId ,kelJnsOrderBy,kellapOrderBy asc
";
 */


?>