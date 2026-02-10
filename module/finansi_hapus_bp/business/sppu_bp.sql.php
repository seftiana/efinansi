<?php
/**
 * @package  SQL-FILE
 */

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_sppu']   = "
SELECT SQL_CALC_FOUND_ROWS
   sppuId AS id,
   sppuNomor AS nomor,
   pr.pengrealNomorPengajuan AS nomorPengajuan,
   sppuTanggal AS tanggal,
   sppuNomorBukti AS nomorBukti,
   sppuBank AS bank,
   sppuNomorRekening AS nomorRekening,
   IFNULL(sppu.nominal, 0) AS nominal,
   IF(trb.`transaksiBankId` IS NULL,sppuBPKBBp,trb.`transaksiBankBpkb`) AS nomorBp,
   IF(trans_kas.sppu_id IS NULL,sppuBPKBCr,trans_kas.no_bpkb)  AS nomorCr,
   sppuBankPayment AS bankPayment,
   sppuCashPayment AS cashReceipt,
   sppuIsTransaksiKas AS isTransaksi
FROM finansi_pa_sppu
JOIN (SELECT
   sppuDetSppuId AS id,
   SUM(sppuDetNominal) AS nominal
FROM finansi_pa_sppu_det
GROUP BY sppuDetSppuId) AS sppu
   ON sppu.id = sppuId
LEFT JOIN finansi_pa_sppu_det sppuDet
ON sppuDet.sppuDetSppuId = sppuId
LEFT JOIN pengajuan_realisasi_detil prd
ON sppuDetPengrealDetId = prd.`pengrealdetId`
JOIN pengajuan_realisasi pr
ON pr.`pengrealId` = prd.`pengrealdetPengRealId`
LEFT JOIN `finansi_pa_transaksi_bank` trb
   ON trb.`transaksiBankSppuId` = sppuId  
LEFT JOIN (
SELECT
    sppu_d.`sppuDetSppuId` AS sppu_id,
    trk.`transaksiKasBpkb` AS no_bpkb
FROM 
`finansi_pa_transaksi_kas` trk
JOIN `finansi_pa_transaksi_kas_detil` trkd
    ON trkd.`transaksiKasDetilTransaksiKasId` = trk.`transaksiKasId`
JOIN `finansi_pa_sppu_det` sppu_d 
    ON sppu_d.`sppuDetId` = trkd.`transaksiKasDetilSppuDetId`
GROUP BY sppu_id
) AS trans_kas ON trans_kas.sppu_id = sppuId 
WHERE 1 = 1
AND sppuNomor LIKE '%s'
AND sppuBPKBBp LIKE '%s'
AND pr.pengrealNomorPengajuan LIKE '%s'
AND sppuTanggal BETWEEN '%s' AND '%s'
AND sppuBPKBBp IS NOT NULL
GROUP BY sppuId
LIMIT %s, %s
";

/**
 * untuk hapus bp
 */

$sql['update_no_bp_sppu'] ="
UPDATE `finansi_pa_sppu`
SET 
  `sppuBPKBBp` = NULL,
  `sppuUserIdHapusBp` = '%s',
  `sppuTanggalHapusBp` = NOW()
WHERE `sppuId` = '%s'        
";

$sql['do_delete_bp'] = "
DELETE FROM `finansi_pa_transaksi_bank`
WHERE 
  `transaksiBankSppuId` = '%s'
";

$sql['do_delete_bp_det'] = "
DELETE FROM  `finansi_pa_transaksi_bank_detil`
WHERE `transaksiBankDetilTransaksiBankId` = (SELECT
  `transaksiBankId`
FROM `finansi_pa_transaksi_bank`
WHERE 
  `transaksiBankSppuId` = '%s')
";
?>