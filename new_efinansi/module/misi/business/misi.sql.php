<?php
$sql['get_data_visi']    = "
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

$sql['count_data_visi']  = "
SELECT
    COUNT(v.`visiId`) AS count
FROM `finansi_pa_ref_visi` AS v 
LEFT JOIN renstra AS r 
ON r.renstraId = v.visiRenstraId
WHERE 
    visiKode LIKE '%s' OR visiNama LIKE '%s' 
ORDER BY visiKode ASC
";

$sql['do_insert_data']  = "
INSERT INTO `finansi_pa_ref_misi`
            (`misiVisiId`,
             `misiKode`,
             `misiNama`,
             `misiUserId`)
VALUES ('%s',
        '%s',
        '%s',
        '%s')
";

$sql['get_data']    = "
SELECT
    misi.`misiId` AS id,
    misi.`misiVisiId` AS visi_id,
    misi.`misiKode` AS kode,
    misi.`misiNama` AS nama,
    misi.`misiTglUbah` AS tgl_ubah,
    misi.`misiUserId` AS user_id,
    visi.`visiKode` AS visi_kode,
    visi.`visiNama` AS visi_nama, 
    renstra.`renstraId` AS renstra_id,
    renstra.`renstraNama` AS renstra_nama 
FROM `finansi_pa_ref_misi` AS misi 
LEFT JOIN finansi_pa_ref_visi AS visi
ON misi.`misiVisiId` = visi.`visiId` 
JOIN renstra AS renstra 
ON renstra.`renstraId` = visi.`visiRenstraid` 
WHERE (misi.`misiKode` LIKE '%s' OR misi.`misiNama` LIKE '%s') %s misi.`misiVisiId` = '%s'
LIMIT %s, %s
";

$sql['count_data']  = "
SELECT
    COUNT(misi.`misiId`) AS total 
FROM `finansi_pa_ref_misi` AS misi 
LEFT JOIN finansi_pa_ref_visi AS visi
ON misi.`misiVisiId` = visi.`visiId` 
JOIN renstra AS renstra 
ON renstra.`renstraId` = visi.`visiRenstraid` 
WHERE (misi.`misiKode` LIKE '%s' OR misi.`misiNama` LIKE '%s') %s misi.`misiVisiId` = '%s'
";

$sql['get_data_id'] = "
SELECT
    misi.`misiId` AS id,
    misi.`misiVisiId` AS visi_id,
    misi.`misiKode` AS kode,
    misi.`misiNama` AS nama,
    misi.`misiTglUbah` AS tgl_ubah,
    misi.`misiUserId` AS user_id,
    visi.`visiKode` AS visi_kode,
    visi.`visiNama` AS visi_nama, 
    renstra.`renstraId` AS renstra_id,
    renstra.`renstraNama` AS renstra_nama 
FROM `finansi_pa_ref_misi` AS misi 
LEFT JOIN finansi_pa_ref_visi AS visi
ON misi.`misiVisiId` = visi.`visiId` 
JOIN renstra AS renstra 
ON renstra.`renstraId` = visi.`visiRenstraid` 
WHERE misi.misiId = '%s'
";

$sql['update_data'] = "
UPDATE `finansi_pa_ref_misi`
SET `misiVisiId` = '%s',
    `misiKode` = '%s',
    `misiNama` = '%s',
    `misiUserId` = '%s'
WHERE `misiId` = '%s'
";

$sql['delete_data']  = "
DELETE
FROM `finansi_pa_ref_misi`
WHERE `misiId` = '%s'
";
?>
