<?php
$sql['get_sub_account_combobox'] = "
SELECT
   CONCAT_WS('-', TRIM(BOTH FROM subaccPertamaKode),
   TRIM(BOTH FROM subaccKeduaKode),
   TRIM(BOTH FROM subaccKetigaKode),
   TRIM(BOTH FROM subaccKeempatKode),
   TRIM(BOTH FROM subaccKelimaKode),
   TRIM(BOTH FROM subaccKeenamKode),
   TRIM(BOTH FROM subaccKetujuhKode)) AS id,
   CONCAT(TRIM(BOTH FROM subaccPertamaKode), ' - ',subaccPertamaNama) AS name
FROM finansi_keu_ref_subacc_1
LEFT JOIN finansi_keu_ref_subacc_2
   ON subaccKeduaKode = 00
LEFT JOIN finansi_keu_ref_subacc_3
   ON subaccKetigaKode = 00
LEFT JOIN finansi_keu_ref_subacc_4
  ON subaccKeempatKode = 00
LEFT JOIN finansi_keu_ref_subacc_5
   ON subaccKelimaKode =  00
LEFT JOIN finansi_keu_ref_subacc_6
   ON subaccKeenamKode = 00
LEFT JOIN finansi_keu_ref_subacc_7
   ON subaccKetujuhKode = 00
";