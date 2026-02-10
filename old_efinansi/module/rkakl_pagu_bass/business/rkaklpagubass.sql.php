<?php
$sql['count']           = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']        = "
SELECT
   SQL_CALC_FOUND_ROWS bas.paguBasId AS id,
   bas.paguBasKode AS kode,
   bas.paguBasKeterangan AS nama,
   IF(
      UPPER(bas.paguBasNilaiDefault) = 'K',
      'KREDIT',
      'DEBET'
   ) AS nilaiDefault,
    IF(`paguBasStatusAktif` = 'Y', 'Y','T') AS statusAktif,
   IFNULL(mak.count, 0) AS child
FROM
   finansi_ref_pagu_bas AS bas
   LEFT JOIN
      (SELECT
         COUNT(paguBasId) AS `count`,
         paguBasParentId AS id
      FROM
         finansi_ref_pagu_bas
      GROUP BY paguBasParentId) AS mak
      ON mak.id = bas.paguBasId
WHERE 1 = 1
   AND bas.paguBasParentId = 0
   AND bas.paguBasKode LIKE '%s'
   AND bas.paguBasKeterangan LIKE '%s'
ORDER BY bas.paguBasKode
LIMIT %s, %s
";

$sql['get_rkakl_pagu_bass']  = "
SELECT
    `paguBasId` AS id,
    `paguBasKode` AS kode,
    `paguBasParentId` AS parent_id,
    IF(`paguBasNilaiDefault` = 'K', 'Kredit','Debet') AS nilai_default,
    IF(`paguBasStatusAktif` = 'Y', 'Y','T') AS status_aktif,
    `paguBasKeterangan` AS keterangan,
    (SELECT COUNT(DISTINCT paguBasId)
	FROM
		finansi_ref_pagu_bas
	WHERE paguBasParentId = id) AS child
FROM
    `finansi_ref_pagu_bas`
WHERE paguBasParentId = 0 AND
    (paguBasKode LIKE '%s'
    %s paguBasKeterangan LIKE '%s')
ORDER BY paguBasStatusAktif, paguBasKode ASC
LIMIT %s, %s
";

$sql['get_count_rkakl_pagu_bass'] =
"
SELECT
    count(paguBasId)   AS `count`
FROM
    finansi_ref_pagu_bas
WHERE
    paguBasParentId = 0 AND
    (paguBasKode LIKE '%s'
    %s paguBasKeterangan LIKE '%s')
";

$sql['get_rkakl_pagu_bass_by_id'] =
"
SELECT
    paguBasId   AS id,
    paguBasKode AS kode,
    paguBasKeterangan AS keterangan,
    paguBasStatusAktif AS status_aktif,
    paguBasNilaiDefault AS nilai_default
FROM
    finansi_ref_pagu_bas
WHERE
    paguBasId = %s
";

#$sql['get_rkakl_pagu_bass'] =
#"
#   SELECT
#	  paguBasId   AS id,
#	  paguBasKode AS kode,
#	  paguBasKeterangan AS keterangan,
#	  paguBasStatusAktif as status_aktif
#	FROM finansi_ref_pagu_bas
#	WHERE paguBasKode LIKE '%s'
#		 AND paguBasKeterangan LIKE '%s'
# 	ORDER BY paguBasStatusAktif, paguBasKode ASC
#	LIMIT %s, %s
#";



$sql['insert_rkakl_pagu_bass'] = "
   INSERT INTO `finansi_ref_pagu_bas` (`paguBasKode`,`paguBasKeterangan`,`paguBasStatusAktif`,`paguBasNilaiDefault`)
	VALUES ('%s','%s','%s','%s')
";

$sql['update_rkakl_pagu_bass'] = "
   UPDATE `finansi_ref_pagu_bas` set `paguBasKode` = '%s', `paguBasKeterangan`='%s',
   `paguBasStatusAktif` ='%s'
	WHERE `paguBasId` ='%s'
";

$sql['delete_rkakl_pagu_bass'] = "
   DELETE FROM `finansi_ref_pagu_bas` WHERE `paguBasId` ='%s'
";

$sql['delete_rkakl_pagu_bass_array'] = "
	DELETE
	FROM finansi_ref_pagu_bas
	WHERE paguBasId IN ('%s')
";

?>
