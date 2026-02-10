<?php
$sql['get_data'] = "
   SELECT
      sknrId as id,
      sknrNama as nama
   FROM 
      sekenario
   WHERE 
      sknrNama LIKE '%s'
";
$sql['get_count_data']="
   SELECT
      COUNT(*) as total
   FROM 
      sekenario
   WHERE 
      sknrNama LIKE '%s'
";
?>