<?php

/**
 * 
 * file : popup_tujuan.sql.php
 * @package sasaran
 * @subpackage business
 * @filename popup_tujuan.sql.php
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
