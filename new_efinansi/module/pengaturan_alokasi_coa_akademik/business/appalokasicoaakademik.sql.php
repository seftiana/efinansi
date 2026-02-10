<?php

$sql['get_count_coa'] = "
   SELECT FOUND_ROWS() AS total
";

$sql['get_data_coa'] = "
SELECT  SQL_CALC_FOUND_ROWS
  acoa.`coaAlokasiAkademikId` AS id,
  acoa.`coaAlokasiAkademikCoaId` AS coa_id,
  c.`coaKodeAkun` AS coa_kode,
  c.`coaNamaAkun` AS coa_nama
  
FROM 
    `finansi_keu_coa_alokasi_akademik` acoa
    JOIN coa c
    ON c.`coaId` = acoa.`coaAlokasiAkademikCoaId`
WHERE
       c.`coaKodeAkun` LIKE '%s'
      AND
       c.`coaNamaAkun` LIKE '%s'
LIMIT %s,%s
";

// do add detil coa kel laporan
$sql['do_add_coa'] =  
"INSERT INTO `finansi_keu_coa_alokasi_akademik`
            (`coaAlokasiAkademikCoaId`)
VALUES ('%s')";

$sql['do_delete_coa_by_id'] =
   "DELETE
FROM `finansi_keu_coa_alokasi_akademik`
WHERE `coaAlokasiAkademikId` ='%s'";

$sql['do_delete_coa_by_array_id'] =
   "DELETE
FROM `finansi_keu_coa_alokasi_akademik`
WHERE `coaAlokasiAkademikId`  IN ('%s')";

?>