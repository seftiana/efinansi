<?php

/**
 * Kumpilan query untuk memanipulasi data pada tabel finansi_keu_ref_subacc_4
 * @copyright 2011 Gamatechno
 */

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']  = "
SELECT SQL_CALC_FOUND_ROWS
   subaccKeempatKode AS kode,
   subaccKeempatNama AS nama
FROM finansi_keu_ref_subacc_4
WHERE 1 = 1
AND subaccKeempatKode LIKE '%s'
OR subaccKeempatNama LIKE '%s'
ORDER BY subaccKeempatKode
LIMIT %s, %s
";

$sql['do_check_unique_data']     = "
SELECT
   COUNT(DISTINCT subaccKeempatKode) AS `count`
FROM finansi_keu_ref_subacc_4
WHERE 1 = 1
AND (subaccKeempatKode != '%s' OR 1 = %s)
AND subaccKeempatKode = '%s'
";

$sql['check_subaccount_default'] = "
SELECT
   COUNT(DISTINCT subaccKeempatKode) AS `count`,
   REPEAT('*', IFNULL(MAX(LENGTH(subaccKeempatKode)), 5)) AS patern
FROM finansi_keu_ref_subacc_4
WHERE 1 = 1
AND LOWER(subaccKeempatNama) = 'default'
";

$sql['get_data_detail']    = "
SELECT
   subaccKeempatKode AS id,
   subaccKeempatKode AS kode,
   subaccKeempatNama AS nama
FROM finansi_keu_ref_subacc_4
WHERE 1 = 1
AND subaccKeempatKode = '%s'
LIMIT 1
";


$sql['do_add_sub_account_keempat']="
INSERT INTO
   finansi_keu_ref_subacc_4
VALUES ('%s','%s')
";

$sql['do_update_sub_account_keempat']="
UPDATE finansi_keu_ref_subacc_4
SET
   subaccKeempatKode ='%s',
   subaccKeempatNama ='%s'
WHERE
   subaccKeempatKode ='%s'
";

$sql['do_delete_sub_account_keempat']="
DELETE FROM
   finansi_keu_ref_subacc_4
WHERE
   subaccKeempatKode ='%s'
";

$sql['do_delete_sub_account_keempat_by_array_id'] ="
DELETE FROM
   finansi_keu_ref_subacc_4
WHERE
   subaccKeempatKode IN ('%s')
";
?>