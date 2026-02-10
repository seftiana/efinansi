<?php
//get
$sql['get_count_data_pinjaman']=
   "SELECT 
      COUNT(*) AS total 
   FROM 
      pinjaman_ref    
   WHERE 
      pinjamanKode LIKE '%s'
   AND
      pinjamanNama LIKE  '%s'
   AND
      Jumlah LIKE  '%s'
   AND 
	  Angsuran LIKE  '%s'";

$sql['get_data_pinjaman']=
   "SELECT	
      pinjamanKode AS pinjaman_kode, 
      pinjamanNama AS pinjaman_nama,
	  Jumlah AS pinjaman_jumlah,
	  Angsuran AS pinjaman_angsuran
   FROM 
      pinjaman_ref 
   WHERE 
      pinjamanKode LIKE '%s'
   AND
      pinjamanNama LIKE  '%s'
   AND
      Jumlah LIKE  '%s'  
   AND
	  Angsuran LIKE '%s'
   ORDER BY pinjamanKode
   LIMIT %s, %s";

$sql['get_data_pinjaman_by_id']=
   "SELECT	
      pinjamanKode AS pinjaman_kode, 
      pinjamanNama AS pinjaman_nama,
	  Jumlah AS pinjaman_jumlah,
	  Angsuran AS pinjaman_angsuran
   FROM 
      pinjaman_ref 
   WHERE 
      pinjamanKode = '%s'";

$sql['get_data_pinjaman_by_array_id']=
   "SELECT	
      pinjamanKode AS pinjaman_kode, 
      pinjamanNama AS pinjaman_nama,
	  Jumlah AS pinjaman_jumlah,
	  Angsuran AS pinjaman_angsuran
   FROM 
      pinjaman_ref 
   WHERE 
      pinjamanKode in('%s')";

//do

$sql['do_add_pinjaman']="
   INSERT INTO  pinjaman_ref 
   (pinjamanKode,pinjamanNama,Jumlah,Angsuran) 
	VALUES  (%s,%s,%s,%s)";

$sql['do_update_pinjaman']=
   "UPDATE 
      pinjaman_ref 
   SET
   	pinjamanKode = '%s' , 
   	pinjamanNama = '%s' ,
	Jumlah = '%s',
	Angsuran = '%s'
   WHERE
   	pinjamanKode = '%s'";

$sql['do_delete_pinjaman_by_id']=
   "DELETE FROM pinjaman_ref 
   WHERE
   	pinjamanKode = '%s'";

$sql['do_delete_pinjaman_by_array_id']=
   "DELETE FROM pinjaman_ref 
   WHERE
   	pinjamanKode IN('%s')";
?>
