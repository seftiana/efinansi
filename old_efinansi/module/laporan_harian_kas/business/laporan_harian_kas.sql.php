<?php
/**
 * @package SQL-FILE
 */

$sql['get_data_laporan_kas']     = "
SELECT
      SQL_CALC_FOUND_ROWS
      transId AS id,
      transTanggalEntri AS transTanggal,
      transReferensi AS nomorReferensi,
      transjenNama AS jenis,
      ttNamaTransaksi AS tipe,
      transTtId AS ttId,
      transTransjenId AS transJenis,
      transCatatan AS transCatatan,
      pr.`pengrealKeterangan` AS uraianFpa,
   IF(transTtId = 4 AND transTransjenId = 5, SUM(transNilai), 0) AS nominalKredit,
   IF(transTtId = 1 AND transTransjenId = 9, SUM(tkd.`transaksiKasDetilNominal`), 0) AS nominalDebet,
      transIsJurnal AS isJurnal
   FROM 
      transaksi
   LEFT JOIN transaksi_tipe_ref ON (ttId = transTtId)
   LEFT JOIN transaksi_jenis_ref ON (transjenId = transTransjenId)
      
   LEFT JOIN transaksi_detail_penerimaan_kas AS trPenerimaan
   ON trPenerimaan.`transdtPenerimaanKasTransId` = transId
   LEFT JOIN finansi_pa_transaksi_kas AS tk
   ON tk.`transaksiKasId` = trPenerimaan.`transdtPenerimaanKasTKasId`
   LEFT JOIN finansi_pa_transaksi_kas_detil AS tkd
   ON tkd.`transaksiKasDetilTransaksiKasId` = tk.`transaksiKasId`
   LEFT JOIN finansi_pa_sppu_det AS sppuDet
   ON sppuDet.`sppuDetId` = tkd.`transaksiKasDetilSppuDetId`
   LEFT JOIN pengajuan_realisasi_detil AS prd
   ON prd.`pengrealdetId` = sppuDet.`sppuDetPengrealDetId`
   LEFT JOIN pengajuan_realisasi AS pr
   ON pr.`pengrealId` = prd.`pengrealdetPengRealId`
   
   LEFT JOIN (
       SELECT
         tdp.transdtpencairanTransId AS maktransId,
         tdp.transdtpencairanKegdetId AS kode,
         tdp.transdtpencairanId AS id,
         kr.kegrefNama AS nama
      FROM
         transaksi_detail_pencairan tdp
         JOIN kegiatan_detail kd ON (kd.kegdetId = tdp.transdtpencairanKegdetId)
         JOIN kegiatan_ref kr ON (kr.kegrefId = kd.kegdetKegrefId)
      ) mk ON  mk.maktransId = transId
   WHERE
   (transTtId = 4 AND transTransjenId = 5) 
   AND transTanggalEntri BETWEEN '%s' AND '%s' AND (transNilai > 500000)
   OR 
   (transTtId = 1 AND transTransjenId = 9)
   AND transTanggalEntri BETWEEN '%s' AND '%s'

GROUP BY pr.`pengrealId`, transId
ORDER BY transReferensi ASC
LIMIT %s, %s
";

$sql['get_data_laporan_kas_export']     = "
SELECT
      SQL_CALC_FOUND_ROWS
      transId AS id,
      transTanggalEntri AS transTanggal,
      transReferensi AS nomorReferensi,
      transjenNama AS jenis,
      ttNamaTransaksi AS tipe,
      transTtId AS ttId,
      transTransjenId AS transJenis,
      transCatatan AS transCatatan,
      pr.`pengrealKeterangan` AS uraianFpa,
   IF(transTtId = 4 AND transTransjenId = 5, SUM(transNilai), 0) AS nominalKredit,
   IF(transTtId = 1 AND transTransjenId = 9, SUM(tkd.`transaksiKasDetilNominal`), 0) AS nominalDebet,
      transIsJurnal AS isJurnal
   FROM 
      transaksi
   LEFT JOIN transaksi_tipe_ref ON (ttId = transTtId)
   LEFT JOIN transaksi_jenis_ref ON (transjenId = transTransjenId)
      
   LEFT JOIN transaksi_detail_penerimaan_kas AS trPenerimaan
   ON trPenerimaan.`transdtPenerimaanKasTransId` = transId
   LEFT JOIN finansi_pa_transaksi_kas AS tk
   ON tk.`transaksiKasId` = trPenerimaan.`transdtPenerimaanKasTKasId`
   LEFT JOIN finansi_pa_transaksi_kas_detil AS tkd
   ON tkd.`transaksiKasDetilTransaksiKasId` = tk.`transaksiKasId`
   LEFT JOIN finansi_pa_sppu_det AS sppuDet
   ON sppuDet.`sppuDetId` = tkd.`transaksiKasDetilSppuDetId`
   LEFT JOIN pengajuan_realisasi_detil AS prd
   ON prd.`pengrealdetId` = sppuDet.`sppuDetPengrealDetId`
   LEFT JOIN pengajuan_realisasi AS pr
   ON pr.`pengrealId` = prd.`pengrealdetPengRealId`
   
   LEFT JOIN (
       SELECT
         tdp.transdtpencairanTransId AS maktransId,
         tdp.transdtpencairanKegdetId AS kode,
         tdp.transdtpencairanId AS id,
         kr.kegrefNama AS nama
      FROM
         transaksi_detail_pencairan tdp
         JOIN kegiatan_detail kd ON (kd.kegdetId = tdp.transdtpencairanKegdetId)
         JOIN kegiatan_ref kr ON (kr.kegrefId = kd.kegdetKegrefId)
      ) mk ON  mk.maktransId = transId
   WHERE
   (transTtId = 4 AND transTransjenId = 5) 
   AND transTanggalEntri BETWEEN '%s' AND '%s' AND (transNilai > 500000)
   OR 
   (transTtId = 1 AND transTransjenId = 9)
   AND transTanggalEntri BETWEEN '%s' AND '%s'

GROUP BY pr.`pengrealId`, transId
ORDER BY transReferensi ASC
";

$sql['get_saldo_awal']  ="
SELECT
   SUM(tpSaldoAkhir) AS saldo_awal
FROM coa
LEFT JOIN tahun_pembukuan ON tpCoaId = coaId
WHERE coaId = 6 /** Kas Besar */
GROUP BY coaId
";

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";
?>