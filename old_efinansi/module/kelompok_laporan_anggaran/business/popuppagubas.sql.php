<?php

/**
 *
 * @filename popuppagubas.sql.php
 * @package kelompok_laporan_anggaran
 * @subpackage business
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since 6 september 2012
 * @copyright 2012 Gamatechno Indonesia
 * 
 */


$sql['get_data_pagu_bas_mak']="
SELECT 
  paguBasId AS mak_id,
  paguBasKode AS mak_kode,
  paguBasKeterangan AS mak_nama 
FROM
  finansi_ref_pagu_bas 
WHERE 
  (paguBasKeterangan LIKE '%s' OR paguBasKode LIKE '%s') 
  AND paguBasStatusAktif = 'Y' 
  AND paguBasParentId NOT IN(0)
ORDER BY paguBasKode ASC 
LIMIT %s, %s 
";


$sql['get_count_data_pagu_bas_mak']="
SELECT 
  COUNT(paguBasId) AS total
FROM
  finansi_ref_pagu_bas 
WHERE 
  (paguBasKeterangan LIKE '%s' OR paguBasKode LIKE '%s') 
  AND paguBasStatusAktif = 'Y' 
  AND paguBasParentId NOT IN(0)
";
