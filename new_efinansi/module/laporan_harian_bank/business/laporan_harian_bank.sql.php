<?php
/**
 * @package SQL-FILE
 */
$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_laporan']      = "
SELECT SQL_CALC_FOUND_ROWS 
   transaksiBankId AS id,
   trTerima.transId AS trTerimaId, 
   trKeluar.transId AS trKeluarId,
   pr.pengrealId AS pengrealId,
   transaksiBankNomor AS nomorBukti,
   transaksiBankBpkb AS bankBpkb,
   transaksiBankTanggal tanggal,
   transaksiBankSppuId AS sppuId,
   transaksiBankTipe AS bankTipe,
   transaksiBankPenerima AS bankPenerima,
   transaksiBankTujuan AS bankTujuan,
   sppu.sppuBankPayment AS sppuBp,
   sppu.sppuCashPayment AS sppuCr,
   trKeluar.transCatatan AS uraianPengeluaran,
   trTerima.transCatatan AS uraianPenerimaan,
   trKeluar.transIsJurnal AS jurnalPengeluaran,
   trTerima.transIsJurnal AS jurnalPenerimaan,
   pr.pengrealKeterangan AS uraianFpa,

   IF(UPPER(transaksiBankTipe) = 'PENERIMAAN', 'DEBET', 'KREDIT') AS `type`,
     CASE
       WHEN UPPER(transaksiBankTipe) = 'PENERIMAAN' THEN IF(trBankDetil.`nominalDetil` = 0, transaksiBankNominal, transaksiBankNominal)
       WHEN UPPER(transaksiBankTipe) = 'PENGELUARAN' THEN 0
     END AS nominalDebet,
     CASE
       WHEN UPPER(transaksiBankTipe) = 'PENERIMAAN' THEN 0
       WHEN UPPER(transaksiBankTipe) = 'PENGELUARAN' THEN IFNULL(pr.`pengrealNominalAprove`, transaksiBankNominal)
     END AS nominalKredit
FROM finansi_pa_transaksi_bank AS trBank

LEFT JOIN (
   SELECT
      transksaiBankDetilId AS id,
      transaksiBankDetilTransaksiBankId AS idBank,
      transaksiBankDetilNominal AS nominalDetil
   FROM finansi_pa_transaksi_bank_detil
   GROUP BY id) AS trBankDetil
ON trBankDetil.`idBank` = trBank.`transaksiBankId`

LEFT JOIN transaksi_detail_penerimaan_bank AS trdTerima
ON trdTerima.`transdtPenerimaanBankTBankId` = trBank.`transaksiBankId`
LEFT JOIN transaksi_detail_pengeluaran_bank AS trdKeluar
ON trdKeluar.`transdtPengeluaranBankTBankId` = trBank.`transaksiBankId`
LEFT JOIN transaksi AS trTerima
ON trTerima.`transId` = trdTerima.`transdtPenerimaanBankTransId`
LEFT JOIN transaksi AS trKeluar
ON trKeluar.`transId` = trdKeluar.`transdtPengeluaranBankTransId`
LEFT JOIN finansi_pa_sppu AS sppu
ON sppu.`sppuId` = trBank.`transaksiBankSppuId`
LEFT JOIN finansi_pa_sppu_det AS sppuDet
ON sppuDet.`sppuDetSppuId` = sppu.`sppuId`
LEFT JOIN pengajuan_realisasi_detil AS prd
ON prd.`pengrealdetId` = sppuDet.`sppuDetPengrealDetId`
LEFT JOIN pengajuan_realisasi AS pr
ON pr.`pengrealId` = prd.`pengrealdetPengRealId`
LEFT JOIN rencana_pengeluaran AS rp
ON rp.`rncnpengeluaranId` = prd.`pengrealdetRncnpengeluaranId`
LEFT JOIN kegiatan_detail AS kd
ON kd.`kegdetId` = rp.`rncnpengeluaranKegdetId`

WHERE 1 = 1
AND transaksiBankTanggal BETWEEN '%s' AND '%s'
%s
AND transaksiBankNomor LIKE '%s'
GROUP BY trTerima.transId, trKeluar.transId, pr.pengrealId
ORDER BY transaksiBankBpkb ASC
LIMIT %s, %s
";

$sql['get_data_laporan_excel']      = "
SELECT SQL_CALC_FOUND_ROWS 
   transaksiBankId AS id,
   trTerima.transId AS trTerimaId, 
   trKeluar.transId AS trKeluarId,
   pr.pengrealId AS pengrealId,
   transaksiBankNomor AS nomorBukti,
   transaksiBankBpkb AS bankBpkb,
   transaksiBankTanggal tanggal,
   transaksiBankSppuId AS sppuId,
   transaksiBankTipe AS bankTipe,
   transaksiBankPenerima AS bankPenerima,
   transaksiBankTujuan AS bankTujuan,
   trKeluar.transCatatan AS uraianPengeluaran,
   trTerima.transCatatan AS uraianPenerimaan,
   trKeluar.transIsJurnal AS jurnalPengeluaran,
   trTerima.transIsJurnal AS jurnalPenerimaan,
   pr.pengrealKeterangan AS uraianFpa,
   
   IF(UPPER(transaksiBankTipe) = 'PENERIMAAN', 'DEBET', 'KREDIT') AS `type`,
     CASE
       WHEN UPPER(transaksiBankTipe) = 'PENERIMAAN' THEN IF(trBankDetil.`nominalDetil` = 0, transaksiBankNominal, transaksiBankNominal)
       WHEN UPPER(transaksiBankTipe) = 'PENGELUARAN' THEN 0
     END AS nominalDebet,
     CASE
       WHEN UPPER(transaksiBankTipe) = 'PENERIMAAN' THEN 0
       WHEN UPPER(transaksiBankTipe) = 'PENGELUARAN' THEN IFNULL(pr.`pengrealNominalAprove`, transaksiBankNominal)
     END AS nominalKredit
FROM finansi_pa_transaksi_bank AS trBank

LEFT JOIN (
   SELECT
      transksaiBankDetilId AS id,
      transaksiBankDetilTransaksiBankId AS idBank,
      transaksiBankDetilNominal AS nominalDetil
   FROM finansi_pa_transaksi_bank_detil
   GROUP BY id) AS trBankDetil
ON trBankDetil.`idBank` = trBank.`transaksiBankId`

LEFT JOIN transaksi_detail_penerimaan_bank AS trdTerima
ON trdTerima.`transdtPenerimaanBankTBankId` = trBank.`transaksiBankId`
LEFT JOIN transaksi_detail_pengeluaran_bank AS trdKeluar
ON trdKeluar.`transdtPengeluaranBankTBankId` = trBank.`transaksiBankId`
LEFT JOIN transaksi AS trTerima
ON trTerima.`transId` = trdTerima.`transdtPenerimaanBankTransId`
LEFT JOIN transaksi AS trKeluar
ON trKeluar.`transId` = trdKeluar.`transdtPengeluaranBankTransId`
LEFT JOIN finansi_pa_sppu AS sppu
ON sppu.`sppuId` = trBank.`transaksiBankSppuId`
LEFT JOIN finansi_pa_sppu_det AS sppuDet
ON sppuDet.`sppuDetSppuId` = sppu.`sppuId`
LEFT JOIN pengajuan_realisasi_detil AS prd
ON prd.`pengrealdetId` = sppuDet.`sppuDetPengrealDetId`
LEFT JOIN pengajuan_realisasi AS pr
ON pr.`pengrealId` = prd.`pengrealdetPengRealId`
LEFT JOIN rencana_pengeluaran AS rp
ON rp.`rncnpengeluaranId` = prd.`pengrealdetRncnpengeluaranId`
LEFT JOIN kegiatan_detail AS kd
ON kd.`kegdetId` = rp.`rncnpengeluaranKegdetId`

WHERE 1 = 1
AND transaksiBankTanggal BETWEEN '%s' AND '%s'
AND transaksiBankNomor LIKE '%s'
GROUP BY trTerima.transId, trKeluar.transId, pr.pengrealId
ORDER BY transaksiBankBpkb ASC
";

$sql['get_data_laporan_excel_filter_bank']      = "
SELECT SQL_CALC_FOUND_ROWS 
   transaksiBankId AS id,
   trTerima.transId AS trTerimaId, 
   trKeluar.transId AS trKeluarId,
   pr.pengrealId AS pengrealId,
   transaksiBankNomor AS nomorBukti,
   transaksiBankBpkb AS bankBpkb,
   transaksiBankTanggal tanggal,
   transaksiBankSppuId AS sppuId,
   transaksiBankTipe AS bankTipe,
   transaksiBankPenerima AS bankPenerima,
   transaksiBankTujuan AS bankTujuan,
   trKeluar.transCatatan AS uraianPengeluaran,
   trTerima.transCatatan AS uraianPenerimaan,
   trKeluar.transIsJurnal AS jurnalPengeluaran,
   trTerima.transIsJurnal AS jurnalPenerimaan,
   pr.pengrealKeterangan AS uraianFpa,
   
   IF(UPPER(transaksiBankTipe) = 'PENERIMAAN', 'DEBET', 'KREDIT') AS `type`,
     CASE
       WHEN UPPER(transaksiBankTipe) = 'PENERIMAAN' THEN IF(trBankDetil.`nominalDetil` = 0, transaksiBankNominal, transaksiBankNominal)
       WHEN UPPER(transaksiBankTipe) = 'PENGELUARAN' THEN 0
     END AS nominalDebet,
     CASE
       WHEN UPPER(transaksiBankTipe) = 'PENERIMAAN' THEN 0
       WHEN UPPER(transaksiBankTipe) = 'PENGELUARAN' THEN IFNULL(pr.`pengrealNominalAprove`, transaksiBankNominal)
     END AS nominalKredit
FROM finansi_pa_transaksi_bank AS trBank

LEFT JOIN (
   SELECT
      transksaiBankDetilId AS id,
      transaksiBankDetilTransaksiBankId AS idBank,
      transaksiBankDetilNominal AS nominalDetil
   FROM finansi_pa_transaksi_bank_detil
   GROUP BY id) AS trBankDetil
ON trBankDetil.`idBank` = trBank.`transaksiBankId`

LEFT JOIN transaksi_detail_penerimaan_bank AS trdTerima
ON trdTerima.`transdtPenerimaanBankTBankId` = trBank.`transaksiBankId`
LEFT JOIN transaksi_detail_pengeluaran_bank AS trdKeluar
ON trdKeluar.`transdtPengeluaranBankTBankId` = trBank.`transaksiBankId`
LEFT JOIN transaksi AS trTerima
ON trTerima.`transId` = trdTerima.`transdtPenerimaanBankTransId`
LEFT JOIN transaksi AS trKeluar
ON trKeluar.`transId` = trdKeluar.`transdtPengeluaranBankTransId`
LEFT JOIN finansi_pa_sppu AS sppu
ON sppu.`sppuId` = trBank.`transaksiBankSppuId`
LEFT JOIN finansi_pa_sppu_det AS sppuDet
ON sppuDet.`sppuDetSppuId` = sppu.`sppuId`
LEFT JOIN pengajuan_realisasi_detil AS prd
ON prd.`pengrealdetId` = sppuDet.`sppuDetPengrealDetId`
LEFT JOIN pengajuan_realisasi AS pr
ON pr.`pengrealId` = prd.`pengrealdetPengRealId`
LEFT JOIN rencana_pengeluaran AS rp
ON rp.`rncnpengeluaranId` = prd.`pengrealdetRncnpengeluaranId`
LEFT JOIN kegiatan_detail AS kd
ON kd.`kegdetId` = rp.`rncnpengeluaranKegdetId`

WHERE 1 = 1
AND transaksiBankTanggal BETWEEN '%s' AND '%s'
AND (transaksiBankPenerima LIKE '%s' OR transaksiBankTujuan LIKE '%s')
AND transaksiBankNomor LIKE '%s'
GROUP BY trTerima.transId, trKeluar.transId, pr.pengrealId
ORDER BY transaksiBankBpkb ASC
";
?>