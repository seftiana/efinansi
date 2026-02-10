<?php
$sql['is_aktif'] = "
SELECT
   renstraIsAktif AS is_aktif
FROM
   renstra
WHERE renstraId = %s AND renstraIsAktif='Y'
";

$sql['get_range_tahun']    = "
SELECT
   IFNULL(YEAR(MIN(renstraTanggalAwal)), YEAR(NOW())-5) AS startYear,
   IFNULL(YEAR(MAX(renstraTanggalAkhir)), YEAR(NOW())+5) AS endYear
FROM
   renstra
";

$sql['count']              = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_renstra']   = "
SELECT SQL_CALC_FOUND_ROWS
   renstraId AS id,
   renstraNama AS nama,
   renstraTanggalAwal AS tanggalAwal,
   renstraTanggalAkhir AS tanggalAkhir,
   renstraIsAktif AS `status`,
   renstraPimpinan AS pimpinan,
   renstraVisi AS visi,
   renstraMisi AS misi,
   renstraTujuanKhusus AS tujuanKhusus,
   renstraTujuanUmum AS tujuanUmum,
   renstraCatatan AS catatan,
   renstraSasaran AS sasaran,
   renstraStrategi AS strategi,
   renstraKebijakan AS kebijakan
FROM renstra
WHERE 1 = 1
AND renstraNama LIKE '%s'
AND YEAR(renstraTanggalAwal) >= '%s'
AND YEAR(renstraTanggalAkhir) <= '%s'
LIMIT %s, %s
";

$sql['do_set_deaktif'] = "
UPDATE renstra
SET renstraIsAktif='T'
";

$sql['check_renstra']      = "
SELECT
   COUNT(renstraId) AS `count`,
   MAX(renstraId) AS `id`
FROM renstra
WHERE LOWER(renstraNama) = LOWER('%s')
AND (renstraId != %s OR 1 = %s)
";

$sql['get_tahun_renstra']     = "
SELECT
   renstraTanggalAwal,
   renstraTanggalAkhir,
   MAX(renstraTanggalAkhir) AS tanggalAkhir,
   YEAR(MAX(renstraTanggalAkhir)) AS tahunAkhir,
   YEAR(MAX(renstraTanggalAkhir))+5 AS tahunMaksimal
FROM renstra WHERE 1 = 1
AND (renstraId = %s OR 1 = %s)
";

$sql['get_data_detail']    = "
SELECT
   renstraId AS id,
   renstraNama AS nama,
   renstraTanggalAwal AS tanggalAwal,
   renstraTanggalAkhir AS tanggalAkhir,
   renstraIsAktif AS `status`,
   renstraPimpinan AS pimpinan,
   renstraVisi AS visi,
   renstraMisi AS misi,
   renstraTujuanKhusus AS tujuanKhusus,
   renstraTujuanUmum AS tujuanUmum,
   renstraCatatan AS catatan,
   renstraSasaran AS sasaran,
   renstraStrategi AS strategi,
   renstraKebijakan AS kebijakan
FROM renstra
WHERE 1 = 1
AND renstraId = %s
LIMIT 1
";

$sql['do_add'] = "
INSERT INTO renstra (
   `renstraNama`,
   `renstraTanggalAwal`,
   `renstraTanggalAkhir`,
   `renstraUserId`,
   `renstraPimpinan`,
   `renstraVisi`,
   `renstraMisi`,
   `renstraTujuanUmum`,
   `renstraTujuanKhusus`,
   `renstraCatatan`,
   `renstraSasaran`,
   `renstraStrategi`,
   `renstraKebijakan`,
   `renstraIsAktif`
)
VALUES
   (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
";


$sql['do_delete'] = "
DELETE FROM renstra
WHERE
   renstraId=%s
";

$sql['do_delete_tahun_anggaran_renstra']  = "
DELETE FROM tahun_anggaran WHERE thanggarRenstraId = %s
";

$sql['do_update'] = "
UPDATE
   renstra
SET
   renstraNama = %s,
   renstraTanggalAwal = %s,
   renstraTanggalAkhir = %s,
   renstraUserId = %s,
   `renstraPimpinan` = '%s',
   `renstraVisi` = '%s',
   `renstraMisi` = '%s',
   `renstraTujuanUmum` = '%s',
   `renstraTujuanKhusus` = '%s',
   `renstraCatatan` = '%s',
   `renstraSasaran` = '%s',
   `renstraStrategi` = '%s',
   `renstraKebijakan` = '%s',
   renstraIsAktif = %s
WHERE renstraId = %s
";

$sql['do_set_aktif'] = "
UPDATE renstra
SET renstraIsAktif='Y'
WHERE renstraId=%s
";

$sql['do_check_renstra']      = "
SELECT
   COUNT(renstraId) AS `count`,
   MAX(renstraId) AS `id`,
   IFNULL(ta.count, 0) AS child,
   UPPER(renstraIsAktif) AS `status`
FROM renstra
LEFT JOIN (
SELECT
   COUNT(thanggarId) AS `count`,
   thanggarRenstraId AS id
FROM tahun_anggaran
GROUP BY thanggarRenstraId
) AS ta ON ta.id = renstraId
WHERE LOWER(renstraNama) = LOWER('%s')
AND (renstraId != %s OR 1 = %s)
";

$sql['get_user_service']      = "
SELECT
   UserId
FROM gtfw_user
WHERE 1 = 1
AND UPPER(UserName) = 'SERVICE'
";

$sql['get_periode_tahun_renstra']   = "
SELECT
   thanggarId AS id,
   thanggarNama AS nama,
   thanggarBuka AS tanggalAwal,
   thanggarTutup AS tanggalAkhir,
   thanggarIsAktif AS statusAktif,
   thanggarIsOpen AS statusOpen
FROM tahun_anggaran
JOIN renstra
   ON renstraId = thanggarRenstraId
WHERE 1 = 1
AND thanggarRenstraId = %s
";
?>