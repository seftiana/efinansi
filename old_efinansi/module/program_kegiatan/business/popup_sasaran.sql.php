<?php

/**
 * 
 * file : popup_sasaran.sql.php
 * @package program_kegiatan
 * @subpackage business
 * @filename popup_sasaran.sql.php
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * @since 3 Agustus 2012
 * 
 */
 
$sql['get_data']    = "
SELECT
    `sasaranId` AS id,
    `sasaranKode` AS kode,
    `sasaranNama` AS nama,
    `sasaranTglUbah` AS tgl_ubah,
    `sasaranUserId` AS user_id
FROM `finansi_pa_ref_sasaran` 
WHERE sasaranKode LIKE '%s' OR sasaranNama LIKE '%s'
LIMIT %s, %s
";

$sql['count_data']  = "
SELECT
    COUNT(`sasaranId`) AS total
FROM `finansi_pa_ref_sasaran` 
WHERE sasaranKode LIKE '%s' OR sasaranNama LIKE '%s'
";
