<?php

/**
 * get data pengelompokan laporan posisi keuangan
 */
$sql['get_root_kode_sistem'] ="
SELECT
    `kellapKodeSistem` AS kode_sistem
FROM
    `kelompok_laporan_ref`
WHERE
    `kellapTipe` ='root'
AND
    `kellapId` = '%s'
";

$sql['get_combo_root'] ="
SELECT
    `kellapId` AS id,
    `kellapNama` AS `name`
FROM 
    `kelompok_laporan_ref`
WHERE
    `kellapTipe` ='root'
ORDER BY kellapNama ASC
";

$sql['get_kelompok_laporan'] ="
SELECT
  `kellapId` AS kellap_id,
  `kellapKodeSistem` AS kellap_ks,
  `kellapParentId` AS kellap_pid,
  `kellapNama` AS kellap_nama,
  `kellapLevel` AS kellap_level,
  `kellapOrderBy` AS kellap_order_by,
  `kellapKelompok` AS kellap_kelompok,
  `kellapTipe` AS kellap_tipe,
  `kellapIsTambah` AS kellap_is_tambah,
  `kellapIsSummary` AS kellap_is_summary
FROM
  `kelompok_laporan_ref`
WHERE 
    `kellapKodeSistem` = '%s'
    OR
    `kellapKodeSistem` LIKE '%s'
ORDER BY
  kellapParentId,
  kellapOrderBy
";


$sql['get_kelompok_laporan_root'] ="
SELECT
  `kellapId` AS kellap_id,
  `kellapKodeSistem` AS kellap_ks,
  `kellapParentId` AS kellap_pid,
  `kellapNama` AS kellap_nama,
  `kellapLevel` AS kellap_level,
  `kellapOrderBy` AS kellap_order_by,
  `kellapKelompok` AS kellap_kelompok,
  `kellapTipe` AS kellap_tipe,
  `kellapIsTambah` AS kellap_is_tambah,
  `kellapIsSummary` AS kellap_is_summary
FROM
  `kelompok_laporan_ref`
WHERE 
    `kellapKodeSistem` IN (%s)
ORDER BY
   kellap_ks ASC
";

$sql['get_kode_sistem'] ="
SELECT
  CONCAT('%s',
  IFNULL((MAX(SUBSTRING_INDEX(kellapKodeSistem,'.',-1)) + 1 ),1)
  ) AS rnumber
FROM
    `kelompok_laporan_ref`
WHERE
   `kellapParentId` = '%s'
";

$sql['get_parent_id'] ="
SELECT 
    `kellapParentId` AS parent_id
FROM
    `kelompok_laporan_ref`
WHERE kellapId = '%s'
";

$sql['get_tipe_parent'] ="
SELECT
  `kellapTipe` AS tipe
FROM
  `kelompok_laporan_ref`
WHERE
    `kellapId` = '%s'
";

$sql['get_count_child'] ="
SELECT
  COUNT(`kellapId`) AS total_child
FROM
  `kelompok_laporan_ref`
WHERE `kellapParentId` = '%s'
";

$sql['get_count_coa'] ="
SELECT
    COUNT(`coakellapId`) AS total_coa
FROM
    `coa_kelompok_laporan_ref`
WHERE
    coakellapIdKellap = '%s'
";

$sql['delete_coa_per_kelompok'] ="
DELETE
    FROM `coa_kelompok_laporan_ref`
WHERE
    `coakellapIdKellap` = '%s'
AND 
    `coakellapIdKellapRef` IS NULL
";

$sql['delete_klp_per_kelompok'] ="
DELETE
    FROM `coa_kelompok_laporan_ref`
WHERE
    `coakellapIdKellap` = '%s'
AND 
    `coakellapIdKellapRef` IS NOT NULL

";

$sql['delete_daftar_klp_per_kelompok'] ="
DELETE
    FROM `coa_kelompok_laporan_ref`
WHERE
    `coakellapIdKellap` = '%s'
AND
    `coakellapIdKellapRef` NOT IN (%s)
AND 
    `coakellapIdKellapRef` IS NOT NULL
";

$sql['delete_daftar_coa_per_kelompok'] ="
DELETE
    FROM `coa_kelompok_laporan_ref`
WHERE
    `coakellapIdKellap` = '%s'
AND
    `coakellapCoaId` NOT IN (%s)
  AND
  coakellapIdKellapRef IS NULL
";

$sql['get_total_klp_per_kelompok'] ="
SELECT
  COUNT(`coakellapIdKellapRef`) AS total_klp
FROM
  `coa_kelompok_laporan_ref`
WHERE
  `coakellapIdKellap` = '%s'
  AND
  coakellapIdKellapRef IS NOT NULL
";

$sql['get_total_coa_per_kelompok'] ="
SELECT
  COUNT(`coakellapCoaId`) AS total_coa
FROM
  `coa_kelompok_laporan_ref`
WHERE
  `coakellapIdKellap` = '%s'
  AND
  coakellapIdKellapRef IS NULL
";

$sql['get_coa_exist_per_kelompok'] ="
SELECT
  `coakellapCoaId` AS coa_id
FROM
  `coa_kelompok_laporan_ref`
WHERE
  `coakellapIdKellap` = '%s'
AND 
  `coakellapCoaId` IN (%s)
";

$sql['get_klp_exist_per_kelompok'] ="
SELECT
  `coakellapIdKellapRef` AS klp_id
FROM
  `coa_kelompok_laporan_ref`
WHERE
  `coakellapIdKellap` = '%s'
AND 
  `coakellapIdKellapRef` IN (%s)
AND
  coakellapIdKellapRef IS NOT NULL
";

$sql['get_urutan_kelompok_terakhir'] ="
SELECT
    IFNULL(MAX(`kellapOrderBy`),0) AS max_urutan
FROM 
    `kelompok_laporan_ref`
WHERE
    `kellapParentId` = '%s'
";

//===GET===
$sql['get_jenis_laporan'] = "
	SELECT
		kellapId AS id,
		kellapNama AS name
	FROM
		kelompok_laporan_ref
	WHERE
		kellapParentId =0
";

$sql['get_child'] = "
	SELECT
		kellapId AS id,
		kellapNama AS name
	FROM
		kelompok_laporan_ref
	WHERE
		kellapParentId != 0
	AND
		kellapParentId = '%s'
";

$sql['get_count'] = "SELECT
      count(*) AS total
		FROM kelompok_laporan_ref
	WHERE
		kellapNama LIKE '%s'
        ";

$sql['get_data'] = "
SELECT
    kellapId AS id,
    kellapParentId AS kellap_pid,
	kellapNama AS keterangan,
	kellapIsTambah AS is_tambah
FROM
  `kelompok_laporan_ref` 
WHERE `kellapKodeSistem` = '2' 
  OR `kellapKodeSistem` LIKE '2.%' 
  AND kellapKelompok = 'AKTIVA' 
ORDER BY kellapParentId,
  kellapOrderBy
";

$sql['get_data_by_id'] = "
SELECT
  child.`kellapId` AS kellap_id,
  child.`kellapKodeSistem` AS kellap_kode_sistem,
  SUBSTRING_INDEX(child.`kellapKodeSistem`,'.',(child.kellapLevel - 1)) AS kellap_kode_sistem_up,
  (child.`kellapLevel` - 1) AS kellap_level_up,
  child.`kellapLevel` AS kellap_level,
  parent.`kellapParentId` AS kellap_pid,
  child.`kellapParentId` AS kellap_parent_id,
  IFNULL(parent.`kellapNama`,'-') AS kellap_parent_nama,
  child.`kellapNama` AS kellap_nama,
  child.`kellapOrderBy` AS kellap_order_by,
  child.`kellapKelompok` AS kellap_kelompok,
  child.`kellapTipe` AS kellap_tipe,
  child.`kellapIsTambah` AS kellap_is_tambah,
  child.`kellapIsSummary` AS kellap_is_summary,
  child.`kellapSummaryDetail` AS kellap_summary_detail
FROM 
    `kelompok_laporan_ref` child
    LEFT JOIN kelompok_laporan_ref parent
    ON parent.`kellapId` = child.`kellapParentId`
WHERE
    child.kellapId = '%s'
";

$sql['get_data_parent_child'] = "
SELECT
  `kellapId` AS kellap_id,
  `kellapKodeSistem` AS kellap_ks,
  `kellapParentId` AS kellap_pid,
  `kellapNama` AS kellap_nama,
  `kellapLevel` AS kellap_level,
  `kellapOrderBy` AS kellap_order_by,
  `kellapKelompok` AS kellap_kelompok,
  `kellapTipe` AS kellap_tipe,
  MAX(kl.`maksNumber`) AS kellap_maks_number
FROM
  `kelompok_laporan_ref`
LEFT JOIN(
  SELECT 
      klr.`kellapParentId` AS parent_id, 
      MAX(klr.`kellapOrderBy`) AS maksNumber
  FROM 
      kelompok_laporan_ref klr
  GROUP BY 
      klr.`kellapId`
  ) AS kl ON kl.`parent_id` = kellapId
WHERE 
    `kellapParentId` = '%s'
    AND `kellapNama` LIKE '%s'
GROUP BY kellapId
ORDER BY
   kellap_ks ASC
LIMIT %s, %s
";

$sql['get_count_parent_child'] = "
SELECT
  COUNT(`kellapId`) AS total
FROM
  `kelompok_laporan_ref`
LEFT JOIN(
  SELECT 
      klr.`kellapParentId` AS parent_id, 
      MAX(klr.`kellapOrderBy`) AS maksNumber
  FROM 
      kelompok_laporan_ref klr
  ) AS kl ON kl.`parent_id` = kellapId
WHERE 
    `kellapParentId` = '%s'
    AND `kellapNama` LIKE '%s'
";

$sql['get_data_by_array_id'] = "SELECT
      kellapId AS id,
      kellapNama AS nama,
      kellapBentukTransaksi AS bentuk_transaksi,
      kellapIsTambah AS is_tambah,
      kellapJenisLaporan AS jns_lap
   FROM
      kelompok_laporan_ref
   WHERE
      kellapId IN ('%s')";

//===DO===
$sql['do_add_summary'] = "
INSERT INTO `kelompok_laporan_ref`
SET
    `kellapKodeSistem` ='%s',
    `kellapLevel` ='%s',
    `kellapParentId` = '%s',
    `kellapNama` ='%s',
    `kellapKelompok` ='%s',
    `kellapTipe` ='%s',
    `kellapIsTambah` = '%s',
    `kellapIsSummary` ='%s',
    `kellapOrderBy` = '%s',
    `kellapSummaryDetail` = '%s'
";

//===DO===
$sql['do_add'] = "
INSERT INTO `kelompok_laporan_ref`
SET    
    `kellapKodeSistem` ='%s',
    `kellapLevel` ='%s',
    `kellapParentId` = '%s',
    `kellapNama` ='%s',
    `kellapKelompok` ='%s',
    `kellapTipe` ='%s',
    `kellapIsTambah` = '%s',
    `kellapIsSummary` ='%s',
    `kellapOrderBy` = '%s'
";

//do update summary
$sql['do_update_summary'] = "
UPDATE kelompok_laporan_ref
SET
    `kellapParentId` = '%s',
    `kellapNama` ='%s',
    `kellapKelompok` ='%s',
    `kellapIsTambah` = '%s',
    `kellapIsSummary` ='%s',
    `kellapOrderBy` = '%s',
    `kellapSummaryDetail` = '%s'
WHERE
    `kellapId` = '%s'
";


$sql['do_update'] = "
UPDATE kelompok_laporan_ref
SET
    `kellapParentId` = '%s',
    `kellapNama` ='%s',
    `kellapKelompok` ='%s',    
    `kellapIsTambah` = '%s',
    `kellapIsSummary` ='%s',
    `kellapOrderBy` = '%s'
WHERE
    `kellapId` = '%s'
";

$sql['do_update_tipe_parent'] = "
UPDATE kelompok_laporan_ref
SET
    `kellapTipe` ='%s'
WHERE
    `kellapId` = '%s'
";

$sql['do_delete_by_id'] = "
DELETE 
    FROM kelompok_laporan_ref
WHERE kellapId='%s'
";

$sql['do_delete_by_array_id'] = "
DELETE 
    FROM kelompok_laporan_ref
WHERE kellapId IN ('%s')
";


// do add detil coa kel laporan
$sql['do_add_detil_coa_kel_lap'] = "
INSERT coa_kelompok_laporan_ref 
SET
    coakellapIdKellap = '%s',
    coakellapCoaId = '%s',
    coakellapDK = '%s',
    coaKellapIsPositif = '%s',
    coaKellapIsSaldoAwal ='%s',
    coakellapIsMutasiDK ='%s',
    coakellapIsMutasiD ='%s',
    coakellapIsMutasiK ='%s'
";

// do update detil coa kel laporan // update status mutasi
$sql['do_update_detil_coa_kel_lap'] = "
UPDATE coa_kelompok_laporan_ref
SET
    coaKellapIsPositif = '%s',
    coaKellapIsSaldoAwal ='%s',
    coakellapIsMutasiDK ='%s',
    coakellapIsMutasiD ='%s',
    coakellapIsMutasiK ='%s'
WHERE
coakellapCoaId = '%s'
AND
coakellapIdKellap = '%s'
";


// do add detil coa kel laporan
$sql['do_add_detil_klp_kel_lap'] = "
INSERT 
INTO coa_kelompok_laporan_ref (coakellapIdKellap, coakellapIdKellapRef, coakellapDK)
VALUES (%s, %s, %s)
";

$sql['get_kelompok_info'] = "
        SELECT
			kellapNama,
			bentuk_transaksi as kelJnsNama,
			jenis_laporan AS jenisLaporan
		FROM kelompok_laporan_ref
		LEFT JOIN(
		SELECT
			jenis_laporan_id,
			bentuk_transaksi_id,
			jenis_laporan,
			CASE WHEN jenis_laporan=bentuk_transaksi
			THEN NULL
			WHEN jenis_laporan!=bentuk_transaksi THEN bentuk_transaksi END AS bentuk_transaksi

		FROM(
			SELECT
				a.kelJnsId AS bentuk_transaksi_id,
				IFNULL(b.kelJnsId, a.kelJnsId) AS jenis_laporan_id,
				IFNULL(b.kelJnsNama,a.kelJnsNama) AS jenis_laporan,
				a.kelJnsNama AS bentuk_transaksi
			FROM kelompok_jenis_laporan_ref a
			LEFT JOIN kelompok_jenis_laporan_ref b ON b.kelJnsId = a.kelJnsPrntId
		)a ORDER BY jenis_laporan_id, bentuk_transaksi_id
		) a ON a.bentuk_transaksi_id = kellapJnsId
	WHERE
	kellapId ='%s'
";

$sql['do_delete_detil_by_id'] = "DELETE FROM coa_kelompok_laporan_ref
   WHERE
      coakellapId='%s'";

$sql['do_delete_detil_by_array_id'] = "DELETE FROM coa_kelompok_laporan_ref
   WHERE
      coakellapId IN ('%s')";


$sql['generate_no_urutan'] = "
SELECT
  IFNULL(MAX(kl.`kellapOrderBy`) + 1, 1) AS no_urutan
FROM
  `kelompok_laporan_ref` kl
WHERE kl.`kellapParentId` = %s
";

$sql['get_coa_perkelompok'] ="
SELECT
  ckl.`coakellapCoaId` AS coa_id,
  c.`coaKodeAkun` AS coa_kode,
  c.`coaNamaAkun` AS coa_nama,
  ckl.`coakellapDK` AS jenis,
  ckl.`coaKellapIsPositif` AS coa_is_positif,
  ckl.`coaKellapIsSaldoAwal` AS coa_is_saldo_awal,
  ckl.`coakellapIsMutasiDK` AS coa_is_mutasi_dk,
  ckl.`coakellapIsMutasiD` AS coa_is_mutasi_d,
  ckl.`coakellapIsMutasiK` AS coa_is_mutasi_k
FROM
    `coa_kelompok_laporan_ref` ckl
    JOIN `kelompok_laporan_ref` klr
        ON klr.`kellapId` = ckl.`coakellapIdKellap`
    JOIN coa c
        ON c.`coaId` = ckl.`coakellapCoaId`
WHERE
klr.`kellapId` = '%s'
AND
ckl.`coakellapIdKellapRef` IS NULL
ORDER BY c.`coaKodeAkun` ASC
";

$sql['get_klp_perkelompok'] ="
SELECT
  ckl.`coakellapIdKellapRef` AS klp_id,
  klr_ref.`kellapNama` AS klp_nama
FROM
    `coa_kelompok_laporan_ref` ckl
    JOIN `kelompok_laporan_ref` klr
        ON klr.`kellapId` = ckl.`coakellapIdKellap`
    JOIN kelompok_laporan_ref klr_ref
        ON klr_ref.`kellapId` = ckl.`coakellapIdKellapRef`
WHERE
    klr.`kellapId` = '%s'
    AND
    ckl.`coakellapIdKellapRef` IS NOT NULL
ORDER BY klp_nama
";


//cek jika sudah memiliki SUM
$sql['get_sum_rows'] ="
SELECT
  COUNT(`kellapId`) AS srows
FROM `kelompok_laporan_ref`
WHERE 
kellapParentId = '%s'
AND
kellapIsSummary = 'Y'
";

?>