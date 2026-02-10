<?php
$sql['get_count_data_unitkerja'] = 
   "SELECT 
      count(ukr.unitkerjaId) AS total
   FROM 
      unit_kerja_ref ukr
	  LEFT JOIN tipe_unit_kerja_ref tukr ON (tukr.tipeunitId = ukr.unitkerjaTipeunitId)
	WHERE 
		ukr.unitkerjaKode LIKE '%s'
		AND ukr.unitkerjaNama LIKE '%s'
		AND ukr.unitkerjaParentId <> 0
		%s
		%s";

$sql['get_data_unitkerja'] = 
   "SELECT 
      ukr.unitkerjaId				as unitkerja_id,
	  ukr.unitkerjaKode				as unitkerja_kode,
	  ukr.unitkerjaNama				as unitkerja_nama,
	  ukr.unitkerjaNamaPimpinan		as unitkerja_pimpinan,
	  tukr.tipeunitNama				as tipeunit_nama
   FROM 
      unit_kerja_ref ukr
	  LEFT JOIN tipe_unit_kerja_ref tukr ON (tukr.tipeunitId = ukr.unitkerjaTipeunitId)
	WHERE 
		(ukr.unitkerjaId ='%s' OR ukr.unitkerjaParentId = '%s')
      AND ukr.unitkerjaKode LIKE '%s'
		AND ukr.unitkerjaNama LIKE '%s'
		AND ukr.unitkerjaParentId <> 0
		%s
   ORDER BY 
	  tukr.tipeunitNama
   LIMIT %s, %s";

//untuk combo box

$sql['get_data_tipe_unit'] = 
   "SELECT 
      tipeunitId		as id,
	  tipeunitNama		as name
   FROM 
      tipe_unit_kerja_ref
   ORDER BY 
      tipeunitNama";
?>