<?php

//===GET===
$sql['get_data'] = "
SELECT 
 sknrId AS id,
 sknrNama AS nama
FROM
 sekenario
LIMIT %s, %s
   ";     

$sql['get_count'] = "
SELECT 
 COUNT(sknrId) AS total
FROM
 sekenario
LIMIT 1
";     

$sql['get_data_detail']="
SELECT
 s.sknrId AS skenario_id,
 s.sknrNama AS skenario_nama,
 sd.sknrdId AS skenariodetail_id,
 sd.sknrdDebetCoaId AS debet_id,
 sd.sknrdKreditCoaId AS kredit_id,
 
 IFNULL((SELECT coaKodeAkun FROM coa WHERE coaId=sknrdDebetCoaId),'') AS skenariodetail_debet_kode,
 IFNULL((SELECT coaNamaAkun FROM coa WHERE coaId=sknrdDebetCoaId),'') AS skenariodetail_debet, 
 
 IFNULL((SELECT coaKodeAkun FROM coa WHERE coaId=sknrdKreditCoaId),'') AS skenariodetail_kredit_kode,
 IFNULL((SELECT coaNamaAkun FROM coa WHERE coaId=sknrdKreditCoaId),'') AS skenariodetail_kredit,
 
 sd.sknrdProsen AS skenariodetail_prosen
 
FROM 
 sekenario s 
 LEFT JOIN sekenario_detail sd ON s.sknrId = sd.sknrdSknrId

WHERE
 s.sknrId LIKE %s

ORDER BY
 skenariodetail_debet DESC
";

	
//===DO===
$sql['do_add']="
   INSERT INTO `sekenario` 
      (`sknrNama` ) 
   VALUES 
      ('%s')
";

$sql['do_update']="
UPDATE `sekenario` 
SET  
  `sknrNama`=%s
WHERE
  `sknrId`=%s
";


$sql['do_delete']="
DELETE FROM sekenario WHERE sknrId = %s;
";

//====meghilangkan doble data===

$sql['select_id_detail']="
SELECT 
	sknrdId
FROM 
	sekenario_detail
WHERE
	sknrdSknrId =%s AND
	sknrdDebetCoaId=%s

order by sknrdId ASC";

$sql['select_delete_dobel_detail']="
DELETE
FROM 
	sekenario_detail
WHERE
	sknrdSknrId =%s ";
	
$sql['do_update_detail']="
    UPDATE sekenario_detail 
	   SET 
	   sknrdProsen= %s
	WHERE	
		sknrdId =%s	
";

	
?>
