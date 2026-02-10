<?php
$sql['do_insert_visi']  = "
INSERT INTO `finansi_pa_ref_visi`
            (`visiRenstraid`,
             `visiKode`,
             `visiNama`,
             `visiUserId`)
VALUES ('%s',
        '%s',
        '%s',
        '%s')
";

$sql['get_data']    = "
SELECT
    v.`visiId` AS id,
    v.`visiRenstraid` AS renstra_id,
    v.`visiKode` AS kode,
    v.`visiNama` AS nama,
    v.`visiTglUbah` AS tgl_ubah,
    v.`visiUserId` AS user_id,
    r.`renstraNama` AS renstra_nama,  
    (SELECT COUNT(DISTINCT `misiId`) FROM `finansi_pa_ref_misi` 
    WHERE `misiVisiId` = v.`visiId`) AS misi
FROM `finansi_pa_ref_visi` AS v 
LEFT JOIN renstra AS r 
ON r.renstraId = v.visiRenstraId
WHERE 
    visiKode LIKE '%s' OR visiNama LIKE '%s' 
ORDER BY visiKode ASC
LIMIT %s, %s
";

$sql['count_data']  = "
SELECT
    COUNT(v.`visiId`) AS count
FROM `finansi_pa_ref_visi` AS v 
LEFT JOIN renstra AS r 
ON r.renstraId = v.visiRenstraId
WHERE 
    visiKode LIKE '%s' OR visiNama LIKE '%s' 
ORDER BY visiKode ASC
";

$sql['get_data_id'] = "
SELECT
    v.`visiId` AS id,
    v.`visiRenstraid` AS renstra_id,
    v.`visiKode` AS kode,
    v.`visiNama` AS nama,
    v.`visiTglUbah` AS tgl_ubah,
    v.`visiUserId` AS user_id,
    r.`renstraNama` AS renstra_nama,  
    (SELECT COUNT(DISTINCT `misiId`) FROM `finansi_pa_ref_misi` 
    WHERE `misiVisiId` = v.`visiId`) AS misi
FROM `finansi_pa_ref_visi` AS v 
LEFT JOIN renstra AS r 
ON r.renstraId = v.visiRenstraId
WHERE 
    v.`visiId` = '%s'
";

$sql['update_data'] = "
UPDATE `finansi_pa_ref_visi`
SET `visiRenstraid` = '%s',
    `visiKode` = '%s',
    `visiNama` = '%s',
    `visiUserId` = '%s'
WHERE `visiId` = '%s'
";

$sql['delete_data'] = "
DELETE
FROM `finansi_pa_ref_visi`
WHERE `visiId` = '%s'
";

$sql['check_visibility']    = "
SELECT
    COUNT(`visiId`) AS COUNT
FROM `finansi_pa_ref_visi`
WHERE visiKode = '%s' AND visiRenstraid = '%s'
";
?>
