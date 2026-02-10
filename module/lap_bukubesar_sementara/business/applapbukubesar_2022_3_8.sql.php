<?php
$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_buku_besar_sementara']="
SELECT SQL_CALC_FOUND_ROWS
    id,
    tp_id,
    coa_id,
    akun_kode,
    akun_nama,
    tanggalJurnalEntri,
    nomorReferensi,
    keterangan,
    saldo_awal,
    debet,
    kredit,
    is_jurnal_balik
FROM 
(
    (SELECT 
        0 AS id,
        `bbTppId` AS tp_id,
        `bbCoaId` AS coa_id,
        c.`coaKodeAkun` AS akun_kode,
        c.`coaNamaAkun` AS akun_nama,
        bbTanggal AS tanggalJurnalEntri,
        NULL AS nomorReferensi,
        'Saldo Awal' AS keterangan,    
        SUM(`bbSaldoAwal` + (IF(c.`coaCoaKelompokId` = 1,bbDebet - bbKredit,`bbSaldo`))) AS saldo_awal,
        0 AS debet,
        0 AS kredit,
        0 as is_jurnal_balik
    FROM
        buku_besar_his
        JOIN tahun_pembukuan_periode tpp
        ON tpp.tppId =  bbTppId AND tpp.tppIsBukaBuku = 'Y'
        JOIN coa c ON c.`coaId` = bbCoaId
    WHERE
        bbPembukuanRefId IS NULL
        AND
        bbPdId IS NULL
        AND (coaId = %s OR 1 = %s)
    GROUP BY bbTppId,bbCoaId
    ORDER BY  akun_kode,tanggalJurnalEntri,is_jurnal_balik ASC)
    UNION
    (SELECT 
        transid AS id,
        transTppId AS tp_id,
        pdCoaId AS coa_id,
        coaKodeAkun AS akun_kode,
        coaNamaAkun AS akun_nama,
        transTanggalEntri AS tanggalJurnalEntri,
        transReferensi AS nomorReferensi,
        transCatatan AS keterangan,
        sa.saldo_awal AS saldoAwal,
        IF(UPPER(pdStatus) = 'D', pdNilai, 0) AS debet,
        IF(UPPER(pdStatus) = 'K', pdNilai, 0) AS kredit,
        prIsJurnalBalik as is_jurnal_balik
    FROM
        transaksi 
        JOIN pembukuan_referensi
          ON   pembukuan_referensi.`prTransId`= transaksi.`transId` #AND prIsJurnalBalik = 0
        JOIN pembukuan_detail 
          ON pembukuan_detail.`pdPrId` = pembukuan_referensi.`prId`
        JOIN coa 
          ON (coaId = `pdCoaId` )
        JOIN tahun_pembukuan_periode 
          ON (tppId = transTppId ) AND tppIsBukaBuku = 'Y' 
        LEFT JOIN (
            SELECT
                `bbTppId` AS tp_id,
                `bbCoaId` AS coa_id,
                bbTanggal AS bbTanggal,
                SUM(`bbSaldoAwal` + (IF(c.`coaCoaKelompokId` = 1,bbDebet - bbKredit,`bbSaldo`))) AS saldo_awal
            FROM
                buku_besar_his
                JOIN tahun_pembukuan_periode tpp
                    ON tpp.tppId =  bbTppId AND tpp.tppIsBukaBuku = 'Y'
                JOIN coa c ON c.`coaId` = bbCoaId
                    WHERE bbPembukuanRefId IS NULL AND bbPdId IS NULL
            GROUP BY bbTppId,bbCoaId
    ) sa ON sa.tp_id = transaksi.`transTppId` AND sa.coa_id = coaId
    WHERE 1 = 1
    AND (transTanggalEntri BETWEEN '%s' AND '%s')
    AND (coaId = %s OR 1 = %s) 
    ORDER BY  akun_kode,tanggalJurnalEntri,nomorReferensi,prIsJurnalBalik ASC )
) AS buku_besar_sementara
ORDER BY akun_kode,tanggalJurnalEntri,nomorReferensi,is_jurnal_balik ASC
";

$sql['get_limit'] = "
LIMIT %s,%s
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
