<?php

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
   ORDER BY ttNamaTransaksi
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

?>