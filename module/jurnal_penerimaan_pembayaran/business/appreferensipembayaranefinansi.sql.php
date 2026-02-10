<?php
/**
 * @package SQL-FILE
 */
$sql['get_data_coa']    = "
SELECT 
	coa.coaId,
	coa.coaKodeAkun,
	coa.coaNamaAkun
FROM finansi_coa_map cp
JOIN coa ON cp.coaId=coa.coaId
WHERE cp.kodeterimaId='%s'
";

?>