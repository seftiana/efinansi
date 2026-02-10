<?php

//===GET===
$sql['get_jenis_belanja'] = 
"
   SELECT 
    `jenisbelanjaId`  AS `id`,
    `jenisbelanjaNama` AS `name`
   FROM `jenis_belanja_ref`
";

$sql['get_uraian_belanja'] = 
"
   SELECT 
    `uraianId`  AS `id_uraian_belanja`,
    `uraianNama` AS `nama_uraian_belanja`,
    `jenisbelanjaNama` AS `jenis_belanja`
   FROM `uraian_ref` LEFT JOIN jenis_belanja_ref ON `uraianJenisbelanjaId` = `jenisbelanjaId`
   WHERE `uraianNama` like '%s'
   limit %d, %d
";

$sql['get_uraian_belanja_by_id'] = 
"
   SELECT 
    `uraianId`  AS `id_uraian_belanja`,
    `uraianNama` AS `nama_uraian_belanja`,
    `uraianJenisbelanjaId` AS `jenis_belanja`
   FROM `uraian_ref` LEFT JOIN jenis_belanja_ref ON `uraianJenisbelanjaId` = `jenisbelanjaId`
   WHERE `uraianId` = '%s'
";


$sql['get_count_uraian_belanja'] = 
"
   SELECT 
    count(`uraianId`)  AS `count`
   FROM `uraian_ref`
   WHERE `uraianNama` like '%s'
";
      
$sql['insert_uraian_belanja'] = "
   INSERT INTO `uraian_ref` (`uraianJenisbelanjaId`,`uraianNama`) VALUES ('%s','%s')
";

$sql['update_uraian_belanja'] = "
   UPDATE `uraian_ref` set `uraianJenisbelanjaId` = '%s', `uraianNama`='%s' WHERE `uraianId` ='%s'
";

$sql['delete_uraian_belanja'] = "
   DELETE FROM `uraian_ref` WHERE `uraianId` ='%s'
";

$sql['cek_uraian_belanja'] = 
"
   SELECT 
    uraianId  AS id,
    lower(uraianNama) AS nama
   FROM uraian_ref
   WHERE lower(uraianNama) like '%s'
";

?>
