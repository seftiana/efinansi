<?php


$sql['get_coa_laba_rugi']     = "
SELECT
   coaId AS coa_id,
   coaKodeAkun AS coa_kode,
   coaNamaAkun AS coa_nama,
   UPPER(coaKelompokNama) AS akun_kelompok,
   coaIsDebetPositif AS saldo_normal
FROM coa 
LEFT JOIN coa_kelompok
   ON coaKelompokId = coaCoaKelompokId
WHERE `coaIsLabaRugiThJln` = '1' ORDER BY coaId ASC 
LIMIT 1
";

?>