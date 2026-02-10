<?php

/**
 * get data
 */
$sql['get_generate_nomor_spp_no_pengajuan'] ="
SELECT 
  CONCAT('SPPT/',
    LPAD(IFNULL(MAX(SUBSTRING_INDEX(SUBSTRING_INDEX(sppPengRealNomor, '/', 2),'/',-1) + 0) + 1,1),3,0),'/',
    CASE	
    WHEN MONTH(NOW())= 1 THEN 'I'
    WHEN MONTH(NOW())= 2 THEN 'II'
    WHEN MONTH(NOW())= 3 THEN 'III'
    WHEN MONTH(NOW())= 4 THEN 'IV'
    WHEN MONTH(NOW())= 5 THEN 'V'
    WHEN MONTH(NOW())= 6 THEN 'VI'
    WHEN MONTH(NOW())= 7 THEN 'VII'
    WHEN MONTH(NOW())= 8 THEN 'VIII'
    WHEN MONTH(NOW())= 9 THEN 'IX'
    WHEN MONTH(NOW())= 10 THEN 'X'
    WHEN MONTH(NOW())= 11 THEN 'XI'
    WHEN MONTH(NOW())= 12 THEN 'XII'
    END,'/',YEAR(NOW()),'/'
  ) AS nomor
  
FROM
  `finansi_pa_spp_pengajuan_real`
WHERE 
 SUBSTRING_INDEX(sppPengRealNomor,'/',1)  = 'SPPT'
 AND 
 (SUBSTRING_INDEX(SUBSTRING_INDEX(sppPengRealNomor, '/', 3),'/',-1) )  = (CASE	
    WHEN MONTH(NOW())= 1 THEN 'I'
    WHEN MONTH(NOW())= 2 THEN 'II'
    WHEN MONTH(NOW())= 3 THEN 'III'
    WHEN MONTH(NOW())= 4 THEN 'IV'
    WHEN MONTH(NOW())= 5 THEN 'V'
    WHEN MONTH(NOW())= 6 THEN 'VI'
    WHEN MONTH(NOW())= 7 THEN 'VII'
    WHEN MONTH(NOW())= 8 THEN 'VIII'
    WHEN MONTH(NOW())= 9 THEN 'IX'
    WHEN MONTH(NOW())= 10 THEN 'X'
    WHEN MONTH(NOW())= 11 THEN 'XI'
    WHEN MONTH(NOW())= 12 THEN 'XII'
    END) 
";

$sql['get_count_data'] ="
SELECT FOUND_ROWS() AS total
";

$sql['get_data'] ="
SELECT 
  SQL_CALC_FOUND_ROWS   
  spr.`sppPengRealId` AS id,
  pr.`programId` AS program_id,
  pr.`programNomor` AS program_kode,
  pr.`programNama` AS program_nama,
  uk.unitkerjaKode AS unit_kerja_kode,
  uk.unitkerjaNama AS unit_kerja_nama,
  spr.`sppPengRealNomor` AS nomor_sppt,
  spr.`sppPengRealTglBuat` AS tanggal,
  SUM(IF(preal.`pengrealIsApprove`IN('Ya','Tidak'),1,0)) AS count_approval,
  (SELECT 
    SUM(tt.`sppPengRealTotal`) 
  FROM
    finansi_pa_spp_pengajuan_real tt 
  WHERE tt.`sppPengRealProgamId` = spr.`sppPengRealProgamId` 
    AND tt.`sppPengRealThAnggarId` = spr.`sppPengRealThAnggarId`) AS total_nominal,
  spr.`sppPengRealTotal` AS nominal,
  spr.`sppPengRealKeterangan` AS keterangan 
FROM
  finansi_pa_spp_pengajuan_real spr 
  LEFT JOIN unit_kerja_ref uk 
    ON uk.`unitkerjaId` = spr.`sppPengRealUnitKerjaId` 
  LEFT JOIN program_ref pr 
    ON pr.`programId` = spr.`sppPengRealProgamId` 
  LEFT JOIN finansi_pa_spp_pengajuan_real_detail sprd
    ON sprd.`sppPengRealDtSppPengRealId` = spr.`sppPengRealId`  
  LEFT JOIN pengajuan_realisasi preal
    ON preal.`pengrealId` = sprd.`sppPengRealDtPengRealId`
WHERE 
  spr.`sppPengRealThAnggarId`=  '%s' 
  AND 
	(
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
  AND
  (spr.`sppPengRealProgamId`  ='%s' OR %s)
  AND spr.`sppPengRealNomor` LIKE '%s' 
GROUP BY spr.`sppPengRealId`  
ORDER BY tanggal DESC,nomor_sppt
LIMIT %s,%s
";

$sql['get_data_by_id'] ="
SELECT 
  spr.`sppPengRealId` AS spp_no_pengajuan_id,
  spr.`sppPengRealThAnggarId` AS tahun_anggaran_id,
  ta.`thanggarNama` AS tahun_anggaran_nama,
  spr.`sppPengRealProgamId` AS program_id,
  prog.`programNomor` AS program_kode,
  prog.`programNama` AS program_nama,  
  spr.`sppPengRealUnitKerjaId` AS unit_kerja_id,
  uk.`unitkerjaNama` AS unit_kerja_nama,
  spr.`sppPengRealNomor` AS nomor_spp_no_pengajuan,
  spr.`sppPengRealTglBuat` AS tanggal,
  spr.`sppPengRealTotal` AS jumlah_total,
  spr.`sppPengRealKeterangan` AS keterangan 
FROM
  finansi_pa_spp_pengajuan_real spr 
  LEFT JOIN unit_kerja_ref uk 
    ON uk.`unitkerjaId` = spr.`sppPengRealUnitKerjaId` 
  LEFT JOIN tahun_anggaran ta ON ta.`thanggarId` = spr.`sppPengRealThAnggarId`  
  LEFT JOIN program_ref prog ON prog.`programId` = spr.`sppPengRealProgamId`    
WHERE
 spr.`sppPengRealId` = '%s'
";

$sql['get_count_spp_no_pengajuan_detail']="
SELECT
  COUNT(`sppPengRealDtId`) AS total
FROM 
	`finansi_pa_spp_pengajuan_real_detail`
WHERE 
	`sppPengRealDtSppPengRealId` = '%s'
";

$sql['get_no_pengajuan'] ="
SELECT 
  spd.`sppPengRealDtId` AS detail_id,
  spd.`sppPengRealDtSppPengRealId` AS spp_pengajuan_id,
  spd.`sppPengRealDtPengRealId` AS pengajuan_id,
  spd.`sppPengRealDtPengRealDetId` AS pengajuan_detail_id,  
  pr.`pengrealNomorPengajuan` AS pengajuan_no,
  pr.`pengrealTanggal` AS tanggal_pengajuan,
  pb.`paguBasKode` AS kode_ma,
  rpeng.`rncnpengeluaranKomponenNama` AS nama_index,
  spd.`sppPengRealDtNominal` AS nominal,
  spd.`sppPengRealDtCatatan` AS catatan
FROM
  `finansi_pa_spp_pengajuan_real_detail` spd 
  LEFT JOIN pengajuan_realisasi pr 
    ON pr.`pengrealId` = spd.`sppPengRealDtPengRealId` 
  LEFT JOIN pengajuan_realisasi_detil prd 
    ON (prd.`pengrealdetPengRealId` = pr.`pengrealId`)
	AND 
	(prd.`pengrealdetId` = spd.`sppPengRealDtPengRealDetId`)
  LEFT JOIN rencana_pengeluaran rpeng 
    ON rpeng.`rncnpengeluaranId` = prd.`pengrealdetRncnpengeluaranId` 
  LEFT JOIN finansi_ref_pagu_bas pb 
    ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId` 
WHERE 
spd.`sppPengRealDtSppPengRealId` = '%s' 
    
GROUP BY  pengajuan_detail_id
";

$sql['get_no_pengajuan_group'] ="
SELECT 
  spd.`sppPengRealDtId` AS detail_id,
  spd.`sppPengRealDtSppPengRealId` AS spp_pengajuan_id,
  spd.`sppPengRealDtPengRealId` AS pengajuan_id,  
  pr.`pengrealNomorPengajuan` AS pengajuan_no,
  pr.`pengrealTanggal` AS tanggal_pengajuan
FROM
  `finansi_pa_spp_pengajuan_real_detail` spd 
  LEFT JOIN pengajuan_realisasi pr 
    ON pr.`pengrealId` = spd.`sppPengRealDtPengRealId`
WHERE 

spd.`sppPengRealDtSppPengRealId` = '%s'
GROUP BY pengajuan_no,tanggal_pengajuan
";

$sql['get_data_index'] ="
SELECT 
  CONCAT('2','.',uk.`unitkerjaKode`,'.',prog.`programNomor`,'.','1','.',pb.`paguBasKode`) AS ma_kode,
  SUM(spd.`sppPengRealDtNominal`) AS ma_nominal 
FROM
  finansi_pa_spp_pengajuan_real_detail spd
  LEFT JOIN pengajuan_realisasi pr 
    ON pr.`pengrealId` = spd.`sppPengRealDtPengRealId`
  LEFT JOIN pengajuan_realisasi_detil prd 
    ON (prd.`pengrealdetPengRealId` = pr.`pengrealId`)
	AND 
	(prd.`pengrealdetId` = spd.`sppPengRealDtPengRealDetId`)
  LEFT JOIN rencana_pengeluaran rpeng 
    ON rpeng.`rncnpengeluaranId` = prd.`pengrealdetRncnpengeluaranId` 
  LEFT JOIN finansi_ref_pagu_bas pb 
    ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId` 
  LEFT JOIN kegiatan_detail kd 
    ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId` 
  LEFT JOIN kegiatan k 
    ON k.`kegId` = kd.`kegdetKegId` 
  LEFT JOIN unit_kerja_ref uk 
    ON uk.`unitkerjaId` = k.`kegUnitkerjaId` 
  LEFT JOIN program_ref prog 
    ON prog.`programId` = k.`kegProgramId` 
WHERE 

pr.`pengrealId` IN (
	SELECT 
		spd.`sppPengRealDtPengRealId` AS pengajuan_id
	FROM
		`finansi_pa_spp_pengajuan_real_detail` spd 
	LEFT JOIN pengajuan_realisasi pr ON pr.`pengrealId` = spd.`sppPengRealDtPengRealId` 
	WHERE 
		spd.`sppPengRealDtSppPengRealId` = '%s'
)
GROUP BY ma_kode
ORDER BY ma_kode ASC
";

$sql['get_data_index_old'] ="
SELECT
CONCAT('2','.',uk.`unitkerjaKode`,'.',prog.`programNomor`,'.','1','.',pb.`paguBasKode`) AS ma_kode,
SUM(sd.`sppDetNominal`) AS ma_nominal
FROM 
pengajuan_realisasi pr
LEFT JOIN pengajuan_realisasi_detil prd ON prd.`pengrealdetPengRealId` = pr.`pengrealId`
LEFT JOIN finansi_pa_spp_det sd ON sd.`sppDetRealdetId` = prd.`pengrealdetId`
LEFT JOIN rencana_pengeluaran rpeng ON rpeng.`rncnpengeluaranId` = prd.`pengrealdetRncnpengeluaranId`
LEFT JOIN finansi_ref_pagu_bas pb ON pb.`paguBasId` = rpeng.`rncnpengeluaranMakId`
LEFT JOIN kegiatan_detail kd ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`
LEFT JOIN kegiatan k ON k.`kegId` = kd.`kegdetKegId`
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = k.`kegUnitkerjaId`
LEFT JOIN program_ref prog ON prog.`programId` = k.`kegProgramId`
WHERE 

pr.`pengrealId` IN (
	SELECT 
		spd.`sppPengRealDtPengRealId` AS pengajuan_id
	FROM
		`finansi_pa_spp_pengajuan_real_detail` spd 
	LEFT JOIN pengajuan_realisasi pr ON pr.`pengrealId` = spd.`sppPengRealDtPengRealId` 
	WHERE 
		spd.`sppPengRealDtSppPengRealId` = '%s'
)
GROUP BY ma_kode
ORDER BY ma_kode ASC
";

$sql['get_data_index_detail']="
SELECT 
  prd.pengrealdetId AS id,
  rpeng.rncnpengeluaranId AS rpId,
  prd.pengrealdetTanggal AS tanggal,
  pb.paguBasKode AS makKode,
  CONCAT('2','.',uk.`unitkerjaKode`,'.',`programNomor`,'.','1','.',`paguBasKode`) AS ma_kode,
  SUM(CONVERT(tmpPagu.nominal, DECIMAL (20, 2))) AS nominalPagu,
  SUM(IFNULL(spd.sppPengRealDtNominal, 0)) AS nominalSpp,
  SUM(
    (SELECT 
      IFNULL(SUM(spd.sppPengRealDtNominal), 0) 
    FROM
      pengajuan_realisasi_detil prd
      LEFT JOIN pengajuan_realisasi preal 
        ON preal.pengrealId = prd.pengrealdetPengRealId 
      LEFT JOIN finansi_pa_spp_pengajuan_real_detail spd
        ON spd.sppPengRealDtPengRealDetId  = prd.pengrealdetId
    WHERE 1 = 1 
      AND UPPER(preal.pengrealIsApprove) = 'YA' 
      AND prd.pengrealdetId IN 
      (SELECT 
        sppPengRealDtPengRealDetId 
      FROM
        finansi_pa_spp_pengajuan_real_detail) 
      AND prd.pengrealdetRncnpengeluaranId = rpId 
      AND prd.pengrealdetTanggal < tanggal)
  ) AS sppLalu,
  SUM(spd.sppPengRealDtNominal) AS sppIni  
  
FROM
  finansi_pa_spp_pengajuan_real_detail spd
  LEFT JOIN pengajuan_realisasi_detil prd
    ON prd.pengrealdetId = spd.sppPengRealDtPengRealDetId
  LEFT JOIN pengajuan_realisasi pr
    ON pr.pengrealId = prd.pengrealdetPengRealId 
  LEFT JOIN rencana_pengeluaran rpeng
    ON rpeng.rncnpengeluaranId = prd.pengrealdetRncnpengeluaranId 
  LEFT JOIN finansi_ref_pagu_bas pb ON pb.paguBasId = rpeng.rncnpengeluaranMakId 
  LEFT JOIN kegiatan_detail kd 
    ON kegdetId = pengrealKegdetId 
  LEFT JOIN kegiatan_ref kr
    ON kegrefId = kegdetKegrefId 
  LEFT JOIN kegiatan k
    ON kegId = kegdetKegId 
  LEFT JOIN sub_program sp
    ON subprogId = kegrefSubprogId 
  LEFT JOIN program_ref pref
    ON pref.programId = sp.subprogProgramId 
  LEFT JOIN unit_kerja_ref uk
    ON uk.unitkerjaId = k.kegUnitkerjaId 
  LEFT JOIN jenis_kegiatan_ref  jkr
    ON jkr.jeniskegId = sp.subprogJeniskegId 
  LEFT JOIN 
    (SELECT rncnpengeluaranId AS id,
      IFNULL(SUM(rncnpengeluaranKomponenNominalAprove * rncnpengeluaranSatuanAprove),0) AS nominal 
    FROM rencana_pengeluaran 
    GROUP BY rncnpengeluaranId) AS tmpPagu 
    ON tmpPagu.id = rncnpengeluaranId 
  LEFT JOIN 
    (SELECT 
      pengrealdetRncnpengeluaranId AS id,
      SUM(pengrealdetNominalApprove) AS nominal 
    FROM
      pengajuan_realisasi_detil 
      JOIN pengajuan_realisasi 
        ON pengrealId = pengrealdetPengRealId 
    WHERE 1 = 1 
      AND UPPER(pengrealIsApprove) = 'YA' 
      AND pengrealdetId IN 
      (SELECT 
        sppDetRealdetId 
      FROM
        finansi_pa_spp_det) 
    GROUP BY pengrealdetRncnpengeluaranId) AS spp 
    ON spp.id = rncnpengeluaranId 
WHERE pengrealdetPengRealId IN 
  (SELECT 
    spd.`sppPengRealDtPengRealId` AS pengajuan_id 
  FROM
    `finansi_pa_spp_pengajuan_real_detail` spd 
    LEFT JOIN pengajuan_realisasi pr 
      ON pr.`pengrealId` = spd.`sppPengRealDtPengRealId` 
  WHERE spd.`sppPengRealDtSppPengRealId` = '%s') 
GROUP BY ma_kode 
ORDER BY ma_kode ASC 
 ";
  
$sql['get_data_index_detail_old']="
SELECT   
  pengrealdetId AS id,
  rncnpengeluaranId AS rpId,
  pengrealdetTanggal AS tanggal,
  paguBasKode AS makKode,
  CONCAT('2','.',`unitkerjaKode`,'.',`programNomor`,'.','1','.',`paguBasKode`) AS ma_kode,
  SUM(CONVERT(tmpPagu.nominal, DECIMAL (20, 2))) AS nominalPagu,
  SUM(IFNULL(spp.nominal, 0)) AS nominalSpp,
  @a := 
  SUM((SELECT 
    IFNULL(SUM(pengrealdetNominalApprove), 0) 
  FROM
    pengajuan_realisasi_detil 
    JOIN pengajuan_realisasi 
      ON pengrealId = pengrealdetPengRealId 
  WHERE 1 = 1 
    AND UPPER(pengrealIsApprove) = 'YA' 
    AND pengrealdetId IN 
    (SELECT 
      sppDetRealdetId 
    FROM
      finansi_pa_spp_det) 
    AND pengrealdetRncnpengeluaranId = rpId 
    AND pengrealdetTanggal < tanggal)) AS sppLalu,
  SUM(sppDetNominal) AS sppIni,
  SUM(CONVERT(tmpPagu.nominal, DECIMAL (20, 2))) - CONVERT(
    (IFNULL(@a, 0) + SUM(sppDetNominal)),
    DECIMAL (20, 2)) AS sisaDana 
FROM
  finansi_pa_spp_det 
  JOIN pengajuan_realisasi_detil 
    ON pengrealdetId = sppDetRealdetId 
  JOIN pengajuan_realisasi 
    ON pengrealId = pengrealdetPengRealId 
  JOIN rencana_pengeluaran 
    ON rncnpengeluaranId = pengrealdetRncnpengeluaranId 
  LEFT JOIN finansi_ref_pagu_bas 
    ON paguBasId = rncnpengeluaranMakId 
  LEFT JOIN kegiatan_detail 
    ON kegdetId = pengrealKegdetId 
  LEFT JOIN kegiatan_ref 
    ON kegrefId = kegdetKegrefId 
  LEFT JOIN kegiatan 
    ON kegId = kegdetKegId 
  LEFT JOIN sub_program 
    ON subprogId = kegrefSubprogId 
  LEFT JOIN program_ref 
    ON programId = subprogProgramId 
  LEFT JOIN unit_kerja_ref 
    ON unitkerjaId = kegUnitkerjaId 
  LEFT JOIN jenis_kegiatan_ref 
    ON jeniskegId = subprogJeniskegId 
  LEFT JOIN 
    (SELECT 
      rncnpengeluaranId AS id,
      IFNULL(
        SUM(
          rncnpengeluaranKomponenNominalAprove * rncnpengeluaranSatuanAprove
        ),
        0
      ) AS nominal 
    FROM
      rencana_pengeluaran 
    GROUP BY rncnpengeluaranId) AS tmpPagu 
    ON tmpPagu.id = rncnpengeluaranId 
  LEFT JOIN 
    (SELECT 
      pengrealdetRncnpengeluaranId AS id,
      SUM(pengrealdetNominalApprove) AS nominal 
    FROM
      pengajuan_realisasi_detil 
      JOIN pengajuan_realisasi 
        ON pengrealId = pengrealdetPengRealId 
    WHERE 1 = 1 
      AND UPPER(pengrealIsApprove) = 'YA' 
      AND pengrealdetId IN 
      (SELECT 
        sppDetRealdetId 
      FROM
        finansi_pa_spp_det) 
    GROUP BY pengrealdetRncnpengeluaranId) AS spp 
    ON spp.id = rncnpengeluaranId 
WHERE 

    pengrealdetPengRealId IN 
    (SELECT 
      spd.`sppPengRealDtPengRealId` AS pengajuan_id 
    FROM
      `finansi_pa_spp_pengajuan_real_detail` spd 
      LEFT JOIN pengajuan_realisasi pr 
        ON pr.`pengrealId` = spd.`sppPengRealDtPengRealId` 
    WHERE spd.`sppPengRealDtSppPengRealId` = '%s')
GROUP BY ma_kode
ORDER BY ma_kode ASC
 ";
  
$sql['get_tahun_anggaran'] = "
SELECT 
	ta.`thanggarId` AS `id`,
	ta.`thanggarNama` AS `name`
FROM 
	tahun_anggaran ta
ORDER BY 
	ta.`thanggarNama` DESC
";

$sql['get_tahun_anggaran_aktif'] = "
SELECT 
	ta.`thanggarId` AS `id`,
	ta.`thanggarNama` AS `name`
FROM 
	tahun_anggaran ta
WHERE
	ta.`thanggarIsAktif` ='Y'
";

$sql['get_data_program'] = "
SELECT
   programId AS id,
   programNama AS name
FROM
  program_ref
WHERE
   programThanggarId = '%s'
ORDER BY
  programNomor ASC
";

/**
 * insert data
 */

$sql['add_sppt'] ="
INSERT INTO `finansi_pa_spp_pengajuan_real` (
  `sppPengRealThAnggarId`,
  `sppPengRealProgamId`,
  `sppPengRealUnitKerjaId`,
  `sppPengRealNomor`,
  `sppPengRealTglBuat`,
  `sppPengRealTotal`,
  `sppPengRealKeterangan`
) 
VALUES
  (
    '%s',
    '%s',
    '%s',
    '%s',
    '%s',
    '%s',
    '%s'
  )
";
 
$sql['add_sppt_detail'] ="
INSERT INTO `finansi_pa_spp_pengajuan_real_detail` (  
  `sppPengRealDtSppPengRealId`,
  `sppPengRealDtPengRealId`,
  `sppPengRealDtPengRealDetId`,
  `sppPengRealDtNominal`,
  `sppPengRealDtCatatan`
) 
VALUES
  (    
    '%s',
    '%s',
    '%s',
    '%s',
    '%s'
  ) 
";

/**
 * update data
 */
$sql['update_sppt'] ="
UPDATE 
  `finansi_pa_spp_pengajuan_real` 
SET  
  `sppPengRealThAnggarId` = '%s',
  `sppPengRealProgamId`  = '%s',
  `sppPengRealUnitKerjaId` = '%s',
  `sppPengRealNomor` = '%s',
  `sppPengRealTglBuat` = '%s',
  `sppPengRealKeterangan` = '%s',
  `sppPengRealTotal` = '%s'
WHERE `sppPengRealId` ='%s'
";

$sql['update_sppt_detail'] = "
UPDATE 
  `finansi_pa_spp_pengajuan_real_detail` 
SET  
  `sppPengRealDtSppPengRealId` = '%s',
  `sppPengRealDtPengRealId` = '%s',
  `sppPengRealDtPengRealDetId` = '%s',
  `sppPengRealDtNominal` = '%s',
  `sppPengRealDtCatatan` = '%s'
WHERE `sppPengRealDtId` = '%s'
";

$sql['delete_sppt'] ="
DELETE FROM
  `finansi_pa_spp_pengajuan_real` 
WHERE `sppPengRealId` IN(%s)
";

$sql['delete_sppt_detail'] = "
DELETE FROM
  `finansi_pa_spp_pengajuan_real_detail` 
WHERE `sppPengRealDtSppPengRealId`  IN(%s)
";

?>