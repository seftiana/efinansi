<?php
      
$sql['get_count_data'] = 
   "SELECT 
      COUNT(kodeterimaId) AS total 
    FROM
      kode_penerimaan_ref
	WHERE
		(kodeterimaKode LIKE '%s' OR kodeterimaNama LIKE '%s' )
		AND
		kodeterimaIsAktif ='Y'
		AND kode_penerimaan_ref.kodeterimaTipe NOT IN ('header')
   ";   

$sql['get_data_kode']="
SELECT
    kode_penerimaan_ref.kodeterimaId AS id, 
    kode_penerimaan_ref.kodeterimaKode AS kode, 
    kode_penerimaan_ref.kodeterimaNama AS nama,
    kode_penerimaan_ref.kodeterimaTipe AS tipe , 
    coa.coaNamaAkun AS nama_coa, 
    finansi_ref_rkakl_kode_penerimaan.rkaklKodePenerimaanNama AS rkakl_nama, 
    kode_penerimaan_ref.kodeterimaIsAktif AS aktif
FROM
    kode_penerimaan_ref
    LEFT JOIN finansi_coa_map  ON (finansi_coa_map.kodeterimaId = kode_penerimaan_ref.kodeterimaId)
    LEFT JOIN coa ON (coa.coaid = finansi_coa_map.coaId)
    LEFT JOIN finansi_ref_rkakl_kode_penerimaan 
        ON (kode_penerimaan_ref.kodeterimaRKAKLKodePenerimaanId = finansi_ref_rkakl_kode_penerimaan.rkaklKodePenerimaanId)
WHERE
    (kode_penerimaan_ref.kodeterimaKode LIKE %s OR kode_penerimaan_ref.kodeterimaNama LIKE %s)
    AND
	kodeterimaIsAktif ='Y'
	AND kode_penerimaan_ref.kodeterimaTipe NOT IN ('header')
    ORDER BY kode_penerimaan_ref.kodeterimaKode
    LIMIT %s, %s
   ";
?>