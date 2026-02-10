<?php
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

# check tahun anggaran is aktif or is open
$sql['check_tahun_anggaran'] = "
    SELECT
        `thanggarId` AS id,
        `thanggarNama` AS nama,
        `thanggarIsAktif` AS is_aktif,
        `thanggarIsOpen` AS is_open,
        `thanggarBuka` AS buka,
        `thanggarTutup` AS tutup
    FROM `tahun_anggaran`
    WHERE `thanggarId` = '%s'
";

$sql['get_data_unitkerja'] = "
	SELECT
		unitkerjaId 			AS idunit,
		unitkerjaKode 			AS kode_satker,
		unitkerjaNama 			AS nama_satker,
		unitkerjaKode 			AS kode_unit,
		unitkerjaNama 			AS nama_unit,
		unitkerjaParentId 		AS parentId,
		if (renterimaTotalTerima IS NULL,0,renterimaTotalTerima) 	AS total,
		if (totalTotalTerima IS NULL,0,totalTotalTerima) 			AS jumlah_total,
		renterimaId 			AS idrencana,
		kodeterimaId 			AS idkode,
		kodeterimaKode 			AS kode,
		kodeterimaNama 			AS nama,
		renterimaRpstatusId		AS approval

	FROM unit_kerja_ref
	LEFT JOIN rencana_penerimaan ON renterimaUnitkerjaId = unitkerjaId AND renterimaThanggarId ='%s'
	LEFT JOIN kode_penerimaan_ref ON renterimaKodeterimaId = kodeterimaId
	LEFT JOIN  (
				SELECT renterimaUnitkerjaId AS totalUnitkerjaId, 
				SUM(renterimaTotalTerima) AS totalTotalTerima 
				FROM 
					rencana_penerimaan 
				WHERE renterimaThanggarId ='%s' 
				GROUP BY totalUnitkerjaId
				) AS total ON totalUnitkerjaId=unitkerjaId
				
	WHERE	
	(
	unit_kerja_ref.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s')
	OR
	unit_kerja_ref.unitkerjaKodeSistem LIKE 
	(
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	) 
	-- AND unitkerjaTipeunitId NOT IN(1)
	%s
	ORDER BY unitkerjaKodeSistem ASC
	LIMIT %s,%s
";

$sql['get_count_data']="
	SELECT
		count(unitkerjaId) 			AS total

	FROM unit_kerja_ref
	LEFT JOIN rencana_penerimaan ON renterimaUnitkerjaId = unitkerjaId AND renterimaThanggarId ='%s'
	LEFT JOIN kode_penerimaan_ref ON renterimaKodeterimaId = kodeterimaId
	LEFT JOIN  (
				SELECT renterimaUnitkerjaId AS totalUnitkerjaId, 
				SUM(renterimaTotalTerima) AS totalTotalTerima 
				FROM 
					rencana_penerimaan 
				WHERE renterimaThanggarId ='%s' 
				GROUP BY totalUnitkerjaId
				) AS total ON totalUnitkerjaId=unitkerjaId
				
	WHERE 
	(
	unit_kerja_ref.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s')
	OR
	unit_kerja_ref.unitkerjaKodeSistem LIKE 
	(
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	) 
	-- AND unitkerjaTipeunitId NOT IN(1)
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
		renterimaKeterangan		AS keterangan,
		rterimaPersenJan		AS pjanuari,
		rterimaPersenFeb		AS pfebruari,
		rterimaPersenMar		AS pmaret,
		rterimaPersenApr		AS papril,
		rterimaPersenMei		AS pmei,
		rterimaPersenJun		AS pjuni,
		rterimaPersenJul		AS pjuli,
		rterimaPersenAgs		AS pagustus,
		rterimaPersenSep		AS pseptember,
		rterimaPersenOkt		AS poktober,
		rterimaPersenNov		AS pnovember,
		rterimaPersenDes		AS pdesember,
		(renterimaJmlJan+renterimaJmlFeb+renterimaJmlMar+renterimaJmlApr+renterimaJmlMei+
		    renterimaJmlJun+renterimaJmlJul+renterimaJmlAgs+renterimaJmlSep+renterimaJmlOkt+
			renterimaJmlNov+renterimaJmlDes) AS tnominal,		
		(rterimaPersenJan+rterimaPersenFeb+rterimaPersenMar+rterimaPersenApr+rterimaPersenMei+
		    rterimaPersenJun+rterimaPersenJul+rterimaPersenAgs+rterimaPersenSep+rterimaPersenOkt+
			rterimaPersenNov+rterimaPersenDes) AS tpersen
	FROM
		rencana_penerimaan

		JOIN kode_penerimaan_ref ON (kodeterimaId = renterimaKodeterimaId)
		JOIN tahun_anggaran ON (thanggarId = renterimaThanggarId)
		JOIN unit_kerja_ref ON (unitkerjaId = renterimaUnitkerjaId)
	WHERE
		renterimaId=%s
";

# get status
$sql['get_status']  = "
SELECT rpstatusId AS id FROM finansi_rp_ref_status_rp WHERE rpstatusNama LIKE '%s'
";

# do insert rencana penerimaan adjust
$sql['insert_into_rencana_penerimaan_adjust'] ="
INSERT INTO `finansi_pa_rencana_penerimaan_adjust`
            (`rencanaPenerimaanAdustRencanaTerimaId`,
             `rencanaPenerimaanAdjustNomor`,
             `rencanaPenerimaanAdjustTanggal`,
             `rencanaPenerimaanAdjustNominalJanLama`,
             `rencanaPenerimaanAdjustNominalJanBaru`,
             `rencanaPenerimaanAdjustNominalFebLama`,
             `rencanaPenerimaanAdjustNominalFebBaru`,
             `rencanaPenerimaanAdjustNominalMaretLama`,
             `rencanaPenerimaanAdjustNominalMaretBaru`,
             `rencanaPenerimaanAdjustNominalAprilLama`,
             `rencanaPenerimaanAdjustNominalAprilBaru`,
             `rencanaPenerimaanAdjustNominalMeiLama`,
             `rencanaPenerimaanAdjustNominalMeiBaru`,
             `rencanaPenerimaanAdjustNominalJunLama`,
             `rencanaPenerimaanAdjustNominalJunBaru`,
             `rencanaPenerimaanAdjustNominalJulLama`,
             `rencanaPenerimaanAdjustNominalJulBaru`,
             `rencanaPenerimaanAdjustNominalAgustLama`,
             `rencanaPenerimaanAdjustNominalAgustBaru`,
             `rencanaPenerimaanAdjustNominalSeptLama`,
             `rencanaPenerimaanAdjustNominalSeptBaru`,
             `rencanaPenerimaanAdjustNominalOktLama`,
             `rencanaPenerimaanAdjustNominalOktBaru`,
             `rencanaPenerimaanAdjustNominalNopLama`,
             `rencanaPenerimaanAdjustNominalNopBaru`,
             `rencanaPenerimaanAdjustNominalDesLama`,
             `rencanaPenerimaanAdjustNominalDesBaru`,
             `rencanaPenerimaanAdjustTotal`,
             `rencanaPenerimaanAdjustStatusId`,
             `rencanaPenerimaanAdjustUserId`)
VALUES ('%s',
        '%s',
        NOW(),
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s')
";

$sql['generate_nomor_adjustment'] = "
SELECT 
    CONCAT(
        YEAR(NOW()),
        '.',
        SUBSTRING(
            '0000000',
            1,
            LENGTH('0000000') - LENGTH(
                COUNT(
                    DISTINCT rencanaPenerimaanAdjustId
                ) + 1
            )
        ),
        COUNT(
            DISTINCT rencanaPenerimaanAdjustId
        ) + 1
    ) AS nomor 
FROM
    finansi_pa_rencana_penerimaan_adjust 
WHERE YEAR(rencanaPenerimaanAdjustTanggal) = YEAR(NOW())
";

# check adjustment rencana penerimaan
$sql['do_check_adjustment_penerimaan'] = "
SELECT 
    COUNT(DISTINCT rencanaPenerimaanAdjustId) AS count 
FROM 
    finansi_pa_rencana_penerimaan_adjust 
WHERE 
    `rencanaPenerimaanAdustRencanaTerimaId` = '%s' 
AND `rencanaPenerimaanAdjustStatusId` = 1
";
?>
