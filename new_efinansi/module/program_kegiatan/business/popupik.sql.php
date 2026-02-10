<?php
$sql['count_data']  = "
SELECT
  COUNT(DISTINCT ikId) AS total 
FROM finansi_pa_ref_ik 
WHERE ikKode LIKE %s OR ikNama LIKE %s
";

$sql['get_data']    = "
SELECT
  ikId AS id,
  ikKode AS kode,
  ikNama AS nama,
  ikValue AS value,
  ikTglUbah AS tgl,
  ikUserUbahId AS user_id
FROM finansi_pa_ref_ik 
WHERE ikKode LIKE %s OR ikNama LIKE %s
";
?>