<?php
$sql['get_count_data_satker'] = 
   "SELECT 
      count(unitkerjaId) AS total
   FROM 
      unit_kerja_ref 
	 WHERE
	  unitkerjaParentId=0
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
		unitkerjaParentId=0
		AND unitkerjaNama LIKE '%s'
	  AND unitkerjaNamaPimpinan LIKE '%s'
   ORDER BY 
      unitkerjaNama
   LIMIT %s, %s";
   
   
//user rekorat

$sql['get_count_data_satker_pusat'] = 
   "SELECT 
       count(unitkerjaId) AS total
   FROM 
      unit_kerja_ref 
	  
   WHERE	 
      unitkerjaNama LIKE '%s'
	  AND unitkerjaNamaPimpinan LIKE '%s'";

$sql['get_data_satker_pusat'] = 
   "SELECT 
      unitkerjaId as satker_id,
	  unitkerjaKodeSistem as satker_kode_sistem,
	   unitkerjaParentId as parent,
	  unitkerjaNama as satker_nama,
	  unitkerjaNamaPimpinan as satker_pimpinan
   FROM 
      unit_kerja_ref
	WHERE 
	unitkerjaNama LIKE '%s'
	AND unitkerjaNamaPimpinan LIKE '%s'
	ORDER BY satker_kode_sistem ASC 
	LIMIT %s, %s";   


//user unit   
   
 $sql['get_count_data_satker_unit'] = 
   "SELECT 
      count(unitkerjaId) AS total
   FROM 
      unit_kerja_ref
   WHERE
	unitkerjaKodeSistem = '%s'
	OR unitkerjaKodeSistem LIKE '%s'
	AND unitkerjaNama LIKE '%s' 
	AND unitkerjaNamaPimpinan LIKE '%s'";   

$sql['get_data_satker_unit'] = 
   "SELECT 
      unitkerjaId as satker_id,
	  unitkerjaKodeSistem as satker_kode_sistem,
	  unitkerjaParentId as parent,
	  unitkerjaNama as satker_nama,
	  unitkerjaNamaPimpinan as satker_pimpinan
   FROM 
      unit_kerja_ref
	WHERE 
	unitkerjaKodeSistem = '%s'
	OR unitkerjaKodeSistem LIKE '%s'
	AND unitkerjaNama LIKE '%s' 
	AND unitkerjaNamaPimpinan LIKE '%s' 
ORDER BY unitkerjaKode ASC 
LIMIT %s,%s ";
   
   //select unit kerja
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

/**
 * added
 * @since 02 januari 2012
 */
$sql['get_total_sub_unit']="
	SELECT COUNT(unitkerjaId) AS total FROM unit_kerja_ref WHERE unitkerjaParentId = %s
";
?>