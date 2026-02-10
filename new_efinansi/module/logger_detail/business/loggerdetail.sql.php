<?php
$sql['insert_logger_detail'] = 
"
   INSERT INTO logger_detail(
      logId,       
      logAksiQuery
   )
   VALUES('%s','%s')
";

?>