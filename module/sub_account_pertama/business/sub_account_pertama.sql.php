<?php

/**
 * Kumpilan query untuk memanipulasi data pada tabel finansi_keu_ref_subacc_1
 * @copyright 2011 Gamatechno
 */

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']  = "
SELECT SQL_CALC_FOUND_ROWS
   subaccPertamaKode AS kode,
   subaccPertamaNama AS nama
FROM finansi_keu_ref_subacc_1
WHERE 1 = 1
AND subaccPertamaKode LIKE '%s'
OR subaccPertamaNama LIKE '%s'
ORDER BY subaccPertamaKode
LIMIT %s, %s
";

$sql['do_check_unique_data']     = "
SELECT
   COUNT(DISTINCT subaccPertamaKode) AS `count`
FROM finansi_keu_ref_subacc_1
WHERE 1 = 1
AND (subaccPertamaKode != '%s' OR 1 = %s)
AND subaccPertamaKode = '%s'
";

$sql['check_subaccount_default'] = "
SELECT
   COUNT(DISTINCT subaccPertamaKode) AS `count`,
   REPEAT('*', IFNULL(MAX(LENGTH(subaccPertamaKode)), 5)) AS patern
FROM finansi_keu_ref_subacc_1
WHERE 1 = 1
AND LOWER(subaccPertamaNama) = 'default' OR subaccPertamaKode = '00'
";

$sql['get_data_detail']    = "
SELECT
   subaccPertamaKode AS id,
   subaccPertamaKode AS kode,
   subaccPertamaNama AS nama
FROM finansi_keu_ref_subacc_1
WHERE 1 = 1
AND subaccPertamaKode = '%s'
LIMIT 1
";


$sql['do_add_sub_account_pertama']="
INSERT INTO
   finansi_keu_ref_subacc_1
VALUES ('%s','%s')
";

$sql['do_update_sub_account_pertama']="
UPDATE finansi_keu_ref_subacc_1
SET
   subaccPertamaKode ='%s',
   subaccPertamaNama ='%s'
WHERE
   subaccPertamaKode ='%s'
";

$sql['do_delete_sub_account_pertama']="
DELETE FROM
   finansi_keu_ref_subacc_1
WHERE
   subaccPertamaKode ='%s'
";

$sql['do_delete_sub_account_pertama_by_array_id'] ="
DELETE FROM
   finansi_keu_ref_subacc_1
WHERE
   subaccPertamaKode IN ('%s')
";
?>