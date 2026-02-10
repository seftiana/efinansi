<?php
$sql['insert_logger'] = 
"
   INSERT INTO logger(      
      logUserId,
      logAlamatIp,
      logUpdateTerakhir,
      logKeterangan)
   VALUES('%s','%s','%s','%s')
";

$sql['get_max_id'] = "
   SELECT 
      logId as maxid
   FROM logger 
   ORDER BY logId DESC LIMIT 1
";
?>