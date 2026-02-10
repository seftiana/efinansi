<?php

//untuk popup coa----
$sql['get_count_coa'] =
   "SELECT
      COUNT(coaId) AS total
    FROM
      coa a
	WHERE
	  a.coaKodeAkun  LIKE %s AND a.coaNamaAkun LIKE %s
	  AND
	  (select count(coaId) from coa where coaParentAkun = a.coaId) =0
   ";

$sql['get_data_coa'] =
   "SELECT
      coaId 		AS id,
      coaKodeAkun  	AS kode,
      coaNamaAkun 	AS nama
    FROM
      coa a
    WHERE
	  a.coaKodeAkun  LIKE %s AND a.coaNamaAkun LIKE %s
	  AND
	  (select count(coaId) from coa where coaParentAkun = a.coaId) =0
    ORDER BY coaKodeAkun
    LIMIT %s, %s";
// end utk popup coa--
?>