<?php
/**
 * @package SQL-FILE
 */
$sql['get_data_coa']    = "
SELECT 
	coaId,
	coaKodeAkun,
	coaNamaAkun,
	coaIsDebetPositif
FROM coa
WHERE coaId=%s
";

?>