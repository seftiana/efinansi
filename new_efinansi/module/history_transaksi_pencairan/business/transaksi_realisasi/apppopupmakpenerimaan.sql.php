<?php
$sql['get_count'] = "
   SELECT 
      count(kodeterimaId) as total
   FROM 
      kode_penerimaan_ref
      JOIN rencana_penerimaan ON kodeterimaId = renterimaKodeterimaId
   WHERE
      (kodeterimaKode LIKE '%s'
      OR kodeterimaNama LIKE '%s')
      AND renterimaUnitkerjaId = %s
";
$sql['get_data'] = "
   SELECT 
      kodeterimaId as id,
      renterimaId as mak,
      kodeterimaKode as kode,
      kodeterimaNama as nama,
      kodeterimaTipe as tipe,
      renterimaId as id_rencana_terima,
      renterimaTotalTerima as total_terima
   FROM 
      kode_penerimaan_ref
      JOIN rencana_penerimaan ON kodeterimaId = renterimaKodeterimaId
   WHERE
      (kodeterimaKode LIKE '%s'
      OR kodeterimaNama LIKE '%s')
      AND renterimaUnitkerjaId = %s
   ORDER BY kodeterimaKode, kodeterimaTipe DESC
   LIMIT %s, %s
";
?>