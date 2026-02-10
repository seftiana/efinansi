<?php

/**
 * @package lap_rencana_penerimaan_alokasi_unit
 * @since 24 Februari 2012
 * @copyright (c) 2012 Gamatechno
 */
 
$sql['get_count_data'] = "
SELECT 
	count(`kode_penerimaan_ref`.`kodeterimaKode`) AS total
	
FROM 
	`rencana_penerimaan`
	LEFT JOIN `kode_penerimaan_ref` 
		ON `kode_penerimaan_ref`.`kodeterimaId` = `rencana_penerimaan`.`renterimaKodeterimaId`
	LEFT JOIN `unit_kerja_ref` 
		ON `unit_kerja_ref`.`unitkerjaId` = `rencana_penerimaan`.`renterimaUnitkerjaId`
WHERE renterimaRpstatusId = 2 AND
(unit_kerja_ref.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	unit_kerja_ref.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)	   
	AND 
	`rencana_penerimaan`.`renterimaThanggarId` = '%s'
	AND 
	(`rencana_penerimaan`.`renterimaKodeterimaId` = '%s' OR %s)
";

$sql['get_data_rencana_penerimaan'] = "
SELECT 
	`kode_penerimaan_ref`.`kodeterimaKode` AS kode_penerimaan,
	`unit_kerja_ref`.`unitkerjaId` AS unit_kerja_id,
	`unit_kerja_ref`.`unitkerjaNama` AS unit_kerja_nama,
	`unit_kerja_ref`.`unitkerjaKode` AS unit_kerja_kode,
	`kode_penerimaan_ref`.`kodeterimaNama` AS kode_penerimaan_nama,
	IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlJan`), 
	`rencana_penerimaan`.`renterimaJmlJan`) AS januari,
	IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlFeb`),
	`rencana_penerimaan`.`renterimaJmlFeb`) AS februari,
	IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlMar`),
	`rencana_penerimaan`.`renterimaJmlMar`) AS maret,
	IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlApr`),
	`rencana_penerimaan`.`renterimaJmlApr`) AS april,
	IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlMei`),
	`rencana_penerimaan`.`renterimaJmlMei`) AS mei,
	IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlJun`),
	`rencana_penerimaan`.`renterimaJmlJun`) AS juni,
	IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlJul`),
	`rencana_penerimaan`.`renterimaJmlJul`) AS juli,
	IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlAgs`),
	`rencana_penerimaan`.`renterimaJmlAgs`) AS agustus,
	IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlSep`),
	`rencana_penerimaan`.`renterimaJmlSep`) AS september,
	IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlOkt`),
	`rencana_penerimaan`.`renterimaJmlOkt`) AS oktober,
	IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlNov`),
	`rencana_penerimaan`.`renterimaJmlNov`) AS november,
	IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlDes`),
	`rencana_penerimaan`.`renterimaJmlDes`) AS desember,
	`rencana_penerimaan`.`rterimaPersenJan` AS pjanuari,
	`rencana_penerimaan`.`rterimaPersenFeb` AS pfebruari,
	`rencana_penerimaan`.`rterimaPersenMar` AS pmaret,
	`rencana_penerimaan`.`rterimaPersenApr` AS papril,
	`rencana_penerimaan`.`rterimaPersenMei` AS pmei,
	`rencana_penerimaan`.`rterimaPersenJun` AS pjuni,
	`rencana_penerimaan`.`rterimaPersenJul` AS pjuli,
	`rencana_penerimaan`.`rterimaPersenAgs` AS pagustus,
	`rencana_penerimaan`.`rterimaPersenSep` AS pseptember,
	`rencana_penerimaan`.`rterimaPersenOkt` AS poktober,
	`rencana_penerimaan`.`rterimaPersenNov` AS pnovember,
	`rencana_penerimaan`.`rterimaPersenDes` AS pdesember,
	IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaTotalTerima`)
	,`rencana_penerimaan`.`renterimaTotalTerima`) AS total_terima
	
FROM 
	`rencana_penerimaan`
	LEFT JOIN `kode_penerimaan_ref` 
		ON `kode_penerimaan_ref`.`kodeterimaId` = `rencana_penerimaan`.`renterimaKodeterimaId`
	LEFT JOIN `unit_kerja_ref` 
		ON `unit_kerja_ref`.`unitkerjaId` = `rencana_penerimaan`.`renterimaUnitkerjaId`
WHERE renterimaRpstatusId = 2 AND
(unit_kerja_ref.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	unit_kerja_ref.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)	   
	AND 
	`rencana_penerimaan`.`renterimaThanggarId` = '%s'
	AND 
	(`rencana_penerimaan`.`renterimaKodeterimaId` = '%s' OR %s)	
ORDER BY
	`unit_kerja_ref`.`unitkerjaId`,`kode_penerimaan_ref`.`kodeterimaKode` ASC
%s
";

$sql['get_total_rencana_penerimaan_per_bulan']="
SELECT
	SUM(IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlJan`), 
	`rencana_penerimaan`.`renterimaJmlJan`) )AS t_januari,
	SUM(IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlFeb`),
	`rencana_penerimaan`.`renterimaJmlFeb`) )AS t_februari,
	SUM(IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlMar`),
	`rencana_penerimaan`.`renterimaJmlMar`) )AS t_maret,
	SUM(IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlApr`),
	`rencana_penerimaan`.`renterimaJmlApr`) )AS t_april,
	SUM(IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlMei`),
	`rencana_penerimaan`.`renterimaJmlMei`) )AS t_mei,
	SUM(IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlJun`),
	`rencana_penerimaan`.`renterimaJmlJun`) )AS t_juni,
	SUM(IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlJul`),
	`rencana_penerimaan`.`renterimaJmlJul`) )AS t_juli,
	SUM(IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlAgs`),
	`rencana_penerimaan`.`renterimaJmlAgs`) )AS t_agustus,
	SUM(IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlSep`),
	`rencana_penerimaan`.`renterimaJmlSep`) )AS t_september,
	SUM(IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlOkt`),
	`rencana_penerimaan`.`renterimaJmlOkt`) )AS t_oktober,
	SUM(IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlNov`),
	`rencana_penerimaan`.`renterimaJmlNov`) )AS t_november,
	SUM(IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaJmlDes`),
	`rencana_penerimaan`.`renterimaJmlDes`) )AS t_desember,
	SUM(IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	(( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * `rencana_penerimaan`.`renterimaTotalTerima`)
	,`rencana_penerimaan`.`renterimaTotalTerima`) )AS t_total_terima
	
FROM 
	`rencana_penerimaan`
	LEFT JOIN `kode_penerimaan_ref` 
		ON `kode_penerimaan_ref`.`kodeterimaId` = `rencana_penerimaan`.`renterimaKodeterimaId`
	LEFT JOIN `unit_kerja_ref` 
		ON `unit_kerja_ref`.`unitkerjaId` = `rencana_penerimaan`.`renterimaUnitkerjaId`
WHERE renterimaRpstatusId = 2 AND
(unit_kerja_ref.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	unit_kerja_ref.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)	   
	AND 
	`rencana_penerimaan`.`renterimaThanggarId` = '%s'
	AND 
	(`rencana_penerimaan`.`renterimaKodeterimaId` = '%s' OR %s)
";

$sql['get_data_rencana_penerimaan_by_id']="
	SELECT
		thanggarId 				AS tahun_anggaran_id,
		thanggarNama 			AS tahun_anggaran_label,
		unitkerjaId 			AS unitkerja_id,
		unitkerjaNama 			AS unitkerja_label,
		kodeterimaId 			AS penerimaan_id,
		kodeterimaKode 			AS kode_penerimaan,
		kodeterimaNama 			AS nama_penerimaan,
		renterimaTotalTerima 	AS total,
		renterimaJmlJan			AS januari,
		renterimaJmlFeb			AS februari,
		renterimaJmlMar			AS maret,
		renterimaJmlApr			AS april,
		renterimaJmlMei			AS mei,
		renterimaJmlJun 		AS juni,
		renterimaJmlJul			AS juli,
		renterimaJmlAgs			AS agustus,
		renterimaJmlSep			AS september,
		renterimaJmlOkt			AS oktober,
		renterimaJmlNov			AS november,
		renterimaJmlDes			AS desember,
		renterimaVolume			AS volume,
		renterimaTarif				AS tarif,
		renterimaJumlah			AS totalterima,
		renterimaPersenPagu		AS pagu,
		renterimaPagu				AS totalpagu,
		renterimaKeterangan		AS keterangan
	FROM
		rencana_penerimaan
		JOIN kode_penerimaan_ref ON (kodeterimaId = renterimaKodeterimaId)
		JOIN tahun_anggaran ON (thanggarId = renterimaThanggarId)
		JOIN unit_kerja_ref ON (unitkerjaId = renterimaUnitkerjaId)
	WHERE
		renterimaId=%s
";


//COMBO
$sql['get_combo_tahun_anggaran']="
	SELECT
		thanggarId 		AS id,
		thanggarNama 	AS name
	FROM
		tahun_anggaran
	ORDER BY thanggarNama
";
//aktif
$sql['get_tahun_anggaran_aktif']="
	SELECT
		thanggarId 		AS id,
		thanggarNama 	AS name
	FROM
		tahun_anggaran
	WHERE
		thanggarIsAktif='Y'
";
//aktif
$sql['get_tahun_anggaran']="
	SELECT
		thanggarId 		AS id,
		thanggarNama 	AS name
	FROM
		tahun_anggaran
	WHERE
		thanggarId='%s'
";

?>