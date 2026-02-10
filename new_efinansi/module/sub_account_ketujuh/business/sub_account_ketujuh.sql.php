<?php

/**
 * Kumpilan query untuk memanipulasi data pada tabel finansi_keu_ref_subacc_7
 * @copyright 2011 Gamatechno
 */

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']  = "
SELECT SQL_CALC_FOUND_ROWS
   subaccKetujuhKode AS kode,
   subaccKetujuhNama AS nama
FROM finansi_keu_ref_subacc_7
WHERE 1 = 1
AND subaccKetujuhKode LIKE '%s'
OR subaccKetujuhNama LIKE '%s'
ORDER BY subaccKetujuhKode
LIMIT %s, %s
";

$sql['do_check_unique_data']     = "
SELECT
   COUNT(DISTINCT subaccKetujuhKode) AS `count`
FROM finansi_keu_ref_subacc_7
WHERE 1 = 1
AND (subaccKetujuhKode != '%s' OR 1 = %s)
AND subaccKetujuhKode = '%s'
";

$sql['check_subaccount_default'] = "
SELECT
   COUNT(DISTINCT subaccKetujuhKode) AS `count`,
   REPEAT('*', IFNULL(MAX(LENGTH(subaccKetujuhKode)), 5)) AS patern
FROM finansi_keu_ref_subacc_7
WHERE 1 = 1
AND LOWER(subaccKetujuhNama) = 'default'
";

$sql['get_data_detail']    = "
SELECT
   subaccKetujuhKode AS id,
   subaccKetujuhKode AS kode,
   subaccKetujuhNama AS nama
FROM finansi_keu_ref_subacc_7
WHERE 1 = 1
AND subaccKetujuhKode = '%s'
LIMIT 1
";


$sql['do_add_sub_account_ketujuh']="
INSERT INTO
   finansi_keu_ref_subacc_7
VALUES ('%s','%s')
";

$sql['do_update_sub_account_ketujuh']="
UPDATE finansi_keu_ref_subacc_7
SET
   subaccKetujuhKode ='%s',
   subaccKetujuhNama ='%s'
WHERE
   subaccKetujuhKode ='%s'
";

$sql['do_delete_sub_account_ketujuh']="
DELETE FROM
   finansi_keu_ref_subacc_7
WHERE
   subaccKetujuhKode ='%s'
";

$sql['do_delete_sub_account_ketujuh_by_array_id'] ="
DELETE FROM
   finansi_keu_ref_subacc_7
WHERE
   subaccKetujuhKode IN ('%s')
";
?>