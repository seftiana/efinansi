<?php

$sql['get_last_transaksi_id']="
SELECT LAST_INSERT_ID() AS id
";

$sql['get_pembukuan_ref_id']="
SELECT prId AS id
FROM pembukuan_referensi
WHERE prTransId = '%s'
";

$sql['get_last_transaksi_ref']="
SELECT transReferensi AS nomor
FROM transaksi
WHERE transReferensi LIKE 'TM%'
ORDER BY transId DESC
LIMIT 1
";

# DO
/*
$sql['insert_transaksi_old']="
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
**/

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
    transKelompok = '%s',
    transIsDariAplikasiKeuangan = '%s'
";

$sql['insert_pembukuan_ref']="
INSERT INTO pembukuan_referensi
SET
    `prTransId` = '%s',
    `prUserId` =  '%s',
    `prTanggal` = '%s',
    `prKeterangan` = '%s'
";

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
	WHERE tr.`ttKodeTransaksi` = '%s' AND tr.`ttIsAktif` = 'Y' ;
";


$sql["auto_jurnal_coa"] = "
   SELECT 
	   coaKodeAkun coa_kode,
	   ttKeterangan keterangan,
      formCoaDK status_dk
   FROM
      transaksi_tipe_ref 
   LEFT JOIN finansi_ref_form_coa ON formCoaTTId = ttId
   LEFT JOIN coa ON formCoaCoaId = coaId
   WHERE ttKodeTransaksi = '%s' 
";

$sql["get_count_transaksi"] ="
SELECT 
	COUNT(transCatatan ) AS total
FROM 
	transaksi
WHERE 
	transCatatan LIKE '%s'
";

$sql['get_count_coa'] ="
SELECT 
	COUNT(coaId) AS total
FROM coa
	WHERE 
	coaKodeAkun = '%s'
";

/**
 */
 
$sql['get_count_ref_form_coa'] ="
SELECT 
  COUNT(`coa`.`coaId`) AS total
FROM
  `finansi_ref_form_coa` 
  JOIN `transaksi_tipe_ref` ON `transaksi_tipe_ref`.`ttId` = `formCoaTTId` 
  LEFT JOIN `coa` ON `coa`.`coaId` = `finansi_ref_form_coa`.`formCoaCoaId` 
WHERE `transaksi_tipe_ref`.`ttKodeTransaksi` = '%s' 
  AND `transaksi_tipe_ref`.`ttIsAktif` = 'Y' 
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

$sql['get_unit_kerja_id']="
SELECT 
	unitkerjaId as unit_id 
FROM 
	unit_kerja_ref 
WHERE unitkerjaKode = '%s'
";

$sql['get_count_unit_kerja']="
SELECT 
	count(unitkerjaId) as total
FROM 
	unit_kerja_ref 
WHERE unitkerjaKode = '%s'
";

$sql['get_user_id'] = "
SELECT UserId AS user_id FROM gtfw_user WHERE UserName = '%s'
";

?>