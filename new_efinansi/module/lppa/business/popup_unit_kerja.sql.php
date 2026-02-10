<?php

//untuk combo box

$sql['get_data_tipe_unit'] = 
   "SELECT 
      tipeunitId        as id,
      tipeunitNama      as name
   FROM 
      tipe_unit_kerja_ref
   ORDER BY 
      tipeunitNama";
   
/**
 * add 
 * @since 4 Januari 2012
 */
$sql['get_total_sub_unit_kerja']="
SELECT 
    count(unitkerjaId) as total
FROM unit_kerja_ref
WHERE unitkerjaParentId = %s
";

$sql['get_list_unit_kerja']="
SELECT 
    IFNULL(p.unitkerjaKode,c.unitkerjaKode) AS kodesatker,
    IFNULL(p.unitkerjaNama,c.unitkerjaNama) AS satker,
    c.unitkerjaId AS id,
    c.unitkerjaKode AS kodeunit,
    c.unitkerjaNama AS unit,
    c.unitkerjaParentId AS parentId,
    c.unitkerjaKodeSistem AS kodesistem,
    tipeunitNama AS tipeunit
FROM 
    unit_kerja_ref c
    LEFT JOIN tipe_unit_kerja_ref 
        ON tipe_unit_kerja_ref.tipeunitId = c.unitkerjaTipeunitId 
    LEFT JOIN
        unit_kerja_ref p ON p.unitkerjaId = c.unitkerjaParentId
WHERE 
(c.unitkerjaKodeSistem LIKE 
    CONCAT((
            SELECT  
                unitkerjaKodeSistem 
            FROM 
                unit_kerja_ref 
            WHERE 
                unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
    c.unitkerjaKodeSistem = 
            (SELECT 
                unitkerjaKodeSistem 
            FROM 
                unit_kerja_ref 
            WHERE 
                unit_kerja_ref.unitkerjaId='%s')
    )   
    AND c.unitkerjaKode LIKE '%s'
    AND c.unitkerjaNama LIKE '%s'
    AND c.unitkerjaTipeunitId LIKE '%s'
ORDER BY
    c.unitkerjaKode ASC
LIMIT %s, %s
";

$sql['get_count_list_unit_kerja']="
SELECT 
    count(c.unitkerjaId) AS total
FROM 
    unit_kerja_ref c
    LEFT JOIN tipe_unit_kerja_ref 
        ON tipe_unit_kerja_ref.tipeunitId = c.unitkerjaTipeunitId 
    LEFT JOIN
        unit_kerja_ref p ON p.unitkerjaId = c.unitkerjaParentId
WHERE 
(c.unitkerjaKodeSistem LIKE 
    CONCAT((
            SELECT  
                unitkerjaKodeSistem 
            FROM 
                unit_kerja_ref 
            WHERE 
                unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
    c.unitkerjaKodeSistem = 
            (SELECT 
                unitkerjaKodeSistem 
            FROM 
                unit_kerja_ref 
            WHERE 
                unit_kerja_ref.unitkerjaId='%s')
    )   
    AND c.unitkerjaKode LIKE '%s'
    AND c.unitkerjaNama LIKE '%s'
    AND c.unitkerjaTipeunitId LIKE '%s'
";
/**
 * end
 */