<?php

$sql['get_date_range'] = "
SELECT
   EXTRACT(YEAR FROM IFNULL(MIN(thanggarBuka), DATE_ADD(DATE(NOW()),INTERVAL -DAY(DATE(NOW()))+1 DAY))) AS minYear,
   EXTRACT(YEAR FROM IFNULL(MAX(thanggarTutup), DATE(LAST_DAY(DATE(NOW()))))) AS maxYear,
   IFNULL(MIN(thanggarBuka), DATE_ADD(DATE(NOW()),INTERVAL -DAY(DATE(NOW()))+1 DAY)) AS tanggalAwal,
   IFNULL(MAX(thanggarTutup), DATE(LAST_DAY(DATE(NOW())))) AS tanggalAkhir
FROM tahun_anggaran
WHERE thanggarIsAktif = 'Y'
AND thanggarIsOpen = 'Y'
";

$sql['get_data_detail'] = "
SELECT
   transId AS id,
   tppId,
   tppTanggalAwal,
   tppTanggalAkhir,
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   transReferensi AS nomorReferensi,
   transTanggal AS tanggal,
   transTanggalEntri AS tanggalEntri,
   transDueDate AS dueDate,
   transCatatan AS keterangan,
   transPenanggungJawabNama AS penanggungJawab,
   transPenerimaNama AS penerima,
   transjenId AS transaksiJenisId,
   transjenKode AS transaksiJenisKode,
   transjenNama AS transaksiJenisNama,
   ttId AS transaksiTipeId,
   ttKodeTransaksi AS transaksiTipeKode,
   ttNamaTransaksi AS transaksiTipeNama,
   c.coaId AS coaId,
   c.coaKodeAkun AS coaKode,
   c.coaNamaAkun AS coaNama,
   kegrefNomor AS kode,
   kegrefNama AS nama,
   SUM(pengrealNominalAprove) AS nominalApprove,
   IFNULL(pencairan.realisasiNominal, 0)-transNilai AS nominalPencairan,
   transNilai AS nominal
FROM
   transaksi
   JOIN transaksi_detail_pencairan
      ON transdtpencairanTransId = transId
   JOIN tahun_pembukuan_periode
      ON tppId = transTppId
   JOIN tahun_anggaran
      ON thanggarId = transThanggarId
   JOIN unit_kerja_ref
      ON unitkerjaId = transUnitkerjaId
   JOIN kegiatan_detail
      ON kegdetId = transdtpencairanKegdetId
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN pengajuan_realisasi
      ON pengrealKegdetId = kegdetId
      AND pengrealId = transdtpencairanPengrealId
   JOIN pengajuan_realisasi_detil prd
      ON pengajuan_realisasi.`pengrealId` = prd.pengrealdetPengRealId
   JOIN rencana_pengeluaran rp
      ON rp.rncnpengeluaranId = prd.pengrealdetRncnpengeluaranId
   LEFT JOIN komponen komp 
      ON komp.`kompKode` = rp.`rncnpengeluaranKomponenKode` 
   LEFT JOIN coa c 
      ON c.`coaId` = komp.`kompCoaId` 
   LEFT JOIN transaksi_tipe_ref
      ON ttId = transTtId
   LEFT JOIN transaksi_jenis_ref
      ON transjenId = transTransjenId
   LEFT JOIN (SELECT
      realisasi.kegiatanId AS kegId,
      realisasi.realisasiId AS realId,
      SUM(realisasi.nominal) AS realisasiNominal
   FROM (SELECT
      kegdetId AS kegiatanId,
      pengrealId AS realisasiId,
      SUM(IFNULL(transNilai,0)) AS nominal
   FROM transaksi
   JOIN transaksi_detail_anggaran
      ON transdtanggarTransId = transId
   JOIN kegiatan_detail
      ON kegdetId = transdtanggarKegdetId
   JOIN pengajuan_realisasi
      ON pengrealKegdetId = kegdetId
      AND pengrealid = transdtanggarPengrealId
   GROUP BY kegdetId, pengrealId
   UNION
   SELECT
      kegdetId AS kegiatanId,
      pengrealId AS realisasiId,
      SUM(IFNULL(transNilai,0)) AS nominal
   FROM transaksi
   JOIN transaksi_detail_pencairan
      ON transdtpencairanTransId = transid
   JOIN kegiatan_detail
      ON kegdetId = transdtpencairanKegdetId
   JOIN pengajuan_realisasi
      ON pengrealKegdetId = kegdetId
      AND pengrealid = transdtpencairanPengrealId
   GROUP BY kegdetId, pengrealId
   UNION
   SELECT
      kegdetId AS kegiatanId,
      pengrealId AS realisasiId,
      SUM(IFNULL(transNilai,0)) AS nominal
   FROM transaksi
   JOIN transaksi_detail_pengembalian
      ON transdtpengembalianTransId = transId
   JOIN kegiatan_detail
      ON kegdetid = transdtpengembalianKegdetId
   JOIN pengajuan_realisasi
      ON pengrealKegdetId = kegdetId
      AND pengrealid = transdtpengembalianPengrealId
   GROUP BY kegdetId, pengrealId
   UNION
   SELECT
      kegdetId AS kegiatanId,
      pengrealId AS realisasiId,
      SUM(IFNULL(transNilai,0)) AS nominal
   FROM transaksi
   JOIN transaksi_detail_realisasi
      ON transdtrealisasiTransId = transId
   JOIN kegiatan_detail
      ON kegdetid = transdtrealisasiKegdetId
   JOIN pengajuan_realisasi
      ON pengrealKegdetId = kegdetId
   GROUP BY kegdetId, pengrealId
   UNION
   SELECT
      kegdetId AS kegiatanId,
      pengrealId AS realisasiId,
      SUM(IFNULL(transNilai,0)) AS nominal
   FROM transaksi
   JOIN transaksi_detail_spj
      ON transdtspjTransId = transid
   JOIN kegiatan_detail
      ON kegdetId = transdtspjKegdetId
   JOIN pengajuan_realisasi
      ON pengrealKegdetId = kegdetId
   GROUP BY kegdetId, pengrealId) AS realisasi
   GROUP BY kegiatanId, realisasiId) AS pencairan ON pencairan.kegId = kegdetId
   AND pencairan.realId = pengrealId
WHERE transId = %s
LIMIT 1
";

$sql['cek_transaksi'] = "
   SELECT
      COUNT(*) as total
   FROM
      transaksi
   WHERE
      TRIM(transReferensi)=TRIM('%s')
";

//KOMBO
$sql['get_combo_jenis_transaksi'] = "
   SELECT
      transjenId as `id`,
      transjenNama as `name`
   FROM
      transaksi_jenis_ref
   WHERE
      transjenNama NOT IN ('Payroll','Aset','Registrasi','Penyusutan Aset','Pengadaan','Beban ATK','Pengendalian', 'Pengelolaan Aset')
   ORDER BY transjenNama
";

$sql['get_combo_tipe_transaksi'] = "
   SELECT
      ttId as `id`,
      ttNamaTransaksi as `name`,
      (SELECT userunitkerjaRoleId FROM gtfw_user JOIN user_unit_kerja ON userunitkerjaUserId = UserId WHERE UserName = %s) as roleId
   FROM
      transaksi_tipe_ref
   HAVING
      roleId = 1 OR
      (roleId = 1 AND ttId != 5) OR
      (roleId != 1 AND ttId !=4)
   ORDER BY ttId ASC
";
//bwt edit data start
$sql['get_transaksi_by_id'] = "
   SELECT
      transId as id,
		(if(tempUnitId IS NULL,unitkerjaId,unitkerjaId)) AS unitkerja,
		(if(tempUnitNama IS NULL,unitkerjaNama,CONCAT_WS('/ ',tempUnitNama, unitkerjaNama))) AS unitkerja_label,
      transTransjenId as jenis,
      transTtId as tipe,
      transReferensi as no_kkb,
      transTanggalEntri as tanggal,
      transDueDate as due_date,
      transCatatan as catatan_transaksi,
      transNilai as nominal,
      transPenanggungJawabNama as penanggung_jawab,
      transPenerimaNama as penerima,
      transIsJurnal as is_jurnal
   FROM
      transaksi
      JOIN unit_kerja_ref ON (unitkerjaId = transUnitkerjaId)
      LEFT JOIN (
        SELECT
            unitkerjaId AS tempUnitId,
            unitkerjaKode AS tempUnitKode,
            unitkerjaNama AS tempUnitNama,
            unitkerjaParentId AS tempParentId
	FROM 
            unit_kerja_ref 
        WHERE unitkerjaParentId = 0) tmpUnitKerja ON (unitkerjaParentId=tempUnitId)
   WHERE
      transId='%s'
";
$sql['get_transaksi_file'] = "
   SELECT
      transfileId as id,
      transfileNama as `nama`,
      transfilePath as `path`
   FROM
      transaksi_file
   WHERE
      transfileTransId='%s'
";

$sql['get_transaksi_invoice'] = "
   SELECT
      transinvoiceId as id,
      transinvoiceNomor as nomor
   FROM
      transaksi_invoice
   WHERE
      transInvoiceTransId='%s'
";

$sql['get_transaksi_mak'] = "
   SELECT
      transdtanggarId as id,
      CONCAT_WS('|', transdtanggarKegdetId, transdtanggarPengrealId) as kode,
      kegrefNama as nama
   FROM
      transaksi_detail_anggaran
      JOIN kegiatan_detail ON (kegdetId = transdtanggarKegdetId)
      JOIN kegiatan_ref ON (kegrefId = kegdetKegrefId)
   WHERE
      transdtanggarTransId='%s'
";

$sql['get_transaksi_mak_untuk_pencairan'] = "
   SELECT
      transdtpencairanId as id,
      CONCAT_WS('|', transdtpencairanKegdetId, transdtpencairanPengrealId) as kode,
      kegrefNama as nama
   FROM
      transaksi_detail_pencairan
      JOIN kegiatan_detail ON (kegdetId = transdtpencairanKegdetId)
      JOIN kegiatan_ref ON (kegrefId = kegdetKegrefId)
   WHERE
      transdtpencairanTransId='%s'
";
//bwt edit data end

$sql['do_add_transaksi'] = "
   INSERT INTO transaksi(
      transTtId,
      transTransjenId,
      transUnitkerjaId,
      transThanggarId,
      transReferensi,
      transUserId,
      transTanggal,
      transTanggalEntri,
      transDueDate,
      transCatatan,
      transNilai,
      transPenanggungJawabNama,
      transIsJurnal
   ) VALUES (
      '%s',/*transTtId*/
      '%s',/*transTransjenId*/
      '%s',/*transUnitkerjaId*/
      (SELECT thanggarId FROM tahun_anggaran WHERE thanggarIsAktif = 'Y' LIMIT 1),
      '%s',/*transReferensi*/
      '%s',/*transUserId*/
      DATE(NOW()),/*transTanggal*/
      '%s',/*transTanggalEntri*/
      '%s',/*transDueDate*/
      '%s',/*transCatatan*/
      '%s',/*transNilai*/
      '%s',/*transPenanggungJawabNama*/
      '%s'/*transIsJurnal*/
   )
";

$sql['do_add_transaksi_file'] = "
   INSERT INTO transaksi_file(
      transfileTransId,
      transfileNama,
      transfilePath
   ) VALUES
      %s
";

$sql['do_add_transaksi_invoice'] = "
   INSERT INTO transaksi_invoice(
      transinvoiceTransId,
      transinvoiceNomor
   ) VALUES
      %s
";

$sql['do_add_transaksi_detil_pengembalian_anggaran'] = "
   INSERT INTO transaksi_detail_pengembalian(
      transdtpengembalianTransId,
      transdtpengembalianKegdetId,
      transdtpengembalianPengrealId
   ) VALUES (
      %s,
      %s,
      %s
   )
";

$sql['do_add_transaksi_detil_anggaran'] = "
   INSERT INTO transaksi_detail_anggaran(
      transdtanggarTransId,
      transdtanggarKegdetId,
      transdtanggarPengrealId
   ) VALUES (
      %s,
      %s,
      %s
   )
";

/**
 * tambahan update detail anggaran
 */
$sql['do_update_transaksi_detil_anggaran'] = "
	UPDATE
   		transaksi_detail_anggaran
    SET
      	transdtanggarKegdetId = '%s',
      	transdtanggarPengrealId = '%s'
 	WHERE
 		 transdtanggarTransId = '%s'
";

/**
 * end
 */
$sql['do_add_transaksi_detil_anggaran_penerimaan'] = "
   INSERT INTO transaksi_detail_anggaran
      (transdtanggarId, transdtanggarTransId, transdtanggarPenerimaanId)
   VALUES
      ('', %s,  %s)
";

//tambahan untuk insert transaksi_detail pencaian
$sql['do_add_transaksi_detil_pencairan'] = "
   INSERT INTO transaksi_detail_pencairan
      (transdtpencairanTransId, transdtpencairanKegdetId, transdtpencairanPengrealId)
   VALUES
      (%s, %s, %s)
";
/**
  tambahan update detail pencairan
 */
$sql['do_update_transaksi_detil_pencairan'] = "
	UPDATE
   		transaksi_detail_pencairan
    SET
      	transdtpencairanKegdetId = '%s',
      	transdtpencairanPengrealId = '%s'
 	WHERE
 		transdtpencairanTransId = '%s'
";
/**
 * end
 */
$sql['do_add_pembukuan'] = "
   INSERT INTO pembukuan_referensi(
      prTransId,
      prUserId,
      prTanggal,
      prKeterangan,
      prIsPosting,
      prDelIsLocked
   ) VALUES (
      '%s',
      '%s',
      DATE(NOW()),
      ' ',
      'T',
      'T'
   )
";

$sql['do_add_pembukuan_detil_debet'] = "
   INSERT INTO pembukuan_detail(
      pdPrId,
      pdCoaId,
      pdNilai,
      pdKeterangan,
      pdStatus
   ) SELECT
      %s,
      sknrdDebetCoaId,
      sknrdProsen * %s/100,
      ' ',
      'D'
     FROM
      sekenario_detail
     WHERE
      sknrdSknrId IN ('%s') AND sknrdKreditCoaId IS NULL
";

$sql['do_add_pembukuan_detil_kredit'] = "
   INSERT INTO pembukuan_detail(
      pdPrId,
      pdCoaId,
      pdNilai,
      pdKeterangan,
      pdStatus
   ) SELECT
      %s,
      sknrdKreditCoaId,
      sknrdProsen*%s/100,
      ' ',
      'K'
     FROM
      sekenario_detail
     WHERE
      sknrdSknrId IN ('%s') AND sknrdDebetCoaId IS NULL
";

//MULAI EDIT DATA
$sql['cek_transaksi_update'] = "
   SELECT
      COUNT(*) as total
   FROM
      transaksi
   WHERE
      TRIM(transReferensi)=TRIM('%s')
      AND transId != '%s'
";
$sql['do_update_transaksi'] = "
   UPDATE
      transaksi
   SET
      transTtId='%s',
      transTransjenId='%s',
      transUnitkerjaId='%s',
      transReferensi='%s',
      transUserId='%s',
      transTanggal=DATE(NOW()),
      transTanggalEntri='%s',
      transDueDate='%s',
      transCatatan='%s',
      transNilai='%s',
      transPenanggungJawabNama='%s',
      transPenerimaNama='%s',
      transIsJurnal='%s'
   WHERE
      transId=%s
";
$sql['do_delete_transaksi_invoice'] = "
   DELETE FROM transaksi_invoice WHERE transinvoiceId IN ('%s')
";
$sql['do_delete_transaksi_file'] = "
   DELETE FROM transaksi_file WHERE transfileId IN ('%s')
";
//SELESAI EDIT DATA
//MULAI DELETE DATA
$sql['do_delete_data_by_array_id'] = "
   DELETE FROM transaksi WHERE transId IN ('%s')
";

$sql['do_delete_data_by_id'] = "
   DELETE FROM transaksi WHERE transId='%s'
";


$sql['get_combo_tahun_anggaran'] = "
   SELECT
      thanggarId as id,
      thanggarNama as name
   FROM
      tahun_anggaran
   ORDER BY thanggarNama
";

$sql['get_combo_tahun_anggaran_aktif'] = "
   SELECT
      thanggarId as `aktif`
   FROM
      tahun_anggaran
   WHERE thanggarIsAktif='Y'
   LIMIT 1
";

$sql['get_data_form_cetak'] = "
   SELECT
      transId as id,
      transReferensi as nomor_bukti,
      transCatatan as untuk_pembayaran,
      transNilai as nilai
   FROM
      transaksi
   WHERE
      transId='%s'
";

//LOGGER LOGGER LOGGER

$sql['do_add_log'] = "
   INSERT INTO logger(logUserId, logAlamatIp, logUpdateTerakhir, logKeterangan)
   VALUES ('%s', '%s', NOW(), '%s')
";

$sql['do_add_log_detil'] = "
   INSERT INTO logger_detail(logId, logAksiQuery)
   VALUES ('%s', '%s')
";

#tambahan update status untuk pengajua realisasi yang sudah di transaksi kan
$sql['update_status_transaksi_di_pengajuan_realisasi'] = "
   UPDATE
      pengajuan_realisasi
   SET
      pengrealIsTransaksi = '1'
   WHERE
      pengrealId = '%s'
";

#tambaha untuk cetak bukti transaksi
$sql['get_jabatan'] = "
   SELECT
      pjbNama AS id,
      pjbJabatanNama AS name
   FROM
      pejabat_ref
   WHERE
      pjbJenisPejabat like '%s'
";

$sql['get_nama_pejabat'] = "
   SELECT
      pjbNama AS nama
   FROM
      pejabat_ref
   WHERE
      pjbJabatanNama = '%s'
";

#tambahan untuk count nomer bkk bkm dan bm
$sql['count_bukti'] = "
   SELECT
      transReferensi
   FROM
      transaksi
   WHERE
      transReferensi LIKE %s
      AND EXTRACT(MONTH FROM transTanggalEntri) = %s
      AND transUnitkerjaId = %s
";

$sql['get_last_insert_trans_id'] = "
	SELECT
		MAX(LAST_INSERT_ID(transId)) AS lastTransId
	FROM
		transaksi
";

$sql['get_nominal_sisa_disetujui'] = "
SELECT
(`pengajuan_realisasi`.`pengrealNominalAprove` -
IFNULL(
      (
         SELECT
            SUM(tr.transNilai)
         FROM transaksi tr
         LEFT JOIN transaksi_detail_pencairan tdp ON tr.transId = tdp.transdtpencairanTransId
         WHERE (tdp.transdtpencairanKegdetId = `kegiatan_detail`.`kegdetId`
			AND tdp.transdtpencairanPengrealId = `pengajuan_realisasi`.`pengrealId`)
      ), 0) + `transaksi`.`transNilai` )AS nominal_sisa_disetujui

FROM
	`transaksi`
	LEFT JOIN  `transaksi_detail_pencairan` ON
		`transaksi_detail_pencairan`.`transdtpencairanTransId` = `transaksi`.`transId`
	LEFT JOIN  `kegiatan_detail` ON
		`kegiatan_detail`.`kegdetId` = `transaksi_detail_pencairan`.`transdtpencairanKegdetId`
	LEFT JOIN `kegiatan_ref` ON
		`kegiatan_ref`.`kegrefId` = `kegiatan_detail`.`kegdetKegrefId`
	LEFT JOIN `pengajuan_realisasi` ON
		`pengajuan_realisasi`.`pengrealId` = `transaksi_detail_pencairan`.`transdtpencairanPengrealId`
WHERE
	`transaksi`.`transId` = %s
HAVING (nominal_sisa_disetujui) > 0
";

$sql['get_komponen_anggaran_by_trans_id'] = "
SELECT
  peng_real.`pengrealId` AS pId, 
  rpeng.`rncnpengeluaranKegdetId` AS kegdetId,  
  peng_real_det.`pengrealdetId` AS pdId,
  rpeng.`rncnpengeluaranKomponenKode` AS kompKode,
  rpeng.`rncnpengeluaranKomponenNama` AS kompNama,
  peng_real_det.`pengrealdetDeskripsi` AS deskripsi,
  c.`coaId` AS coaId,
  c.`coaKodeAkun` AS coaKode,
 tdp_komp.`transdtpencairanKompBelanjaNominal` AS nominal
FROM 
pengajuan_realisasi_detil peng_real_det 
JOIN rencana_pengeluaran rpeng 
  ON rpeng.`rncnpengeluaranId` = peng_real_det.`pengrealdetRncnpengeluaranId` 
JOIN pengajuan_realisasi peng_real
  ON peng_real.`pengrealId` = peng_real_det.`pengrealdetPengRealId`
JOIN komponen komp 
  ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode` 
LEFT JOIN coa c 
  ON c.`coaId` = komp.`kompCoaId` 
JOIN kegiatan_detail kd
  ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`  
JOIN kegiatan k
  ON k.`kegId` = kd.`kegdetId`  
JOIN `transaksi_detail_pencairan` tdp
  ON tdp.`transdtpencairanKegdetId` = kd.`kegdetId` AND tdp.`transdtpencairanPengrealId` = peng_real.`pengrealId`
JOIN transaksi tr
  ON tr.`transId`  = tdp.`transdtpencairanTransId` AND tr.`transThanggarId` = k.`kegThanggarId`
JOIN `transaksi_detail_pencairan_komponen_belanja` tdp_komp
  ON tdp_komp.`transdtpencairanKompBelanjaTransDtPencairanId` = tdp.`transdtpencairanId`
   AND tdp_komp.`transdtpencairanKompBelanjaPengrealDetId` = peng_real_det.`pengrealdetId`  
WHERE
tr.`transId` = '%s'
ORDER BY peng_real.`pengrealId` ASC
";

$sql['delete_transaksi_detail_pencairan_by_trans_id'] = "
DELETE FROM `transaksi_detail_pencairan`
WHERE `transdtpencairanTransId` = '%s'
";

$sql['delete_transaksi_detail_anggaran_by_trans_id'] = "
DELETE FROM `transaksi_detail_anggaran` 
WHERE `transdtanggarTransId` = '%s'
";


$sql['get_rows_transaksi_detail_anggaran'] = "
SELECT 
  COUNT(`transdtanggarId`) AS total
FROM
    `transaksi_detail_anggaran`
WHERE `transdtanggarTransId` = '%s'
";

$sql['get_rows_transaksi_detail_pencairan'] = "
SELECT 
  COUNT(`transdtpencairanId`) AS total
FROM
  `transaksi_detail_pencairan` 
WHERE `transdtpencairanTransId` = '%s'
";

$sql['delete_komponen_anggaran_by_trans_id'] = "
DELETE FROM  `transaksi_detail_pencairan_komponen_belanja` 
WHERE `transdtpencairanKompBelanjaTransDtPencairanId` IN 
  (SELECT  `transdtpencairanId` FROM `transaksi_detail_pencairan` WHERE `transdtpencairanTransId` = '%s')
";

$sql['get_rows_komponen_anggaran_by_trans_id'] = "
SELECT 
  COUNT(`transdtpencairanKompBelanjaId`) AS total
FROM
  `transaksi_detail_pencairan_komponen_belanja` 
WHERE `transdtpencairanKompBelanjaTransDtPencairanId` IN 
  (SELECT 
    `transdtpencairanId` FROM `transaksi_detail_pencairan`
  WHERE `transdtpencairanTransId` = '%s')
";

$sql['do_insert_transaksi_det_anggaran'] = "
INSERT INTO transaksi_detail_anggaran
SET 
   `transdtanggarTransId`   = '%s',
   `transdtanggarKegdetId`  = '%s',
   `transdtanggarPengrealId`= '%s'
";

$sql['do_insert_transaksi_det_pencairan'] = "
INSERT INTO transaksi_detail_pencairan
SET transdtpencairanTransId = '%s',
   transdtpencairanKegdetId = '%s',
   transdtpencairanPengrealId = '%s'
";

$sql['do_insert_transaksi_det_pencairan_komp_belanja'] = "
INSERT INTO `transaksi_detail_pencairan_komponen_belanja`
SET 
  `transdtpencairanKompBelanjaTransDtPencairanId` = '%s',
  `transdtpencairanKompBelanjaPengrealDetId` = '%s',
  `transdtpencairanKompBelanjaNominal` = '%s'
";
