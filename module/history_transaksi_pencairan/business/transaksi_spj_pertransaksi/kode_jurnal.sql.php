<?php

//===GET===
$sql['get_count_data'] = "
SELECT 
      count(*) AS total
   FROM 
      jurnal_kode
	WHERE 
		(jurkodeNama LIKE '%s'  OR
      jurkodeKode LIKE '%s')
";

$sql['get_data'] = "
   SELECT 
      jurkodeId as id,
      jurkodeKode as kode,
      jurkodeNama as nama
   FROM 
      jurnal_kode
	WHERE 
		(jurkodeNama LIKE '%s'  OR
      jurkodeKode LIKE '%s')
   ORDER BY 
	  jurkodeKode
   LIMIT %s, %s";

$sql['get_data_by_id'] ="
   SELECT 
      jurkodeId as id,
      jurkodeKode as kode,
      jurkodeNama as nama
   FROM 
      jurnal_kode
   WHERE
      jurkodeId='%s'";

$sql['get_detail_kode_jurnal'] = "
SELECT 
   C.coaId as id,
   C.coaKodeAkun as kode,
   C.coaNamaAkun as nama,
   IF(J.jurkodedtIsDebet,'debet','kredit') as debetORkredit
FROM 
   coa C
   JOIN jurnal_kode_detail J ON C.coaId = J.jurkodedtCoaId
WHERE 
   jurkodedtJurkodeId = '%s'
ORDER BY
   debetORkredit,
   kode
";
//===DO===
$sql['do_add_data'] = 
   "INSERT INTO jurnal_kode
      (jurkodeKode, jurkodeNama)
   VALUES 
      ('%s', '%s')";
      
$sql['do_add_detil_jurnal'] = 
   "INSERT INTO jurnal_kode_detail
      (jurkodedtJurkodeId, jurkodedtCoaId, jurkodedtIsDebet)
   VALUES 
      ('%s', '%s', '%s')";

$sql['do_update_data'] = "
   UPDATE 
      jurnal_kode
   SET
      jurkodeKode = '%s',
      jurkodeNama = '%s'
   WHERE 
      jurkodeId = '%s'";

$sql['do_delete_data'] = 
   "DELETE from 
   jurnal_kode
   WHERE 
      jurkodeId='%s'";

$sql['do_delete_data_by_array_id'] = 
   "DELETE from jurnal_kode
   WHERE 
      jurkodeId IN ('%s')";


?>
