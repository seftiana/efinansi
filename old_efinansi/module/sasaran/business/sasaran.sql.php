<?php
$sql['get_data']    = "
SELECT
    `sasaranId` AS id,
    `sasaranKode` AS kode,
    `sasaranNama` AS nama,
    `sasaranTglUbah` AS tgl_ubah,
    `sasaranUserId` AS user_id
FROM `finansi_pa_ref_sasaran` 
WHERE sasaranKode LIKE '%s' OR sasaranNama LIKE '%s'
LIMIT %s, %s
";

$sql['get_data_by_id']    = "
SELECT 
  s.`sasaranId` AS id,
  t.`tujuanId` AS tujuan_id,
  t.`tujuanNama` AS tujuan,
  s.`sasaranKode` AS kode,
  s.`sasaranNama` AS nama,
  s.`sasaranUserId` AS user_id 
FROM
  `finansi_pa_ref_sasaran` s 
  LEFT JOIN finansi_pa_ref_tujuan t 
    ON t.`tujuanId` = s.`sasaranTujuanId` 
WHERE `sasaranId` = '%s'
";

$sql['count_data']  = "
SELECT
    COUNT(`sasaranId`) AS total
FROM `finansi_pa_ref_sasaran` 
WHERE sasaranKode LIKE '%s' OR sasaranNama LIKE '%s'
";

$sql['insert_into_sasaran'] = "
INSERT INTO `finansi_pa_ref_sasaran`
            (`sasaranTujuanId`,
            `sasaranKode`,
             `sasaranNama`,
             `sasaranUserId`)
VALUES ('%s',
        '%s',
        '%s',
        '%s')
";

$sql['update_sasaran']  = "
UPDATE `finansi_pa_ref_sasaran`
SET `sasaranTujuanId` ='%s',
	`sasaranKode` = '%s',
    `sasaranNama` = '%s',
    `sasaranUserId` = '%s'
WHERE `sasaranId` = '%s'
";

$sql['delete_sasaran']  = "
DELETE
FROM `finansi_pa_ref_sasaran`
WHERE `sasaranId` = '%s'
";

$sql['delete']="
DELETE
FROM `finansi_pa_ref_sasaran`
WHERE `sasaranId` IN ('%s')
";
