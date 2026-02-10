<?php

/**
 * Kumpilan query untuk memanipulasi data pada tabel finansi_keu_ref_subacc_5
 * @copyright 2011 Gamatechno
 */

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']  = "
SELECT SQL_CALC_FOUND_ROWS
   subaccKelimaKode AS kode,
   subaccKelimaNama AS nama
FROM finansi_keu_ref_subacc_5
WHERE 1 = 1
AND subaccKelimaKode LIKE '%s'
OR subaccKelimaNama LIKE '%s'
ORDER BY subaccKelimaKode
LIMIT %s, %s
";

$sql['do_check_unique_data']     = "
SELECT
   COUNT(DISTINCT subaccKelimaKode) AS `count`
FROM finansi_keu_ref_subacc_5
WHERE 1 = 1
AND (subaccKelimaKode != '%s' OR 1 = %s)
AND subaccKelimaKode = '%s'
";

$sql['check_subaccount_default'] = "
SELECT
   COUNT(DISTINCT subaccKelimaKode) AS `count`,
   REPEAT('*', IFNULL(MAX(LENGTH(subaccKelimaKode)), 5)) AS patern
FROM finansi_keu_ref_subacc_5
WHERE 1 = 1
AND LOWER(subaccKelimaNama) = 'default'
";

$sql['get_data_detail']    = "
SELECT
   subaccKelimaKode AS id,
   subaccKelimaKode AS kode,
   subaccKelimaNama AS nama
FROM finansi_keu_ref_subacc_5
WHERE 1 = 1
AND subaccKelimaKode = '%s'
LIMIT 1
";


$sql['do_add_sub_account_kelima']="
INSERT INTO
   finansi_keu_ref_subacc_5
VALUES ('%s','%s')
";

$sql['do_update_sub_account_kelima']="
UPDATE finansi_keu_ref_subacc_5
SET
   subaccKelimaKode ='%s',
   subaccKelimaNama ='%s'
WHERE
   subaccKelimaKode ='%s'
";

$sql['do_delete_sub_account_kelima']="
DELETE FROM
   finansi_keu_ref_subacc_5
WHERE
   subaccKelimaKode ='%s'
";

$sql['do_delete_sub_account_kelima_by_array_id'] ="
DELETE FROM
   finansi_keu_ref_subacc_5
WHERE
   subaccKelimaKode IN ('%s')
";
?>