<?php

$sql['get_transaksi_by_id'] ="
SELECT 
  transId AS transaksi_id,
  transTanggalEntri AS transaksi_tanggal,
  transReferensi AS transaksi_no_bukti,
  transjenNama AS transaksi_jenis,
  ttNamaTransaksi AS transaksi_tipe,
  transTtId AS transaksi_tipe_id,
  transCatatan AS transaksi_uraian,
  transNilai AS transaksi_nominal,
  transPenanggungJawabNama AS transaksi_penanggung_jawab
FROM
  transaksi tr
  INNER JOIN transaksi_tipe_ref tpr
    ON (tpr.ttId = tr.transTtId) 
  INNER JOIN transaksi_jenis_ref tjr
    ON (tjr.transjenId = tr.`transTransjenId`) 
  INNER JOIN tahun_pembukuan_periode tpp
    ON (tpp.tppId = tr.transTppId) 
WHERE 
tr.transId = '%s'
";

$sql['get_transaksi_jurnal_by_id'] ="
SELECT 
  c.`coaId` AS akun_id,
  c.`coaKodeAkun` AS akun_kode,
  c.`coaNamaAkun` AS akun_nama,
  pdet.`pdStatus` AS akun_dk ,
  pdet.`pdNilai` AS akun_nominal
FROM
  pembukuan_referensi pref
  LEFT JOIN pembukuan_detail pdet ON pdet.`pdPrId` = pref.`prId`
  LEFT JOIN coa c ON c.`coaId` = pdet.`pdCoaId`
WHERE 
pref.`prTransId` = '%s'
ORDER BY akun_dk 
";

$sql['get_transaksi_info']="
SELECT 
	transTanggalEntri AS tanggal,
	transReferensi AS referensi,
	transNilai  AS nominal,
	transCatatan  AS catatan
FROM 
	transaksi 
WHERE transId = '%s'
LIMIT 1
";


# DO
$sql['insert_transaksi']="
INSERT INTO transaksi
SET
	transTtId = (SELECT ttId FROM transaksi_tipe_ref WHERE ttKodeTransaksi = '%s'),
	transTransjenId = 1,
    transUnitkerjaId = '%s',
    transTppId = (SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'),
    transReferensi = '%s',
    transUserId = '%s',
    transThanggarId = (SELECT ta.`thanggarId` FROM tahun_anggaran ta WHERE ta.`thanggarIsAktif` ='Y'),
    transTanggal = DATE(NOW()),
    transTanggalEntri =  '%s',
    transDueDate = '%s',
    transCatatan = '%s',
    transNilai = '%s',
    transPenanggungJawabNama = '%s',
    transIsJurnal = 'Y'
";

$sql['insert_pembukuan_ref']="
INSERT INTO pembukuan_referensi
SET
    `prTransId` = '%s',
    `prUserId` =  '%s',
    `prTanggal` = '%s',
    `prKeterangan` = '%s'
";

$sql['insert_pembukuan_detail_2']="
INSERT INTO `pembukuan_detail`(
	`pdPrId`,
	`pdCoaId`,
	`pdNilai`,
	`pdKeterangan`,
	`pdKeteranganTambahan`,
	`pdStatus`
    )
VALUES     	
";

/**
$sql['insert_pembukuan_detail']="
INSERT INTO `pembukuan_detail`(
	`pdPrId`,
	`pdCoaId`,
	`pdNilai`,
	`pdKeterangan`,
	`pdKeteranganTambahan`,
	`pdStatus`
    )	
	SELECT
		'%s' AS pdPrId,
		`coa`.`coaId` AS pdCoaId,
		'%s' AS pdNilai,
		'%s' AS pdKeterangan,
		'%s' AS pdKeteranganTambahan,
		rc.`formCoaDK` AS pdStatus
	FROM `finansi_ref_form_coa` rc
		JOIN `transaksi_tipe_ref` tr ON tr.`ttId` = rc.`formCoaTTId`
		JOIN `coa` ON  `coa`.`coaId` = rc.`formCoaCoaId`
	WHERE tr.`ttId` = '%s' AND tr.`ttIsAktif` = 'Y' ;
";
*/

$sql['set_status_jurnal_y'] ="
UPDATE `transaksi`
SET  
  `transIsJurnal` = 'Y'
WHERE `transId` = '%s'
";

$sql['delete_pembukuan_detail_by_trans_id']="
DELETE FROM `pembukuan_detail` 
WHERE `pdPrId` IN(SELECT  pr.`prId` FROM `pembukuan_referensi` pr WHERE pr.`prTransId` = '%s');
";

$sql['delete_pembukuan_referensi_by_trans_id']="
DELETE FROM `pembukuan_referensi` WHERE `prTransId` = '%s';
";

$sql['set_status_jurnal_t'] ="
UPDATE `transaksi`
SET  
  `transIsJurnal` = 'T'
WHERE `transId` = '%s'
";

?>