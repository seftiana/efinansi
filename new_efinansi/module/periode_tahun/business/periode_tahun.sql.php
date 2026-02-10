<?php
/**
 * file periode_tahun.sql.php
 * @sub_package business
 * @param string %s id tahun anggaran
 */
$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']  = "
SELECT SQL_CALC_FOUND_ROWS
   renstraId,
   renstraNama,
   thanggarId AS id,
   thanggarNama AS nama,
   thanggarBuka AS tglAwal,
   thanggarTutup AS tglAkhir,
   thanggarIsAktif AS statusAktif,
   thanggarIsOpen AS statusOpen,
   renstraIsAktif AS statusRenstra
FROM tahun_anggaran
JOIN renstra
   ON renstraId = thanggarRenstraId
WHERE 1 = 1
AND (thanggarRenstraId = %s OR 1 = %s)
AND thanggarNama LIKE '%s'
ORDER BY renstraId, thanggarBuka DESC, thanggarIsAktif
LIMIT %s, %s
";

$sql['get_renstra']     = "
SELECT
   renstraId AS `id`,
   renstraNama AS `name`,
   renstraTanggalAwal AS tanggalAwal,
   renstraTanggalAkhir AS tanggalAkhir
FROM renstra
WHERE 1 = 1
AND (renstraIsAktif = 'Y' OR 1 = %s)
ORDER BY renstraTanggalAwal DESC
";

$sql['get_data_detail']       = "
SELECT
   renstraId,
   renstraNama,
   thanggarId AS id,
   thanggarNama AS nama,
   thanggarBuka AS tglAwal,
   thanggarTutup AS tglAkhir,
   thanggarIsAktif AS statusAktif,
   thanggarIsOpen AS statusOpen,
   renstraIsAktif AS statusRenstra
FROM tahun_anggaran
JOIN renstra
   ON renstraId = thanggarRenstraId
WHERE 1 = 1
AND thanggarId = %s
LIMIT 1
";

$sql['check_periode_tahun']   = "
SELECT
   COUNT(thanggarId) AS `count`
FROM tahun_anggaran
WHERE 1 = 1
AND thanggarRenstraId = '%s'
AND thanggarNama = '%s'
AND (thanggarId != %s OR 1 = %s)
";

$sql['do_set_deaktif'] = "
UPDATE tahun_anggaran SET thanggarIsAktif='T'
";

$sql['do_insert_periode_tahun']  = "
INSERT INTO tahun_anggaran
SET thanggarNama = '%s',
   thanggarIsAktif = '%s',
   thanggarIsOpen = '%s',
   thanggarBuka = '%s',
   thanggarTutup = '%s',
   thanggarRenstraId = '%s'
";

$sql['do_update_periode_tahun']  = "
UPDATE tahun_anggaran
SET thanggarNama = '%s',
   thanggarIsAktif = '%s',
   thanggarIsOpen = '%s',
   thanggarBuka = '%s',
   thanggarTutup = '%s',
   thanggarRenstraId = '%s'
WHERE thanggarId = %s
";

$sql['do_delete'] = "
DELETE FROM tahun_anggaran
WHERE thanggarId=%s
";

$sql['do_set_aktif'] = "
UPDATE tahun_anggaran SET thanggarIsAktif = 'Y' WHERE thanggarId = %s
";
?>