<?php

$sql['get_sql_nomor_jurnal_penyusutan'] = "
   SELECT formulaFormula FROM finansi_ref_formula WHERE formulaCode = 'NO_JURNAL_PENYUSUTAN' AND formulaIsAktif = 'Y' LIMIT 1;
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
      transjenNama NOT IN ('Payroll','Aset','Registrasi')
   ORDER BY transjenNama
";

$sql['get_combo_tipe_transaksi'] = "
   SELECT
      ttId as `id`,
      ttNamaTransaksi as `name`
   FROM
      transaksi_tipe_ref
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
      transIsJurnal as is_jurnal
   FROM
      transaksi
      JOIN unit_kerja_ref ON (unitkerjaId = transUnitkerjaId)
      LEFT JOIN
         (SELECT
            unitkerjaId AS tempUnitId,
            unitkerjaKode AS tempUnitKode,
            unitkerjaNama AS tempUnitNama,
            unitkerjaParentId AS tempParentId
         FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUnitKerja ON(unitkerjaParentId=tempUnitId)
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

//get user unit_kerja
$sql['get_user_unit_kerja'] = "
   SELECT
      userunitkerjaUnitkerjaId AS unit_kerja
   FROM
      user_unit_kerja
   WHERE
      userunitkerjaUserId = %s
";

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
      '%s',/*transTtId deffault nya pengeluaran */
      '%s',/*transTransjenId untuk jenis trans = Penyusutan Aset*/
      '%s',/*transUnitkerjaId*/
      (SELECT thanggarId FROM tahun_anggaran WHERE thanggarIsAktif = 'y'),/*thn anggar id*/
      '%s',/*transReferensi diisi no BA / berita acara */
      '%s',/*transUserId*/
      DATE(NOW()),/*transTanggal*/
      '%s',/*transTanggalEntri*/
      '%s',/*transDueDate*/
      '%s',/*transCatatan*/
      '%s',/*transNilai*/
      '%s',/*transPenanggungJawabNama*/
      '%s'
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

$sql['do_add_transaksi_detil_anggaran_penerimaan'] = "
   INSERT INTO transaksi_detail_anggaran
      (transdtanggarId, transdtanggarTransId, transdtanggarPenerimaanId)
   VALUES
      ('', %s,  %s)
";

//tambahan untuk insert transaksi_detail penyusutan
$sql['do_add_transaksi_detil_penyusutan'] = "
   INSERT INTO transaksi_detail_penyusutan
      (transdtsusutTransId, transdtsusutIdBarang, transdtsusutKib, transdtsusutNama, transdtsusutTanggalPenyusutan, transdtsusutTanggalPeriodePenyusutan, transdtsusutNominalPenyusutan)
   VALUES
      ('%s', '%s', '%s', '%s', '%s', '%s', '%s')
";

$sql['do_add_transaksi_detil_penyusutan_gedung'] = "
   INSERT INTO transaksi_detail_penyusutan
      (transdtsusutTransId, transdtsusutIdGedung, transdtsusutKib, transdtsusutNama, transdtsusutTanggalPenyusutan, transdtsusutTanggalPeriodePenyusutan, transdtsusutNominalPenyusutan)
   VALUES
      ('%s', '%s', '%s', '%s', '%s', '%s', '%s')
";

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
      AND transId<>'%s'
";
$sql['do_update_transaksi'] = "
   UPDATE
      transaksi
   SET
      transUnitkerjaId='%s',
      transReferensi='%s',
      transUserId='%s',
      transTanggal=DATE(NOW()),
      transTanggalEntri='%s',
      transDueDate='%s',
      transCatatan='%s',
      transNilai='%s',
      transPenanggungJawabNama='%s',
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
?>