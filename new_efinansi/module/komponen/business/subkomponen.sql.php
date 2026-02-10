<?php
$sql['get_limit_sub_komponen_from_komponen'] = "
   SELECT
      subkompId,       
      subkompNama,
      subkompBiaya,
      subkompKompId 
   FROM sub_komponen
   WHERE subkompKompId = '%s' AND subkompNama LIKE '%%%s%%'
   LIMIT %d, %d
";

$sql['jumlah_list_sub_komponen_from_komponen'] = "
   SELECT
      COUNT(subkompId) AS jumlah
   FROM sub_komponen
   WHERE subkompKompId = '%s' AND subkompNama LIKE '%%%s%%'
";

$sql['get_sub_komponen_from_id'] = "
    SELECT
      subkompId,       
      subkompNama,
      subkompBiaya,
      subkompKompId 
   FROM sub_komponen
   WHERE subkompId = '%s'
";

$sql['insert_sub_komponen'] = "
      INSERT INTO sub_komponen(subkompNama,subkompBiaya,subkompKompId)
      VALUES('%s','%s','%s')
";

$sql['update_sub_komponen'] = "
     UPDATE sub_komponen
     SET
         subkompNama = '%s',
         subkompBiaya  = '%s'
      WHERE subkompId = '%s'
";

$sql['delete_sub_komponen'] = "
     DELETE FROM sub_komponen
      WHERE subkompId IN(%s)
";
?>