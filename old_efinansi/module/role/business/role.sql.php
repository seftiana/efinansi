<?php
   $sql['get_combo_role'] = "
      SELECT
         roleId AS id,       
         roleName AS name
      FROM gtfw_role 
      ORDER BY roleId
   ";
?>