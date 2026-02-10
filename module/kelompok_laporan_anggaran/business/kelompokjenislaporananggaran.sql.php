<?php

/**
 *
 * @filename kelompokjenislaporananggaran.sql.php
 * @package kelompok_laporan_anggaran
 * @subpackage business
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since 6 september 2012
 * @copyright 2012 Gamatechno Indonesia
 * 
 */

$sql['get_data_jenis_laporan_combo'] = "
SELECT
	`paguBasKelJnsLapRefId` AS `id`,
	`paguBasKelJnsLapRefNama`AS `name`
FROM `finansi_pa_pagu_bas_kelompok_jenis_laporan_ref`
WHERE
	paguBasKelJnsLapRefParentId IN(0)
ORDER BY `paguBasKelJnsLapRefNama` ASC
";

$sql['get_bentuk_transaksi_combo'] = "
SELECT
	`paguBasKelJnsLapRefId` AS `id`,
	`paguBasKelJnsLapRefNama`AS `name`
FROM `finansi_pa_pagu_bas_kelompok_jenis_laporan_ref`
WHERE 
	paguBasKelJnsLapRefParentId NOT IN(0)
	AND
	paguBasKelJnsLapRefParentId = '%s'
ORDER BY paguBasKelJnsLapRefOrderBy ASC
";
