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
      jurkodeNama as nama,
      jurkodeStatusAktif as status_aktif
   FROM 
      jurnal_kode
	WHERE 
		(jurkodeNama LIKE '%s'  OR
      jurkodeKode LIKE '%s')
   ORDER BY 
   		jurkodeStatusAktif, jurkodeKode
   LIMIT %s, %s";

$sql['get_data_by_id'] = "
   SELECT 
      jurkodeId as id,
      jurkodeKode as kode,
      jurkodeNama as nama,
      jurkodeStatusAktif as status_aktif,
      jurkodeIdJenisBiaya as jenis_biaya_id,
      jurkodeNamaJenisBiaya as jenis_biaya_nama,
      jurKodeMetodeCatat as metode_catat
   FROM 
      jurnal_kode
   WHERE
      jurkodeId='%s'";


//===DO===
$sql['do_add_data'] = "INSERT INTO jurnal_kode (
       jurkodeKode, 
       jurkodeNama,
       jurkodeStatusAktif,      
       jurKodeIdJenisBiaya,
       jurKodeNamaJenisBiaya,
       jurKodeMetodeCatat
   )
   VALUES ('%s', '%s','%s','%s', '%s','%s')";

$sql['do_add_detil_jurnal'] = "INSERT INTO jurnal_kode_detail
      (jurkodedtJurkodeId, jurkodedtCoaId, jurkodedtIsDebet)
   VALUES 
      ('%s', '%s', '%s')";

$sql['do_update_data'] = "
   UPDATE 
      jurnal_kode
   SET
      jurkodeKode = '%s',
      jurkodeNama = '%s',
      jurkodeStatusAktif ='%s',
      jurKodeIdJenisBiaya ='%s',
      jurKodeNamaJenisBiaya ='%s',
      jurKodeMetodeCatat ='%s'
   WHERE 
      jurkodeId = '%s'";

$sql['do_delete_data'] = "DELETE from 
   jurnal_kode
   WHERE 
      jurkodeId='%s'";

$sql['do_delete_data_by_array_id'] = "DELETE from jurnal_kode
   WHERE 
      jurkodeId IN ('%s')";
?>