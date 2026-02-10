<?php

$sql['get_combo_coa_tipe'] = 
"
   SELECT
      ctrId AS id,
      ctrNamaTipe AS name
   FROM coa_tipe_ref 
";

$sql['get_list_coa_tipe'] = 
"
   SELECT
      ctrId,
      ctrNamaTipe
   FROM coa_tipe_ref 
";

$sql['get_coa_tipe_from_id'] = 
"
   SELECT
      ctrId,
      ctrNamaTipe
   FROM coa_tipe_ref
   WHERE ctrId = '%s'
";
?>