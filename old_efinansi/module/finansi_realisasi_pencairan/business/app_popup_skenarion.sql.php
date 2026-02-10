<?php
$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']  = "
SELECT SQL_CALC_FOUND_ROWS
   sknrId AS id,
   sknrNama AS nama
FROM
   sekenario
WHERE sknrNama LIKE '%s'
";
?>