<?php

/**
 * Kumpilan query untuk memanipulasi data pada tabel finansi_keu_ref_subacc_3
 * @copyright 2011 Gamatechno
 */

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']  = "
SELECT SQL_CALC_FOUND_ROWS
   subaccKetigaKode AS kode,
   subaccKetigaNama AS nama
FROM finansi_keu_ref_subacc_3
WHERE 1 = 1
AND subaccKetigaKode LIKE '%s'
OR subaccKetigaNama LIKE '%s'
ORDER BY subaccKetigaKode
LIMIT %s, %s
";

$sql['do_check_unique_data']     = "
SELECT
   COUNT(DISTINCT subaccKetigaKode) AS `count`
FROM finansi_keu_ref_subacc_3
WHERE 1 = 1
AND (subaccKetigaKode != '%s' OR 1 = %s)
AND subaccKetigaKode = '%s'
";

$sql['check_subaccount_default'] = "
SELECT
   COUNT(DISTINCT subaccKetigaKode) AS `count`,
   REPEAT('*', IFNULL(MAX(LENGTH(subaccKetigaKode)), 5)) AS patern
FROM finansi_keu_ref_subacc_3
WHERE 1 = 1
AND LOWER(subaccKetigaNama) = 'default'
";

$sql['get_data_detail']    = "
SELECT
   subaccKetigaKode AS id,
   subaccKetigaKode AS kode,
   subaccKetigaNama AS nama
FROM finansi_keu_ref_subacc_3
WHERE 1 = 1
AND subaccKetigaKode = '%s'
LIMIT 1
";


$sql['do_add_sub_account_ketiga']="
INSERT INTO
   finansi_keu_ref_subacc_3
VALUES ('%s','%s')
";

$sql['do_update_sub_account_ketiga']="
UPDATE finansi_keu_ref_subacc_3
SET
   subaccKetigaKode ='%s',
   subaccKetigaNama ='%s'
WHERE
   subaccKetigaKode ='%s'
";

$sql['do_delete_sub_account_ketiga']="
DELETE FROM
   finansi_keu_ref_subacc_3
WHERE
   subaccKetigaKode ='%s'
";

$sql['do_delete_sub_account_ketiga_by_array_id'] ="
DELETE FROM
   finansi_keu_ref_subacc_3
WHERE
   subaccKetigaKode IN ('%s')
";
?>