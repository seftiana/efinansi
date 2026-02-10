<?php

/**
 * @package lap_rencana_penerimaan
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
   JOIN (SELECT unitkerjaId AS id,
      CASE
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN unitkerjaKodeSistem
      END AS `code`
      FROM unit_kerja_ref
   ) AS tmp_unit
      ON tmp_unit.id = unitkerjaId
WHERE 1 = 1 
AND `rencana_penerimaan`.`renterimaThanggarId` = '%s'
AND `renterimaRpstatusId` = '%s'
AND (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
";

$sql['get_data_rencana_penerimaan'] = "
SELECT 
	`kode_penerimaan_ref`.`kodeterimaKode` AS kode_penerimaan,
	`unit_kerja_ref`.`unitkerjaId` AS unit_kerja_id,
	`unit_kerja_ref`.`unitkerjaNama` AS unit_kerja_nama,
	`unit_kerja_ref`.`unitkerjaKode` AS unit_kerja_kode,
	`kode_penerimaan_ref`.`kodeterimaNama` AS kode_penerimaan_nama,
	`rencana_penerimaan`.`renterimaKeterangan` AS keterangan,
	`rencana_penerimaan`.`renterimaJmlJan` AS januari,
	`rencana_penerimaan`.`renterimaJmlFeb` AS februari,
	`rencana_penerimaan`.`renterimaJmlMar` AS maret,
	`rencana_penerimaan`.`renterimaJmlApr` AS april,
	`rencana_penerimaan`.`renterimaJmlMei` AS mei,
	`rencana_penerimaan`.`renterimaJmlJun` AS juni,
	`rencana_penerimaan`.`renterimaJmlJul` AS juli,
	`rencana_penerimaan`.`renterimaJmlAgs` AS agustus,
	`rencana_penerimaan`.`renterimaJmlSep` AS september,
	`rencana_penerimaan`.`renterimaJmlOkt` AS oktober,
	`rencana_penerimaan`.`renterimaJmlNov` AS november,
	`rencana_penerimaan`.`renterimaJmlDes` AS desember,
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
	`rencana_penerimaan`.`renterimaTotalTerima` AS total_terima
	
FROM 
	`rencana_penerimaan`
	LEFT JOIN `kode_penerimaan_ref` 
		ON `kode_penerimaan_ref`.`kodeterimaId` = `rencana_penerimaan`.`renterimaKodeterimaId`
	LEFT JOIN `unit_kerja_ref` 
		ON `unit_kerja_ref`.`unitkerjaId` = `rencana_penerimaan`.`renterimaUnitkerjaId`
   JOIN (SELECT unitkerjaId AS id,
      CASE
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN unitkerjaKodeSistem
      END AS `code`
      FROM unit_kerja_ref
   ) AS tmp_unit
      ON tmp_unit.id = unitkerjaId
WHERE 1 = 1 
AND `rencana_penerimaan`.`renterimaThanggarId` = '%s'
AND `renterimaRpstatusId` = '%s'
AND (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
	
ORDER BY
	SUBSTRING_INDEX(tmp_unit.code, '.', 1)+0,
	SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 2), '.', -1)+0,
	SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 3), '.', -1)+0,
	SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 4), '.', -1)+0,
	SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 5), '.', -1)+0,
	`unit_kerja_ref`.`unitkerjaId`,`kode_penerimaan_ref`.`kodeterimaKode` ASC
%s
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

$sql['get_data_for_total'] = "
SELECT
   IF (totalTotalTerima IS NULL, 0, totalTotalTerima) AS tot_jumlah,
   IF (totalterima IS NULL, 0, totalterima) AS tot_terima
FROM unit_kerja_ref
   LEFT JOIN rencana_penerimaan
     ON renterimaUnitkerjaId = unitkerjaId
       AND renterimaThanggarId = '%s'
   LEFT JOIN (SELECT
                 renterimaUnitkerjaId AS totalUnitkerjaId,
                 SUM(renterimaTotalTerima) AS totalTotalTerima,
                 SUM(renterimaJumlah) AS totalterima
              FROM rencana_penerimaan
              WHERE renterimaThanggarId = '%s'
              GROUP BY totalUnitkerjaId) AS total
     ON totalUnitkerjaId = unitkerjaId
WHERE renterimaRpstatusId = '%s' AND (unitkerjaId = '%s' OR  unitkerjaParentId = '%s')
GROUP BY unitkerjaKode
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

$sql['get_total_rencana_penerimaan_perbulan'] ="
SELECT 
        SUM(`rencana_penerimaan`.`renterimaJmlJan`) AS t_januari,
        SUM(`rencana_penerimaan`.`renterimaJmlFeb`) AS t_februari,
        SUM(`rencana_penerimaan`.`renterimaJmlMar`) AS t_maret,
        SUM(`rencana_penerimaan`.`renterimaJmlApr`) AS t_april,
        SUM(`rencana_penerimaan`.`renterimaJmlMei`) AS t_mei,
        SUM(`rencana_penerimaan`.`renterimaJmlJun`) AS t_juni,
        SUM(`rencana_penerimaan`.`renterimaJmlJul`) AS t_juli,
        SUM(`rencana_penerimaan`.`renterimaJmlAgs`) AS t_agustus,
        SUM(`rencana_penerimaan`.`renterimaJmlSep`) AS t_september,
        SUM(`rencana_penerimaan`.`renterimaJmlOkt`) AS t_oktober,
        SUM(`rencana_penerimaan`.`renterimaJmlNov`) AS t_november,
        SUM(`rencana_penerimaan`.`renterimaJmlDes`) AS t_desember,
        SUM(`rencana_penerimaan`.`renterimaTotalTerima`) AS t_total_terima 
FROM
        `rencana_penerimaan` 
        LEFT JOIN `kode_penerimaan_ref` 
                ON `kode_penerimaan_ref`.`kodeterimaId` = `rencana_penerimaan`.`renterimaKodeterimaId` 
	LEFT JOIN `unit_kerja_ref` 
		ON `unit_kerja_ref`.`unitkerjaId` = `rencana_penerimaan`.`renterimaUnitkerjaId`
   JOIN (SELECT unitkerjaId AS id,
      CASE
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN unitkerjaKodeSistem
      END AS `code`
      FROM unit_kerja_ref
   ) AS tmp_unit
      ON tmp_unit.id = unitkerjaId
WHERE 1 = 1 
AND `rencana_penerimaan`.`renterimaThanggarId` = '%s'
AND `renterimaRpstatusId` = '%s'
AND (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
";

?>