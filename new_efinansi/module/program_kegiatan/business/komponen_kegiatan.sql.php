<?php
$sql['get_data']="
   SELECT 
      k.kompId AS komponen_id,
      k.kompNama AS komponen_nama,
      k.kompNamaSatuan AS komponen_satuan,
      kg.kompkegBiaya AS komponen_nominal,
      kg.kompkegKegrefId AS kegref_id,
      kompHargaSatuan
   FROM 
      komponen k INNER JOIN komponen_kegiatan kg ON k.kompId=kg.kompkegKompId
   WHERE kg.kompkegKegrefId=%s
   LIMIT %s,%s
";

$sql['get_data_count']="
   SELECT       
      COUNT(*) AS total
   FROM 
      komponen k INNER JOIN komponen_kegiatan kg ON k.kompId=kg.kompkegKompId
   WHERE kg.kompkegKegrefId=%s   
";

$sql['get_data_by_id']="
   SELECT 
      k.kompId AS komponen_id,
      k.kompNama AS komponen_nama,
      k.kompNamaSatuan AS komponen_satuan,
      kg.kompkegBiaya AS komponen_nominal,
      kg.kompkegKegrefId AS kegref_id,
      kompHargaSatuan
   FROM 
      komponen k INNER JOIN komponen_kegiatan kg ON k.kompId=kg.kompkegKompId
   WHERE kg.kompkegKegrefId=%s AND kg.kompkegKompId = %s   
";

$sql['get_data_detail']="
SELECT 
  ta.thanggarNama AS tahunperiode,
  p.programNama AS program,
  sp.subprogNama AS kegiatan,
  kr.kegrefNama AS subkegiatan 
FROM
  tahun_anggaran ta JOIN
  (
    program_ref p JOIN 
        (kegiatan_ref kr JOIN sub_program sp ON kr.kegrefSubprogId=sp.subprogId)
     ON p.programId=sp.subprogProgramId
  ) ON ta.thanggarId= p.programThanggarId

WHERE kr.kegrefId=%s

LIMIT 1
";

$sql['get_komponen']="
SELECT 
    kompId AS komponen_id,
    kompKode AS komponen_kode,
    kompNama AS komponen_nama,
    kompFormula AS komponen_formula,
    kompHargaSatuan AS harga_satuan, 
    paguBasId AS mak_id, 
    paguBasKode AS mak_kode, 
    paguBasKeterangan AS mak_nama  
FROM
    komponen 
LEFT JOIN finansi_ref_pagu_bas 
ON kompMakId = paguBasId 
WHERE 
  (kompKode LIKE %s  OR %d )
  AND 
  kompNama LIKE %s 
  AND 
  kompId NOT IN (SELECT kompkegKompId FROM komponen_kegiatan WHERE kompkegKegrefId=%s)

LIMIT %s, %s
";

$sql['get_komponen_count']="
SELECT
   COUNT(*) AS total
FROM 
   komponen
WHERE 
    (kompKode LIKE %s  OR %d )
	AND 
	kompNama LIKE %s
";

$sql['do_add']="
   INSERT INTO `komponen_kegiatan` 
   ( `kompkegKompId`, `kompkegKegrefId`, `kompkegBiaya` ) 
   VALUES
   (  %s,  %s,  %s )
";

$sql['do_delete']="
   DELETE FROM komponen_kegiatan
   WHERE kompkegKompId=%s AND kompkegKegrefId=%s
";

$sql['do_update']="
   UPDATE `komponen_kegiatan` 
   SET 
     `kompkegKompId`=%s,
     `kompkegBiaya`=%s 
   WHERE
     `kompkegKegrefId`=%s  AND `kompkegKompId`=%s  
";

 /** 	 
  * ke tabel finansi_pa_komponen_unit_kerja
  * untuk mapping komponen dengan unit kerja
  */
  	 	  	 
$sql['get_count_komponen_unit_kerja']= " 	 
SELECT 	 
   count(kompId) as jumlah 	 
FROM finansi_pa_komponen_unit_kerja 	 
WHERE 
	kompId = %s 	 
"; 	 
	  	 
$sql['do_add_komponen_unit_kerja']=" 	 
INSERT INTO 
	finansi_pa_komponen_unit_kerja 	( kompId,unitkerjaId ) 	 
VALUES(%s , %s )";
$sql['do_delete_unit_kerja_ref']= " 	 
DELETE FROM 
	finansi_pa_komponen_unit_kerja 
WHERE 
	kompId=%s 	 
";

 
 $sql['get_unit_kerja_komponen']="
SELECT 
	unit_kerja_ref.unitkerjaId AS unitkerja_id,
	unit_kerja_ref.unitkerjaNama AS unitkerja_nama,
	unit_kerja_ref.unitkerjaParentId AS unitkerja_parent,
	COUNT(finansi_pa_komponen_unit_kerja.`kompId`) AS total
FROM unit_kerja_ref
	LEFT JOIN  finansi_pa_komponen_unit_kerja 
        ON finansi_pa_komponen_unit_kerja.`unitkerjaId` = `unit_kerja_ref`.`unitkerjaId`
	AND finansi_pa_komponen_unit_kerja.`kompId`  = %s
GROUP BY unit_kerja_ref.unitkerjaId		
ORDER BY unitkerjaKode ASC
";
/**
 * end
 */