<?php
/**
 * @package SQL-FILE
 */
$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']  = "
SELECT
   SQL_CALC_FOUND_ROWS mak.paguBasId AS id,
   mak.paguBasKode AS kode,
   mak.paguBasKeterangan AS nama,
   bas.paguBasId AS basId,
   bas.paguBasKode AS basKode,
   bas.paguBasKeterangan AS basNama,
   tipe.paguBasTipeId AS tipeId,
   tipe.paguBasTipeKode AS tipeKode,
   tipe.paguBasTipeNama AS tipeNama,
   coa.coaId AS akunId,
   IFNULL(coa.coaKodeAkun, '-') AS akunKode,
   IFNULL(coa.coaNamaAkun, '-') AS akunNama,
   IFNULL(komp.count, 0) AS komponen,
   mak.paguBasStatusAktif AS `status`
FROM
   finansi_ref_pagu_bas AS mak
   JOIN finansi_ref_pagu_bas AS bas
      ON bas.paguBasParentId = 0
      AND bas.paguBasId = mak.paguBasParentId
   LEFT JOIN finansi_ref_pagu_bas_tipe_bas AS cain
      ON cain.paguBasId = mak.paguBasId
   LEFT JOIN finansi_ref_pagu_bas_tipe AS tipe
      ON tipe.paguBasTipeId = cain.paguBasTipeId
   LEFT JOIN finansi_coa_mak AS rc
      ON rc.paguBasId = mak.paguBasId
   LEFT JOIN coa
      ON coa.coaId = rc.coaId
   LEFT JOIN
      (SELECT
         kompMakId AS id,
         COUNT(kompId) AS `count`
      FROM
         komponen
      GROUP BY kompMakId) AS komp
      ON komp.id = mak.paguBasId
WHERE 1 = 1
   AND (bas.paguBasKode LIKE '%s' OR bas.paguBasKeterangan LIKE '%s')
   AND mak.paguBasKode LIKE '%s'
   AND mak.paguBasKeterangan LIKE '%s'
ORDER BY bas.paguBasKode,
   mak.paguBasKode
LIMIT %s, %s
";

$sql['get_data_detail'] = "
SELECT
   SQL_CALC_FOUND_ROWS mak.paguBasId AS id,
   mak.paguBasKode AS kode,
   mak.paguBasKeterangan AS nama,
   bas.paguBasId AS basId,
   bas.paguBasKode AS basKode,
   bas.paguBasKeterangan AS basNama,
   tipe.paguBasTipeId AS tipeId,
   tipe.paguBasTipeKode AS tipeKode,
   tipe.paguBasTipeNama AS tipeNama,
   coa.coaId AS akunId,
   coa.coaKodeAkun AS akunKode,
   coa.coaNamaAkun AS akunNama,
   IFNULL(komp.count, 0) AS komponen,
   mak.paguBasStatusAktif AS `status`,
   mak.paguBasNilaiDefault AS nilaiDefault
FROM
   finansi_ref_pagu_bas AS mak
   JOIN finansi_ref_pagu_bas AS bas
      ON bas.paguBasParentId = 0
      AND bas.paguBasId = mak.paguBasParentId
   LEFT JOIN finansi_ref_pagu_bas_tipe_bas AS cain
      ON cain.paguBasId = mak.paguBasId
   LEFT JOIN finansi_ref_pagu_bas_tipe AS tipe
      ON tipe.paguBasTipeId = cain.paguBasTipeId
   LEFT JOIN finansi_coa_mak AS rc
      ON rc.paguBasId = mak.paguBasId
   LEFT JOIN coa
      ON coa.coaId = rc.coaId
   LEFT JOIN
      (SELECT
         kompMakId AS id,
         COUNT(kompId) AS `count`
      FROM
         komponen
      GROUP BY kompMakId) AS komp
      ON komp.id = mak.paguBasId
WHERE 1 = 1
   AND mak.paguBasId = %s
LIMIT 1
";

$sql['get_bas_tipe']    = "
SELECT
   paguBasTipeId AS id,
   paguBasTipeNama AS `name`
FROM finansi_ref_pagu_bas_tipe
ORDER BY paguBasTipeNama
";

$sql['do_insert_mata_anggaran']  = "
INSERT INTO finansi_ref_pagu_bas
SET paguBasKode = '%s',
   paguBasParentId = '%s',
   paguBasNilaiDefault = '%s',
   paguBasStatusAktif = '%s',
   paguBasKeterangan = '%s'
";

$sql['do_delete_pagu_bas_tipe']  = "
DELETE FROM finansi_ref_pagu_bas_tipe_bas WHERE paguBasId = %s
";

$sql['do_insert_mata_anggaran_tipe']  = "
INSERT INTO finansi_ref_pagu_bas_tipe_bas
SET paguBasTipeId = '%s',
   paguBasId = '%s'
";

$sql['do_delete_coa_mata_anggaran'] = "
DELETE FROM finansi_coa_mak WHERE paguBasId = %s
";

$sql['do_insert_mata_anggaran_coa'] = "
INSERT INTO finansi_coa_mak
SET coaId = '%s',
   paguBasId = '%s'
";

$sql['do_check_mata_anggaran']   = "
SELECT
   COUNT(paguBasId) AS `count`,
   MAX(paguBasId) AS `id`
FROM finansi_ref_pagu_bas
WHERE 1 = 1
AND paguBasParentId = %s
AND (paguBasId != %s OR 1 = %s)
AND paguBasKode = '%s'
";

$sql['do_update_mata_anggaran']  = "
UPDATE finansi_ref_pagu_bas
SET paguBasKode = '%s',
   paguBasParentId = '%s',
   paguBasNilaiDefault = '%s',
   paguBasStatusAktif = '%s',
   paguBasKeterangan = '%s'
WHERE paguBasId = '%s'
";

$sql['do_delete_mata_anggaran']  = "
DELETE
FROM finansi_ref_pagu_bas
WHERE paguBasId = '%s'
";
?>