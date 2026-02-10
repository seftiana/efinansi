<?php

$sql['get_data_detil_usulan_kegiatan'] = "
	SELECT
      SQL_CALC_FOUND_ROWS
		kegdetId as id,
		kegdetKegId as kegiatan_id,
		subprogId,
		kegdetKegRefId as subkegiatan_id,
		subprogNama as kegiatan,
		kegrefId,
		kegrefNama as subkegiatan,
		jeniskegNama as jenis,
		kegdetIsAprove as `approval`,
		kegdetPrioritasId as prioritas_id,
		prioritasNama as prioritas

	FROM
		kegiatan_detail
			JOIN kegiatan ON (kegId = kegdetKegId)
			JOIN kegiatan_ref ON (kegRefId = kegdetKegRefId)
			JOIN sub_program ON (subprogId = kegrefSubProgId)
			JOIN jenis_kegiatan_ref ON (jeniskegId = subprogJeniskegId)
			LEFT JOIN prioritas_ref ON (kegdetPrioritasId=prioritasId)
	WHERE
		kegdetKegId=%s
		%s
		%s
	ORDER BY kegdetPrioritasId, kegrefNama, subprogNama ASC
	LIMIT %s, %s
";
$sql['get_count_data_detil_usulan_kegiatan'] = "
   SELECT FOUND_ROWS() as total
";
/*
$sql['get_count_data_detil_usulan_kegiatan'] = "
	SELECT
		COUNT(kegdetId) as total
	FROM
		kegiatan_detail
			JOIN kegiatan ON (kegId = kegdetKegId)
			JOIN kegiatan_ref ON (kegRefId = kegdetKegRefId)
			JOIN sub_program ON (subprogId = kegrefSubProgId)
			JOIN jenis_kegiatan_ref ON (jeniskegId = subprogJeniskegId)
	WHERE
		kegdetKegId=%s
		%s
		%s
";
*/
$sql['get_data_detil_usulan_kegiatan_by_id'] = "
	SELECT
		kegdetId as id,
		kegdetKegId as kegiatan_id,
		subprogId as subprogram,
		subprogNama as subprogram_label,
		kegrefId as kegiatanref,
		kegrefNama as kegiatanref_label,
		jeniskegNama as jenis,
     kegdetDeskripsi as deskripsi,
     kegdetCatatan as catatan,
     kegdetOutPut as output,
     kegdetWaktuMulaiPelaksanaan as waktu_mulai,
     kegdetWaktuSelesaiPelaksanaan as waktu_selesai,
	    kegdetPrioritasId,
		kegdetMasTUK,
		kegdetMasTk,
		kegdetKelTUK,
		kegdetKelTk,
		kegdetIkkId as ikk_id,
		ikkNama as ikk_nama,
		kegdetIkuId as iku_id,
		ikuNama as iku_nama,
		kegdetRkaklOutputId as output_id,
		rkaklOutputNama as output_nama,
		kegdetTupoksiId as tupoksi_id,
		tupoksiNama as tupoksi_nama
	FROM
		kegiatan_detail
			JOIN kegiatan ON (kegId = kegdetKegId)
			JOIN kegiatan_ref ON (kegRefId = kegdetKegRefId)
			JOIN sub_program ON (subprogId = kegrefSubProgId)
			JOIN jenis_kegiatan_ref ON (jeniskegId = subprogJeniskegId)
			LEFT JOIN finansi_pa_ref_ikk ON kegdetIkkId = ikkId
			LEFT JOIN finansi_pa_ref_iku ON kegdetIkuId = ikuId
			LEFT JOIN finansi_ref_rkakl_output ON kegdetRkaklOutputId = rkaklOutputId
			LEFT JOIN finansi_pa_ref_tupoksi 
				ON finansi_pa_ref_tupoksi.tupoksiId = kegiatan_detail.kegdetTupoksiId
	WHERE
		kegdetId=%s
";

$sql['get_data_usulan_kegiatan_by_id']="
	SELECT
		kegId as kegiatan_id,
		kegThanggarId as tahun_anggaran,
		thanggarNama as tahun_anggaran_label,
		unitkerjaId AS satker,
		unitkerjaNama AS satker_label,
		unitkerjaId AS unitkerja,
		unitkerjaNama AS unitkerja_label,
		programId as program,
		programNama as program_label,
		kegLatarBelakang as latarbelakang
	FROM unit_kerja_ref
		JOIN kegiatan ON (unitkerjaId = kegUnitkerjaId)
		JOIN program_ref ON (programId = kegProgramId)
		JOIN tahun_anggaran ON (thanggarId = kegThanggarId)
	WHERE
		kegId=%s
";
/**
$sql['get_data_usulan_kegiatan_by_id_old']="
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
		kegLatarBelakang as latarbelakang
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
$sql['do_add_detil_usulan_kegiatan']="
	INSERT INTO kegiatan_detail
	SET
      kegdetKegId = '%s',
      kegdetKegrefId = '%s',
      kegdetDeskripsi ='%s',
      kegdetCatatan='%s',
      kegdetOutPut='%s',
      kegdetWaktuMulaiPelaksanaan ='%s',
      kegdetWaktuSelesaiPelaksanaan ='%s',
      kegdetPrioritasId = '%s',
      kegdetMasTUK = '%s',
      kegdetMasTk = '%s',
      kegdetKelTUK = '%s',
      kegdetKelTk = '%s',
      kegdetIkkId =NULLIF('%s',''),
      kegdetIkuId = NULLIF('%s',''),
      kegdetRkaklOutputId = NULLIF('%s',''),
      kegdetTupoksiId = NULLIF('%s',''),
      kegdetUserId = '%s'
";

$sql['do_update_detil_usulan_kegiatan']="
   UPDATE
      kegiatan_detail
   SET
      kegdetKegId='%s',
      kegdetKegrefId='%s',
      kegdetDeskripsi='%s',
      kegdetCatatan='%s',
      kegdetOutPut='%s',
      kegdetWaktuMulaiPelaksanaan=NULLIF('%s',''),
      kegdetWaktuSelesaiPelaksanaan=NULLIF('%s',''),
	  kegdetPrioritasId='%s',
	  kegdetMasTUK ='%s',
	  kegdetMasTk ='%s',
	  kegdetKelTUK ='%s',
	  kegdetKelTk ='%s',
	  kegdetIkkId ='%s',
	  kegdetIkuId ='%s',
	  kegdetRkaklOutputId ='%s', 
	  kegdetTupoksiId ='%s',
	  kegdetUserId = '%s' 
   WHERE
      kegdetId='%s'
";

$sql['do_delete_detil_usulan_kegiatan_by_id']="
	DELETE from kegiatan_detail
   WHERE
      kegdetId='%s'
";

$sql['do_delete_detil_usulan_kegiatan_by_array_id']="
	DELETE from kegiatan_detail
   WHERE
      kegdetId IN ('%s')
";

//COMBO
$sql['get_combo_jenis_kegiatan']="
	SELECT
		jeniskegId as id,
		jeniskegNama as name
	FROM
		jenis_kegiatan_ref
   WHERE jeniskegId < 3
	ORDER BY jeniskegNama
";

$sql['get_combo_prioritas']="
	SELECT
		prioritasId  as id,
		prioritasNama as name
	FROM
		prioritas_ref
	ORDER BY prioritasId ASC
";
/*
$sql['get_min_max_waktu_pelaksanaan'] = "
   SELECT
      YEAR(MIN(thanggarBuka)) as `awal`,
      DATE(NOW()) as `selected`,
      (YEAR(MAX(thanggarTutup))+2) as `akhir`
   FROM
      tahun_anggaran
";
*/
?>
