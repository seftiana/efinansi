<?php

/**
 *
 * @filename kelompoklaporananggaran.sql.php
 * @package kelompok_laporan_anggaran
 * @subpackage business
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since 6 september 2012
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
$sql['do_add'] ="
INSERT INTO `finansi_pa_pagu_bas_kelompok_laporan_ref`
	(
	 `paguBasKelLapRefNama`,
     `paguBasKelLapRefJnsLapRefId`,
     `paguBasKelLapRefIsTambah`,
     `paguBasKelLapRefOrderBy`
    )
VALUES 
	(
    '%s',
    '%s',
    '%s',
    '%s'
    )
";

$sql['do_update'] ="
UPDATE 
  `finansi_pa_pagu_bas_kelompok_laporan_ref`
SET 
  `paguBasKelLapRefNama` = '%s',
  `paguBasKelLapRefJnsLapRefId` = '%s',
  `paguBasKelLapRefIsTambah` = '%s',
  `paguBasKelLapRefOrderBy` = '%s'
WHERE 
  `paguBasKelLapRefId` = '%s';
";

$sql['do_delete'] ="
DELETE FROM
	`finansi_pa_pagu_bas_kelompok_laporan_ref`
WHERE 
  `paguBasKelLapRefId` IN ('%s')";
      
$sql['get_count'] ="
SELECT
  COUNT(kl.`paguBasKelLapRefId`) AS total
FROM 
  `finansi_pa_pagu_bas_kelompok_laporan_ref` kl
  LEFT JOIN finansi_pa_pagu_bas_kelompok_jenis_laporan_ref kbt
	ON kbt.`paguBasKelJnsLapRefId` = kl.`paguBasKelLapRefJnsLapRefId`
  LEFT JOIN finansi_pa_pagu_bas_kelompok_jenis_laporan_ref kjl
	ON kjl.`paguBasKelJnsLapRefId` = kbt.`paguBasKelJnsLapRefParentId`
WHERE
  kl.`paguBasKelLapRefNama` LIKE '%s'
  AND
  (kjl.`paguBasKelJnsLapRefId` ='%s' OR kbt.`paguBasKelJnsLapRefId` ='%s' OR %s)
";

$sql['get_data'] ="
SELECT
  kl.`paguBasKelLapRefId` AS id,
  kl.`paguBasKelLapRefNama` AS nama,
  IFNULL(kjl.`paguBasKelJnsLapRefNama`,kbt.`paguBasKelJnsLapRefNama`) AS jenis_laporan,
  IF(kbt.`paguBasKelJnsLapRefParentId` =0,NULL, kbt.`paguBasKelJnsLapRefNama`) AS bentuk_transaksi,
  kl.`paguBasKelLapRefIsTambah`AS is_tambah,
  kl.`paguBasKelLapRefOrderBy` AS no_urutan
FROM 
  `finansi_pa_pagu_bas_kelompok_laporan_ref` kl
  LEFT JOIN finansi_pa_pagu_bas_kelompok_jenis_laporan_ref kbt
	ON kbt.`paguBasKelJnsLapRefId` = kl.`paguBasKelLapRefJnsLapRefId`
  LEFT JOIN finansi_pa_pagu_bas_kelompok_jenis_laporan_ref kjl
	ON kjl.`paguBasKelJnsLapRefId` = kbt.`paguBasKelJnsLapRefParentId`
WHERE
  kl.`paguBasKelLapRefNama` LIKE '%s'
  AND
  (kjl.`paguBasKelJnsLapRefId` ='%s' OR kbt.`paguBasKelJnsLapRefId` ='%s'OR %s)
ORDER BY jenis_laporan,nama,no_urutan ASC
LIMIT %s, %s
";

$sql['get_data_by_id'] = "
SELECT
  kl.`paguBasKelLapRefId` AS id,
  kl.`paguBasKelLapRefNama` AS nama,
  IFNULL(kjl.`paguBasKelJnsLapRefId`,kbt.`paguBasKelJnsLapRefId`) AS jenis_laporan_id,
  IF(kbt.`paguBasKelJnsLapRefParentId` =0,NULL, kbt.`paguBasKelJnsLapRefId`) AS bentuk_transaksi_id,
  kl.`paguBasKelLapRefIsTambah`AS is_tambah,
  kl.`paguBasKelLapRefOrderBy` AS no_urutan
  
FROM 
  `finansi_pa_pagu_bas_kelompok_laporan_ref` kl
  LEFT JOIN finansi_pa_pagu_bas_kelompok_jenis_laporan_ref kbt
	ON kbt.`paguBasKelJnsLapRefId` = kl.`paguBasKelLapRefJnsLapRefId`
  LEFT JOIN finansi_pa_pagu_bas_kelompok_jenis_laporan_ref kjl
	ON kjl.`paguBasKelJnsLapRefId` = kbt.`paguBasKelJnsLapRefParentId`
WHERE
  kl.`paguBasKelLapRefId` = '%s'
";

$sql['get_data_detil'] = "
SELECT
  kl.`paguBasKelLapRefId` AS id,
  kl.`paguBasKelLapRefNama` AS nama,
  IFNULL(kjl.`paguBasKelJnsLapRefNama`,kbt.`paguBasKelJnsLapRefNama`) AS jenis_laporan,
  IF(kbt.`paguBasKelJnsLapRefParentId` =0,NULL, kbt.`paguBasKelJnsLapRefNama`) AS bentuk_transaksi
  
FROM 
  `finansi_pa_pagu_bas_kelompok_laporan_ref` kl
  LEFT JOIN finansi_pa_pagu_bas_kelompok_jenis_laporan_ref kbt
	ON kbt.`paguBasKelJnsLapRefId` = kl.`paguBasKelLapRefJnsLapRefId`
  LEFT JOIN finansi_pa_pagu_bas_kelompok_jenis_laporan_ref kjl
	ON kjl.`paguBasKelJnsLapRefId` = kbt.`paguBasKelJnsLapRefParentId`
WHERE
  kl.`paguBasKelLapRefId` = '%s'
";


/**
 * untuk mendapatkan data pagu bas mak
 */
 
$sql['get_data_pagu_bas_mak'] ="
SELECT
  klpb.`paguBasKelLapId` AS id,
  pb.`paguBasId` AS mak_id,
  pb.`paguBasKode` AS mak_kode,
  pb.`paguBasKeterangan` AS mak_nama
FROM `finansi_pa_pagu_bas_kelompok_laporan_ref_pagu_bas` klpb
  LEFT JOIN finansi_ref_pagu_bas pb
	ON pb.`paguBasId` = klpb.`paguBasKelLapPaguBasId`
WHERE
  klpb.`paguBasKelLapKelLapRefId` = '%s'
";

$sql['get_count_data_pagu_bas_mak'] ="
SELECT
  COUNT(klpb.`paguBasKelLapId` AS id) AS total
FROM `finansi_pa_pagu_bas_kelompok_laporan_ref_pagu_bas` klpb
  LEFT JOIN finansi_ref_pagu_bas pb
	ON pb.`paguBasId` = klpb.`paguBasKelLapPaguBasId`
WHERE
  klpb.`paguBasKelLapKelLapRefId` = '%s'
";

$sql['cek_pagu_bas_mak']="

";
$sql['do_add_pagu_bas_mak']="
INSERT INTO `finansi_pa_pagu_bas_kelompok_laporan_ref_pagu_bas`
	(
	  `paguBasKelLapKelLapRefId`,
	  `paguBasKelLapPaguBasId`
	)
VALUES ('%s','%s')";

$sql['do_delete_pagu_bas_mak']="
DELETE FROM `finansi_pa_pagu_bas_kelompok_laporan_ref_pagu_bas`
WHERE 
	`paguBasKelLapKelLapRefId` = '%s'
";

$sql['generate_no_urutan'] = "
SELECT 
	IFNULL(MAX(kl.`paguBasKelLapRefOrderBy`) + 1,1)  AS no_urutan
FROM
    `finansi_pa_pagu_bas_kelompok_laporan_ref` kl
     LEFT JOIN `finansi_pa_pagu_bas_kelompok_jenis_laporan_ref` kj 
		ON kj.`paguBasKelJnsLapRefId` = kl.`paguBasKelLapRefJnsLapRefId`
	 LEFT JOIN `finansi_pa_pagu_bas_kelompok_jenis_laporan_ref` kj_p 
		ON kj_p.`paguBasKelJnsLapRefId` = kj.`paguBasKelJnsLapRefId`
WHERE 
	kj_p.`paguBasKelJnsLapRefId` = %s OR  kj.`paguBasKelJnsLapRefId` =  %s
";
