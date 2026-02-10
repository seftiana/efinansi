<?php
/*SELECT
	unitkerjaId AS subUnitId,
	(if(tempUnitKode IS NULL,unitkerjaKode,tempUnitKode)) AS sat_kode,
	(if(tempUnitNama IS NULL,unitkerjaNama,tempUnitNama)) AS sat_nama,
	(if(tempUnitKode IS NULL,'-',unitkerjaKode)) AS unit_kode,
	(if(tempUnitNama IS NULL,'-',unitkerjaNama)) AS unit_nama,
	unitkerjaNama AS subUnitNama,
	unitkerjaParentId AS paretnId
FROM unit_kerja_ref
LEFT JOIN
(SELECT
	unitkerjaId AS tempUnitId,
	unitkerjaKode AS tempUnitKode,
	unitkerjaNama AS tempUnitNama,
	unitkerjaParentId AS tempParetnId
FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUniKerja ON(unitkerjaParentId=tempUnitId)
;
*/
/**
$sql['get_data_usulan_kegiatan'] = "
	SELECT
		kegId as id,
		kegProgramId as program_id,
		(if(tempUnitId IS NULL,unitkerjaId,tempUnitId)) AS idsatker,
		(if(tempUnitKode IS NULL,unitkerjaKode,tempUnitKode)) AS kodesatker,
		(if(tempUnitNama IS NULL,unitkerjaNama,tempUnitNama)) AS satker,
		(if(tempUnitId IS NULL,'-',unitkerjaId)) AS idunit,
		(if(tempUnitKode IS NULL,'-',unitkerjaKode)) AS kodeunit,
		(if(tempUnitNama IS NULL,'-',unitkerjaNama)) AS unit,
		programNomor as kodeprogram,
		programNama as program,
		kegLatarBelakang as latarbelakang,
		unitkerjaNama AS subUnitNama,
		unitkerjaParentId AS paretnId
	FROM unit_kerja_ref
		LEFT JOIN
			(SELECT
				unitkerjaId AS tempUnitId,
				unitkerjaKode AS tempUnitKode,
				unitkerjaNama AS tempUnitNama,
				unitkerjaParentId AS tempParetnId
			FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUniKerja ON(unitkerjaParentId=tempUnitId)
		JOIN kegiatan ON (unitkerjaId = kegUnitkerjaId)
		JOIN program_ref ON (programId = kegProgramId)
	WHERE
		(programNomor = '%s' OR programNama LIKE '%s') 		
		%s
		%s
		%s
	ORDER BY satker,unit
	LIMIT %s, %s
";
*/
$sql['get_data_usulan_kegiatan'] = "
	SELECT
		kegId as id,
		kegProgramId as program_id,
		unitkerjaId AS idunit,
		unitkerjaKode AS kodeunit,
		unitkerjaNama AS unit,
		programNomor as kodeprogram,
		programNama as program,
		kegLatarBelakang as latarbelakang,
		unitkerjaNama AS subUnitNama,
		unitkerjaParentId AS paretnId
	FROM unit_kerja_ref
		
		JOIN kegiatan ON (unitkerjaId = kegUnitkerjaId)
		JOIN program_ref ON (programId = kegProgramId)
	WHERE
		(programNomor = '%s' OR programNama LIKE '%s') 		
		%s
		%s
		%s
	ORDER BY unitkerjaKodeSistem ASC
	LIMIT %s, %s
";

$sql['get_count_data_usulan_kegiatan'] = "
	SELECT
		COUNT(kegId) as total
	FROM unit_kerja_ref
		LEFT JOIN
			(SELECT
				unitkerjaId AS tempUnitId,
				unitkerjaKode AS tempUnitKode,
				unitkerjaNama AS tempUnitNama,
				unitkerjaParentId AS tempParetnId
			FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUniKerja ON(unitkerjaParentId=tempUnitId)
		JOIN kegiatan ON (unitkerjaId = kegUnitkerjaId)
		JOIN program_ref ON (programId = kegProgramId)
	WHERE
		(programNomor = '%s' OR programNama LIKE '%s') 
		%s 
		%s
		%s
";
/**
$sql['get_data_usulan_kegiatan_by_id']="
	SELECT
		kegId as kegiatan_id,
		kegThanggarId as tahun_anggaran,
		thanggarNama as tahun_anggaran_label,
		(if(tempUnitId IS NULL,unitkerjaId,tempUnitId)) AS satker,
		(if(tempUnitNama IS NULL,unitkerjaNama,tempUnitNama)) AS satker_label,
		(if(tempUnitId IS NULL,'-',unitkerjaId)) AS unitkerja,
		(if(tempUnitNama IS NULL,'-',unitkerjaNama)) AS unitkerja_label,
		programId as program,
		programNama as program_label,
		kegLatarBelakang as latarbelakang,
		kegIndikator as indikator,
		kegBaseline as baseline,
		kegFinal as final,
		kegPimpinanSatuanKerja AS satker_pimpinan,
		kegPimpinanUnitKerja AS unitkerja_pimpinan,
		kegPIC AS nama_pic
	FROM unit_kerja_ref
		LEFT JOIN
			(SELECT
				unitkerjaId AS tempUnitId,
				unitkerjaKode AS tempUnitKode,
				unitkerjaNama AS tempUnitNama,
				unitkerjaParentId AS tempParetnId
			FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUniKerja ON(unitkerjaParentId=tempUnitId)
		JOIN kegiatan ON (unitkerjaId = kegUnitkerjaId)
		JOIN program_ref ON (programId = kegProgramId)
		JOIN tahun_anggaran ON (thanggarId = kegThanggarId)
	WHERE
		kegId=%s
";
*/

$sql['get_data_usulan_kegiatan_by_id']="
	SELECT
		kegId as kegiatan_id,
		kegThanggarId as tahun_anggaran,
		thanggarNama as tahun_anggaran_label,
		unitkerjaId AS unitkerja,
		unitkerjaNama AS unitkerja_label,
		programId as program,
		programNama as program_label,
		kegLatarBelakang as latarbelakang,
		kegIndikator as indikator,
		kegBaseline as baseline,
		kegFinal as final,
		kegPimpinanSatuanKerja AS satker_pimpinan,
		kegPimpinanUnitKerja AS unitkerja_pimpinan,
		kegPIC AS nama_pic
	FROM unit_kerja_ref
		JOIN kegiatan ON (unitkerjaId = kegUnitkerjaId)
		JOIN program_ref ON (programId = kegProgramId)
		JOIN tahun_anggaran ON (thanggarId = kegThanggarId)
	WHERE
		kegId=%s
";

$sql['get_satker_pimpinan_by_id'] = "
	SELECT
      kegPimpinanSatuanKerja AS satker_pimpinan,
      unitkerjaNama AS satker_pimpinan_label
   FROM kegiatan
   JOIN unit_kerja_ref ON kegPimpinanSatuanKerja=unitkerjaId
   WHERE
      kegId=%s
";

$sql['get_unit_kerja_pimpinan_by_id'] = "
	SELECT
      kegPimpinanUnitKerja AS unitkerja_pimpinan,
      unitkerjaNama AS unitkerja_pimpinan_label
   FROM kegiatan
   JOIN unit_kerja_ref ON kegPimpinanUnitKerja=unitkerjaId
   WHERE
      kegId=%s
";

$sql['do_add_usulan_kegiatan']="
	INSERT INTO
		kegiatan(
		    kegUnitkerjaId, 
		    kegProgramId, 
		    kegLatarBelakang, 
		    kegIndikator, 
		    kegBaseline, 
		    kegFinal, 
		    kegThanggarId,
		    kegPimpinanSatuanKerja,
		    kegPimpinanUnitKerja,
		    kegPIC,
		    kegUserId)
	VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s')
";
$sql['check_kegiatan'] = "
    SELECT COUNT(DISTINCT kegId) AS kegiatan_count FROM kegiatan 
    WHERE kegUnitkerjaId = '%s' AND kegProgramId = '%s' AND kegThanggarId = '%s'
";
$sql['do_update_usulan_kegiatan']="
	UPDATE
		kegiatan
	SET
		kegUnitkerjaId=%s,
		kegProgramId=%s,
		kegLatarBelakang=%s,
		kegIndikator=%s,
		kegBaseline=%s,
		kegFinal=%s,
		kegThanggarId=%s,
		kegPimpinanSatuanKerja=%s,
      kegPimpinanUnitKerja=%s,
      kegPIC=%s,
      kegUserId=%s
	WHERE
		kegId=%s
";
//COMBO
$sql['get_combo_tahun_anggaran']="
	SELECT
		thanggarId as id,
		thanggarNama as name
	FROM
		tahun_anggaran
	ORDER BY thanggarNama DESC
";

//aktif
$sql['get_tahun_anggaran_aktif']="
	SELECT
		thanggarId as id,
		thanggarNama as name
	FROM
		tahun_anggaran
	WHERE
		thanggarIsAktif='Y'
";

$sql['do_delete_usulankegiatan_by_id']="
	DELETE from kegiatan
   WHERE
      kegId='%s'
";

$sql['do_deleteusulankegiatan_by_array_id']="
	DELETE from kegiatan
   WHERE
      kegId IN ('%s')
";

$sql['get_unit'] = "
SELECT ukr.unitkerjaId AS id, 
ukr.unitkerjaNama AS nama, 
ukr.unitkerjaKode AS kode,
ukr.unitkerjaKodeSistem as kodeSistem,
ukr.`unitKerjaJenisId` AS jenis,
ukr.`unitkerjaParentId` AS parent,
ukr.unitkerjaNamaPimpinan as pimpinan,
usr.`RealName` AS namaUser           
FROM user_unit_kerja AS uk 
JOIN unit_kerja_ref AS ukr ON uk.userunitkerjaUnitkerjaId = ukr.unitkerjaId 
JOIN gtfw_user AS usr ON uk.`userunitkerjaUserId` = usr.`UserId`

WHERE uk.userunitkerjaUserId = '%s'
";

/**
 * Untuk mendapatkan total sub unit
 * added
 * @since 02 januari 2012
 */
$sql['get_total_sub_unit']="
	SELECT COUNT(unitkerjaId) AS total FROM unit_kerja_ref WHERE unitkerjaParentId = %s
";

?>
