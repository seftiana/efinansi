<?php 

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
	AND (mpcoaProdiId = '%s' OR 'all' = '%s')
	AND (mpcoaCoaId = '%s' OR 'all' = '%s')
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
	 AND (mpcoaProdiId = '%s' OR 'all' = '%s')
	 AND (mpcoaCoaId = '%s' OR 'all' = '%s')
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
   
$sql['get_data_jenis_pembayaran_all']="
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

$sql['get_prodi']="
SELECT
	prodiId AS id,
	prodiId AS kode,
	CONCAT(jenjangKode,' ',prodiNamaProdi) AS name
FROM
    pm_program_studi_ref
	LEFT JOIN pm_jenjang ON prodiJenjangId=jenjangId
WHERE 1=1
ORDER BY jenjangKode,prodiNamaProdi ASC
";

?>