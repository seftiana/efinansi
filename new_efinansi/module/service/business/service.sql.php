<?php

/**
 * get alokasi anggaran
 *
 */
$sql['get_alokasi_penerimaan']="
SELECT 
  uk.`unitkerjaKode` AS unit_kerja_sumber_id,
  IFNULL(kpr_p_p.`kodeterimaKode`,kpr_p.`kodeterimaKode`) AS header_kp_kode,
  IFNULL(kpr_p_p.`kodeterimaNama` ,kpr_p.`kodeterimaNama`)AS header_kp_nama,
  kpr_p.`kodeterimaId` AS kode_penerimaan_id,
  kpr_p.`kodeterimaKode` AS kode_penerimaan,
  GROUP_CONCAT(
    uk_al.`unitkerjaKode`
  ) AS unit_kerja_penerima_id,
  GROUP_CONCAT(
    al.`penerimaAlokasiUnitNilaiAlokasi`
  ) AS alokasi_nilai 
FROM
  `finansi_pa_penerima_alokasi_unit` al 
  LEFT JOIN finansi_pa_kode_penerimaan_ref_unit_alokasi kp_a 
    ON kp_a.`penerimaanUnitAlokasiId` = al.`penerimaAlokasiUnitAlokasiId` 
  LEFT JOIN kode_penerimaan_ref kpr_p 
    ON kpr_p.`kodeterimaId` = kp_a.`penerimaanUnitAlokasiIdKdPenRef` 
  LEFT JOIN unit_kerja_ref uk
    ON uk.`unitkerjaId`= kp_a.`penerimaanUnitAlokasiIdUnitKerja`
  LEFT JOIN unit_kerja_ref uk_al
    ON uk_al.`unitkerjaId`=al.`penerimaAlokasiUnitUnitKerjaId`
  LEFT JOIN kode_penerimaan_ref kpr_p_p
    ON kpr_p_p.`kodeterimaId` = kpr_p.`kodeterimaParentId`  
GROUP BY kode_penerimaan_id,
  penerimaanUnitAlokasiIdUnitKerja 
ORDER BY unit_kerja_sumber_id,
  kode_penerimaan 
";

/**
 * end alokasi anggaran
 */

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
$sql['insert_transaksi']="
INSERT INTO transaksi
SET
	transTtId = (SELECT ttId FROM transaksi_tipe_ref WHERE ttKodeTransaksi = '%s'),
	transTransjenId = 1,
    transUnitkerjaId = '%s',
    transTppId = (SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'),
    transReferensi = '%s',
    transUserId = (SELECT UserId FROM gtfw_user WHERE UserName = 'service'),
    transThanggarId = (SELECT ta.`thanggarId` FROM tahun_anggaran ta WHERE ta.`thanggarIsAktif` ='Y'),
    transTanggal = DATE(NOW()),
    transTanggalEntri =  '%s',
    transDueDate = '%s',
    transCatatan = '%s',
    transNilai = '%s',
    transPenanggungJawabNama = '%s',
    transIsJurnal = 'Y'
";
/**
$sql['insert_transaksi_old']="
INSERT INTO transaksi
SET
	transTtId = (SELECT ttId FROM transaksi_tipe_ref WHERE ttKodeTransaksi = '%s'),
	transTransjenId = 1,
    transUnitkerjaId = (SELECT unitkerjaId FROM unit_kerja_ref WHERE unitkerjaKode = '%s'),
    transTppId = (SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'),
    transReferensi = (SELECT IFNULL((SELECT CONCAT('TM',LPAD((RIGHT(LEFT(b.transReferensi,8),6)+1),6,'0'),CONCAT('-','%s')) FROM transaksi b WHERE b.transReferensi LIKE 'TM%%' ORDER BY b.transId DESC LIMIT 1),CONCAT('TM000001-','%s'))),
    transUserId = (SELECT UserId FROM gtfw_user WHERE UserName = 'service'),
    transTanggal = DATE(NOW()),
    transTanggalEntri =  '%s',
    transDueDate = '%s',
    transCatatan = '%s',
    transNilai = '%s',
    transPenanggungJawabNama = '%s',
    transIsJurnal = 'Y'
";
*/

$sql['insert_pembukuan_ref']="
INSERT INTO pembukuan_referensi
SET
    `prTransId` = '%s',
    `prUserId` = (SELECT UserId FROM gtfw_user WHERE UserName = 'service'),
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
/**
$sql['insert_pembukuan_detil_old2']="
INSERT INTO `pembukuan_detail`
SET 
   `pdPrId` = '%s',
   `pdCoaId` = (SELECT coaId FROM coa WHERE coaKodeAkun = '%s'),
   `pdKeterangan` = '%s',
   `pdStatus` = '%s',
   `pdNilai` = '%s',
   `pdSubaccPertamaKode` = '%s',
   `pdSubaccKeduaKode` = '%s',
   `pdSubaccKetigaKode` = '%s',
   `pdSubaccKeempatKode` = '%s',
   `pdSubaccKelimaKode` = '%s',
   `pdSubaccKeenamKode` = '%s',
   `pdSubaccKetujuhKode` = '%s'
";

$sql['insert_pembukuan_detil_old']="
INSERT INTO pembukuan_detail
            (`pdPrId`,
             `pdCoaId`,
             `pdNilai`,
             `pdKeterangan`,
             `pdStatus`,
             `pdSubaccPertamaKode`,
             `pdSubaccKeduaKode`,
             `pdSubaccKetigaKode`,
             `pdSubaccKeempatKode`,
             `pdSubaccKelimaKode`,
             `pdSubaccKeenamKode`,
             `pdSubaccKetujuhKode`)
VALUES %s
";
*/
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

$sql['get_count_unit_kerja_id']="
SELECT 
	count(unitkerjaId) as total
FROM 
	unit_kerja_ref 
WHERE unitkerjaKode = '%s'
";
?>