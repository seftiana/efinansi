<?php
   $sql['get_module_information'] ="
      SELECT
         `Description`
      FROM `gtfw_module`
      WHERE
	      `Module`='%s'
      AND
         `SubModule`='%s'
      AND
         `Action` = '%s'
      AND
         `Type` = '%s'
   ";
?>