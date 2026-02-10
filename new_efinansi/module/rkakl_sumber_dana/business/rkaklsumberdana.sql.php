<?php

/**
 * @copyright 2011 gamatechno
 * @todo query modul sumber dana pada tabel finansi_ref_sumber_dana
 */
 
 /**
  * Retrieve data
  */
  
$sql['get_rkakl_sumber_dana'] = 
"
SELECT 
	sumberdanaId as sumber_dana_id,
	sumberdanaNama as sumber_dana_nama,
	isaktif as is_aktif
FROM finansi_ref_sumber_dana
WHERE 
	sumberdanaNama like '%s'
ORDER BY 
	isaktif, sumberdanaNama ASC
	LIMIT %s, %s
";

$sql['get_rkakl_sumber_dana_by_id'] = 
"
SELECT 
	sumberdanaId as sumber_dana_id,
	sumberdanaNama as sumber_dana_nama,
	isaktif as is_aktif
FROM finansi_ref_sumber_dana
WHERE 
	sumberdanaId = '%s'
";

$sql['get_count_rkakl_sumber_dana'] = 
"
SELECT
	  count(sumberdanaId)   AS total
FROM finansi_ref_sumber_dana
WHERE 
	sumberdanaNama like '%s'";
  
  
 /**
  * data manipulation
  */
     
$sql['insert_rkakl_sumber_dana'] = "
   INSERT INTO finansi_ref_sumber_dana(sumberdanaNama,isaktif) 
	VALUES ('%s','%s')
";

$sql['update_rkakl_sumber_dana'] = "
   UPDATE finansi_ref_sumber_dana 
  	SET
   		sumberdanaNama='%s',
		isaktif ='%s'
	WHERE
	sumberdanaId = '%s'
";

$sql['delete_rkakl_sumber_dana'] = "
   DELETE FROM finansi_ref_sumber_dana 
   WHERE sumberdanaId = '%s'
";

$sql['delete_rkakl_sumber_dana_array'] = "
	DELETE
	FROM finansi_ref_sumber_dana
	WHERE sumberdanaId IN ('%s')
";