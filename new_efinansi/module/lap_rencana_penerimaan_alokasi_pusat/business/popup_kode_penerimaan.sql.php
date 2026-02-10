<?php

/**
 * 
 * popup_kode_penerimaan.sql
 * @since 11 November 2012
 * @analyst nanang_ruswianto<nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
$sql['get_count_data'] =" 
	SELECT 
		COUNT(kodeterimaId) as total
	FROM 
		kode_penerimaan_ref
	WHERE
		kodeterimaKode LIKE '%s' 
		AND 
		kodeterimaNama LIKE '%s' 
		AND
		kodeterimaIsAktif ='Y'
";
$sql['get_data']="
	SELECT 
		kodeterimaId as id,
		kodeterimaKode as kode,
		kodeterimaNama as nama,
		kodeterimaTipe as tipe
		
	FROM 
		kode_penerimaan_ref
	WHERE
		kodeterimaKode LIKE '%s' 
		AND 
		kodeterimaNama LIKE '%s' 
		AND
		kodeterimaIsAktif ='Y'
	ORDER BY kodeterimaKode, kodeterimaTipe DESC
	LIMIT %s, %s
";