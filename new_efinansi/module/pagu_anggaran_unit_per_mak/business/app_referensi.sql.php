<?php
$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_program']   = "
SELECT SQL_CALC_FOUND_ROWS
   rkaklProgramId AS `id`,
   rkaklProgramKode AS `kode`,
   rkaklProgramNama AS nama
FROM finansi_ref_rkakl_prog
WHERE rkaklProgramKode LIKE '%s'
AND rkaklProgramNama LIKE '%s'
LIMIT %s, %s
";

$sql['get_data_output']    = "
SELECT SQL_CALC_FOUND_ROWS
   rkaklOutputId AS `id`,
   rkaklOutputKode AS `kode`,
   rkaklOutputNama AS `nama`,
   rkaklOutputKegiatanId AS `kegiatanId`,
   rkaklKegiatanKode AS kegiatanKode,
   rkaklKegiatanNama AS kegiatanNama,
   rkaklProgramId AS programId,
   rkaklProgramKode AS programKode,
   rkaklProgramNama AS programNama
FROM finansi_ref_rkakl_output
   JOIN finansi_ref_rkakl_kegiatan
      ON rkaklKegiatanId = rkaklOutputKegiatanId
   JOIN finansi_ref_rkakl_prog
      ON rkaklProgramId = rkaklKegiatanRkaklProgramId
WHERE 1 = 1
AND (rkaklProgramId = %s OR 1 = %s)
AND (rkaklKegiatanKode LIKE '%s' OR rkaklKegiatanNama LIKE '%s')
AND (rkaklOutputKode LIKE '%s'  OR rkaklOutputNama LIKE '%s')
ORDER BY rkaklKegiatanKode, rkaklOutputKode
LIMIT %s, %s
";

$sql['get_data_komponen']     = "
SELECT SQL_CALC_FOUND_ROWS
   rkaklSubKegiatanId AS `id`,
   rkaklSubKegiatanKode AS `kode`,
   rkaklSubKegiatanNama AS `nama`
FROM finansi_ref_rkakl_subkegiatan
WHERE 1 = 1
AND (rkaklSubKegiatanKode LIKE '%s' OR rkaklSubKegiatanNama LIKE '%s')
ORDER BY rkaklSubKegiatanKode
LIMIT %s, %s
";

$sql['get_data_mak']    = "
SELECT SQL_CALC_FOUND_ROWS
   mak.paguBasId AS `id`,
   mak.paguBasKode AS `kode`,
   mak.paguBasKeterangan AS `nama`,
   bas.paguBasId AS `basId`,
   bas.paguBasKode AS basKode,
   bas.paguBasKeterangan AS basNama
FROM finansi_ref_pagu_bas AS mak
   JOIN finansi_ref_pagu_bas AS bas
   ON bas.paguBasId = mak.paguBasParentId AND bas.paguBasParentId = 0
WHERE 1 = 1
AND (SUBSTR(mak.paguBasKode, 1, 2) NOT IN('41', '42'))
AND (bas.paguBasKode LIKE '%s' OR bas.paguBasKeterangan LIKE '%s')
AND (mak.paguBasKode LIKE '%s' OR mak.paguBasKeterangan LIKE '%s')
ORDER BY bas.paguBasKode, mak.paguBasKode
LIMIT %s, %s
";
?>