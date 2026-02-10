<?php
$sql['get_count_data_satker'] = 
   "SELECT 
      count(unitkerjaId) AS total
   FROM 
      unit_kerja_ref
   WHERE
	  unitkerjaKodeSistem = '%s'
	OR unitkerjaKodeSistem LIKE '%s'
      AND unitkerjaNama LIKE '%s'
	  AND unitkerjaNamaPimpinan LIKE '%s'";

$sql['get_data_satker'] = 
   "SELECT 
      unitkerjaId as satker_id,
	  unitkerjaNama as satker_nama,
	  unitkerjaNamaPimpinan as satker_pimpinan
   FROM 
      unit_kerja_ref
	WHERE 
		unitkerjaKodeSistem = '%s'
	OR unitkerjaKodeSistem LIKE '%s'
		AND unitkerjaNama LIKE '%s'
	  AND unitkerjaNamaPimpinan LIKE '%s'
   ORDER BY 
      unitkerjaKodeSistem
   LIMIT %s, %s";

/**
 * added
 * @since 02 januari 2012
 */

$sql['get_unit'] = "
SELECT unitkerjaId AS id, 
unitkerjaNama AS nama, 
unitkerjaKode AS kode,
`unitKerjaJenisId` AS jenis,
`unitkerjaParentId` AS parent,
unitkerjaKodeSistem as kodeSistem        
FROM user_unit_kerja AS uk 
LEFT JOIN 
unit_kerja_ref AS ukr 
ON uk.userunitkerjaUnitkerjaId = unitkerjaId 
WHERE uk.userunitkerjaUserId = '%s'
";

$sql['get_total_sub_unit']="
	SELECT COUNT(unitkerjaId) AS total FROM unit_kerja_ref WHERE unitkerjaParentId = %s
";

/**
 * end
 */
 
$sql['get_count_data_satker_old'] = 
   "SELECT 
      count(unitkerjaId) AS total
   FROM 
      unit_kerja_ref
   WHERE
	  unitkerjaParentId=0
      AND unitkerjaNama LIKE '%s'
	  AND unitkerjaNamaPimpinan LIKE '%s'";

$sql['get_data_satker_old'] = 
   "SELECT 
      unitkerjaId as satker_id,
	  unitkerjaNama as satker_nama,
	  unitkerjaNamaPimpinan as satker_pimpinan
   FROM 
      unit_kerja_ref
	WHERE 
		unitkerjaParentId=0
		AND unitkerjaNama LIKE '%s'
	  AND unitkerjaNamaPimpinan LIKE '%s'
   ORDER BY 
      unitkerjaNama
   LIMIT %s, %s";

/**
 * added
 * @since 02 januari 2012
 */
$sql['get_total_sub_unit']="
	SELECT COUNT(unitkerjaId) AS total FROM unit_kerja_ref WHERE unitkerjaParentId = %s
";