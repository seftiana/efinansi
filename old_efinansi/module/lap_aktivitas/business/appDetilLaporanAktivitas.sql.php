<?php

//===GET===


$sql['get_data_detil_klp_laporan'] = "
SELECT 
   cl.coaNamaAkun AS coa_nama,
   cl.coaKodeAkun AS coa_kode,
   (IFNULL(sa.saldo_awal,0) + IFNULL(bbTrans.saldo,0)) AS coa_nominal 
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
          IF(c.`coaCoaKelompokId` = 1/*aktiva*/, (bhis.`bbDebet` - bhis.`bbKredit`),
            IF(c.`coaCoaKelompokId` = 4/*pendapatan*/,(bhis.`bbKredit`-bhis.`bbDebet`),
                IF(c.`coaCoaKelompokId` = 5/*beban*/,(bhis.`bbDebet` -bhis.`bbKredit`),
                    bhis.bbSaldo
                )
            )
          )
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
              AND
              prIsJurnalBalik = 0  AND `prIsPosting` = 'Y' 
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
  cklr.coakellapIdKellap = '%s' 
  AND 
  kjlr.kelJnsPrntId = '6' 
ORDER BY coa_kode ";
?>