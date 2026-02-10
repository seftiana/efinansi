<?php
$sql['get_range_year']  = "
SELECT
   EXTRACT(YEAR FROM IFNULL(MIN(tppTanggalAwal), DATE_ADD(DATE(NOW()),INTERVAL -DAY(DATE(NOW()))+1 DAY))) AS minYear,
   EXTRACT(YEAR FROM IFNULL(MAX(tppTanggalAkhir), DATE(LAST_DAY(DATE(NOW()))))) AS maxYear,
   IFNULL(MIN(tppTanggalAwal), DATE_ADD(DATE(NOW()),INTERVAL -DAY(DATE(NOW()))+1 DAY)) AS tanggalAwal,
   IFNULL(MAX(tppTanggalAkhir), DATE(LAST_DAY(DATE(NOW())))) AS tanggalAkhir
FROM tahun_pembukuan_periode
WHERE 1 = 1
AND tppIsBukaBuku = 'Y'
";

$sql['count']           = "
SELECT FOUND_ROWS() AS `count`
";

/**
 * query baru
 * @description Get nominal saldo dan transaksi terposting dan belum dan ter approve
 * 
 * @since 28 January 2016
 * @modified 30 Agustus 2017
 */
$sql['get_data_laporan'] = "
SELECT
       cklr.`coakellapIdKellap` AS kellap_id,
       klr.`kellapOrderBy` AS kellap_order_by,
       klr.`kellapJnsId` AS kellap_jns_id,      
       IFNULL(kjlr.`kelJnsOrderBy`,0) AS kellap_jns_order_by,
       kjlr.`kelJnsNama` AS kellap_jns_nama,
       klr.`kellapNama` AS kellap_nama,
       coa.coaId AS id,	
       coa.`coaKodeAkun` AS coa_kode,
       coa.`coaNamaAkun` AS coa_nama,
       coa.`coaCoaKelompokId` AS coa_kelompok_id,
       coa.`coaIsDebetPositif` AS saldo_normal,
       akumulasi_rl.flag,
       IF(akumulasi_rl.flag = 'LR',
	IF((akumulasi_rl.flag = 'LR' AND  akumulasi_rl.nominal_rl < 0),(akumulasi_rl.nominal_rl *-1),0),
	  IFNULL(fromBB.debet,0) + IFNULL( jurnal.nominalDebet,0) 
       ) AS nominal_debet,
       IF(akumulasi_rl.flag = 'LR',
	IF((akumulasi_rl.flag = 'LR' AND  akumulasi_rl.nominal_rl > 0),akumulasi_rl.nominal_rl,0),       
	  IFNULL(fromBB.kredit,0) + IFNULL( jurnal.nominalKredit,0)
       ) AS nominal_kredit
    FROM 
        coa
        JOIN coa_kelompok_laporan_ref cklr
           ON cklr.coakellapCoaId = coa.coaId 
        JOIN kelompok_laporan_ref klr ON klr.`kellapId` = cklr.`coakellapIdKellap`
        JOIN kelompok_jenis_laporan_ref kjlr ON kjlr.`kelJnsId` = klr.`kellapJnsId`
        LEFT JOIN (
            SELECT
                tr.`transTppId` AS tppId, 
                pd.`pdCoaId` AS coaId,
                pd.`pdStatus` AS pdStatus,              
                SUM(IF(pd.`pdStatus` = 'D' ,pd.`pdNilai`,0)) AS nominalDebet,
                SUM(IF(pd.`pdStatus` = 'K' ,pd.`pdNilai`,0)) AS nominalKredit
            FROM
                transaksi tr
                JOIN pembukuan_referensi pr
                   ON pr.prTransId = tr.transId
                JOIN pembukuan_detail pd
                   ON pd.pdPrId = pr.prId
                JOIN coa c
                   ON c.`coaId` = pd.`pdCoaId`
            WHERE                
                tr.`transTanggalEntri` BETWEEN '%s' AND '%s'
                AND
                pr.`prIsApproved` ='Y'
                AND tr.`transTppId` = (SELECT tpp.tppId FROM tahun_pembukuan_periode tpp WHERE tpp.`tppIsBukaBuku` ='Y')
            GROUP BY pd.`pdCoaId`
        ) jurnal ON jurnal.coaId = coa.`coaId`
        LEFT JOIN (
           SELECT
               `bbCoaId` AS bbCoaId,
                SUM(`bbSaldo`) AS bbSaldo,
                SUM(`bbSaldoAwal`) AS saldoAwal,
                SUM(`bbDebet`) AS debet,
                SUM(`bbKredit`) AS kredit
           FROM `buku_besar_his`
           WHERE bbPembukuanRefId IS NULL AND bbPdId IS NULL
               AND `bbTppId` = (SELECT tpp.tppId FROM tahun_pembukuan_periode tpp WHERE tpp.`tppIsBukaBuku` ='Y')
               GROUP BY bbTppId,bbCoaId 
       ) fromBB ON fromBB.bbCoaId = coa.coaId       
            LEFT JOIN (
                SELECT 
                c.`coaId` AS coaId,
                'LR' AS flag,
                (SELECT
                 SUM(IF(c.`coaCoaKelompokId` = 4,
			IF(pd.`pdStatus` = 'D', ( pd.`pdNilai` * -1), (pd.`pdNilai`) ),
				IF(pd.`pdStatus` = 'D', ( pd.`pdNilai` * -1), (pd.`pdNilai`) )))
                  AS nominal
                FROM transaksi tr
                 JOIN pembukuan_referensi pr
                   ON tr.`transId` = pr.`prTransId`
                 JOIN pembukuan_detail pd
                   ON pd.`pdPrId` = pr.`prId`
                 JOIN coa c
                   ON c.`coaId` = pd.`pdCoaId`
                 JOIN coa_kelompok ck
                   ON ck.`coaKelompokId` = c.`coaCoaKelompokId`  
                 WHERE
                   ck.`coaKelompokNama` IN ('Pendapatan','Biaya')
                   AND   
                   tr.`transTanggalEntri` BETWEEN '%s' AND '%s'
                   AND tr.`transTppId` = (SELECT tpp.tppId FROM tahun_pembukuan_periode tpp WHERE tpp.`tppIsBukaBuku` ='Y')
                   AND pr.prIsJurnalBalik = 0
                   ) AS nominal_rl
                FROM coa c
                WHERE c.`coaIsLabaRugiThJln` = 1
            ) AS akumulasi_rl ON akumulasi_rl.coaId   = coa.coaId             
 WHERE 1 = 1
 AND kelJnsPrntId = 14
 GROUP BY coa.CoaId
 ORDER BY kellap_jns_id,kellap_jns_order_by,kellap_order_by
";

$sql['get_detail_laporan']    = "
SELECT
   kellapId AS id,
   coaId AS akunId,
   coaKodeAkun AS kodeAkun,
   coaNamaAkun AS namaAkun,
   IFNULL(jbb.nominalDebet, 0) AS nominalDebet,
   IFNULL(jbb.nominalKredit, 0) AS nominalKredit,
   IFNULL(jbb.saldo, 0) + IFNULL(jbb.trans,0) AS saldo
FROM
    coa
    LEFT JOIN coa_kelompok_laporan_ref
        ON coakellapCoaId = coaId
    LEFT JOIN kelompok_laporan_ref
        ON kellapId = coakellapIdKellap
    LEFT JOIN kelompok_jenis_laporan_ref
        ON kellapJnsId = kelJnsId
    LEFT JOIN (
        SELECT
            coa.coaId AS akunId,
            kellapId AS kelompokId,            
            (IF(UPPER(jurnal.pdStatus) = 'D', jurnal.pdNilai, 0) + IFNULL(fromBB.debet,0)) AS nominalDebet,
            (IF(UPPER(jurnal.pdStatus) = 'K', jurnal.pdNilai, 0) + IFNULL(fromBB.kredit,0)) AS nominalKredit,
            
       IF(coa.`coaIsDebetPositif` = 0,
            IF(coa.`coaCoaKelompokId` = 1,
                IF(UPPER(jurnal.pdStatus) = 'D', jurnal.pdNilai, 0) - IF(UPPER(jurnal.pdStatus) = 'K', jurnal.pdNilai, 0),
                IF(UPPER(jurnal.pdStatus) = 'K', jurnal.pdNilai, 0) - IF(UPPER(jurnal.pdStatus) = 'D', jurnal.pdNilai, 0)),
          IF(UPPER(jurnal.pdStatus) = 'D', jurnal.pdNilai, 0) - IF(UPPER(jurnal.pdStatus) = 'K', jurnal.pdNilai, 0)) AS trans,
        
        -- IF(akumulasi_rl.flag ='LR',akumulasi_rl.nominal_rl,       
        -- (
          IF(coa.`coaIsDebetPositif` = 0,
            IF(coa.`coaCoaKelompokId` = 1,
                IF(UPPER(jurnal.pdStatus) = 'D', jurnal.pdNilai, 0) - IF(UPPER(jurnal.pdStatus) = 'K', jurnal.pdNilai, 0),
                IF(UPPER(jurnal.pdStatus) = 'K', jurnal.pdNilai, 0) - IF(UPPER(jurnal.pdStatus) = 'D', jurnal.pdNilai, 0)),
          IF(UPPER(jurnal.pdStatus) = 'D', jurnal.pdNilai, 0) - IF(UPPER(jurnal.pdStatus) = 'K', jurnal.pdNilai, 0))
          +
          IF(coa.`coaIsDebetPositif` = 0,
             IF(coa.`coaCoaKelompokId` = 1,
                ((IFNULL(fromBB.debet,0) - IFNULL(fromBB.kredit,0)) + (IFNULL(fromBB.saldoAwal,0))),
                ((IFNULL(fromBB.kredit,0) - IFNULL(fromBB.debet,0)) + (IFNULL(fromBB.saldoAwal,0)))),
          ((IFNULL(fromBB.debet,0) - IFNULL(fromBB.kredit,0)) + (IFNULL(fromBB.saldoAwal,0))))
          -- ))
           AS saldo
        FROM
            coa         
            LEFT JOIN coa_kelompok_laporan_ref
                ON coakellapCoaId = coaId
            LEFT JOIN kelompok_laporan_ref
                ON kellapId = coakellapIdKellap
            LEFT JOIN (
                SELECT
                    tr.`transTppId` AS tppId, 
                    pd.`pdCoaId` AS coaId,
                    pd.`pdStatus` AS pdStatus,
                    pd.`pdNilai` AS pdNilai
                FROM
                    transaksi tr
                    JOIN pembukuan_referensi pr
                       ON pr.prTransId = tr.transId
                    JOIN pembukuan_detail pd
                       ON pd.pdPrId = pr.prId
                WHERE 
                  
                    tr.`transTanggalEntri` BETWEEN '%s' AND '%s'
                    AND tr.`transTppId` = (SELECT tpp.tppId FROM tahun_pembukuan_periode tpp WHERE tpp.`tppIsBukaBuku` ='Y')    
                ) jurnal ON jurnal.coaId = coa.`coaId`
            LEFT JOIN (    
                SELECT
                    `bbTppId` AS bbTaPeriodeId,
                    `bbCoaId` AS bbCoaId,
                    SUM(`bbSaldoAwal`) AS saldoAwal,
                    SUM(`bbDebet`) AS debet,
                    SUM(`bbKredit`) AS kredit
                    FROM `buku_besar_his`
                        WHERE bbPembukuanRefId IS NULL AND bbPdId IS NULL
                        AND `bbTppId` =  (SELECT tpp.tppId FROM tahun_pembukuan_periode tpp WHERE tpp.`tppIsBukaBuku` ='Y')    
                    GROUP BY bbTppId,bbCoaId 
                 ) fromBB ON  fromBB.bbCoaId = coa.coaId 
            LEFT JOIN (
                SELECT 
                c.`coaId` AS coaId,
                'LR' AS flag,
                (SELECT
                 SUM(IF(c.`coaCoaKelompokId` = 4,
                 IF(c.`coaIsDebetPositif` = 0 ,pd.`pdNilai`,(pd.`pdNilai` * (-1))),
                 IF(c.`coaIsDebetPositif` = 0 ,pd.`pdNilai`,(pd.`pdNilai` * (-1))))) AS nominal
                FROM transaksi tr
                 JOIN pembukuan_referensi pr
                   ON tr.`transId` = pr.`prTransId`
                 JOIN pembukuan_detail pd
                   ON pd.`pdPrId` = pr.`prId`
                 JOIN coa c
                   ON c.`coaId` = pd.`pdCoaId`
                 JOIN coa_kelompok ck
                   ON ck.`coaKelompokId` = c.`coaCoaKelompokId`  
                 WHERE
                   ck.`coaKelompokNama` IN ('Pendapatan','Biaya')
                   AND   
                   tr.`transTanggalEntri` BETWEEN '%s' AND '%s'
                   AND tr.`transTppId` = (SELECT tpp.tppId FROM tahun_pembukuan_periode tpp WHERE tpp.`tppIsBukaBuku` ='Y')
                   AND tr.`transId` NOT IN (SELECT prTransId FROM pembukuan_referensi WHERE prIsJurnalBalik = 1 AND prTransId IS NOT NULL) ) AS nominal_rl
                FROM coa c
                JOIN coa_tipe_coa ctc ON ctc.`coatipecoaCoaId` = c.`coaId`
                WHERE ctc.`coatipecoaCtrId` = 3    
            ) AS akumulasi_rl ON akumulasi_rl.coaId   = coa.coaId             
        GROUP BY akunId, kellapId) AS jbb
    ON  jbb.akunId = coaId  AND jbb.kelompokId = kellapId
WHERE 1 = 1
    AND kellapId = '%s'
    AND kelJnsPrntId = '14'
    ORDER BY kodeAkun
";

/**
 * @since 28 January 2016
$sql['get_data_laporan']   = "
SELECT
   kelJnsOrderBy,
   kellapId,
   kellapJnsId,
   kelJnsNama,
   kellapNama,
   kellapIsTambah AS `status`,
   SUM(IFNULL(jurnal.nominalDebet, 0)) AS nominalDebet,
   SUM(IFNULL(jurnal.nominalKredit, 0)) AS nominalKredit,
   SUM(IFNULL(jurnal.saldo, 0)) AS nominal
FROM kelompok_laporan_ref
LEFT JOIN kelompok_jenis_laporan_ref ON kelJnsId = kellapJnsId
LEFT JOIN coa_kelompok_laporan_ref ON coakellapIdKellap = kellapId
LEFT JOIN (SELECT
   coaId AS id,
   (IF(UPPER(pdStatus) = 'D', pdNilai, 0) + IFNULL(fromBB.debet,0)) AS nominalDebet,
   (IF(UPPER(pdStatus) = 'K', pdNilai, 0) + IFNULL(fromBB.kredit,0)) AS nominalKredit,
   (SUM(IF(UPPER(pdStatus) = 'D', pdNilai, 0) - IF(UPPER(pdStatus) = 'K', pdNilai, 0))
   + (IFNULL(fromBB.debet,0)) - (IFNULL(fromBB.kredit,0))) AS saldo
FROM transaksi
JOIN pembukuan_referensi
   ON prTransId = transId
JOIN pembukuan_detail
   ON pdPrId = prId
JOIN coa
   ON coaId = pdCoaId
LEFT JOIN coa_kelompok_laporan_ref
   ON coakellapCoaId = coaId
 LEFT JOIN (
        SELECT
            `bbTppId` AS bbTaPeriodeId,
            `bbCoaId` AS bbCoaId,
            SUM(IF(`bbSaldo` > 0, (`bbSaldoAwal` + `bbSaldo`), 0)) AS debet,
            SUM(IF(`bbSaldo` < 0, (`bbSaldoAwal` +`bbSaldo`), 0)) AS kredit
            FROM `buku_besar_his`
                WHERE bbPembukuanRefId IS NULL AND bbPdId IS NULL
            GROUP BY bbTppId,bbCoaId 
         ) fromBB ON fromBB.bbTaPeriodeId = transaksi.`transTppId`
         AND fromBB.bbCoaId = coa.coaId    
WHERE 1 = 1
AND prTanggal BETWEEN '%s' AND '%s'
GROUP BY pdCoaId) AS jurnal ON jurnal.id = coakellapCoaId
WHERE kelJnsPrntId = 14
GROUP BY kellapId
ORDER BY kellapJnsId,kelJnsOrderBy,kellapOrderBy
";

      
$sql['get_detail_laporan']    = "
SELECT
   kellapId AS id,
   coaId AS akunId,
   coaKodeAkun AS kodeAkun,
   coaNamaAkun AS namaAkun,
   IFNULL(jurnal.nominalDebet, 0) AS nominalDebet,
   IFNULL(jurnal.nominalKredit, 0) AS nominalKredit,
   IFNULL(jurnal.saldo, 0) AS saldo
FROM
   coa
   LEFT JOIN coa_kelompok_laporan_ref
      ON coakellapCoaId = coaId
   LEFT JOIN kelompok_laporan_ref
      ON kellapId = coakellapIdKellap
   JOIN kelompok_jenis_laporan_ref
      ON kellapJnsId = kelJnsId
   LEFT JOIN
      (SELECT
         coaId AS akunId,
         kellapId AS kelompokId,
         GROUP_CONCAT(transId),
         (SUM(IF(UPPER(pdStatus) = 'D',pdNilai, 0)) + IFNULL(fromBB.debet,0)) AS nominalDebet,
         (SUM(IF(UPPER(pdStatus) = 'K', pdNilai, 0)) +  IFNULL(fromBB.kredit,0)) AS nominalKredit,
          (SUM( IF(UPPER(pdStatus) = 'D', pdNilai, 0) - IF(UPPER(pdStatus) = 'K', pdNilai, 0)) 
            + (IFNULL(fromBB.debet,0)) - (IFNULL(fromBB.kredit,0))) AS saldo 
      FROM
         transaksi
         JOIN pembukuan_referensi
            ON prTransId = transId
         JOIN pembukuan_detail
            ON pdPrId = prId
         JOIN coa
            ON coaId = pdCoaId
         LEFT JOIN coa_kelompok_laporan_ref
            ON coakellapCoaId = coaId
         JOIN kelompok_laporan_ref
            ON kellapId = coakellapIdKellap
         LEFT JOIN (
        SELECT
            `bbTppId` AS bbTaPeriodeId,
            `bbCoaId` AS bbCoaId,
      SUM(IF(`bbSaldo` > 0, (`bbSaldoAwal` + `bbSaldo`), 0)) AS debet,
      SUM(IF(`bbSaldo` < 0, (`bbSaldoAwal` +`bbSaldo`), 0)) AS kredit
            FROM `buku_besar_his`
                WHERE bbPembukuanRefId IS NULL AND bbPdId IS NULL
            GROUP BY bbTppId,bbCoaId 
         ) fromBB ON fromBB.bbTaPeriodeId = transaksi.`transTppId`
         AND fromBB.bbCoaId = coa.coaId 
      AND prTanggal BETWEEN '%s' AND '%s'
      GROUP BY coaId,
         kellapId) AS jurnal
      ON jurnal.akunId = coaId
      AND jurnal.kelompokId = kellapId
WHERE 1 = 1
   AND kellapId = '%s'
   AND kelJnsPrntId = '14'
    ORDER BY kodeAkun
";

 * 
 * 
 */
?>