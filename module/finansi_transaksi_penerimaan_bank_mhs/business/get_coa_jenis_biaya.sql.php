<?php

$sql['get_coa_jenis_biaya'] ="
SELECT
   c.`coaId` AS coa_id,
   c.`coaKodeAkun` AS coa_kode,
   c.`coaNamaAkun` AS coa_nama,
   c.`coaIsDebetPositif` AS coa_is_d_pos,
   IF(c.`coaIsDebetPositif`= 0,'K','D') AS dk
FROM
    coa c
WHERE
  c.`coaId` IN (%s)   
";


$sql['get_coa_deposit_masuk'] ="
SELECT
   c.`coaId` AS coa_id,
   c.`coaKodeAkun` AS coa_kode,
   c.`coaNamaAkun` AS coa_nama,
   c.`coaIsDebetPositif` AS coa_is_d_pos,
   IF(c.`coaIsDebetPositif`= 0,'K','D') AS dk
FROM
    coa c
WHERE 
    c.`coaIsDepMasuk` = 1
LIMIT 1
";