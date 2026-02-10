<?php

//===GET===

$sql['get_data_detil_klp_laporan'] = "
SELECT 
   cl.coaNamaAkun AS coa_nama,
   cl.coaKodeAkun AS coa_kode, 
   ( IFNULL(sa.saldo_awal,0) + IFNULL(bbTrans.saldo,0)  + IFNULL(bbLR.saldo,0) ) AS coa_nominal 
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
        JOIN coa c ON c.coaId = bhis.bbCoaId AND c.`coaIsLabaRugiThJln` NOT IN( 1)
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
              #AND  prIsJurnalBalik = 0  
              AND `prIsPosting` = 'Y' 
              GROUP BY prId,pdId,coaId
        ) AS jurnal ON jurnal.prId = bhis.`bbPembukuanRefId` 
            AND jurnal.pdId = bhis.`bbPdId` 
            AND jurnal.coaId = `bhis`.`bbCoaId` 
	GROUP BY bhis.bbCoaId
  ) bbTrans ON bbTrans.coa_id = coakellapCoaId 
  LEFT JOIN (
    SELECT 
      bhis.`bbCoaId` AS coa_id,
      SUM( bhis.bbSaldo ) AS saldo 
    FROM
      buku_besar_his bhis 
      JOIN coa c 
        ON c.coaId = bhis.bbCoaId  AND c.`coaIsLabaRugiThJln` = 1
      JOIN  pembukuan_referensi pr 
        ON pr.`prId` = bhis.`bbPembukuanRefId`
      JOIN transaksi tr 
       ON tr.`transId` = pr.`prTransId` 
      WHERE tr.transTanggalEntri BETWEEN '%s' AND '%s'
          #AND prIsJurnalBalik = 0 
          AND `prIsPosting` = 'Y' 
    GROUP BY bhis.bbCoaId
  ) bbLR ON bbLR.coa_id = coakellapCoaId
LEFT JOIN ( 
  SELECT 
    `bbCoaId` AS coa_id,
    SUM(`bbSaldoAwal` + (IF(c.`coaCoaKelompokId` = 1/*Aktiva*/,
    bhis.`bbDebet` - `bhis`.`bbKredit`,`bbSaldo`))) AS saldo_awal
  FROM 
  `buku_besar_his` bhis 
  JOIN coa c ON c.`coaId` = `bhis`.`bbCoaId`
  WHERE 
    bbPembukuanRefId IS NULL 
      AND bbPdId IS NULL  
  GROUP BY bbTppId,bbCoaId 
) sa ON sa.coa_id = coakellapCoaId 
  
WHERE
  cklr.coakellapIdKellap = '%s' 
  AND 
  kjlr.kelJnsPrntId = '14' 
ORDER BY
substring_index(replace(replace(cl.coaKodeAkun,'.','*'),'-','*'),'*',1)+0, 
substring_index(substring_index(concat(replace(replace(cl.coaKodeAkun,'.','*'),'-','*'),'*0*0*0*0*0*0*0*0'),'*',2),'*',-1)+0,
substring_index(substring_index(concat(replace(replace(cl.coaKodeAkun,'.','*'),'-','*'),'*0*0*0*0*0*0*0*0'),'*',3),'*',-1)+0,
substring_index(substring_index(concat(replace(replace(cl.coaKodeAkun,'.','*'),'-','*'),'*0*0*0*0*0*0*0*0'),'*',4),'*',-1)+0,
substring_index(substring_index(concat(replace(replace(cl.coaKodeAkun,'.','*'),'-','*'),'*0*0*0*0*0*0*0*0'),'*',5),'*',-1)+0,
substring_index(substring_index(concat(replace(replace(cl.coaKodeAkun,'.','*'),'-','*'),'*0*0*0*0*0*0*0*0'),'*',6),'*',-1)+0,
substring_index(substring_index(concat(replace(replace(cl.coaKodeAkun,'.','*'),'-','*'),'*0*0*0*0*0*0*0*0'),'*',7),'*',-1)+0,
substring_index(substring_index(concat(replace(replace(cl.coaKodeAkun,'.','*'),'-','*'),'*0*0*0*0*0*0*0*0'),'*',8),'*',-1)+0  ASC ";

$sql['get_data_detil_klp_laporan_old'] = "
SELECT 
    coaNamaAkun AS coa_nama,
    coaKodeAkun AS coa_kode,
    IFNULL(saldo.saldo_akhir,0) AS coa_nominal
    FROM kelompok_laporan_ref
        LEFT JOIN kelompok_jenis_laporan_ref ON kellapJnsId = kelJnsId
        LEFT JOIN coa_kelompok_laporan_ref ON coakellapIdKellap = kellapId
        LEFT JOIN coa ON coaId=coakellapCoaId
        LEFT JOIN (
         SELECT
            bhis.`bbCoaId` AS coa_id,
            SUM(`bbSaldo`) AS saldo_akhir
          FROM 
                  `buku_besar_his` bhis
                  JOIN coa c ON c.`coaId` = bhis.`bbCoaId`
                  LEFT JOIN pembukuan_referensi pr ON pr.`prId` = bhis.`bbPembukuanRefId` AND pr.`prIsPosting` = 'Y'	
                  LEFT JOIN transaksi tr   
                  ON  tr.transId = pr.prTransId AND tr.`transTppId` = bhis.`bbPembukuanRefId` AND tr.`transIsJurnal` = 'Y'
                  AND tr.`transTanggalEntri` BETWEEN '%s' AND '%s'
          WHERE
           bhis.bbTppId = (SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y')
          GROUP BY `bbCoaId` 
          ORDER BY c.`coaKodeAkun` ASC      
      ) saldo ON saldo.coa_id = coakellapCoaId
    WHERE 
        coakellapIdKellap = '%s' AND
	kelJnsPrntId = '14'			
    ORDER BY coa_kode";
?>