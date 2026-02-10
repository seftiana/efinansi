<?php

/**
 * Kumpilan query untuk memanipulasi data pada tabel finansi_keu_ref_subacc_2
 * @copyright 2011 Gamatechno
 */

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']  = "
SELECT SQL_CALC_FOUND_ROWS
   subaccKeduaKode AS kode,
   subaccKeduaNama AS nama
FROM finansi_keu_ref_subacc_2
WHERE 1 = 1
AND subaccKeduaKode LIKE '%s'
OR subaccKeduaNama LIKE '%s'
ORDER BY subaccKeduaKode
LIMIT %s, %s
";

$sql['do_check_unique_data']     = "
SELECT
   COUNT(DISTINCT subaccKeduaKode) AS `count`
FROM finansi_keu_ref_subacc_2
WHERE 1 = 1
AND (subaccKeduaKode != '%s' OR 1 = %s)
AND subaccKeduaKode = '%s'
";

$sql['check_subaccount_default'] = "
SELECT
   COUNT(DISTINCT subaccKeduaKode) AS `count`,
   REPEAT('*', IFNULL(MAX(LENGTH(subaccKeduaKode)), 5)) AS patern
FROM finansi_keu_ref_subacc_2
WHERE 1 = 1
AND LOWER(subaccKeduaNama) = 'default'
";

$sql['get_data_detail']    = "
SELECT
   subaccKeduaKode AS id,
   subaccKeduaKode AS kode,
   subaccKeduaNama AS nama
FROM finansi_keu_ref_subacc_2
WHERE 1 = 1
AND subaccKeduaKode = '%s'
LIMIT 1
";


$sql['do_add_sub_account_kedua']="
INSERT INTO
   finansi_keu_ref_subacc_2
VALUES ('%s','%s')
";

$sql['do_update_sub_account_kedua']="
UPDATE finansi_keu_ref_subacc_2
SET
   subaccKeduaKode ='%s',
   subaccKeduaNama ='%s'
WHERE
   subaccKeduaKode ='%s'
";

$sql['do_delete_sub_account_kedua']="
DELETE FROM
   finansi_keu_ref_subacc_2
WHERE
   subaccKeduaKode ='%s'
";

$sql['do_delete_sub_account_kedua_by_array_id'] ="
DELETE FROM
   finansi_keu_ref_subacc_2
WHERE
   subaccKeduaKode IN ('%s')
";
?>