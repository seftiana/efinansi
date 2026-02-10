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
$sql['get_tipe_transaksi'] = "
   SELECT
      ttId as `id`,
      ttNamaTransaksi as `name`,
      (SELECT userunitkerjaRoleId FROM gtfw_user JOIN user_unit_kerja ON userunitkerjaUserId = UserId WHERE UserName = %s) as roleId
   FROM
      transaksi_tipe_ref 
	WHERE ttNamaTransaksi = '%s'
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

$sql['do_add_pembukuan'] = "
   INSERT INTO pembukuan_referensi(
      prTransId, 
      prUserId,
      prTanggal,
      prKeterangan,
      prIsPosting,
      prDelIsLocked
   )
   SELECT
      transId,
      '%s',
      DATE(NOW()),
      transReferensi,
      'T',
      'T'
   FROM
      transaksi
   WHERE
      transId = %s
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

$sql['do_add_pembukuan_detil_jurnal_kode'] = "
   INSERT INTO pembukuan_detail(
      pdPrId, 
      pdCoaId,
      pdNilai,
      pdStatus
   )
   VALUES (%s, %s, %s, %s)
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
      pjbJabatanNama like '%s'
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


$sql['get_last_insert_trans_id']="
	SELECT 
		MAX(LAST_INSERT_ID(transId)) AS lastTransId 
	FROM 
		transaksi
";

$sql['data_jurnal']="
SELECT
`coa`.`coaKodeAkun` as kode_akun,
`coa`.`coaNamaAkun` as nama_akun,
IF(`pembukuan_detail`.`pdStatus` ='D',`pembukuan_detail`.`pdNilai`,0) AS debet,
IF(`pembukuan_detail`.`pdStatus` ='K', `pembukuan_detail`.`pdNilai`,0) AS kredit
FROM 
	`pembukuan_detail`
	LEFT JOIN `pembukuan_referensi` ON `pembukuan_referensi`.`prId` = `pembukuan_detail`.`pdPrId` 
	LEFT JOIN `coa` ON `coa`.`coaId` = `pembukuan_detail`.`pdCoaId`
	LEFT JOIN `transaksi` ON `transaksi`.`transId` = `pembukuan_referensi`.`prTransId`
WHERE
	`transaksi`.`transId` = %s
";