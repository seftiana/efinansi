<?php

/**
 * file query database aset
 * @package komponen
 * @copyright 2012 Gamatechno Indonesia
 * @author noor hadi <noor.hadi@gamatechno.com>
 */
 
 
$sql['get_kode_aset'] ="
SELECT * FROM(
SELECT 
        CONCAT(
                LPAD(golbrgKode, 1, 0),
                '.',
                LPAD(bidangbrgKode, 2, 0),
                '.',
                LPAD(kelbrgKode, 2, 0),
                '.',
                LPAD(subkelbrgKode, 2, 0)
        ) AS `kode`,
        `subkelbrgNama` AS `nama`
FROM
        `golongan_barang_ref` 
        JOIN `bidang_barang_ref` 
                ON (`bidangbrgGolbrgId` = `golbrgId`) 
        JOIN `kelompok_barang_ref` 
                ON (`kelbrgBidangbrgId` = `bidangbrgId` ) 
        LEFT JOIN `sub_kelompok_barang_ref` 
                ON (`subkelbrgKelbrgId` = `kelbrgId`)
 ) a
 WHERE 
	kode LIKE '%s'
	AND
	nama LIKE '%s'
LIMIT %s,%s
";

$sql['get_count_kode_aset'] ="
SELECT 
count(kode) as total 
FROM(
SELECT 
        CONCAT(
                LPAD(golbrgKode, 1, 0),
                '.',
                LPAD(bidangbrgKode, 2, 0),
                '.',
                LPAD(kelbrgKode, 2, 0),
                '.',
                LPAD(subkelbrgKode, 2, 0)
        ) AS `kode`,
        `subkelbrgNama` AS `nama`
FROM
        `golongan_barang_ref` 
        JOIN `bidang_barang_ref` 
                ON (`bidangbrgGolbrgId` = `golbrgId`) 
        JOIN `kelompok_barang_ref` 
                ON (`kelbrgBidangbrgId` = `bidangbrgId` ) 
        LEFT JOIN `sub_kelompok_barang_ref` 
                ON (`subkelbrgKelbrgId` = `kelbrgId`)
 ) a
 WHERE 
	kode LIKE '%s'
	AND
	nama LIKE '%s'    
";