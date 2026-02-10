<?php

//===GET===
$sql['get_count_data_tipeunit'] = 
   "SELECT 
      count(tipeunitId) AS total
   FROM 
      tipe_unit_kerja_ref
   WHERE
      tipeunitNama like '%s'";

$sql['get_data_tipeunit'] = 
   "SELECT 
      tipeunitId as tipeunit_id,
	  tipeunitNama as tipeunit_nama
   FROM 
      tipe_unit_kerja_ref
	WHERE tipeunitNama LIKE '%s'
   ORDER BY 
      tipeunitNama
   LIMIT %s, %s";

$sql['get_data_tipeunit_by_id'] = 
   "SELECT 
      tipeunitId as tipeunit_id,
	  tipeunitNama as tipeunit_nama
   FROM 
      tipe_unit_kerja_ref
   WHERE
      tipeunitId='%s'";
//===DO===

$sql['do_add_tipeunit'] = 
   "INSERT INTO tipe_unit_kerja_ref
      (tipeunitNama)
   VALUES 
      ('%s')";

$sql['do_update_tipeunit'] = 
   "UPDATE tipe_unit_kerja_ref
   SET 
      tipeunitNama='%s'
   WHERE 
      tipeunitId='%s'";

$sql['do_delete_tipeunit_by_id'] = 
   "DELETE from tipe_unit_kerja_ref
   WHERE 
      tipeunitId='%s'";

$sql['do_delete_tipeunit_by_array_id'] = 
   "DELETE from tipe_unit_kerja_ref
   WHERE 
      tipeunitId IN('%s')";
	  
$sql['cek_data_tipeunit'] = 
   "SELECT 
      tipeunitId as id,
	  lower(tipeunitNama) as nama
   FROM 
      tipe_unit_kerja_ref
	WHERE lower(tipeunitNama) LIKE '%s'";
?>
