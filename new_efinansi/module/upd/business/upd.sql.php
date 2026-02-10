<?php

$sql['get_combo_upd'] = 
"
   SELECT
      concat(updId,'-',updCoaPrecode) AS id,        
      concat(substring_index(updCoaPrecode,'.',-1),' (',updNama,')') AS name
   FROM upd_ref
   WHERE updId <> 1
   ORDER BY updId ASC
";

$sql['get_list_upd'] = 
"
   SELECT
      updId,        
      updCoaPrecode,        
      updNama
   FROM upd_ref
   ORDER BY updId ASC
";

$sql['get_upd_from_id'] = 
"
   SELECT
      updId,        
      updCoaPrecode,        
      updNama
   FROM upd_ref
   WHERE updId = '%s'
";
?>