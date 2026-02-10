<?php
$sql['count']        = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']     = "
SELECT
   SQL_CALC_FOUND_ROWS tupoksi.tupoksiId AS id,
   tupoksi.tupoksiKode AS kode,
   tupoksi.tupoksiNama AS nama,
   indikator.tupoksiTypeIndikatorNama AS tipe,
   satuan.tupoksiSatuanNama AS satuan
FROM
   finansi_pa_ref_tupoksi AS tupoksi
   LEFT JOIN finansi_pa_ref_tupoksi_satuan AS satuan
      ON satuan.tupoksiSatuanId = tupoksi.tupoksiSatuanId
   LEFT JOIN finansi_pa_ref_tupoksi_type_indikator AS indikator
      ON indikator.tupoksiTypeIndikatorId = tupoksi.tupoksiTypeIndikatorId
WHERE 1 = 1
   AND tupoksi.tupoksiKode LIKE '%%'
   AND tupoksi.tupoksiNama LIKE '%%'
ORDER BY tupoksiKode
LIMIT 0, 20
";