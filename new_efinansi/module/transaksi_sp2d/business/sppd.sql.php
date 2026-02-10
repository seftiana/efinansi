<?php
$sql['get_data_transaksi']    = "
SELECT 
   SQL_CALC_FOUND_ROWS DISTINCT spmNomor, 
   spmNama, 
   spmNPWP, 
   spmNominal, 
   transReferensi, 
   transNilai, 
   transThanggarId, 
   `thanggarNama`, 
   `jenisPembayaranKode`, 
   `jenisPembayaranNama`, 
   `sifatPembayaranKode`, 
   `sifatPembayaranNama`, 
   `caraBayarKode`, 
   `caraBayarNama`, 
   paguBasKode, 
   paguBasKeterangan, 
   transTanggal, 
   transPenanggungJawabNama, 
   transId, 
   pengrealId
FROM 
   finansi_pa_spm 
   LEFT JOIN finansi_pa_spm_det 
      ON spmId = spmDetSpmId
   LEFT JOIN pengajuan_realisasi_detil 
      ON pengrealdetId = spmDetRealDetId 
   LEFT JOIN pengajuan_realisasi 
      ON pengrealId = pengrealdetPengRealId 
   LEFT JOIN transaksi_detail_anggaran 
      ON transdtanggarPengrealId = pengrealId 
   LEFT JOIN transaksi 
      ON transId = transdtanggarTransId 
   LEFT JOIN `tahun_anggaran` 
      ON `transThanggarId` = `thanggarId` 
   LEFT JOIN `finansi_pa_ref_sifat_pembayaran` 
      ON `sifatPembayaranId` = `spmSifatBayarId` 
   LEFT JOIN `finansi_pa_ref_jenis_pembayaran` 
      ON `jenisPembayaranId` = `spmJenisBayarId` 
   LEFT JOIN `finansi_pa_ref_cara_bayar` 
      ON `caraBayarId` = `spmCaraBayarId` 
   LEFT JOIN finansi_ref_pagu_bas 
      ON paguBasId = spmPaguBasId
WHERE transReferensi IS NOT NULL 
AND `transTanggal` BETWEEN '%s' AND '%s' 
AND transReferensi LIKE '%s' 
LIMIT %s, %s
";

$sql['count_data']      = "
SELECT FOUND_ROWS() AS total
";

$sql['get_transaksi_by_id']   = "
SELECT 
   SQL_CALC_FOUND_ROWS DISTINCT spmNomor, 
   `spmId`, 
   spmNama, 
   spmNPWP, 
   spmNominal, 
   transReferensi, 
   transNilai, 
   transThanggarId, 
   `thanggarNama`, 
   `jenisPembayaranKode`, 
   `jenisPembayaranNama`, 
   `sifatPembayaranKode`, 
   `sifatPembayaranNama`, 
   `caraBayarKode`, 
   `caraBayarNama`, 
   paguBasKode, 
   paguBasKeterangan, 
   `transId`, 
   spmTanggal, 
   spmRekening, 
   spmBank,
   pengrealId AS realId, 
   (SELECT CONCAT(IF(rm.paguBasParentId = 0, rm.paguBasKode, bas.paguBasKode),' ', IF(rm.paguBasParentId = 0,rm.paguBasKeterangan,bas.paguBasKeterangan)) AS bas_nama
FROM
   pengajuan_realisasi AS pengreal 
   LEFT JOIN pengajuan_realisasi_detil AS rd 
      ON pengreal.`pengrealId` = rd.`pengrealdetPengRealId` 
   JOIN rencana_pengeluaran AS rp 
      ON rp.rncnpengeluaranId = rd.pengrealdetRncnpengeluaranId 
   LEFT JOIN finansi_ref_pagu_bas AS rm 
      ON rm.paguBasId = rp.rncnpengeluaranMakId 
   LEFT JOIN finansi_ref_pagu_bas AS bas 
      ON bas.paguBasId = rm.paguBasParentId 
WHERE pengreal.`pengrealId` = realId 
AND rm.`paguBasKode` IS NOT NULL 
LIMIT 0, 1) AS bas_kode, 
   unitkerjaKode, 
   unitkerjaNama
FROM 
   finansi_pa_spm 
   LEFT JOIN finansi_pa_spm_det 
      ON spmId = spmDetSpmId
   LEFT JOIN pengajuan_realisasi_detil 
      ON pengrealdetId = spmDetRealDetId 
   LEFT JOIN pengajuan_realisasi 
      ON pengrealId = pengrealdetPengRealId 
   LEFT JOIN transaksi_detail_anggaran 
      ON transdtanggarPengrealId = pengrealId 
   LEFT JOIN transaksi 
      ON transId = transdtanggarTransId 
   LEFT JOIN `tahun_anggaran` 
      ON `transThanggarId` = `thanggarId` 
   LEFT JOIN `finansi_pa_ref_sifat_pembayaran` 
      ON `sifatPembayaranId` = `spmSifatBayarId` 
   LEFT JOIN `finansi_pa_ref_jenis_pembayaran` 
      ON `jenisPembayaranId` = `spmJenisBayarId` 
   LEFT JOIN `finansi_pa_ref_cara_bayar` 
      ON `caraBayarId` = `spmCaraBayarId` 
   LEFT JOIN finansi_ref_pagu_bas 
      ON paguBasId = spmPaguBasId 
   LEFT JOIN unit_kerja_ref 
      ON transUnitkerjaId = unitkerjaId
WHERE transId = '%s' 
LIMIT 0, 1
"; 

$sql['insert_sp2d']     = "
INSERT INTO `finansi_pa_sppd`
SET `sppdSpmId` = '%s',
   `sppdTransId` = '%s',
   `sppdNomor` = '%s',
   `sppdKepada` = '%s', 
   `sppdNpwp` = '%s',
   `sppdNoRekening` = '%s',
   `sppdBank` = '%s',
   `sppdKeterangan` = '%s',
   `sppdNominal` = '%s',
   `sppdThAnggar` = '%s',
   `sppdTanggal` = '%s',
   `sppdUserId` = '%s'
";

$sql['update_sp2d']  = "
UPDATE `finansi_pa_sppd`
SET `sppdNomor` = '%s', 
   `sppdKepada` = '%s',
   `sppdNpwp` = '%s',
   `sppdNoRekening` = '%s',
   `sppdBank` = '%s',
   `sppdKeterangan` = '%s', 
   `sppdNominal` = '%s'
WHERE `sppdId` = '%s'
";

$sql['get_last_insert_id']    = "
SELECT LAST_INSERT_ID() AS last_id
";

$sql['generate_nomor_formula'] = "
SELECT
   `formulaFormula`
FROM `finansi_ref_formula`
WHERE formulaCode = 'CREATE_SP2D_NOMOR' 
AND formulaIsAktif = 'Y'
";

$sql['get_data_sppd_cetak']   = "
SELECT
   `sppdId`,
   `sppdSpmId`,
   `sppdTransId`,
   `sppdNomor`,
   `sppdKepada`,
   `sppdNpwp`,
   `sppdNoRekening`,
   `sppdBank`,
   `sppdKeterangan`,
   `sppdNominal`,
   `sppdThAnggar`,
   `sppdTanggal`,
   `sppdTglUbah`,
   `sppdUserId`, 
   data.spmNomor, 
   data.`spmId`, 
   data.spmNama, 
   data.spmNPWP, 
   data.spmNominal, 
   data.transReferensi, 
   data.transNilai, 
   data.transThanggarId, 
   data.`thanggarNama`, 
   data.`jenisPembayaranKode`, 
   data.`jenisPembayaranNama`, 
   data.`sifatPembayaranKode`, 
   data.`sifatPembayaranNama`, 
   data.`caraBayarKode`, 
   data.`caraBayarNama`, 
   data.paguBasKode, 
   data.paguBasKeterangan, 
   data.`transId`, 
   data.spmTanggal, 
   data.spmRekening, 
   data.spmBank,
   data.realId, 
   data.bas_kode, 
   data.unitkerjaKode, 
   data.unitkerjaNama
FROM `finansi_pa_sppd` 
LEFT JOIN (SELECT DISTINCT spmNomor, 
   `spmId`, 
   spmNama, 
   spmNPWP, 
   spmNominal, 
   transReferensi, 
   transNilai, 
   transThanggarId, 
   `thanggarNama`, 
   `jenisPembayaranKode`, 
   `jenisPembayaranNama`, 
   `sifatPembayaranKode`, 
   `sifatPembayaranNama`, 
   `caraBayarKode`, 
   `caraBayarNama`, 
   paguBasKode, 
   paguBasKeterangan, 
   `transId`, 
   spmTanggal, 
   spmRekening, 
   spmBank,
   pengrealId AS realId, 
   (SELECT CONCAT(IF(rm.paguBasParentId = 0, rm.paguBasKode, bas.paguBasKode),' ', IF(rm.paguBasParentId = 0,rm.paguBasKeterangan,bas.paguBasKeterangan)) AS bas_nama
FROM
   pengajuan_realisasi AS pengreal 
   LEFT JOIN pengajuan_realisasi_detil AS rd 
      ON pengreal.`pengrealId` = rd.`pengrealdetPengRealId` 
   JOIN rencana_pengeluaran AS rp 
      ON rp.rncnpengeluaranId = rd.pengrealdetRncnpengeluaranId 
   LEFT JOIN finansi_ref_pagu_bas AS rm 
      ON rm.paguBasId = rp.rncnpengeluaranMakId 
   LEFT JOIN finansi_ref_pagu_bas AS bas 
      ON bas.paguBasId = rm.paguBasParentId 
WHERE pengreal.`pengrealId` = realId 
AND rm.`paguBasKode` IS NOT NULL 
LIMIT 0, 1) AS bas_kode,  
   unitkerjaKode, 
   unitkerjaNama
FROM 
   finansi_pa_spm 
   LEFT JOIN finansi_pa_spm_det 
      ON spmId = spmDetSpmId
   LEFT JOIN pengajuan_realisasi_detil 
      ON pengrealdetId = spmDetRealDetId 
   LEFT JOIN pengajuan_realisasi 
      ON pengrealId = pengrealdetPengRealId 
   LEFT JOIN transaksi_detail_anggaran 
      ON transdtanggarPengrealId = pengrealId 
   LEFT JOIN transaksi 
      ON transId = transdtanggarTransId 
   LEFT JOIN `tahun_anggaran` 
      ON `transThanggarId` = `thanggarId` 
   LEFT JOIN `finansi_pa_ref_sifat_pembayaran` 
      ON `sifatPembayaranId` = `spmSifatBayarId` 
   LEFT JOIN `finansi_pa_ref_jenis_pembayaran` 
      ON `jenisPembayaranId` = `spmJenisBayarId` 
   LEFT JOIN `finansi_pa_ref_cara_bayar` 
      ON `caraBayarId` = `spmCaraBayarId` 
   LEFT JOIN finansi_ref_pagu_bas 
      ON paguBasId = spmPaguBasId 
   LEFT JOIN unit_kerja_ref 
      ON transUnitkerjaId = unitkerjaId ) AS `data` ON data.transId = sppdTransId
WHERE sppdId = '%s'
";
?>
