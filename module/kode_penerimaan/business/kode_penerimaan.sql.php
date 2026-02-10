<?php

//===GET===
$sql['get_last_kode_penerimaan_id']=
"SELECT MAX(kodeterimaId) as last_id
      FROM kode_penerimaan_ref";
      
$sql['get_count'] = 
   "SELECT 
      COUNT(kodeterimaId) AS total 
    FROM
      kode_penerimaan_ref
   WHERE
     kodeterimaKode LIKE %s AND kodeterimaNama LIKE %s
   ";     

$sql['get_data']=
"
SELECT
    kode_penerimaan_ref.kodeterimaId AS id, 
    kode_penerimaan_ref.kodeterimaKode AS kode, 
    kode_penerimaan_ref.kodeterimaNama AS nama,
    kode_penerimaan_ref.kodeterimaTipe AS tipe , 
    coa.coaNamaAkun AS nama_coa, 
    finansi_ref_rkakl_kode_penerimaan.rkaklKodePenerimaanNama AS rkakl_nama, 
    kode_penerimaan_ref.kodeterimaIsAktif AS aktif, 
    paguBasId AS mak_id, 
    paguBasKode AS mak_kode, 
    paguBasKeterangan AS mak_nama,
    (SELECT COUNT(kpr.`kodeterimaId`) AS total 
		FROM `kode_penerimaan_ref` kpr 
	  WHERE kpr.`kodeterimaParentId` = kode_penerimaan_ref.kodeterimaId) AS total_child
FROM
    kode_penerimaan_ref
    LEFT JOIN finansi_coa_map  ON (finansi_coa_map.kodeterimaId = kode_penerimaan_ref.kodeterimaId)
    LEFT JOIN coa ON (coa.coaid = finansi_coa_map.coaId)
    LEFT JOIN finansi_ref_rkakl_kode_penerimaan 
        ON (kode_penerimaan_ref.kodeterimaRKAKLKodePenerimaanId = finansi_ref_rkakl_kode_penerimaan.rkaklKodePenerimaanId) 
    LEFT JOIN finansi_ref_pagu_bas 
      ON paguBasId = kodeterimaPaguBasId 
WHERE
     kode_penerimaan_ref.kodeterimaKode LIKE %s AND kode_penerimaan_ref.kodeterimaNama LIKE %s
    ORDER BY kode_penerimaan_ref.kodeterimaKode
    LIMIT %s, %s
";

$sql['get_data_by_id'] ="
SELECT
    kp.kodeterimaId AS id, 
    kp.kodeterimaKode AS kode, 
    kp.kodeterimaNama AS nama,
    kp.kodeterimaTipe AS tipe , 
    kp.kodeterimaRKAKLKodePenerimaanId AS kode_rkakl,
    coa.coaNamaAkun AS nama_coa, 
    coa.coaKodeAkun AS kode_coa,
    coa.coaid AS coaid,
    rkakl.rkaklKodePenerimaanNama AS kode_rkakl_nama, 
    kp.kodeterimaIsAktif AS aktif,
    kp.kodeterimaSumberdanaId AS sd_id,
    sd.sumberdanaNama AS sd_nama, 
    paguBasId AS mak_id, 
    CONCAT(paguBasKode, ' - ', paguBasKeterangan) AS mak_nama,
    kp.kodeterimaParentId AS parent_id,
    CONCAT(kpr_p.kodeterimaKode,' - ', kpr_p.`kodeterimaNama`) AS parent_nama
	,kp.kodeterimaJenisPembayaranId
FROM
    kode_penerimaan_ref kp
    LEFT JOIN finansi_coa_map fcp ON (fcp.kodeterimaId = kp.kodeterimaId)
    LEFT JOIN coa ON (coa.coaid = fcp.coaId)
    LEFT JOIN finansi_ref_rkakl_kode_penerimaan  rkakl
        ON (kp.kodeterimaRKAKLKodePenerimaanId = 
      rkakl.rkaklKodePenerimaanId)
    LEFT JOIN finansi_ref_sumber_dana sd ON sd.sumberdanaId =  kp.kodeterimaSumberdanaId 
    LEFT JOIN finansi_ref_pagu_bas 
      ON paguBasId = kp.kodeterimaPaguBasId
    LEFT JOIN kode_penerimaan_ref kpr_p
     ON kpr_p.`kodeterimaId`= kp.`kodeterimaParentId`  
WHERE
     kp.kodeterimaId = '%s'
    LIMIT 1
";
/** 
 * old
 * "SELECT
    kode_penerimaan_ref.kodeterimaId AS id, 
    kode_penerimaan_ref.kodeterimaKode AS kode, 
    kode_penerimaan_ref.kodeterimaNama AS nama,
    kode_penerimaan_ref.kodeterimaTipe AS tipe , 
    kode_penerimaan_ref.kodeterimaRKAKLKodePenerimaanId AS rkakl_id,
    coa.coaNamaAkun AS nama_coa, 
    coa.coaKodeAkun AS kode_coa,
    coa.coaid AS coid,
    finansi_ref_rkakl_kode_penerimaan.rkaklKodePenerimaanNama AS rkakl_nama, 
    kode_penerimaan_ref.kodeterimaIsAktif AS aktif
FROM
    kode_penerimaan_ref
    LEFT JOIN finansi_coa_map  ON (finansi_coa_map.kodeterimaId = kode_penerimaan_ref.kodeterimaId)
    LEFT JOIN coa ON (coa.coaid = finansi_coa_map.coaId)
    LEFT JOIN finansi_ref_rkakl_kode_penerimaan 
        ON (kode_penerimaan_ref.kodeterimaRKAKLKodePenerimaanId = 
      finansi_ref_rkakl_kode_penerimaan.rkaklKodePenerimaanId)
WHERE
     kode_penerimaan_ref.kodeterimaId = %s
    LIMIT 1
";
*/


$sql['get_coa_map']="
SELECT * FROM finansi_coa_map WHERE kodeterimaId = %s
";

$sql['get_count_coa_map']="
SELECT 
	COUNT(coaId) AS total
FROM finansi_coa_map 
	WHERE kodeterimaId = %s
";
   
//===DO===

$sql['do_add'] = 
   "INSERT INTO kode_penerimaan_ref(
      kodeterimaKode,
      kodeterimaNama,
      kodeterimaTipe,
      kodeterimaRKAKLKodePenerimaanId,
      kodeterimaPaguBasId,
      kodeterimaIsAktif,
      kodeterimaSumberdanaId,
      kodeterimaParentId
   )
   VALUES 
      ('%s','%s','%s','%s','%s','%s', '%s', '%s')";

$sql['do_add_coa_map'] = 
   "INSERT INTO finansi_coa_map(
         coaId,
        kodeterimaId
        )
   VALUES 
      ('%s','%s')";

$sql['do_update'] = 
   "UPDATE kode_penerimaan_ref
   SET       
      kodeterimaKode = '%s',
      kodeterimaNama = '%s',
      kodeterimaTipe = '%s',
      kodeterimaRKAKLKodePenerimaanId = '%s',
      kodeterimaPaguBasId = '%s', 
      kodeterimaIsAktif = '%s',
      kodeterimaSumberdanaId = '%s',
      kodeterimaParentId = '%s'
   WHERE 
      kodeterimaId = '%s'
 ";

$sql['do_update_coa_map'] = 
   "UPDATE finansi_coa_map
   SET       
      kodeterimaId = '%s',
      coaId = '%s'
   WHERE 
      kodeterimaId = '%s'
 ";

$sql['do_delete'] = 
   "DELETE FROM kode_penerimaan_ref
   WHERE 
      kodeterimaId = %s ";
      
$sql['do_delete_coa_map'] = 
   "DELETE FROM finansi_coa_map
   WHERE 
      kodeterimaId = %s ";
      
/**
 * added
 * @since 29 Februari 2012
 * Query untuk satuan dari satuan komponen
 */
$sql['get_list_satuan'] = "
	SELECT
		satkompId AS `id`,
		satkompNama AS `name`
	FROM
		satuan_komponen
";
$sql['get_satuan_by_id'] = "
	SELECT
		satuan_komponen.satkompId AS `id`,
		satuan_komponen.satkompNama AS `name`
	FROM
		kode_penerimaan_ref
		LEFT JOIN satuan_komponen ON satuan_komponen.satkompId = kode_penerimaan_ref.kodeterimaSatKompId 
	WHERE
		kode_penerimaan_ref.kodeterimaId = '%s'
";

/* add cecep 30 juli 2025 */
$sql['get_coa_map_by_id_pembayaran'] = "
	SELECT
		kpr.kodeterimaId,
		kpr.kodeterimaKode,
		kpr.kodeterimaNama,
		kpr.kodeterimaIsAktif,
		coa.coaKodeAkun,
		coa.coaNamaAkun,
		CONCAT('[',kpr.kodeterimaKode,'] ',kpr.kodeterimaNama)AS ket_penerimaan,
		CONCAT('[',coa.coaKodeAkun,'] ',coa.coaNamaAkun)AS ket_coa
	FROM kode_penerimaan_ref kpr
	LEFT JOIN finansi_coa_map fcm ON kpr.kodeterimaId = fcm.kodeterimaId
	LEFT JOIN coa ON fcm.coaId = coa.coaId
	WHERE kpr.kodeterimaJenisPembayaranId = '%s'
";
 
/* add cecep 22 januari 2026 */
$sql['get_coa_map_id_pembayaran'] = "
SELECT 
	mapKodeterimaId,
	mapJenisPembayaranId,
	kpr.kodeterimaId,
	kpr.kodeterimaKode,
	kpr.kodeterimaNama,
	kpr.kodeterimaIsAktif,
	coa.coaKodeAkun,
	coa.coaNamaAkun,
	CONCAT('[',kpr.kodeterimaKode,'] ',kpr.kodeterimaNama)AS ket_penerimaan,
	CONCAT('[',coa.coaKodeAkun,'] ',coa.coaNamaAkun)AS ket_coa
FROM finansi_penerimaan_pembayaran_map 
LEFT JOIN kode_penerimaan_ref kpr ON mapKodeterimaId=kpr.kodeterimaId
LEFT JOIN finansi_coa_map fcm ON kpr.kodeterimaId = fcm.kodeterimaId
LEFT JOIN coa ON fcm.coaId = coa.coaId
WHERE mapJenisPembayaranId = '%s'
";

$sql['do_delete_map_pembayaran_penerimaan'] = "
DELETE FROM finansi_penerimaan_pembayaran_map WHERE mapJenisPembayaranId = %s 
";
	  
$sql['do_update_mapping_pembayaran_id'] = "
INSERT INTO finansi_penerimaan_pembayaran_map(
        mapJenisPembayaranId,
        mapKodeterimaId
   )VALUES 
      (%s,%s)
";

?>