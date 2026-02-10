<?php

/**
 * 
 * lappendapatanbelanjaagregat
 * @package lap_pendapatan_belanja_agregat
 * untuk kebutuhan unsri
 * @todo Untuk olah data
 * @subpackage business
 * @since 10 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
/**
 * TODO : untuk menampilkan data laporan
 */
 
$sql['get_data_laporan_p']  ="
SELECT 
  pb.`paguBasId` AS map_id,
  pb.`paguBasKode` AS map_kode,
  pb.`paguBasKeterangan` AS map_nama,
  kp.`kodeterimaKode` AS kode_penerimaan_kode,
  kp.`kodeterimaNama` AS kode_penerimaan_nama,  
  SUM(
  IF(rpen.`renterimaThanggarId`  = '%s',
  IFNULL(real_pen.`realterimaTotalTerima`,0),0)) AS realisasi_sebelum,
  SUM(
  IF(rpen.`renterimaThanggarId`  = '%s',
  rpen.`renterimaTotalTerima`,0)
  ) AS nominal_sekarang
FROM
  rencana_penerimaan rpen 
  LEFT JOIN kode_penerimaan_ref kp 
    ON kp.`kodeterimaId` = rpen.`renterimaKodeterimaId`
  LEFT JOIN finansi_ref_pagu_bas pb 
    ON pb.`paguBasId` = kp.`kodeterimaPaguBasId` 
  LEFT JOIN unit_kerja_ref uk 
    ON uk.`unitkerjaId` = rpen.`renterimaUnitkerjaId` 
  LEFT JOIN realisasi_penerimaan real_pen 
    ON real_pen.`realrenterimaId` = rpen.`renterimaId` 
WHERE rpen.`renterimaRpstatusId` = '2' 
  AND rpen.`renterimaThanggarId` IN('%s','%s') 
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
 GROUP BY map_kode,kode_penerimaan_kode 
";
$sql['get_data_laporan_p_per_map']  ="
SELECT 
  pb.`paguBasId` AS map_id,
  SUM(
  IF(rpen.`renterimaThanggarId`  = '%s',
  IFNULL(real_pen.`realterimaTotalTerima`,0),0)) AS realisasi_sebelum,
  SUM(
  IF(rpen.`renterimaThanggarId`  = '%s',
  rpen.`renterimaTotalTerima`,0)
  ) AS nominal_sekarang
FROM
  rencana_penerimaan rpen 
  LEFT JOIN kode_penerimaan_ref kp 
    ON kp.`kodeterimaId` = rpen.`renterimaKodeterimaId`
  LEFT JOIN finansi_ref_pagu_bas pb 
    ON pb.`paguBasId` = kp.`kodeterimaPaguBasId` 
  LEFT JOIN unit_kerja_ref uk 
    ON uk.`unitkerjaId` = rpen.`renterimaUnitkerjaId` 
  LEFT JOIN realisasi_penerimaan real_pen 
    ON real_pen.`realrenterimaId` = rpen.`renterimaId` 
WHERE rpen.`renterimaRpstatusId` = '2' 
  AND rpen.`renterimaThanggarId` IN('%s','%s') 
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
 GROUP BY map_id
";
$sql['get_data_laporan_p_old']  ="
SELECT
pb.`paguBasKode` AS mak_kode,
pb.`paguBasKeterangan` AS mak_nama,
SUM(rpen.`renterimaTotalTerima`) AS nominal,
SUM(real_pen.`realterimaTotalTerima`) AS realisasi
FROM
rencana_penerimaan rpen
LEFT JOIN kode_penerimaan_ref kp
ON kp.`kodeterimaId` = rpen.`renterimaId`
LEFT JOIN finansi_ref_pagu_bas pb
ON pb.`paguBasId` = kp.`kodeterimaPaguBasId`
LEFT JOIN unit_kerja_ref uk
ON uk.`unitkerjaId` = rpen.`renterimaUnitkerjaId`
LEFT JOIN realisasi_penerimaan real_pen
ON real_pen.`realrenterimaId` = rpen.`renterimaId`
WHERE 
rpen.`renterimaRpstatusId` = '2'
AND
rpen.`renterimaThanggarId` = '%s'
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
";

$sql['get_data_laporan_b']="
SELECT
sd.`sumberdanaId` AS sumber_dana_id,
sd.`sumberdanaNama` AS sumber_dana_nama,
CONCAT(sd.`sumberdanaId`,pb_p.`paguBasId`) AS mak_parent_sd_id,
pb_p.`paguBasId` AS mak_parent_id,
pb_p.`paguBasKode` AS mak_parent_kode,
pb_p.`paguBasKeterangan` AS mak_parent_nama,
pb.`paguBasKode` AS mak_kode,
pb.`paguBasKeterangan` AS mak_nama,
SUM(
IF(pr.`programThanggarId` = '%s',
IFNULL(peng_real.pengrealNominalAprove,0),0)

) AS realisasi_sebelum,
SUM( 
IF(pr.`programThanggarId` = '%s',
(rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    ))
    ,0)
   ) AS nominal_sekarang
FROM
rencana_pengeluaran rpeng
 LEFT JOIN komponen komp ON komp.kompKode = rpeng.rncnpengeluaranKomponenKode 
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k ON  kd.kegdetKegId = k.kegId
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
LEFT JOIN kegiatan_ref kr ON kr.kegrefId = kd.kegdetKegrefId
LEFT JOIN sub_program sp ON kr.kegrefSubprogId = sp.subprogId
LEFT JOIN program_ref pr ON sp.subprogProgramId = pr.programId
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN finansi_ref_pagu_bas pb_p ON pb_p.`paguBasId` = pb.`paguBasParentId`
LEFT JOIN finansi_ref_sumber_dana sd ON sd.`sumberdanaId` = komp.`kompSumberDanaId`
LEFT JOIN pengajuan_realisasi peng_real ON peng_real.pengrealKegdetId=  kd.`kegdetId`
WHERE
 (rpeng.`rncnpengeluaranIsAprove` = 'Ya' OR peng_real.`pengrealIsApprove` = 'Ya')
 AND
 pr.`programThanggarId` IN('%s','%s')
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
GROUP BY mak_kode
ORDER BY mak_parent_kode,mak_kode
";

$sql['get_total_laporan_b_per_sd']="
SELECT
pb_p.`paguBasId` AS mak_parent_id,
SUM(
IF(pr.`programThanggarId` = '%s',
IFNULL(peng_real.pengrealNominalAprove,0),0)

) AS realisasi_sebelum,
SUM( 
IF(pr.`programThanggarId` = '%s',
(rpeng.rncnpengeluaranSatuan * rpeng.rncnpengeluaranKomponenNominal * IF(
      komp.kompFormulaHasil = '0',
      1,
      IFNULL(komp.kompFormulaHasil, 1)
    ))
    ,0)
   ) AS nominal_sekarang
FROM
rencana_pengeluaran rpeng
 LEFT JOIN komponen komp ON komp.kompKode = rpeng.rncnpengeluaranKomponenKode 
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k ON  kd.kegdetKegId = k.kegId
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
LEFT JOIN kegiatan_ref kr ON kr.kegrefId = kd.kegdetKegrefId
LEFT JOIN sub_program sp ON kr.kegrefSubprogId = sp.subprogId
LEFT JOIN program_ref pr ON sp.subprogProgramId = pr.programId
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN finansi_ref_pagu_bas pb_p ON pb_p.`paguBasId` = pb.`paguBasParentId`
LEFT JOIN finansi_ref_sumber_dana sd ON sd.`sumberdanaId` = komp.`kompSumberDanaId`
LEFT JOIN pengajuan_realisasi peng_real ON peng_real.pengrealKegdetId=  kd.`kegdetId`
WHERE
 (rpeng.`rncnpengeluaranIsAprove` = 'Ya'
 OR
 peng_real.`pengrealIsApprove` = 'Ya')
 AND
 pr.`programThanggarId` IN('%s','%s')
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
GROUP BY mak_parent_id
";
$sql['get_data_laporan']="
SELECT 
  * 
FROM
  (
    (SELECT 
      pbt.`paguBasTipeId` AS id_tipe,
      pbt.`paguBasTipeKode` AS kode_tipe,
      pbt.`paguBasTipeNama` AS nama_tipe,
      CONCAT(
        pbt.`paguBasTipeId`,
        pbp.`paguBasId`
      ) AS id_pagu,
      pbp.`paguBasKode` AS kode_pagu,
      pbp.`paguBasKeterangan` AS nama_pagu,
      pb.`paguBasKode` AS kode_mak,
      pb.`paguBasKeterangan` AS nama_mak,
      SUM(rpen.`renterimaTotalTerima`) AS nominal,
      rpen.`renterimaThanggarId` AS tahun_anggaran_id,
      uk.`unitkerjaKodeSistem` AS unit_kerja_kode_sistem 
    FROM
      rencana_penerimaan rpen 
      LEFT JOIN kode_penerimaan_ref kpen 
        ON kpen.`kodeterimaId` = rpen.`renterimaKodeterimaId` 
      LEFT JOIN finansi_ref_pagu_bas_tipe_bas pbtb 
        ON pbtb.`paguBasId` = kpen.`kodeterimaPaguBasId` 
      LEFT JOIN finansi_ref_pagu_bas pb 
        ON pb.`paguBasId` = pbtb.`paguBasId` 
      LEFT JOIN finansi_ref_pagu_bas_tipe pbt 
        ON pbt.`paguBasTipeId` = pbtb.`paguBasTipeId` 
      LEFT JOIN finansi_ref_pagu_bas pbp 
        ON pbp.`paguBasId` = pb.`paguBasParentId` 
      LEFT JOIN unit_kerja_ref uk 
        ON uk.`unitkerjaId` = rpen.`renterimaUnitkerjaId` 
    WHERE rpen.`renterimaRpstatusId` = 2 
    GROUP BY kode_mak 
    ORDER BY id_tipe,
      nama_tipe,
      kode_mak 
   
    ) 
    UNION
    (SELECT 
      pbt.`paguBasTipeId` AS id_tipe,
      pbt.`paguBasTipeKode` AS kode_tipe,
      pbt.`paguBasTipeNama` AS nama_tipe,
      CONCAT(
        pbt.`paguBasTipeId`,
        pbp.`paguBasId`
      ) AS id_pagu,
      pbp.`paguBasKode` AS kode_pagu,
      pbp.`paguBasKeterangan` AS nama_pagu,
      pb.`paguBasKode` AS kode_mak,
      pb.`paguBasKeterangan` AS nama_mak,
      SUM(
        rpeng.`rncnpengeluaranSatuan` * rpeng.`rncnpengeluaranKomponenNominal`
      ) AS nominal,
      k.`kegThanggarId` AS tahun_anggaran_id,
      uk.`unitkerjaKodeSistem` AS unit_kerja_kode_sistem 
    FROM
      rencana_pengeluaran rpeng 
      LEFT JOIN finansi_ref_pagu_bas_tipe_bas pbtb 
        ON pbtb.`paguBasId` = rpeng.`rncnpengeluaranMakId` 
      LEFT JOIN finansi_ref_pagu_bas pb 
        ON pb.`paguBasId` = pbtb.`paguBasId` 
      LEFT JOIN finansi_ref_pagu_bas_tipe pbt 
        ON pbt.`paguBasTipeId` = pbtb.`paguBasTipeId` 
      LEFT JOIN finansi_ref_pagu_bas pbp 
        ON pbp.`paguBasId` = pb.`paguBasParentId` 
      LEFT JOIN kegiatan_detail kd 
        ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId` 
      LEFT JOIN kegiatan k 
        ON k.`kegId` = kd.`kegdetKegId` 
      LEFT JOIN unit_kerja_ref uk 
        ON uk.`unitkerjaId` = k.`kegUnitkerjaId` 
    WHERE rpeng.`rncnpengeluaranIsAprove` = 'Ya' 
    GROUP BY kode_mak 
    ORDER BY id_tipe,
      nama_tipe,
      kode_mak 
   
 )
  ) p 
WHERE p.tahun_anggaran_id = '%s' 
  AND (
    p.unit_kerja_kode_sistem LIKE 
    (SELECT 
      unitkerjaKodeSistem 
    FROM
      unit_kerja_ref 
    WHERE unitkerjaId = '%s') 
    OR p.unit_kerja_kode_sistem LIKE CONCAT(
      (SELECT 
        unitkerjaKodeSistem 
      FROM
        unit_kerja_ref 
      WHERE unitkerjaId = '%s'),
      '.',
      '%s'
    )
  )
";

$sql['get_count_data']="
SELECT 
count(kl_id) as total 
FROM(
(
SELECT 
  kjl.`paguBasKelJnsLapRefId` AS kl_id,
  kjl.`paguBasKelJnsLapRefParentId` AS kl_parent_id 
FROM
  finansi_pa_pagu_bas_kelompok_jenis_laporan_ref kjl 
)
UNION
(SELECT 
  CONCAT(kl.`paguBasKelLapRefJnsLapRefId`,'.',kl.`paguBasKelLapRefId`) AS kl_id ,
  kl.`paguBasKelLapRefJnsLapRefId` AS kl_parent_id
FROM
  finansi_pa_pagu_bas_kelompok_laporan_ref  kl
  )
UNION
(
SELECT 
  CONCAT(kl.`paguBasKelLapRefJnsLapRefId`,'.',kl.`paguBasKelLapRefId`,'.',pb.`paguBasId`) AS kl_id,
  CONCAT(kl.`paguBasKelLapRefJnsLapRefId`,'.',kl.`paguBasKelLapRefId`) AS kl_parent_id 
FROM
  `finansi_pa_pagu_bas_kelompok_laporan_ref` kl 
  left JOIN `finansi_pa_pagu_bas_kelompok_laporan_ref_pagu_bas` klpb
  ON klpb.`paguBasKelLapKelLapRefId` = kl.`paguBasKelLapRefId`
  inner JOIN finansi_ref_pagu_bas pb
  ON (pb.`paguBasId` = klpb.`paguBasKelLapPaguBasId` AND pb.`paguBasStatusAktif` = 'Y')
)
) kelompok
WHERE 
kelompok.kl_parent_id = %s
";

$sql['get_nominal_mak']="
SELECT 
  SUM((rp.`rncnpengeluaranSatuan` *
 rp.`rncnpengeluaranKomponenNominal`)) AS kl_nominal
FROM
  `finansi_pa_pagu_bas_kelompok_laporan_ref` kl2 
  LEFT JOIN `finansi_pa_pagu_bas_kelompok_jenis_laporan_ref` klj
  ON klj.`paguBasKelJnsLapRefId` = kl2.`paguBasKelLapRefJnsLapRefId`
  LEFT JOIN `finansi_pa_pagu_bas_kelompok_laporan_ref_pagu_bas` klpb
  ON klpb.`paguBasKelLapKelLapRefId` = kl2.`paguBasKelLapRefId`
  LEFT JOIN finansi_ref_pagu_bas pb
  ON (pb.`paguBasId` = klpb.`paguBasKelLapPaguBasId` AND pb.`paguBasStatusAktif` = 'Y')
  LEFT JOIN rencana_pengeluaran rp 
  ON rp.`rncnpengeluaranMakId` = pb.`paguBasId`
WHERE 
klj.`paguBasKelJnsLapRefKodeSistem` LIKE CONCAT(
(SELECT 
 klj2.`paguBasKelJnsLapRefKodeSistem`
FROM `finansi_pa_pagu_bas_kelompok_jenis_laporan_ref` klj2 WHERE klj2.`paguBasKelJnsLapRefId` = '%s'))
OR
klj.`paguBasKelJnsLapRefKodeSistem` LIKE CONCAT(
(SELECT 
 klj2.`paguBasKelJnsLapRefKodeSistem`
FROM `finansi_pa_pagu_bas_kelompok_jenis_laporan_ref` klj2 WHERE klj2.`paguBasKelJnsLapRefId` = '%s'),'.','%s')
";


$sql['get_nilai_proyeksi']="
SELECT 
	settingValue AS nilai
FROM 
	setting 
WHERE 
	settingName = 'nilai_proyeksi'
LIMIT 0,1	
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
  `thanggarId` AS `id`,
  `thanggarNama` AS `name`,
  `thanggarBuka` AS tgl_buka,
  `thanggarTutup` AS tgl_tutup,
  (YEAR(`thanggarBuka`)) AS `tahun_buka`,
  (YEAR(`thanggarTutup`)) AS `tahun_tutup` 
FROM
  `tahun_anggaran`
WHERE
  `thanggarId` = '%s'
";

$sql['get_tahun_anggaran_kemarin']="
SELECT 
  `thanggarId` AS `id`,
  `thanggarNama` AS `name`,
  `thanggarBuka` AS tgl_buka,
  `thanggarTutup` AS tgl_tutup,
  (YEAR(`thanggarBuka`)) AS `tahun_buka`,
  (YEAR(`thanggarTutup`)) AS `tahun_tutup` 
FROM
  `tahun_anggaran`
 WHERE 
 `thanggarBuka` < (
SELECT 
  `thanggarBuka`
FROM
  `tahun_anggaran`
 WHERE 
 thanggarId ='%s'
 )
ORDER BY tgl_buka DESC LIMIT 1
";

?>