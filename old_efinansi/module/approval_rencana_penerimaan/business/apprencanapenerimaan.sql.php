<?php

//===GET===
$sql['get_count_data'] = "
	SELECT 
	count(unitkerjaId) 			AS total		
	FROM unit_kerja_ref 
	LEFT JOIN rencana_penerimaan ON renterimaUnitkerjaId = unitkerjaId AND renterimaThanggarId ='%s'
	LEFT JOIN kode_penerimaan_ref ON renterimaKodeterimaId = kodeterimaId
	LEFT JOIN (SELECT renterimaUnitkerjaId AS totalUnitkerjaId, SUM(renterimaTotalTerima) AS totalTotalTerima FROM rencana_penerimaan WHERE renterimaThanggarId ='%s' GROUP BY totalUnitkerjaId) AS total ON totalUnitkerjaId=unitkerjaId
	WHERE 
	(kodeterimaKode LIKE '%s' OR
	kodeterimaNama like '%s') AND
	(
	unitkerjaKodeSistem 
	LIKE CONCAT((SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitkerjaId = '%s'),'.','%s')
	OR 
	unitkerjaKodeSistem 
	LIKE CONCAT((SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitkerjaId = '%s'))
	)
	%s
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
	LEFT JOIN (SELECT renterimaUnitkerjaId AS totalUnitkerjaId, SUM(renterimaTotalTerima) AS totalTotalTerima FROM rencana_penerimaan WHERE renterimaThanggarId ='%s' GROUP BY totalUnitkerjaId) AS total ON totalUnitkerjaId=unitkerjaId
	WHERE 
	(kodeterimaKode LIKE '%s' OR
	kodeterimaNama like '%s') AND
	(
	unitkerjaKodeSistem 
	LIKE CONCAT((SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitkerjaId = '%s'),'.','%s')
	OR 
	unitkerjaKodeSistem 
	LIKE CONCAT((SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitkerjaId = '%s'))
	)
	%s
	ORDER BY unitkerjaKodeSistem ASC
LIMIT %s,%s
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
		renterimaRpstatusId		AS approval,
		finansi_ref_sumber_dana.sumberdanaNama as sumber_dana_label,
		finansi_ref_sumber_dana.sumberdanaId as sumber_dana,
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
		renterimaCatatan  		AS note,
		satuan_komponen.satkompNama AS satuan
	FROM
		rencana_penerimaan
		
		JOIN kode_penerimaan_ref ON (kodeterimaId = renterimaKodeterimaId)
		JOIN tahun_anggaran ON (thanggarId = renterimaThanggarId)
		JOIN unit_kerja_ref ON (unitkerjaId = renterimaUnitkerjaId)
		LEFT JOIN finansi_ref_sumber_dana 
			ON finansi_ref_sumber_dana.sumberdanaId = rencana_penerimaan.renterimaSumberDanaId
		LEFT JOIN satuan_komponen ON satuan_komponen.satkompId = kode_penerimaan_ref.kodeterimaSatKompId
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

/*$sql['do_add_rencana_penerimaan']="
	INSERT INTO
		rencana_penerimaan(
			renterimaUnitkerjaId, 
			renterimaKodeterimaId,
			renterimaTotalTerima,
			renterimaThanggarId,
			renterimaJmlJan,
			renterimaJmlFeb,
			renterimaJmlMar,
			renterimaJmlApr,
			renterimaJmlMei,
			renterimaJmlJun,
			renterimaJmlJul,
			renterimaJmlAgs,
			renterimaJmlSep,
			renterimaJmlOkt,
			renterimaJmlNov,
			renterimaJmlDes,
			renterimaVolume,
			renterimaTarif,
			renterimaJumlah,
			renterimaPersenPagu,
			renterimaPagu,
			renterimaKeterangan
		) VALUES (
			%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s
		)
";*/
/*belum keinsert 
renterimaDeskripsi,
renterimaTipedistribusiId,
renterimaIsAktif,
*/
$sql['do_update_rencana_penerimaan']="
	UPDATE
		rencana_penerimaan
	SET 
		renterimaCatatan = '%s',
		renterimaRpstatusId = '%s'
	WHERE
		renterimaId= %s
";

/*$sql['do_delete_rencana_penerimaan_by_id'] = 
   "DELETE from rencana_penerimaan
   WHERE 
      renterimaId='%s'";

$sql['do_delete_rencana_penerimaan_by_array_id'] = 
   "DELETE from rencana_penerimaan
   WHERE 
      renterimaId IN ('%s')";*/
/**
 * combo approval
 */
$sql['status_approval'] = 
	"SELECT
		rpstatusId   AS id,
		rpstatusNama AS name
	FROM 
		finansi_rp_ref_status_rp
	";
/**
 * untuk mendapatkan jumlah sub unit
 * @since 11 Januari 2012
 */
$sql['get_total_sub_unit_kerja']="
SELECT 
	count(unitkerjaId) as total
FROM unit_kerja_ref
WHERE unitkerjaParentId = %s
";

/**
 * untuk mendapatkan jumlah status approval
 * @since 27 Februari 2012
 */

$sql['get_status_approval'] = 
	"SELECT
		rpstatusId   AS id,
		rpstatusNama AS nama
	FROM 
		finansi_rp_ref_status_rp
	WHERE 
		rpstatusId ='%s'
";