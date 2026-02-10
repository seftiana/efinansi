<?php

//untuk combo box

$sql['get_data_tipe_unit'] = 
   "SELECT 
      tipeunitId		as id,
	  tipeunitNama		as name
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
	c.unitkerjaId AS id,
	c.unitkerjaKode AS kodeunit,
	c.unitkerjaNama AS unit,
	c.unitkerjaParentId AS parent_id,
	c.unitkerjaKodeSistem AS kodesistem,
	tipeunitNama AS tipeunit
FROM 
	unit_kerja_ref c
	LEFT JOIN tipe_unit_kerja_ref 
		ON tipe_unit_kerja_ref.tipeunitId = c.unitkerjaTipeunitId 
WHERE 
    
	 c.unitkerjaKode LIKE '%s'
	AND c.unitkerjaNama LIKE '%s'
	AND (c.unitkerjaTipeunitId = '%s' OR %s)
	
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
WHERE 

 c.unitkerjaKode LIKE '%s'
	AND c.unitkerjaNama LIKE '%s'
	AND (c.unitkerjaTipeunitId = '%s' OR %s)
";

$sql['get_data_unit_anak']="
SELECT 
	uk.`unitkerjaId` AS unit_kerja_id,
	uk.`unitkerjaKode` AS unit_kerja_kode,
	uk.`unitkerjaNama` AS unit_kerja_nama,
	uk.`unitkerjaParentId` AS unit_parent_id
FROM unit_kerja_ref uk
WHERE 
	uk.`unitkerjaParentId` = '%s'
	OR
	uk.`unitkerjaId` = '%s'
";

$sql['get_data_unit_parent_id']="
SELECT 
  uk.`unitkerjaId`  AS unit_parent_id 
FROM
  unit_kerja_ref uk 
WHERE
  uk.unitkerjaParentId = '%s' 
  OR
  uk.`unitkerjaId` ='%s'
GROUP BY unit_parent_id  
";

/**
 * end
 */
?> 