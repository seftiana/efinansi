<?php

/**
 * Kumpilan query untuk memanipulasi data pada tabel finansi_keu_ref_subacc_6
 * @copyright 2011 Gamatechno
 */

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']  = "
SELECT SQL_CALC_FOUND_ROWS
   subaccKeenamKode AS kode,
   subaccKeenamNama AS nama
FROM finansi_keu_ref_subacc_6
WHERE 1 = 1
AND subaccKeenamKode LIKE '%s'
OR subaccKeenamNama LIKE '%s'
ORDER BY subaccKeenamKode
LIMIT %s, %s
";

$sql['do_check_unique_data']     = "
SELECT
   COUNT(DISTINCT subaccKeenamKode) AS `count`
FROM finansi_keu_ref_subacc_6
WHERE 1 = 1
AND (subaccKeenamKode != '%s' OR 1 = %s)
AND subaccKeenamKode = '%s'
";

$sql['check_subaccount_default'] = "
SELECT
   COUNT(DISTINCT subaccKeenamKode) AS `count`,
   REPEAT('*', IFNULL(MAX(LENGTH(subaccKeenamKode)), 5)) AS patern
FROM finansi_keu_ref_subacc_6
WHERE 1 = 1
AND LOWER(subaccKeenamNama) = 'default'
";

$sql['get_data_detail']    = "
SELECT
   subaccKeenamKode AS id,
   subaccKeenamKode AS kode,
   subaccKeenamNama AS nama
FROM finansi_keu_ref_subacc_6
WHERE 1 = 1
AND subaccKeenamKode = '%s'
LIMIT 1
";


$sql['do_add_sub_account_keenam']="
INSERT INTO
   finansi_keu_ref_subacc_6
VALUES ('%s','%s')
";

$sql['do_update_sub_account_keenam']="
UPDATE finansi_keu_ref_subacc_6
SET
   subaccKeenamKode ='%s',
   subaccKeenamNama ='%s'
WHERE
   subaccKeenamKode ='%s'
";

$sql['do_delete_sub_account_keenam']="
DELETE FROM
   finansi_keu_ref_subacc_6
WHERE
   subaccKeenamKode ='%s'
";

$sql['do_delete_sub_account_keenam_by_array_id'] ="
DELETE FROM
   finansi_keu_ref_subacc_6
WHERE
   subaccKeenamKode IN ('%s')
";
?>