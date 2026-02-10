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
   ( SUM(IFNULL(sa.saldo_awal,0) + IFNULL(bbTrans.saldo,0) + IFNULL(bbLR.saldo,0) ) ) AS nilai
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
      bhis.`bbCoaId` AS coa_id,
      SUM(
        IF(
          c.`coaCoaKelompokId` = 1 /*aktiva*/ ,
          bhis.`bbDebet` - bhis.`bbKredit`,
          bhis.bbSaldo
        )
      ) AS saldo 
    FROM
      buku_besar_his bhis 
      JOIN coa c 
        ON c.coaId = bhis.bbCoaId AND c.`coaIsLabaRugiThJln` = 1
      JOIN  pembukuan_referensi pr 
        ON pr.`prId` = bhis.`bbPembukuanRefId`
      JOIN transaksi tr 
       ON tr.`transId` = pr.`prTransId` 
      WHERE tr.transTanggalEntri BETWEEN '%s' AND '%s'
          AND prIsJurnalBalik = 0 
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
    JOIN tahun_pembukuan_periode tpp
	   ON tpp.tppId =  bhis.bbTppId AND tpp.tppIsBukaBuku = 'Y'   
    JOIN coa c ON c.`coaId` = `bhis`.`bbCoaId`
     WHERE bbPembukuanRefId IS NULL 
      AND bbPdId IS NULL 
GROUP BY bbTppId,bbCoaId
) sa ON sa.coa_id = coakellapCoaId 
WHERE
  kjlr.kelJnsPrntId = '14' 
GROUP BY kellapId
 %s
ORDER BY  kellapJnsId ASC ,kelJnsOrderBy ASC,kellapOrderBy ASC

";



$sql['get_laporan_all_detil'] ="
SELECT 
   klr.`kellapOrderBy`,
   kjlr.`kelJnsOrderBy`,
   klr.kellapId,
   klr.kellapJnsId,
   kjlr.kelJnsNama,
   klr.`kellapNama` AS nama_kel_lap,
   klr.`kellapIsTambah` AS `status`,
   cl.`coaKodeAkun`,
   cl.`coaNamaAkun` AS nama_coa,
   cklr.`coakellapDK` AS  saldo_normal,
   ( SUM(IFNULL(sa.saldo_awal,0) + IFNULL(bbTrans.saldo,0) + IFNULL(bbLR.saldo,0) ) ) AS nilai
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
      bhis.`bbCoaId` AS coa_id,
      SUM(
        IF(
          c.`coaCoaKelompokId` = 1 /*aktiva*/ ,
          bhis.`bbDebet` - bhis.`bbKredit`,
          bhis.bbSaldo
        )
      ) AS saldo 
    FROM
      buku_besar_his bhis 
      JOIN coa c 
        ON c.coaId = bhis.bbCoaId AND c.`coaIsLabaRugiThJln` = 1
      JOIN  pembukuan_referensi pr 
        ON pr.`prId` = bhis.`bbPembukuanRefId`
      JOIN transaksi tr 
       ON tr.`transId` = pr.`prTransId` 
      WHERE tr.transTanggalEntri BETWEEN '%s' AND '%s'
          AND prIsJurnalBalik = 0 
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
    JOIN tahun_pembukuan_periode tpp
	   ON tpp.tppId =  bhis.bbTppId AND tpp.tppIsBukaBuku = 'Y'
    JOIN coa c ON c.`coaId` = `bhis`.`bbCoaId`
     WHERE bbPembukuanRefId IS NULL 
      AND bbPdId IS NULL 
GROUP BY bbTppId,bbCoaId
) sa ON sa.coa_id = coakellapCoaId 
WHERE
  kjlr.kelJnsPrntId = '14'
GROUP BY cl.`coaId`
 %s
ORDER BY  kellapId,kellapJnsId ,kelJnsOrderBy,kellapOrderBy ASC

";


/*
$sql['get_laporan_all_old'] = "
   SELECT 
      kellapOrderBy,
      kelJnsOrderBy, 
      kellapId,
      kellapJnsId,
      kelJnsNama,
      nama_kel_lap,     
      `status`,
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
      (saldo.saldo_akhir) AS nilai
      FROM kelompok_laporan_ref
      LEFT JOIN kelompok_jenis_laporan_ref ON kellapJnsId = kelJnsId
      LEFT JOIN coa_kelompok_laporan_ref ON coakellapIdKellap = kellapId
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
            kelJnsPrntId = '14'
      ORDER BY kellapJnsId, kelJnsOrderBy,kellapOrderBy
   ) a %s
   GROUP BY kellapId
   ORDER BY kellapJnsId,kelJnsOrderBy,kellapOrderBy
";

*/
?>
