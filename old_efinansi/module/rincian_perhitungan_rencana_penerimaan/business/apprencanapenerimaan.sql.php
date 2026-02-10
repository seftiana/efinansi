<?php

//===GET===
$sql['get_count_data'] = "
	SELECT 
		count(unitkerjaId ) 			AS total	
	FROM unit_kerja_ref 
	LEFT JOIN rencana_penerimaan ON renterimaUnitkerjaId = unitkerjaId AND renterimaThanggarId ='%s'
	LEFT JOIN kode_penerimaan_ref ON renterimaKodeterimaId = kodeterimaId
	LEFT JOIN (SELECT renterimaUnitkerjaId AS totalUnitkerjaId, SUM(renterimaTotalTerima) AS totalTotalTerima, SUM(renterimaJumlah) AS totalterima FROM rencana_penerimaan WHERE renterimaRpstatusId = 2  AND renterimaThanggarId ='%s' GROUP BY totalUnitkerjaId) AS total ON totalUnitkerjaId=unitkerjaId
	WHERE 
	renterimaRpstatusId = 2 AND
	(
	unitkerjaKodeSistem 
	LIKE CONCAT((SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitkerjaId='%s'),'.','%s')  or
	 
	unitkerjaKodeSistem 
	LIKE CONCAT((SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitkerjaId='%s')) 
	) 
	
	 AND unitkerjaTipeunitId NOT IN(1)
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
		renterimaVolume			AS volume,
		renterimaTarif      AS tarif,
		if((renterimaVolume*renterimaTarif)IS NULL,0,(renterimaVolume*renterimaTarif)) AS total_kali,
		if (totalterima IS NULL,0,totalterima)     AS totalterima,
		renterimaPersenPagu AS pagu,
		renterimaPagu       AS totalpagu,
		renterimaKeterangan AS keterangan		
	FROM unit_kerja_ref 
	LEFT JOIN rencana_penerimaan ON renterimaUnitkerjaId = unitkerjaId AND renterimaThanggarId ='%s'
	LEFT JOIN kode_penerimaan_ref ON renterimaKodeterimaId = kodeterimaId
	LEFT JOIN (SELECT renterimaUnitkerjaId AS totalUnitkerjaId, SUM(renterimaTotalTerima) AS totalTotalTerima, SUM(renterimaJumlah) AS totalterima FROM rencana_penerimaan WHERE  renterimaRpstatusId = 2  AND  renterimaThanggarId ='%s' GROUP BY totalUnitkerjaId) AS total ON totalUnitkerjaId=unitkerjaId
	WHERE renterimaRpstatusId = 2 AND
	(
	unitkerjaKodeSistem 
	LIKE CONCAT((SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitkerjaId='%s'),'.','%s')  or
	 
	unitkerjaKodeSistem 
	LIKE CONCAT((SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitkerjaId='%s')) 
	) 
	
	 AND unitkerjaTipeunitId NOT IN(1)
	ORDER BY unitkerjaKodeSistem,kode ASC
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
              WHERE  renterimaRpstatusId = 2  AND renterimaThanggarId = '%s'
              GROUP BY totalUnitkerjaId) AS total
     ON totalUnitkerjaId = unitkerjaId
WHERE 	renterimaRpstatusId = 2 AND (
	unitkerjaKodeSistem 
	LIKE CONCAT((SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitkerjaId='%s'),'.','%s')  or
	 
	unitkerjaKodeSistem 
	LIKE CONCAT((SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitkerjaId='%s')) 
	) 
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
?>