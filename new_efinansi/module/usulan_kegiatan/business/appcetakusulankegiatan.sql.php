<?php
/**
$sql['get_data_program']="
	SELECT 
		kegId as kegiatan_id,
		kegThanggarId as tahun_anggaran,
		thanggarNama as tahun_anggaran_label,
		(if(tempUnitId IS NULL,unitkerjaId,tempUnitId)) AS satker,
		(if(tempUnitKode IS NULL,unitkerjaKode,tempUnitKode)) AS satker_kode,
		(if(tempUnitNama IS NULL,unitkerjaNama,tempUnitNama)) AS satker_label,
		(if(tempUnitNamaPimpinan IS NULL,unitkerjaNamaPimpinan,tempUnitNamaPimpinan)) AS satker_pimpinan_label,
		(if(tempUnitId IS NULL,'-',unitkerjaId)) AS unitkerja,
		(if(tempUnitKode IS NULL,'-',unitkerjaKode)) AS unitkerja_kode,
		(if(tempUnitNama IS NULL,'-',unitkerjaNama)) AS unitkerja_label,
		(if(tempUnitNamaPimpinan IS NULL,'-',unitkerjaNamaPimpinan)) AS unitkerja_pimpinan_label,
		programId as program,
		programNomor as program_kode,
		programNama as program_label,
		(if(kegLatarBelakang IS NULL,'-',kegLatarBelakang)) as latarbelakang,
		(if(kegIndikator IS NULL,'-',kegIndikator)) as indikator,
		(if(kegBaseline IS NULL,'-',kegBaseline)) as baseline,
		(if(kegFinal IS NULL,'-',kegFinal)) as `final`
	FROM unit_kerja_ref
		LEFT JOIN 
			(SELECT 
				unitkerjaId AS tempUnitId,
				unitkerjaKode AS tempUnitKode,
				unitkerjaNama AS tempUnitNama,
				unitkerjaNamaPimpinan AS tempUnitNamaPimpinan,
				unitkerjaParentId AS tempParetnId
			FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUniKerja ON(unitkerjaParentId=tempUnitId)
		JOIN kegiatan ON (unitkerjaId = kegUnitkerjaId)
		JOIN program_ref ON (programId = kegProgramId)
		JOIN tahun_anggaran ON (thanggarId = kegThanggarId)
	WHERE 
		kegId=%s
";
*/

$sql['get_data_program']="
	SELECT 
		kegId as kegiatan_id,
		kegThanggarId as tahun_anggaran,
		thanggarNama as tahun_anggaran_label,
		unitkerjaId AS unitkerja,
		unitkerjaKode AS unitkerja_kode,
		unitkerjaNama AS unitkerja_label,
		unitkerjaNamaPimpinan AS unitkerja_pimpinan_label,
		programId as program,
		programNomor as program_kode,
		programNama as program_label,
		(if(kegLatarBelakang IS NULL,'-',kegLatarBelakang)) as latarbelakang,
		(if(kegIndikator IS NULL,'-',kegIndikator)) as indikator,
		(if(kegBaseline IS NULL,'-',kegBaseline)) as baseline,
		(if(kegFinal IS NULL,'-',kegFinal)) as `final`
	FROM unit_kerja_ref
		JOIN kegiatan ON (unitkerjaId = kegUnitkerjaId)
		JOIN program_ref ON (programId = kegProgramId)
		JOIN tahun_anggaran ON (thanggarId = kegThanggarId)
	WHERE 
		kegId=%s
";
$sql['get_kegiatan'] = "
	SELECT
		kegdetId as id,
		kegdetKegId as kegiatan_id,
		subprogId,
		kegrefId,
		kegdetKegRefId as subkegiatan_id,
		subprogNomor as kegiatan_kode,
		subprogNama as kegiatan,
		kegrefNomor as subkegiatan_kode,
		kegrefNama as subkegiatan,
		jeniskegNama as jenis_kegiatan,
    kegdetOutPut as output,
    kegdetWaktuMulaiPelaksanaan as waktu_mulai,
    kegdetWaktuSelesaiPelaksanaan as waktu_selesai
	FROM
		kegiatan_detail
			JOIN kegiatan ON (kegId = kegdetKegId)
			JOIN kegiatan_ref ON (kegRefId = kegdetKegRefId)
			JOIN sub_program ON (subprogId = kegrefSubProgId)
			JOIN jenis_kegiatan_ref ON (jeniskegId = subprogJeniskegId)
	WHERE
		kegId=%s
	ORDER BY subprogNomor, kegrefNomor
";
/*
$sql['get_count_data_unitkerja'] = 
   "SELECT 
      count(ukr.unitkerjaId) AS total
   FROM 
      unit_kerja_ref ukr
	  LEFT JOIN tipe_unit_kerja_ref tukr ON (tukr.tipeunitId = ukr.unitkerjaTipeunitId)
	WHERE 
		ukr.unitkerjaKode LIKE '%s'
		AND ukr.unitkerjaNama LIKE '%s'
		AND ukr.unitkerjaParentId <> 0
		%s
		%s";

$sql['get_data_unitkerja'] = 
   "SELECT 
      ukr.unitkerjaId				as unitkerja_id,
	  ukr.unitkerjaKode				as unitkerja_kode,
	  ukr.unitkerjaNama				as unitkerja_nama,
	  ukr.unitkerjaNamaPimpinan		as unitkerja_pimpinan,
	  tukr.tipeunitNama				as tipeunit_nama
   FROM 
      unit_kerja_ref ukr
	  LEFT JOIN tipe_unit_kerja_ref tukr ON (tukr.tipeunitId = ukr.unitkerjaTipeunitId)
	WHERE 
		ukr.unitkerjaKode LIKE '%s'
		AND ukr.unitkerjaNama LIKE '%s'
		AND ukr.unitkerjaParentId <> 0
		%s
		%s
   ORDER BY 
	  ukr.unitkerjaNama
   LIMIT %s, %s";

//untuk combo box

$sql['get_data_tipe_unit'] = 
   "SELECT 
      tipeunitId		as id,
	  tipeunitNama		as name
   FROM 
      tipe_unit_kerja_ref
   ORDER BY 
      tipeunitNama";
*/
?>
