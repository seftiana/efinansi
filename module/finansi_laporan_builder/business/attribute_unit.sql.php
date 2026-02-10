<?php

$sql['get_unit_kerja_info'] = "
SELECT
    uk.`unitkerjaId` AS unit_id,
    uk.`unitkerjaKode` AS unit_kode,
    uk.`unitkerjaNama` AS unit_nama,
    uk.`unitkerjaParentId` AS unit_parent
FROM
    unit_kerja_ref uk
WHERE
    uk.`unitkerjaKode` = '%s'
";

$sql['get_child_unit_kerja_kode'] ="
SELECT
    DISTINCT SUBSTRING_INDEX(ukr.`unitkerjaKode` ,'.',1) AS kode
FROM
    unit_kerja_ref ukr 
WHERE
    ukr.`unitkerjaParentId`= '%s'
";