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

$sql['get_data_history'] = "
SELECT 
    unitkerjaId AS idunit,
    unitkerjaKode AS kode_satker,
    unitkerjaNama AS nama_satker,
    unitkerjaKode AS kode_unit,
    unitkerjaNama AS nama_unit,
    unitkerjaParentId AS parentId,
    IF (
        renterimaTotalTerima IS NULL,
        0,
        renterimaTotalTerima
    ) AS total,
    IF (
        totalTotalTerima IS NULL,
        0,
        totalTotalTerima
    ) AS jumlah_total,
    renterimaId AS idrencana,
    kodeterimaId AS idkode,
    kodeterimaKode AS kode,
    kodeterimaNama AS nama,
    renterimaRpstatusId AS approval,
    pa.`rencanaPenerimaanAdjustId` AS adjust_id, 
    pa.`rencanaPenerimaanAdjustTotal` AS total_adjust, 
    rp.`rpstatusNama` AS `status`, 
    pa.`rencanaPenerimaanAdjustNomor` AS nomor_adjust, 
    pa.`rencanaPenerimaanAdjustTanggal` AS tgl_adjust   
FROM
    unit_kerja_ref 
    LEFT JOIN rencana_penerimaan 
        ON renterimaUnitkerjaId = unitkerjaId 
        AND renterimaThanggarId = '%s' 
    LEFT JOIN kode_penerimaan_ref 
        ON renterimaKodeterimaId = kodeterimaId 
    LEFT JOIN 
        (SELECT 
            renterimaUnitkerjaId AS totalUnitkerjaId,
            SUM(renterimaTotalTerima) AS totalTotalTerima 
        FROM
            rencana_penerimaan 
        WHERE renterimaThanggarId = '%s' 
        GROUP BY totalUnitkerjaId) AS total 
        ON totalUnitkerjaId = unitkerjaId 
    JOIN finansi_pa_rencana_penerimaan_adjust AS pa 
        ON pa.`rencanaPenerimaanAdustRencanaTerimaId` = renterimaId 
    LEFT JOIN `finansi_rp_ref_status_rp` AS rp 
        ON rp.`rpstatusId` = pa.`rencanaPenerimaanAdjustStatusId` 
WHERE (
        unit_kerja_ref.unitkerjaKodeSistem LIKE CONCAT(
            (SELECT 
                unitkerjaKodeSistem 
            FROM
                unit_kerja_ref 
            WHERE unit_kerja_ref.unitkerjaId = '%s'),
            '.',
            '%s'
        ) 
        OR unit_kerja_ref.unitkerjaKodeSistem LIKE 
        (SELECT 
            unitkerjaKodeSistem 
        FROM
            unit_kerja_ref 
        WHERE unit_kerja_ref.unitkerjaId = '%s')
    ) 
    AND unitkerjaTipeunitId NOT IN (1) 
    %s 
ORDER BY unitkerjaKodeSistem ASC 
LIMIT %s, %s 
";

$sql['count_data']  = "
SELECT 
    COUNT(unitkerjaId) AS total   
FROM
    unit_kerja_ref 
    LEFT JOIN rencana_penerimaan 
        ON renterimaUnitkerjaId = unitkerjaId 
        AND renterimaThanggarId = '%s' 
    LEFT JOIN kode_penerimaan_ref 
        ON renterimaKodeterimaId = kodeterimaId 
    LEFT JOIN 
        (SELECT 
            renterimaUnitkerjaId AS totalUnitkerjaId,
            SUM(renterimaTotalTerima) AS totalTotalTerima 
        FROM
            rencana_penerimaan 
        WHERE renterimaThanggarId = '%s' 
        GROUP BY totalUnitkerjaId) AS total 
        ON totalUnitkerjaId = unitkerjaId 
    JOIN finansi_pa_rencana_penerimaan_adjust AS pa 
        ON pa.`rencanaPenerimaanAdustRencanaTerimaId` = renterimaId 
    LEFT JOIN `finansi_rp_ref_status_rp` AS rp 
        ON rp.`rpstatusId` = pa.`rencanaPenerimaanAdjustStatusId` 
WHERE (
        unit_kerja_ref.unitkerjaKodeSistem LIKE CONCAT(
            (SELECT 
                unitkerjaKodeSistem 
            FROM
                unit_kerja_ref 
            WHERE unit_kerja_ref.unitkerjaId = '%s'),
            '.',
            '%s'
        ) 
        OR unit_kerja_ref.unitkerjaKodeSistem LIKE 
        (SELECT 
            unitkerjaKodeSistem 
        FROM
            unit_kerja_ref 
        WHERE unit_kerja_ref.unitkerjaId = '%s')
    ) 
    AND unitkerjaTipeunitId NOT IN (1) 
    %s 
ORDER BY unitkerjaKodeSistem ASC 
";

$sql['get_data_adjustment_by_id']   = "
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
			rterimaPersenNov+rterimaPersenDes) AS tpersen, 
		pa.`rencanaPenerimaanAdjustId` AS adjust_id, 
		pa.`rencanaPenerimaanAdjustNominalJanLama` AS januari_l,
		pa.`rencanaPenerimaanAdjustNominalFebLama` AS februari_l,
		pa.`rencanaPenerimaanAdjustNominalMaretLama` AS maret_l,
		pa.`rencanaPenerimaanAdjustNominalAprilLama` AS april_l,
		pa.`rencanaPenerimaanAdjustNominalMeiLama` AS mei_l,
		pa.`rencanaPenerimaanAdjustNominalJunLama` AS juni_l,
		pa.`rencanaPenerimaanAdjustNominalJulLama` AS juli_l,
		pa.`rencanaPenerimaanAdjustNominalAgustLama` AS agustus_l,
		pa.`rencanaPenerimaanAdjustNominalSeptLama` AS september_l,
		pa.`rencanaPenerimaanAdjustNominalOktLama` AS oktober_l,
		pa.`rencanaPenerimaanAdjustNominalNopLama` AS nopember_l,
		pa.`rencanaPenerimaanAdjustNominalDesLama` AS desember_l,
		pa.`rencanaPenerimaanAdjustNominalJanBaru` AS januari_b,
		pa.`rencanaPenerimaanAdjustNominalFebBaru` AS februari_b,
		pa.`rencanaPenerimaanAdjustNominalMaretBaru` AS maret_b,
		pa.`rencanaPenerimaanAdjustNominalAprilBaru` AS april_b,
		pa.`rencanaPenerimaanAdjustNominalMeiBaru` AS mei_b,
		pa.`rencanaPenerimaanAdjustNominalJunBaru` AS juni_b,
		pa.`rencanaPenerimaanAdjustNominalJulBaru` AS juli_b,
		pa.`rencanaPenerimaanAdjustNominalAgustBaru` AS agustus_b,
		pa.`rencanaPenerimaanAdjustNominalSeptBaru` AS september_b,
		pa.`rencanaPenerimaanAdjustNominalOktBaru` AS oktober_b,
		pa.`rencanaPenerimaanAdjustNominalNopBaru` AS nopember_b,
		pa.`rencanaPenerimaanAdjustNominalDesBaru` AS desember_b,
		pa.`rencanaPenerimaanAdjustTotal` AS total,
		pa.`rencanaPenerimaanAdjustNomor` AS nomor_adjust,
		pa.`rencanaPenerimaanAdjustTanggal` AS tanggal_adjust,
		pa.`rencanaPenerimaanAdjustTglApprove` AS tanggal_approve,
		stat.`rpstatusNama` AS status_adjustment  
	FROM
        finansi_pa_rencana_penerimaan_adjust AS pa
		LEFT JOIN rencana_penerimaan AS rp ON pa.`rencanaPenerimaanAdustRencanaTerimaId` = rp.`renterimaId`
        LEFT JOIN finansi_rp_ref_status_rp AS stat ON pa.`rencanaPenerimaanAdjustStatusId` = stat.`rpstatusId`
		JOIN kode_penerimaan_ref pr ON (kodeterimaId = renterimaKodeterimaId)
		JOIN tahun_anggaran AS ta ON (thanggarId = renterimaThanggarId)
		JOIN unit_kerja_ref AS uk ON (unitkerjaId = renterimaUnitkerjaId)
	WHERE
		pa.`rencanaPenerimaanAdjustId` = '%s';
";

$sql['update_adjustment_rencana_penerimaan'] = "
UPDATE `finansi_pa_rencana_penerimaan_adjust`
SET `rencanaPenerimaanAdjustNominalJanLama` = '%s',
    `rencanaPenerimaanAdjustNominalJanBaru` = '%s',
    `rencanaPenerimaanAdjustNominalFebLama` = '%s',
    `rencanaPenerimaanAdjustNominalFebBaru` = '%s',
    `rencanaPenerimaanAdjustNominalMaretLama` = '%s',
    `rencanaPenerimaanAdjustNominalMaretBaru` = '%s',
    `rencanaPenerimaanAdjustNominalAprilLama` = '%s',
    `rencanaPenerimaanAdjustNominalAprilBaru` = '%s',
    `rencanaPenerimaanAdjustNominalMeiLama` = '%s',
    `rencanaPenerimaanAdjustNominalMeiBaru` = '%s',
    `rencanaPenerimaanAdjustNominalJunLama` = '%s',
    `rencanaPenerimaanAdjustNominalJunBaru` = '%s',
    `rencanaPenerimaanAdjustNominalJulLama` = '%s',
    `rencanaPenerimaanAdjustNominalJulBaru` = '%s',
    `rencanaPenerimaanAdjustNominalAgustLama` = '%s',
    `rencanaPenerimaanAdjustNominalAgustBaru` = '%s',
    `rencanaPenerimaanAdjustNominalSeptLama` = '%s',
    `rencanaPenerimaanAdjustNominalSeptBaru` = '%s',
    `rencanaPenerimaanAdjustNominalOktLama` = '%s',
    `rencanaPenerimaanAdjustNominalOktBaru` = '%s',
    `rencanaPenerimaanAdjustNominalNopLama` = '%s',
    `rencanaPenerimaanAdjustNominalNopBaru` = '%s',
    `rencanaPenerimaanAdjustNominalDesLama` = '%s',
    `rencanaPenerimaanAdjustNominalDesBaru` = '%s',
    `rencanaPenerimaanAdjustTotal` = '%s',
    `rencanaPenerimaanAdjustUserId` = '%s'
WHERE `rencanaPenerimaanAdjustId` = '%s'
";

$sql['do_approval_adjustment'] = "
UPDATE `finansi_pa_rencana_penerimaan_adjust`
SET `rencanaPenerimaanAdjustStatusId` = '%s',
    `rencanaPenerimaanAdjustTglApprove` = NOW(),
    `rencanaPenerimaanAdjustUserId` = '%s'
WHERE `rencanaPenerimaanAdjustId` = '%s'
";

$sql['do_update_rencana_penerimaan'] = "
UPDATE `rencana_penerimaan`
SET `renterimaTotalTerima` = (`renterimaTotalTerima`+%s)
WHERE `renterimaId` = '%s'
";

# get status
$sql['get_status']  = "
SELECT rpstatusId AS id FROM finansi_rp_ref_status_rp WHERE rpstatusNama LIKE '%s'
";
?>
