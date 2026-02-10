<?php

$sql['do_set_is_tambah'] = "
UPDATE `kelompok_laporan_ref` SET `kellapIsTambah` = '%s' WHERE `kellapId` = '%s'
";

$sql['do_set_is_tambah_with_child'] ="
UPDATE `kelompok_laporan_ref` SET `kellapIsTambah` = '%s' WHERE kellapKodeSistem LIKE '%s'
";

$sql['get_laporan_nama'] ="
SELECT
    `kellapNama` AS nama
FROM
    `kelompok_laporan_ref`
WHERE
    `kellapId` = '%s'
";

$sql['get_kode_sistem'] ="
SELECT
    `kellapKodeSistem` AS kode_sistem
FROM
    `kelompok_laporan_ref`
WHERE`kellapId` =   '%s'
";

?>