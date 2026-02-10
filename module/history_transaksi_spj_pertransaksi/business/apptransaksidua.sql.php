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
      ref_transaksi.ref AS ref_transaksi,
      transPenanggungJawabNama as penanggung_jawab,
      transIsJurnal as is_jurnal
   FROM
      transaksi
      LEFT JOIN unit_kerja_ref ON (unitkerjaId = transUnitkerjaId)
      LEFT JOIN finansi_transaksi_ref_transaksi ON (transaksiTranskasiId = transId)
		LEFT JOIN
			(SELECT
				unitkerjaId AS tempUnitId,
				unitkerjaKode AS tempUnitKode,
				unitkerjaNama AS tempUnitNama,
				unitkerjaParentId AS tempParentId
			FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUnitKerja ON(unitkerjaParentId=tempUnitId)
		LEFT JOIN (
			SELECT 
				tr.transId AS id,
				tr.transReferensi AS ref
			FROM 
				transaksi tr
      		) ref_transaksi ON ref_transaksi.id = transaksiTransaksiRefId
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
   INSERT INTO
      `transaksi`
   SET
      `transTtId` = '%s',
      `transTransjenId` = '%s',
      `transUnitkerjaId` = '%s',
      `transTppId` = (SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y' LIMIT 1),
      `transThanggarId` = (SELECT thanggarId FROM tahun_anggaran WHERE thanggarIsAktif = 'Y' LIMIT 1),
      `transReferensi` = '%s',
      `transUserId` = '%s',
      `transTanggal` = DATE(NOW()),
      `transTanggalEntri` = '%s',
      `transDueDate` = '%s',
      `transCatatan` = '%s',
      `transNilai` = '%s',
      `transPenanggungJawabNama` = '%s',
      `transIsJurnal` = '%s'

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

$sql['do_add_pembukuan'] = "
   INSERT INTO
      `pembukuan_referensi`
   SET
      `prTransId` = '%s',
      `prUserId` = '%s',
      `prTanggal` = '%s',
      `prKeterangan` = '%s'
";

$sql['do_add_pembukuan_detil']= "
   INSERT INTO
      `pembukuan_detail`
   SET
      `pdPrId` = '%s',
      `pdCoaId` = '%s',
      `pdNilai` = '%s',
      `pdKeterangan` = '%s',
      `pdKeteranganTambahan` = '%s',
      `pdStatus` = '%s'
";

$sql['do_add_pembukuan_referensi']="
   INSERT INTO `pembukuan_referensi` 
      ( `prTransId`, `prUserId`, `prTanggal`, `prKeterangan`, `prIsPosting`, `prDelIsLocked`, `prIsApproved` )       
   VALUES 
      ( '%s',  '%s',  DATE(NOW()),  '%s',  'T',  'T',  'T' )
";

$sql['do_add_pembukuan_detail']="
   INSERT INTO `pembukuan_detail` 
      ( `pdPrId`, `pdCoaId`, `pdNilai`, `pdKeterangan`, `pdStatus` ) 
   VALUES
      ( '%s',  '%s',  '%s',  '%s',  '%s' )
";

$sql['update_status_is_jurnal'] = "
UPDATE
   transaksi
SET
   transIsJurnal = 'Y'
WHERE
   transId = %s
";

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
      transTanggal= DATE(NOW()),
      transTanggalEntri= '%s',
      transDueDate='%s',
      transCatatan='%s',
      transNilai='%s',
      transPenanggungJawabNama='%s',
      transIsJurnal='%s'
   WHERE
      transId=%s
";

$sql['do_delete_transaksi_file'] = "
   DELETE FROM transaksi_file WHERE transfileId IN ('%s')
";
//SELESAI EDIT DATA
//MULAI DELETE DATA
$sql['do_delete_data_by_array_id'] = "
   DELETE FROM transaksi WHERE transId IN ('%s')
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


$sql['get_tahun_pembukuan_periode'] = "
	SELECT
	  tppId
	FROM tahun_pembukuan_periode
	WHERE tppIsBukaBuku = 'Y'
	LIMIT 1
";

$sql['insert_invoice'] = "
   INSERT INTO
      `transaksi_invoice`
   SET
      `transinvoiceNomor` = '%s',
      `transinvoiceTransId` = '%s',
      `transiinvoiceInvTransId` = '%s'
";

/**
 * mendapatkan transId terakhir
 */
$sql['get_last_insert_trans_id']="
	SELECT 
		MAX(LAST_INSERT_ID(transId)) AS lastTransId 
	FROM 
		transaksi
";

/**
 * mendapatkan data jurna
 */
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
ORDER BY 
	`pembukuan_detail`.`pdId` DESC
";

$sql['do_add_ref_transaksi']="
INSERT INTO finansi_transaksi_ref_transaksi(transaksiTranskasiId, transaksiTransaksiRefId)
   VALUES ('%s', '%s')
";