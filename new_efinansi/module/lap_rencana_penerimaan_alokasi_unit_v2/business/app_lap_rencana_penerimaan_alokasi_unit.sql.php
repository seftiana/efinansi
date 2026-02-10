<?php

/**
 * @package lap_rencana_penerimaan_alokasi_unit
 * @since 24 Februari 2012
 * @copyright (c) 2012 Gamatechno
 */
 
$sql['get_count_data'] = "
SELECT 
	FOUND_ROWS() AS total
";

$sql['get_data_rencana_penerimaan'] = "
SELECT 
   SQL_CALC_FOUND_ROWS
	uk.`unitkerjaKode` AS unit_kerja_sumber_kode,
	uk.`unitkerjaNama` AS unit_kerja_sumber_nama,
	uk.`unitkerjaId` AS unit_kerja_sumber_id,
	uk_alo.`unitkerjaKode` AS unit_kerja_kode,
	uk_alo.`unitkerjaNama` AS unit_kerja_nama,
	kpr.`kodeterimaKode` AS kode_penerimaan,
	kpr.`kodeterimaNama` AS kode_penerimaan_nama,
	(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
    ((rpdet.`renterimadtAlokasi`/100) * rpen.`renterimaTotalTerima`),
    ((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaTotalTerima`))) AS total_terima,
	(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlJan`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlJan`))) AS januari,
	(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlFeb`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlFeb`))) AS februari,
	(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlMar`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlMar`))) AS maret,
	(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlApr`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlApr`))) AS april,
	(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlMei`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlMei`))) AS mei,
	(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlJun`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlJun`))) AS juni,
	(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlJul`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlJul`))) AS juli,
	(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlAgs`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlAgs`))) AS agustus,
	(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlSep`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlSep`))) AS september,
	(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlOkt`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlOkt`))) AS oktober,
	(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlNov`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlNov`))) AS november,
	(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlDes`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlDes`))) AS desember,
	
	rpen.`rterimaPersenJan` AS pjanuari,
	rpen.`rterimaPersenFeb` AS pfebruari,
	rpen.`rterimaPersenMar` AS pmaret,
	rpen.`rterimaPersenApr` AS papril,
	rpen.`rterimaPersenMei` AS pmei,
	rpen.`rterimaPersenJun` AS pjuni,
	rpen.`rterimaPersenJul` AS pjuli,
	rpen.`rterimaPersenAgs` AS pagustus,
	rpen.`rterimaPersenSep` AS pseptember,
	rpen.`rterimaPersenOkt` AS poktober,
	rpen.`rterimaPersenNov` AS pnovember,
	rpen.`rterimaPersenDes` AS pdesember,
	IFNULL(rpen.renterimaKeterangan,'-') AS keterangan
FROM
  `rencana_penerimaan` rpen 
  LEFT JOIN `kode_penerimaan_ref` kpr 
    ON kpr.`kodeterimaId` = rpen.`renterimaKodeterimaId` 
  LEFT JOIN `unit_kerja_ref` uk 
    ON uk.`unitkerjaId` = rpen.`renterimaUnitkerjaId` 
  LEFT JOIN rencana_penerimaan_detil rpdet
    ON (rpdet.`renterimadtRenterimaId` = rpen.`renterimaId`)
    LEFT JOIN `unit_kerja_ref` uk_alo 
      ON uk_alo.`unitkerjaId` = rpdet.`renterimadtUnitKerjaId`
		
WHERE 
	(
	(uk_alo.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	uk_alo.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)
	OR
	(uk.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	uk.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)
	)
	AND 
	rpen.`renterimaThanggarId` = '%s'
	AND 
	(rpen.`renterimaKodeterimaId` = '%s' OR %s)
	AND( rpdet.`renterimadtAlokasi` > 0 OR  rpdet.`renterimadtAlokasi` IS NULL )
ORDER BY
	unit_kerja_sumber_kode,kode_penerimaan ASC
%s
";

$sql['get_total_rencana_penerimaan_per_bulan']="
SELECT 
	SUM(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
    ((rpdet.`renterimadtAlokasi`/100) * rpen.`renterimaTotalTerima`),
    ((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaTotalTerima`))) AS t_total_terima,
	SUM(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlJan`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlJan`))) AS t_januari,
	SUM(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlFeb`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlFeb`))) AS t_februari,
	SUM(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlMar`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlMar`))) AS t_maret,
	SUM(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlApr`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlApr`))) AS t_april,
	SUM(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlMei`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlMei`))) AS t_mei,
	SUM(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlJun`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlJun`))) AS t_juni,
	SUM(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlJul`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlJul`))) AS t_juli,
	SUM(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlAgs`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlAgs`))) AS t_agustus,
	SUM(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlSep`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlSep`))) AS t_september,
	SUM(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlOkt`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlOkt`))) AS t_oktober,
	SUM(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlNov`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlNov`))) AS t_november,
	SUM(IF(rpdet.`renterimadtAlokasi`IS NOT NULL,
	(( (rpdet.`renterimadtAlokasi`/100)) * rpen.`renterimaJmlDes`),
	((rpen.`renterimaAlokasiUnit`/100) * rpen.`renterimaJmlDes`))) AS t_desember
FROM
  `rencana_penerimaan` rpen 
  LEFT JOIN `kode_penerimaan_ref` kpr 
    ON kpr.`kodeterimaId` = rpen.`renterimaKodeterimaId` 
  LEFT JOIN `unit_kerja_ref` uk 
    ON uk.`unitkerjaId` = rpen.`renterimaUnitkerjaId` 
  LEFT JOIN rencana_penerimaan_detil rpdet
    ON (rpdet.`renterimadtRenterimaId` = rpen.`renterimaId`)
    LEFT JOIN `unit_kerja_ref` uk_alo 
      ON uk_alo.`unitkerjaId` = rpdet.`renterimadtUnitKerjaId`
		
WHERE 
	(
	(uk_alo.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	uk_alo.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)
	OR
	(uk.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	uk.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)
	)
	AND 
	rpen.`renterimaThanggarId` = '%s'
	AND 
	(rpen.`renterimaKodeterimaId` = '%s' OR %s)
	AND( rpdet.`renterimadtAlokasi` > 0 OR  rpdet.`renterimadtAlokasi` IS NULL )
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
		renterimaTarif			AS tarif,
		renterimaJumlah			AS totalterima,
		renterimaPersenPagu		AS pagu,
		renterimaPagu			AS totalpagu,
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