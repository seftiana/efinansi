<?php

/**
 * @package alokasi_penerimaan
 * @since 27 Maret 2012
 * @copyright 2012 Gamatechno
 */ 

$sql['get_count_data'] =" 
	SELECT 
		COUNT(*) as total
	FROM 
		kode_penerimaan_ref
		LEFT JOIN satuan_komponen ON satuan_komponen.satkompId = kode_penerimaan_ref.kodeterimaSatKompId
	WHERE
		(kodeterimaKode LIKE '%s'
		OR kodeterimaNama LIKE '%s')
		AND
				kodeterimaIsAktif ='Y'
";
$sql['get_data']="
	SELECT 
		kodeterimaId as id,
		kodeterimaKode as kode,
		kodeterimaNama as nama,
		kodeterimaTipe as tipe,
		satuan_komponen.satkompNama as satuan
		
	FROM 
		kode_penerimaan_ref
		LEFT JOIN satuan_komponen ON satuan_komponen.satkompId = kode_penerimaan_ref.kodeterimaSatKompId
	WHERE
		(kodeterimaKode LIKE '%s'
		OR kodeterimaNama LIKE '%s')
		AND
		kodeterimaIsAktif ='Y'
        
	ORDER BY kodeterimaKode, kodeterimaTipe DESC
	LIMIT %s, %s
";

?>