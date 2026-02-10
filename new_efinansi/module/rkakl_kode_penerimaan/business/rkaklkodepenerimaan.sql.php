<?php
$sql['get_rkakl_kode_penerimaan'] = 
"
   SELECT
	  rkaklKodePenerimaanId   AS id,
	  rkaklKodePenerimaanKode AS kode,
	  rkaklKodePenerimaanNama AS nama
	FROM finansi_ref_rkakl_kode_penerimaan
	WHERE rkaklKodePenerimaanKode LIKE '%s'
		 AND rkaklKodePenerimaanNama LIKE '%s' ORDER BY rkaklKodePenerimaanKode ASC
	LIMIT %s, %s
";

$sql['get_rkakl_kode_penerimaan_by_id'] = 
"
   SELECT
	  rkaklKodePenerimaanId   AS id,
	  rkaklKodePenerimaanKode AS kode,
	  rkaklKodePenerimaanNama AS nama
	FROM finansi_ref_rkakl_kode_penerimaan
	WHERE rkaklKodePenerimaanId = %s
";


$sql['get_count_rkakl_kode_penerimaan'] = 
"
   SELECT
	  count(rkaklKodePenerimaanId)   AS `count`
	FROM finansi_ref_rkakl_kode_penerimaan
   WHERE rkaklKodePenerimaanKode LIKE '%s'
		 AND rkaklKodePenerimaanNama LIKE '%s'
";
      
$sql['insert_rkakl_kode_penerimaan'] = "
   INSERT INTO `finansi_ref_rkakl_kode_penerimaan` (`rkaklKodePenerimaanKode`,`rkaklKodePenerimaanNama`) 
	VALUES ('%s','%s')
";

$sql['update_rkakl_kode_penerimaan'] = "
   UPDATE `finansi_ref_rkakl_kode_penerimaan` set `rkaklKodePenerimaanKode` = '%s', `rkaklKodePenerimaanNama`='%s'
	WHERE `rkaklKodePenerimaanId` ='%s'
";

$sql['delete_rkakl_kode_penerimaan'] = "
   DELETE FROM `finansi_ref_rkakl_kode_penerimaan` WHERE `rkaklKodePenerimaanId` ='%s'
";

$sql['delete_rkakl_kode_penerimaan_array'] = "
	DELETE
	FROM finansi_ref_rkakl_kode_penerimaan
	WHERE rkaklKodePenerimaanId IN ('%s')
";

?>
