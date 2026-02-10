<?php


/**
 * applaprdpperkegiatan.sql.php
 * @package lap_rpd_per_kegiatan
 * @subpackage business
 * @todo kumpulan query
 * @since mei 2012
 * @copyright 2012 Gamatechno Indonesia
 * @author noor hadi <noor.hadi@gamatechno.com>
 */
 
//===GET===
$sql['get_count_rpd']="
SELECT
	COUNT(rncnpengeluaranId) AS total
FROM
	rencana_pengeluaran
LEFT JOIN kegiatan_detail ON kegdetId = rncnpengeluaranKegdetId
LEFT JOIN kegiatan ON  kegdetKegId = kegId
LEFT JOIN unit_kerja_ref uk ON unitkerjaId= kegUnitkerjaId
LEFT JOIN kegiatan_ref ON kegrefId = kegdetKegrefId
LEFT JOIN sub_program ON kegrefSubprogId = subprogId
LEFT JOIN program_ref ON subprogProgramId = programId
LEFT JOIN jenis_kegiatan_ref ON subprogJeniskegId = jeniskegId
LEFT JOIN finansi_ref_rkakl_subkegiatan ON rkaklSubKegiatanId = kegrefRkaklSubKegiatanId
LEFT JOIN finansi_ref_rkakl_kegiatan ON rkaklKegiatanId = subprogRKAKLKegiatanId
LEFT JOIN finansi_ref_rkakl_prog ON rkaklProgramId = programRKAKLProgramId
LEFT JOIN finansi_pa_ref_ikk ON kegdetIkkId = ikkId
LEFT JOIN finansi_pa_ref_iku ON kegdetIkuId = ikuId
LEFT JOIN finansi_ref_rkakl_output ON kegdetRkaklOutputId = rkaklOutputId
LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
LEFT JOIN finansi_ref_pagu_bas ON (paguBasId = rncnpengeluaranMakId) OR (paguBasId = kompMakId)

	WHERE
	  kegThanggarId=%s
      AND
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
     AND rncnpengeluaranIsAprove= 'Ya'
";
$sql['get_rpd'] = "
SELECT
	programId AS program_id,
	programNomor AS program_nomor,
	programNama AS program_nama,
	rkaklProgramNama AS program_nama_rkakl,
	subprogId AS subprogram_id,
	subprogNomor AS kegiatan_nomor,
	subprogNama AS kegiatan_nama,
	rkaklKegiatanNama AS kegiatan_nama_rkakl,
	kegrefId AS subkegiatan_id,
	kegrefNomor AS subkegiatan_nomor,
	kegrefNama AS subkegiatan_nama,
	kegdetId AS kegiatan_detil_id,
	rkaklSubKegiatanNama AS subkegiatan_nama_rkakl,
	jeniskegNama AS jenis_kegiatan,
	jeniskegId AS jenis_keg_id,
	rncnpengeluaranId AS id,
	rncnpengeluaranKomponenKode AS komponen_kode,
	rncnpengeluaranKomponenNama AS komponen_nama,
	rncnpengeluaranKomponenNominal * IF(kompFormulaHasil=0,1,kompFormulaHasil) AS nominal_usulan,
	rncnpengeluaranSatuan AS satuan_usulan,
	rncnpengeluaranSatuan * rncnpengeluaranKomponenNominal * IF(kompFormulaHasil=0,1,kompFormulaHasil) AS jumlah_usulan,
	rncnpengeluaranKomponenNominalAprove * IF(kompFormulaHasil=0,1,kompFormulaHasil) AS nominal_setuju,
	rncnpengeluaranSatuanAprove AS satuan_setuju,
	rncnpengeluaranNamaSatuan AS nama_satuan,
	rncnpengeluaranSatuanAprove * rncnpengeluaranKomponenNominalAprove * IF(kompFormulaHasil=0,1,kompFormulaHasil) AS jumlah_setuju,
	rncnpengeluaranKomponenDeskripsi AS deskripsi,
	rncnpengeluaranIsAprove AS approval,
	IFNULL(rncnpengeluaranMakId, kompMakId) AS mak_id,
	paguBasKode as makKode,
	paguBasKeterangan as makNama,
	ikkKode,
	ikkNama AS ikk,
	ikuKode,
	ikuNama AS iku,
	rkaklOutputKode,
	rkaklOutputNama AS output,
    uk.unitkerjaNama as unit_subunit,
    uk.unitkerjaId as unit_id
FROM
	rencana_pengeluaran
LEFT JOIN kegiatan_detail ON kegdetId = rncnpengeluaranKegdetId
LEFT JOIN kegiatan ON  kegdetKegId = kegId
LEFT JOIN unit_kerja_ref uk ON unitkerjaId= kegUnitkerjaId
LEFT JOIN kegiatan_ref ON kegrefId = kegdetKegrefId
LEFT JOIN sub_program ON kegrefSubprogId = subprogId
LEFT JOIN program_ref ON subprogProgramId = programId
LEFT JOIN jenis_kegiatan_ref ON subprogJeniskegId = jeniskegId
LEFT JOIN finansi_ref_rkakl_subkegiatan ON rkaklSubKegiatanId = kegrefRkaklSubKegiatanId
LEFT JOIN finansi_ref_rkakl_kegiatan ON rkaklKegiatanId = subprogRKAKLKegiatanId
LEFT JOIN finansi_ref_rkakl_prog ON rkaklProgramId = programRKAKLProgramId
LEFT JOIN finansi_pa_ref_ikk ON kegdetIkkId = ikkId
LEFT JOIN finansi_pa_ref_iku ON kegdetIkuId = ikuId
LEFT JOIN finansi_ref_rkakl_output ON kegdetRkaklOutputId = rkaklOutputId
LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
LEFT JOIN finansi_ref_pagu_bas ON (paguBasId = rncnpengeluaranMakId) OR (paguBasId = kompMakId)
/* LEFT JOIN finansi_ref_mak ON makId = kompMakId*/
	WHERE
	  kegThanggarId=%s
      AND
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
     AND rncnpengeluaranIsAprove= 'Ya'
	ORDER BY 
            program_nomor , 
            kegiatan_nomor, 
            subkegiatan_nomor,
            uk.unitkerjaId,mak_id, 
            rncnpengeluaranKomponenKode 
   LIMIT %s, %s
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

$sql['get_tahun_anggaran_cetak']="
	SELECT
		thanggarId as id,
		thanggarNama as name
	FROM
		tahun_anggaran
	WHERE
      thanggarId = %s
";

$sql['get_unit_kerja']="
	SELECT
     unitkerjaId AS unit_kerja_id,
	  unitkerjaKode AS unit_kerja_kode,
	  unitkerjaNama AS unit_kerja_nama,
	  unitkerjaParentId AS unit_kerja_parent_id,
	  unitkerjaParentId AS is_unit_kerja
   FROM
      unit_kerja_ref
   WHERE
      unitkerjaId=%s;
";

$sql['get_data_rpd_cetak'] = "
SELECT
	programId AS program_id,
	programNomor AS program_nomor,
	programNama AS program_nama,
	rkaklProgramNama AS program_nama_rkakl,
	subprogId AS subprogram_id,
	subprogNomor AS kegiatan_nomor,
	subprogNama AS kegiatan_nama,
	rkaklKegiatanNama AS kegiatan_nama_rkakl,
	kegrefId AS subkegiatan_id,
	kegrefNomor AS subkegiatan_nomor,
	kegrefNama AS subkegiatan_nama,
	kegdetId AS kegiatan_detil_id,
	rkaklSubKegiatanNama AS subkegiatan_nama_rkakl,
	jeniskegNama AS jenis_kegiatan,
	jeniskegId AS jenis_keg_id,
	rncnpengeluaranId AS id,
	rncnpengeluaranKomponenKode AS komponen_kode,
	rncnpengeluaranKomponenNama AS komponen_nama,
	rncnpengeluaranKomponenNominal * IF(kompFormulaHasil=0,1,kompFormulaHasil) AS nominal_usulan,
	rncnpengeluaranSatuan AS satuan_usulan,
	rncnpengeluaranSatuan * rncnpengeluaranKomponenNominal * IF(kompFormulaHasil=0,1,kompFormulaHasil) AS jumlah_usulan,
	rncnpengeluaranKomponenNominalAprove * IF(kompFormulaHasil=0,1,kompFormulaHasil) AS nominal_setuju,
	rncnpengeluaranSatuanAprove AS satuan_setuju,
	rncnpengeluaranNamaSatuan AS nama_satuan,
	rncnpengeluaranSatuanAprove * rncnpengeluaranKomponenNominalAprove * IF(kompFormulaHasil=0,1,kompFormulaHasil) AS jumlah_setuju,
	rncnpengeluaranKomponenDeskripsi AS deskripsi,
	rncnpengeluaranIsAprove AS approval,
	IFNULL(rncnpengeluaranMakId, kompMakId) AS mak_id,
	paguBasKode as makKode,
	paguBasKeterangan as makNama,
	ikkKode,
	ikkNama AS ikk,
	ikuKode,
	ikuNama AS iku,
	rkaklOutputKode,
	rkaklOutputNama AS output,
    uk.unitkerjaNama as unit_subunit,
    uk.unitkerjaId as unit_id
FROM
	rencana_pengeluaran
LEFT JOIN kegiatan_detail ON kegdetId = rncnpengeluaranKegdetId
LEFT JOIN kegiatan ON  kegdetKegId = kegId
LEFT JOIN unit_kerja_ref uk ON unitkerjaId= kegUnitkerjaId
LEFT JOIN kegiatan_ref ON kegrefId = kegdetKegrefId
LEFT JOIN sub_program ON kegrefSubprogId = subprogId
LEFT JOIN program_ref ON subprogProgramId = programId
LEFT JOIN jenis_kegiatan_ref ON subprogJeniskegId = jeniskegId
LEFT JOIN finansi_ref_rkakl_subkegiatan ON rkaklSubKegiatanId = kegrefRkaklSubKegiatanId
LEFT JOIN finansi_ref_rkakl_kegiatan ON rkaklKegiatanId = subprogRKAKLKegiatanId
LEFT JOIN finansi_ref_rkakl_prog ON rkaklProgramId = programRKAKLProgramId
LEFT JOIN finansi_pa_ref_ikk ON kegdetIkkId = ikkId
LEFT JOIN finansi_pa_ref_iku ON kegdetIkuId = ikuId
LEFT JOIN finansi_ref_rkakl_output ON kegdetRkaklOutputId = rkaklOutputId
LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
LEFT JOIN finansi_ref_pagu_bas ON (paguBasId = rncnpengeluaranMakId) OR (paguBasId = kompMakId)
/* LEFT JOIN finansi_ref_mak ON makId = kompMakId*/
	WHERE
	  kegThanggarId=%s
      AND
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
     AND rncnpengeluaranIsAprove= 'Ya'
	ORDER BY 
            program_nomor , 
            kegiatan_nomor, 
            subkegiatan_nomor,
            uk.unitkerjaId,mak_id, 
            rncnpengeluaranKomponenKode 
";

$sql['get_mak'] = "
SELECT 
	SUM(rncnpengeluaranSatuanAprove * 
		rncnpengeluaranKomponenNominalAprove * 
	IF(kompFormulaHasil=0,1,kompFormulaHasil)) AS jumlah_per_mak,
    IFNULL(rncnpengeluaranMakId, kompMakId) AS mak_id,
    paguBasKode as makKode,
    paguBasKeterangan as makNama   
FROM
        rencana_pengeluaran 
        LEFT JOIN kegiatan_detail 
                ON kegdetId = rncnpengeluaranKegdetId 
        LEFT JOIN kegiatan 
                ON kegdetKegId = kegId 
        LEFT JOIN unit_kerja_ref uk 
                ON unitkerjaId = kegUnitkerjaId 
        LEFT JOIN kegiatan_ref 
                ON kegrefId = kegdetKegrefId 
        LEFT JOIN sub_program 
                ON kegrefSubprogId = subprogId 
        LEFT JOIN program_ref 
                ON subprogProgramId = programId 
        LEFT JOIN jenis_kegiatan_ref 
                ON subprogJeniskegId = jeniskegId 
        LEFT JOIN finansi_ref_rkakl_subkegiatan 
                ON rkaklSubKegiatanId = kegrefRkaklSubKegiatanId 
        LEFT JOIN finansi_ref_rkakl_kegiatan 
                ON rkaklKegiatanId = subprogRKAKLKegiatanId 
        LEFT JOIN finansi_ref_rkakl_prog 
                ON rkaklProgramId = programRKAKLProgramId 
        LEFT JOIN finansi_pa_ref_ikk 
                ON kegdetIkkId = ikkId 
        LEFT JOIN finansi_pa_ref_iku 
                ON kegdetIkuId = ikuId 
        LEFT JOIN finansi_ref_rkakl_output 
                ON kegdetRkaklOutputId = rkaklOutputId 
        LEFT JOIN komponen 
                ON kompKode = rncnpengeluaranKomponenKode 
        LEFT JOIN finansi_ref_pagu_bas
                ON (paguBasId = rncnpengeluaranMakId) 
                OR (paguBasId = kompMakId) 
                
WHERE kegThanggarId = '%s' 
        AND (
                uk.unitkerjaKodeSistem LIKE CONCAT(
                        (SELECT 
                                unitkerjaKodeSistem 
                        FROM
                                unit_kerja_ref 
                        WHERE unit_kerja_ref.unitkerjaId = '%s'),
                        '.',
                        '%s'
                ) 
                OR uk.unitkerjaKodeSistem = 
                (SELECT 
                        unitkerjaKodeSistem 
                FROM
                        unit_kerja_ref 
                WHERE unit_kerja_ref.unitkerjaId = '%s')
        ) 
        AND rncnpengeluaranIsAprove = 'Ya' 
GROUP BY mak_id
";
