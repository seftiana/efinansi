<?php

/**
 * 
 * file : tujuan.sql.php
 * @package tujuan
 * @subpackage business
 * @filename tujuan.sql.php
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * @since 2 Agustus 2012
 * 
 */
 
$sql['get_data'] = "
SELECT 
	tujuanId AS id,
	tujuanKode AS kode,
	tujuanNama AS nama,
	tujuanTglUbah AS tgl_ubah,
	tujuanUserId AS user_id
FROM
	finansi_pa_ref_tujuan 
WHERE 
	tujuanKode LIKE '%s' 
	OR 
	tujuanNama LIKE '%s' 
ORDER BY kode	
LIMIT %s, %s 
";

$sql['get_data_by_id']    = "
SELECT 
	tujuanId AS id,
	tujuanKode AS kode,
	tujuanNama AS nama,
	tujuanTglUbah AS tgl_ubah,
	tujuanUserId AS user_id
FROM
	finansi_pa_ref_tujuan 
WHERE 
	tujuanId = %s
";

$sql['count_data']  = "
SELECT 
	COUNT(tujuanId) AS total
FROM
	finansi_pa_ref_tujuan 
WHERE 
	tujuanKode LIKE '%s' 
	OR 
	tujuanNama LIKE '%s' 
";

$sql['add'] = "
INSERT INTO `finansi_pa_ref_tujuan` (
	`tujuanKode`,
	`tujuanNama`,
	`tujuanUserId`
) 
VALUES
	('%s', '%s', '%s')
";

$sql['update']  = "
UPDATE 
	`finansi_pa_ref_tujuan` 
SET
	`tujuanKode` = '%s',
	`tujuanNama` = '%s',
	`tujuanUserId` = '%s' 
WHERE 
	`tujuanId` = '%s' 
";

$sql['delete']  = "
DELETE
FROM 
	`finansi_pa_ref_tujuan`
WHERE 
	`tujuanId` IN('%s')
";
