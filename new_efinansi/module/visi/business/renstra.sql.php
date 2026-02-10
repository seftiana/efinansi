<?php
$sql['get_combo_renstra']   = "
SELECT 
    renstraId AS id,
    renstraNama AS `name` 
FROM 
    renstra 
WHERE 1=1 
ORDER BY renstraIsAktif = 'Y' DESC
";

$sql['get_renstra_aktif']   = "
SELECT 
    renstraId AS id,
    renstraNama AS `name`, 
    renstraPimpinan AS pimpinan 
FROM renstra 
WHERE 
    renstraIsAktif = 'Y' 
LIMIT 0,1
";
?>
