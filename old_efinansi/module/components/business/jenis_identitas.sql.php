<?php  
   
   $sql['get_all_jenis_identitas'] = '
      SELECT 
         jns_identitas_id AS Id,
         jns_identitas_desc AS Nama
      FROM
         jns_identitas_ref
      WHERE
         jns_identitas_isaktif = 1
   ';   
   
   
   // ==================== code above this line are used ==========================================
?>
