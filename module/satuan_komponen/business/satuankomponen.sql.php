<?php
$sql['get_limit_satuan_komponen'] = "
      SELECT
         satkompId,
         satkompNama
      FROM satuan_komponen
      WHERE satkompNama LIKE '%%%s%%'
      ORDER BY satkompNama
      LIMIT %d,%d
";

$sql['jumlah_list_satuan_komponen'] = "
     SELECT
         COUNT(satkompId) AS jumlah
      FROM satuan_komponen
      WHERE satkompNama LIKE '%%%s%%'
";

$sql['get_satuan_komponen_from_id'] = "
     SELECT
         satkompId,
         satkompNama
      FROM satuan_komponen
      WHERE satkompId = '%s'
";

$sql['insert_satuan_komponen'] = "
      INSERT INTO satuan_komponen(satkompNama)
      VALUES('%s')
";

$sql['update_satuan_komponen'] = "
     UPDATE satuan_komponen
     SET
         satkompNama = '%s'
     WHERE satkompId = '%s'
";

$sql['delete_satuan_komponen'] = "
     DELETE FROM satuan_komponen
      WHERE satkompId IN(%s)
";

$sql['cek_satuan_komponen'] = "
      SELECT
         satkompId AS id,
         lower(satkompNama) AS nama
      FROM satuan_komponen
      WHERE lower(satkompNama) LIKE '%%%s%%'
";
?>