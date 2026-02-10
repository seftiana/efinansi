<?php
$sql['get_count_data_tupoksi'] = "
	SELECT 
      count(tupoksiId) AS total
   	FROM 
      finansi_pa_ref_tupoksi
   	WHERE
   		tupoksiNama LIKE '%s' 
   		AND 
   		tupoksiKode LIKE '%s'
";

$sql['get_data_tupoksi'] = "
SELECT 
  finansi_pa_ref_tupoksi.tupoksiId AS tupoksi_id,
  finansi_pa_ref_tupoksi.tupoksiKode AS tupoksi_kode,
  finansi_pa_ref_tupoksi.tupoksiNama AS tupoksi_nama,
  finansi_pa_ref_tupoksi_type_indikator.tupoksiTypeIndikatorNama AS tupoksi_indikator_nama,
  finansi_pa_ref_tupoksi_satuan.tupoksiSatuanNama AS tupoksi_satuan
  
FROM 
   finansi_pa_ref_tupoksi
   LEFT JOIN finansi_pa_ref_tupoksi_satuan 
	ON finansi_pa_ref_tupoksi_satuan.tupoksiSatuanId = finansi_pa_ref_tupoksi.tupoksiSatuanId
   LEFT JOIN finansi_pa_ref_tupoksi_type_indikator
	ON finansi_pa_ref_tupoksi_type_indikator.tupoksiTypeIndikatorId = finansi_pa_ref_tupoksi.tupoksiTypeIndikatorId
WHERE
	finansi_pa_ref_tupoksi.tupoksiKode like %s
	AND
	finansi_pa_ref_tupoksi.tupoksiNama like %s
LIMIT %s,%s
";