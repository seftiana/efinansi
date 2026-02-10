<?php

$sql['get_pagu_bas']="
SELECT 
        paguBasId AS id,
        paguBasKode AS kode,
        paguBasKeterangan AS nama 
FROM
        finansi_ref_pagu_bas 
WHERE 
	    paguBasParentId = 0 
        AND 
        paguBasStatusAktif = 'Y' 
        AND
        paguBasKode LIKE '%s'
        AND 
        paguBasKeterangan LIKE '%s'
ORDER BY paguBasKode ASC 
LIMIT %s,%s
";

$sql['count_pagu_bas']="
SELECT 
        count(paguBasId) AS total 
FROM
        finansi_ref_pagu_bas 
WHERE 
	    paguBasParentId = 0 
        AND 
        paguBasStatusAktif = 'Y' 
        AND
        paguBasKode LIKE '%%'
        AND 
        paguBasKeterangan LIKE '%%'
";