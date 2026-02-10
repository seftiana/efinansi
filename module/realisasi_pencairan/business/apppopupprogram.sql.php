<?php
$sql['get_count_data_program'] = 
   "SELECT 
      DISTINCT
      COUNT(prog.programId) AS total
FROM 
   program_ref prog      
   JOIN  kegiatan k ON (prog.programId = k.kegProgramId)
   JOIN kegiatan_detail kd ON (k.kegId=kd.kegdetKegId)
   
WHERE  
  prog.programThanggarId=%s
  AND prog.programNama LIKE %s
  AND prog.programNomor LIKE %s
  AND k.kegUnitkerjaId = %s 
  AND kd.kegdetIsAprove = 'Ya'

LIMIT 1
";

$sql['get_data_program'] = 
"SELECT 
  DISTINCT
  prog.programId as id,
  CONCAT(			
			CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
			WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.0.00.00') as kode,
  prog.programNama as nama,  
  k.kegId AS kegiatanunit_id
  
FROM 
   program_ref prog      
   JOIN  kegiatan k ON (prog.programId = k.kegProgramId)
   JOIN kegiatan_detail kd ON (k.kegId=kd.kegdetKegId)   
   
WHERE  
  prog.programThanggarId=%s
  AND prog.programNama LIKE %s
  AND prog.programNomor LIKE %s
  AND k.kegUnitkerjaId = %s 
  AND kd.kegdetIsAprove = 'Ya'
  

LIMIT %s, %s
";
?>