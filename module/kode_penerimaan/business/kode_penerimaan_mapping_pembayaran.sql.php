<?php

//===GET===
$sql['get_last_kode_penerimaan_id']=
"SELECT MAX(kodeterimaId) as last_id
      FROM kode_penerimaan_ref";   

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

$sql['do_add'] = "
	INSERT INTO finansi_mapping_coa(
      mpcoaJenisBiayaId,
      mpcoaProdiId,
      mpcoaCoaId
	)VALUES ('%s','%s','%s')
";

$sql['do_update'] = 
   "UPDATE finansi_mapping_coa
   SET       
      mpcoaJenisBiayaId = '%s',
      mpcoaProdiId = '%s',
      mpcoaCoaId = '%s'
   WHERE 
      mpcoaId = '%s'
 ";

$sql['do_delete'] = "
	DELETE FROM finansi_mapping_coa WHERE mpcoaId = %s 
";
      
      
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

//new add cecep 30 juli 2025
$sql['get_data_jenis_pembayaran']="
SELECT
	mpcoaId AS id,
	jenisBiayaId,
	jenisBiayaKode AS kode,
	jenisBiayaNama AS nama,
	1 AS total_child,
	mpcoaCoaId,
	CONCAT(jenjangKode,' ',prodiNamaProdi) AS prodi
FROM finansi_mapping_coa
LEFT JOIN pm_jenis_biaya ON mpcoaJenisBiayaId=jenisBiayaId
LEFT JOIN pm_program_studi_ref ON mpcoaProdiId=prodiId
LEFT JOIN pm_jenjang ON prodiJenjangId=jenjangId
WHERE 1=1
	AND jenisBiayaKode LIKE %s AND jenisBiayaNama LIKE %s
	ORDER BY jenisBiayaNama ASC
    LIMIT %s, %s
";

$sql['get_count'] = 
   "SELECT 
      COUNT(mpcoaId) AS total 
    FROM
      finansi_mapping_coa
	  LEFT JOIN pm_jenis_biaya ON mpcoaJenisBiayaId=jenisBiayaId
   WHERE
     jenisBiayaKode LIKE %s AND jenisBiayaNama LIKE %s
   ";

$sql['get_data_jenis_pembayaran_id'] = "
	SELECT
		mpcoaId AS id,
		jenisBiayaId,
		jenisBiayaKode AS kode,
		jenisBiayaNama AS nama,
		1 AS total_child,
		mpcoaCoaId,
		mpcoaProdiId,
		CONCAT(jenjangKode,' ',prodiNamaProdi) AS prodi,
		prodiKodeProdi
	FROM finansi_mapping_coa
	LEFT JOIN pm_jenis_biaya ON mpcoaJenisBiayaId=jenisBiayaId
	LEFT JOIN pm_program_studi_ref ON mpcoaProdiId=prodiId
	LEFT JOIN pm_jenjang ON prodiJenjangId=jenjangId
	WHERE 1=1
    AND mpcoaId = '%s'
 ";
   
$sql['get_data_jenis_pembayaran_all']=
"
SELECT
	jenisBiayaId AS id,
	jenisBiayaKode AS kode,
	jenisBiayaNama AS nama,
	1 as total_child
FROM
    pm_jenis_biaya
WHERE jenisBiayaId > 0
	ORDER BY jenisBiayaNama ASC
";

?>