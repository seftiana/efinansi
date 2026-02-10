<?php

//===GET===
$sql['get_data']="
SELECT
   sknrId AS id,
   sknrNama AS nama
FROM
   sekenario
LIMIT %s, %s
";

$sql['get_count']="
SELECT
   (sknrId) AS total
FROM
   sekenario
";


	
//===DO===

$sql['do_add']="
    INSERT INTO `sekenario_detail` 
	   ( `sknrdId`, `sknrdSknrId`, `sknrdDebetCoaId`, `sknrdKreditCoaId`, `sknrdProsen` ) 
	VALUES
    	(  '', %s , %s , %s ,%s )

";

$sql['do_update']="
    UPDATE sekenario_detail 
	   SET 
	   sknrdProsen= %s
	WHERE	
		sknrdId =%s	
";


$sql['do_delete']="
DELETE FROM `sekenario_detail`  WHERE `sknrdId` = %s
";

$sql['do_delete_all_skenario']="
DELETE FROM sekenario_detail WHERE sknrdSknrId = %s
";



?>
