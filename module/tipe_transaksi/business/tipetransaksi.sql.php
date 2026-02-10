<?php
$sql['get_combo_tipe_transaksi'] = 
"
  SELECT 
      ttId AS id,
      ttNamaTransaksi AS name
      FROM transaksi_tipe 
   ORDER BY ttId
";

?>