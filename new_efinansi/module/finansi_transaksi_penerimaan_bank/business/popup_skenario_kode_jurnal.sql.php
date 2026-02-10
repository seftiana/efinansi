<?php
$sql['get_type_unit']      = "
SELECT
   tipeunitId AS `id`,
   tipeunitNama AS `name`
FROM tipe_unit_kerja_ref
WHERE 1 = 1
ORDER BY tipeunitNama ASC
";

$sql['count']        = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']     = "
SELECT
  SQL_CALC_FOUND_ROWS
  jk.`jurkodeId` AS jk_id,
  jk.`jurkodeKode` AS jk_kode,
  jk.`jurkodeNama` AS jk_nama,
  jk.`jurKodeIdJenisBiaya` AS jk_jb_id,
  jk.`jurKodeNamaJenisBiaya` AS jk_jb_nama,  
  jk.`jurKodeMetodeCatat` AS jk_mode_catat,
  jkd.`jurkodedtCoaId` AS coa_id,
  c.`coaKodeAkun` AS coa_kode,
  c.`coaNamaAkun` AS coa_nama,
  jkd.`jurkodedtIsDebet` AS is_debet
FROM `jurnal_kode` jk
JOIN  `jurnal_kode_detail` jkd
ON jkd.`jurkodedtJurkodeId` = jk.`jurkodeId`
JOIN coa c
ON c.`coaId` = jkd.`jurkodedtCoaId`
WHERE
(jk.`jurkodeNama` LIKE '%s' OR c.`coaNamaAkun` LIKE '%s')
AND
jk.`jurkodeStatusAktif` = 'Y'
LIMIT %s,%s
";
?>