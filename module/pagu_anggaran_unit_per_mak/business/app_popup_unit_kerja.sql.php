<?php
$sql['get_count_data_unitkerja'] = "
SELECT 
   COUNT(unitkerjaId) as total
FROM unit_kerja_ref
   LEFT JOIN 
      (SELECT 
         unitkerjaId AS tempUnitId,
         unitkerjaKode AS tempUnitKode,
         unitkerjaNama AS tempUnitNama,
         unitkerjaParentId AS tempParentId
      FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUnitKerja ON(unitkerjaParentId=tempUnitId)
WHERE 
   (unitkerjaKode LIKE '%s' OR tempUnitKode LIKE '%s' )
   AND (unitkerjaNama LIKE '%s' OR tempUnitNama LIKE '%s')
   %s
   %s
 ";
$sql['get_data_unitkerja'] = "
SELECT 
   (if(tempUnitNama IS NULL,unitkerjaNama,tempUnitNama))    AS satker,
   (if(tempUnitId IS NULL,unitkerjaId,unitkerjaId))      AS id,
   (if(tempUnitKode IS NULL,unitkerjaKode,unitkerjaKode))   AS kodeunit,
   (if(tempUnitNama IS NULL,unitkerjaNama,unitkerjaNama))   AS unit,
   tipeunitNama as tipeunit,
   unitkerjaParentId AS parentId, 
   tipeunitNama AS tipe_unit 
FROM unit_kerja_ref
   LEFT JOIN 
      (SELECT 
         unitkerjaId       AS tempUnitId,
         unitkerjaKode     AS tempUnitKode,
         unitkerjaNama     AS tempUnitNama,
         unitkerjaParentId    AS tempParentId
         FROM unit_kerja_ref
      LEFT JOIN 
         tipe_unit_kerja_ref 
      ON (tipeunitId = unitkerjaTipeunitId)
      WHERE 
         unitkerjaParentId = 0
      )
   tmpUnitKerja ON
   (unitkerjaParentId=tempUnitId)
   LEFT JOIN 
      tipe_unit_kerja_ref 
   ON 
      (tipeunitId = unitkerjaTipeunitId)
WHERE 
   (unitkerjaKode LIKE '%s' OR tempUnitKode LIKE '%s' )
   AND (unitkerjaNama LIKE '%s' OR tempUnitNama LIKE '%s')
   %s
   %s
ORDER BY 
   parentId ASC 
LIMIT %s, %s
";   
    
$sql['get_data_tipe_unit'] = "
SELECT 
   tipeunitId AS id,
   tipeunitNama AS name 
FROM tipe_unit_kerja_ref 
ORDER BY tipeunitNama";

$sql['get_data']     = "
SELECT SQL_CALC_FOUND_ROWS
   unitkerjaId AS id, 
   unitkerjaKodeSistem AS kodeSistem,
   unitkerjaKode AS kode, 
   unitkerjaNama AS nama, 
   tipeunitNama AS tipeUnit, 
   unitkerjaNamaPimpinan AS namaPimpinan, 
   unitkerjaParentId AS parentId, 
   SUBSTRING_INDEX(tmp.code, '.', 1)+0 AS a, 
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.code, '.', 2), '.', -1)+0 AS b, 
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.code, '.', 3), '.', -1)+0 AS c, 
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.code, '.', 4), '.', -1)+0 AS d, 
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.code, '.', 5), '.', -1)+0 AS e
FROM unit_kerja_ref 
JOIN (SELECT unitkerjaId AS id, 
CASE 
   WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
   WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0') 
   WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0') 
   WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0') 
   WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN unitkerjaKodeSistem
END AS `code` 
FROM unit_kerja_ref) AS tmp 
   ON tmp.id = unitkerjaId 
LEFT JOIN tipe_unit_kerja_ref 
   ON unitkerjaTipeunitId = tipeunitId
WHERE 1 = 1 
   AND unitkerjaKode LIKE '%s' 
   AND unitkerjaNama LIKE '%s' 
   AND (tipeunitId = '%s' OR 1 = %s)
   AND CASE (SELECT `roleName` AS role_name 
      FROM `gtfw_role` 
         JOIN user_unit_kerja 
            ON (user_unit_kerja.`userunitkerjaRoleId` = gtfw_role.`roleId`)  
      WHERE user_unit_kerja.`userunitkerjaUserId` = %s) 
         WHEN 'Administrator' THEN (1 = 1 AND (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)) 
      WHEN 'OperatorUnit' THEN (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s) 
      WHEN 'OperatorSubUnit' THEN  unitkerjaId = %s
   END 
ORDER BY SUBSTRING_INDEX(tmp.code, '.', 1)+0, 
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.code, '.', 2), '.', -1)+0, 
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.code, '.', 3), '.', -1)+0, 
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.code, '.', 4), '.', -1)+0, 
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.code, '.', 5), '.', -1)+0 
LIMIT %s, %s
";

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";
?>
