<?php
$sql['get_limit_pph_gpp'] = "
      SELECT
         gppId,
         gppNama
      FROM finansi_pph_ref_gpp
      WHERE gppNama LIKE '%%%s%%'
      ORDER BY gppNama
      LIMIT %d,%d
";

$sql['jumlah_list_pph_gpp'] = "
     SELECT
         COUNT(gppId) AS jumlah
      FROM finansi_pph_ref_gpp
      WHERE gppNama LIKE '%%%s%%'
";

$sql['get_pph_gpp_from_id'] = "
     SELECT
         gppId,
         gppNama
      FROM finansi_pph_ref_gpp
      WHERE gppId = '%s'
";

$sql['insert_pph_gpp'] = "
      INSERT INTO finansi_pph_ref_gpp(gppNama)
      VALUES('%s')
";

$sql['update_pph_gpp'] = "
     UPDATE finansi_pph_ref_gpp
     SET
         gppNama = '%s'
     WHERE gppId = '%s'
";

$sql['delete_pph_gpp'] = "
     DELETE FROM finansi_pph_ref_gpp
      WHERE gppId IN(%s)
";
?>