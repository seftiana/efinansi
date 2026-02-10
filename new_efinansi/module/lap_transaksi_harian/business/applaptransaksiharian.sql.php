<?php

#get count data 
$sql['get_count_data'] = "
    SELECT FOUND_ROWS() AS total
";

#get list data
$sql['get_data'] = "
  SELECT
      SQL_CALC_FOUND_ROWS
      b.`transReferensi` AS no_bpkb,
        b.transCatatan AS transaksi_catatan, 
        b.transTanggalEntri AS tgl_transaksi,
        b.transNilai AS transaksi_nilai,          
        IF(c.pdStatus = 'D', c.`pdNilai`, 0) AS transaksi_nilai_d,
        IF(c.pdStatus = 'K', c.`pdNilai`, 0) AS transaksi_nilai_k,        
        sa.saldo_awal as saldo_awal,
        d.coaKodeAkun AS coa_kode_akun,
        d.coaNamaAkun AS coa_nama_akun,
        d.coaId AS coa_id,
        c.pdStatus AS status_pembukuan,
        d.coaIsDebetPositif AS coa_status_debet,
        d.`coaCoaKelompokId` as coa_kelompok_id
    FROM
      pembukuan_referensi a
        JOIN transaksi b ON a.prTransId=b.transId
        JOIN pembukuan_detail c ON a.prId=c.pdPrId
        JOIN coa d ON d.coaId=c.pdCoaId
        LEFT JOIN (SELECT 
            `bbTppId` as tp_id,
            `bbCoaId` as coa_id,
            SUM(`bbSaldoAwal` + `bbSaldo`) AS saldo_awal
            FROM `buku_besar_his`
                WHERE bbPembukuanRefId IS NULL 
                AND bbPdId IS NULL
            GROUP BY bbTppId,bbCoaId) sa ON sa.coa_id = d.coaId AND sa.tp_id =b.transTppId

    WHERE
      b.transTanggalEntri BETWEEN '%s' AND '%s' 
      /* AND
      a.prIsApproved='Y' */
      AND
      (b.`transTransjenId` = '%s' OR %s) 
    ORDER BY
      d.coaKodeAkun,c.pdStatus
    LIMIT %s, %s
";

$sql['get_total_debet_kredit'] = "
    SELECT
        SUM(IF(c.pdStatus = 'D', c.`pdNilai`, 0)) AS t_d,
        SUM(IF(c.pdStatus = 'K', c.`pdNilai`, 0)) AS t_k
    FROM
        pembukuan_referensi a
        JOIN transaksi b ON a.prTransId=b.transId
        JOIN pembukuan_detail c ON a.prId=c.pdPrId
        JOIN coa d ON d.coaId=c.pdCoaId
    WHERE
        b.transTanggalEntri BETWEEN '%s' AND '%s' 
        /* AND
        a.prIsApproved='Y' */
        AND
        (b.`transTransjenId` = '%s' OR %s) 
";
$sql['get_data_cetak'] = "
  SELECT
      b.`transReferensi` AS no_bpkb,
        b.transCatatan AS transaksi_catatan, 
        b.transNilai AS transaksi_nilai,
        IF(c.pdStatus = 'D', c.`pdNilai`, 0) AS transaksi_nilai_d,
        IF(c.pdStatus = 'K', c.`pdNilai`, 0) AS transaksi_nilai_k,    
        sa.saldo_awal as saldo_awal,
        d.coaKodeAkun AS coa_kode_akun,
        d.coaNamaAkun AS coa_nama_akun,
        d.coaId AS coa_id,
        c.pdStatus AS status_pembukuan,
        d.coaIsDebetPositif AS coa_status_debet
    FROM
      pembukuan_referensi a
        JOIN transaksi b ON a.prTransId=b.transId
        JOIN pembukuan_detail c ON a.prId=c.pdPrId
        JOIN coa d ON d.coaId=c.pdCoaId
        LEFT JOIN (SELECT 
            `bbTppId` as tp_id,
            `bbCoaId` as coa_id,
            SUM(`bbSaldoAwal` + `bbSaldo`) AS saldo_awal
            FROM `buku_besar_his`
                WHERE bbPembukuanRefId IS NULL 
                AND bbPdId IS NULL
            GROUP BY bbTppId,bbCoaId) sa ON sa.coa_id = d.coaId AND sa.tp_id =b.transTppId        
    WHERE
      b.transTanggalEntri BETWEEN '%s' AND '%s'  
      /* AND
      a.prIsApproved='Y' */
      AND
        (b.`transTransjenId` = '%s' OR %s) 
    ORDER BY
        d.coaKodeAkun,c.pdStatus
";

$sql['get_saldo_transaksi'] = "
SELECT
    a.bbhisId AS bbhis_id,
    IFNULL(a.bbSaldoAwal,0) AS saldo_awal_transaksi
  FROM 
    buku_besar_his a
  LEFT JOIN pembukuan_detail b ON a.bbCoaId = b.pdCoaId
    LEFT JOIN pembukuan_referensi c ON b.pdPrId = c.prID
    LEFT JOIN transaksi d ON d.transId = c.prTransId
    LEFT JOIN coa e ON e.coaId = a.bbCoaId
  WHERE 
    a.bbCoaId = %s 
    AND 
    d.transTanggalEntri = '%s' 
    /* AND 
    prIsApproved = 'Y' */
    AND
    (d.`transTransjenId` = '%s' OR %s) 
  ORDER BY
    e.coaKodeAkun, d.transId, a.bbTanggal DESC
  LIMIT 0,1
";

#get min-max tahun
$sql['get_minmax_tahun_transaksi'] = "
  SELECT
    YEAR(MIN(transTanggalEntri)) - 5 AS minTahun,
    YEAR(MAX(transTanggalEntri)) + 5 AS maxTahun
  FROM
    transaksi
";

$sql['get_jenis_transaksi'] = "
SELECT
  `transjenId` AS id,
  `transjenNama` AS `name`
FROM 
  `transaksi_jenis_ref`
ORDER BY
  `transjenNama`  ASC
";

$sql['get_akun_transaksi'] = "
SELECT 
  DISTINCT
  d.`coaId` AS `id`,
  d.coaNamaAkun AS `name`
FROM
  pembukuan_referensi a 
  JOIN transaksi b 
    ON a.prTransId = b.transId 
  JOIN pembukuan_detail c 
    ON a.prId = c.pdPrId 
  JOIN coa d 
    ON d.coaId = c.pdCoaId 
";

//perhitungan saldo awal dan saldo berjalan (sesuai dengan filter tanggal

$sql['get_saldo_awal']  ="
SELECT
   bb.`bbCoaId` AS coa_id,
   SUM(bb.`bbSaldoAkhir`) AS saldo_awal
FROM 
`buku_besar_his` bb
 JOIN `tahun_pembukuan_periode` tpp ON tpp.`tppId`= bb.`bbTppId`
WHERE
(bb.`bbPembukuanRefId` IS NULL  AND  bb.`bbPdId` IS NULL)
AND
(tpp.`tppTanggalAwal` <= '%s' AND tpp.`tppTanggalAkhir` >= '%s')
GROUP BY bb.`bbCoaId`
";

$sql['get_saldo_awal_berjalan'] ="
SELECT
  pd.`pdCoaId` AS coa_id,
  SUM(pd.`pdNilai`) AS nominal,
  pd.`pdStatus` AS dk,
  IF(c.coaIsDebetPositif = 1,'D','K') AS saldo_normal
FROM
  pembukuan_detail pd
  JOIN pembukuan_referensi pr ON pr.`prId` = pd.`pdPrId`
  JOIN transaksi tr ON tr.`transId`  = pr.`prTransId`
  JOIN coa c ON c.`coaId` = pd.`pdCoaId`
WHERE
tr.transTanggalEntri BETWEEN '%s' AND (DATE_SUB('%s', INTERVAL 1 DAY))
AND
(tr.`transTransjenId` = '%s' OR %s)
GROUP BY pd.`pdCoaId`, pd.`pdStatus`
ORDER BY c.coaKodeAkun,pd.pdStatus
";


$sql['get_saldo_berjalan'] ="
SELECT
  pd.`pdCoaId` AS coa_id,
  SUM(pd.`pdNilai`) AS nominal,
  pd.`pdStatus` AS dk
FROM
  pembukuan_detail pd
  JOIN pembukuan_referensi pr ON pr.`prId` = pd.`pdPrId`
  JOIN transaksi tr ON tr.`transId`  = pr.`prTransId`
  JOIN coa c ON c.`coaId` = pd.`pdCoaId`
WHERE
tr.transTanggalEntri BETWEEN '%s' AND '%s'
AND 
(tr.`transTransjenId` = '%s' OR %s)
GROUP BY pd.`pdCoaId`, pd.`pdStatus`
ORDER BY c.coaKodeAkun,pd.pdStatus
";

$sql['get_tanggal_periode_pembukuan_aktif'] ="
SELECT
  `tppTanggalAwal` AS tanggal_awal,
  `tppTanggalAkhir` AS tanggal_akhir
FROM `tahun_pembukuan_periode`
WHERE
    tppIsBukaBuku = 'Y'
";

//end


?>