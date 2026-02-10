<?php
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
   IFNULL(komp.count, 0) AS komponen
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

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_count_referensi_mak'] =
"
   SELECT
	  count(makId)   AS `count`
	FROM finansi_ref_mak
   WHERE makKode LIKE '%s'
   AND makNama LIKE '%s'
";


//get referensi mak by pagu unit anggaran
$sql['get_count_referensi_mak_unit'] =
"
SELECT
   COUNT(DISTINCT fm.makId) AS count
FROM
   finansi_ref_mak AS fm
LEFT JOIN gtfinansi_anggaran.finansi_ref_pagu_bas AS fp
   ON (fm.makPaguBasId = fp.paguBasId)
INNER JOIN gtfinansi_anggaran.finansi_pagu_anggaran_unit AS fu
   ON (fu.paguAnggUnitPaguBasId = fp.paguBasId)
JOIN
	(SELECT @th_id := thanggarId
	FROM tahun_anggaran
	WHERE thanggarIsAktif='Y') tmp_tahun

   WHERE makKode LIKE '%s'
   AND makNama LIKE '%s'
   AND fu.paguAnggUnitThAnggaranId = @th_id
";

$sql['insert_referensi_mak'] = "
   INSERT INTO finansi_ref_mak (makKode,makNama,makUserUpdate,makTglUpdate,makPaguBasId,makStatusAktif)
	VALUES ('%s','%s','%s',NOW(),'%s','%s')
";

$sql['update_referensi_mak'] = "
   UPDATE finansi_ref_mak set
	makKode = '%s',
	makNama = '%s',
	makUserUpdate = '%s',
	makTglUpdate = NOW(),
	makPaguBasId = '%s',
	makStatusAktif ='%s'
	WHERE makId ='%s'
";

$sql['delete_referensi_mak'] = "
   DELETE FROM finansi_ref_mak WHERE makId ='%s'
";

$sql['delete_referensi_mak_array'] = "
	DELETE
	FROM finansi_ref_mak
	WHERE makId IN ('%s')
";

$sql['get_last_mak_id']=
"SELECT MAX(makId) as last_id
      FROM finansi_ref_mak";

//coa
$sql['get_coa_mak']="
	SELECT * FROM finansi_coa_mak WHERE makId = %s
";
$sql['do_add_coa_mak'] =
   "INSERT INTO finansi_coa_mak(
   		coaId,
	  	makId
	  	)
   VALUES
      ('%s','%s')";

$sql['do_update_coa_mak'] =
   "UPDATE finansi_coa_mak
   SET
      makId = '%s',
      coaId = '%s'
   WHERE
      makId = '%s'
 ";

$sql['do_delete_coa_map'] =
   "DELETE FROM finansi_coa_map
   WHERE
      makId = %s ";

/* **
Referensi MAK dari table finansi_ref_pagu_bas
**** */
// count MAK
$sql['count_ref_mak'] = "
SELECT
    COUNT(paguBasId) AS `count`
FROM `finansi_ref_pagu_bas`
WHERE
	paguBasParentId <> 0
	AND (paguBasKode LIKE '%s'
	AND paguBasKeterangan LIKE '%s')
";
$sql['get_ref_mak'] = "
SELECT
    bas.`paguBasId` AS id,
    bas.`paguBasKode` AS kode,
    bas.`paguBasParentId` AS parent_id,
    bas.`paguBasNilaiDefault` AS nilai_default,
    IF(bas.`paguBasStatusAktif` = 'Y', 'Y','T') AS status_aktif,
    bas.`paguBasKeterangan` AS nama,
    (SELECT paguBasKeterangan
    FROM finansi_ref_pagu_bas
    WHERE paguBasId = parent_id) AS pagubasname,
    bas_tipe.`paguBasTipeId` AS bas_tipe_id,
    IFNULL(bas_tipe.`paguBasTipeNama`, '-') AS bas_tipe,
    coa.`coaId` AS coa_id,
    IFNULL(coa.`coaKodeAkun`, '-') AS coa_kode,
    IFNULL(coa.`coaNamaAkun`, '-') AS coa_nama
FROM `finansi_ref_pagu_bas` AS bas
LEFT JOIN
	finansi_ref_pagu_bas_tipe_bas AS btb
ON
	btb.paguBasId = bas.paguBasId
LEFT JOIN
	`finansi_ref_pagu_bas_tipe` AS bas_tipe
ON
	btb.`paguBasTipeId` = bas_tipe.`paguBasTipeId`
LEFT JOIN
	finansi_coa_mak AS cm
ON
	cm.`paguBasId` = bas.`paguBasId`
LEFT JOIN coa
ON
	coa.`coaId` = cm.`coaId`
WHERE
	paguBasParentId <> 0
	AND (paguBasKode LIKE '%s'
	AND paguBasKeterangan LIKE '%s')
ORDER BY
    paguBasStatusAktif ='Y', paguBasKode ASC
LIMIT %s, %s
";

$sql['get_ref_mak_by_id'] = "
SELECT
    bas.`paguBasId` AS id,
    bas.`paguBasKode` AS kode,
    bas.`paguBasParentId` AS id_pagubas,
    bas.`paguBasNilaiDefault` AS nilai_default,
    IF(bas.`paguBasStatusAktif` = 'Y', 'Y','T') AS status_aktif,
    bas.`paguBasKeterangan` AS nama,
    (SELECT paguBasKode
    FROM finansi_ref_pagu_bas
    WHERE paguBasId = id_pagubas) AS kode_pagubas,
    bas_tipe.`paguBasTipeId` AS bas_tipe_id,
    IFNULL(bas_tipe.`paguBasTipeNama`, '-') AS bas_tipe,
    coa.`coaId` AS coa_id,
    IFNULL(coa.`coaKodeAkun`, '') AS coa_kode_akun,
    IFNULL(coa.`coaNamaAkun`, '') AS coa_nama_akun
FROM `finansi_ref_pagu_bas` AS bas
LEFT JOIN
	finansi_ref_pagu_bas_tipe_bas AS btb
ON
	btb.paguBasId = bas.paguBasId
LEFT JOIN
	`finansi_ref_pagu_bas_tipe` AS bas_tipe
ON
	btb.`paguBasTipeId` = bas_tipe.`paguBasTipeId`
LEFT JOIN
	finansi_coa_mak AS cm
ON
	cm.`paguBasId` = bas.`paguBasId`
LEFT JOIN coa
ON
	coa.`coaId` = cm.`coaId`
WHERE
    bas.paguBasId = '%s'
";
//ambil data pagu bas
$sql['get_data_pagu_bas'] ="
SELECT
    paguBasId AS id_pagubas,
    paguBasKode AS kode_pagubas,
    paguBasKeterangan AS keterangan_pagubas
FROM
    finansi_ref_pagu_bas
WHERE
    (paguBasKeterangan LIKE %s
    OR
    paguBasKode LIKE %s)
    AND paguBasParentId = 0
    AND paguBasStatusAktif ='Y'
ORDER BY
    paguBasStatusAktif ='Y', paguBasKode ASC
LIMIT %s, %s
";

// count pagu bas
$sql['get_count_pagu_bas'] ="
SELECT
    COUNT(paguBasId) AS total
FROM
    finansi_ref_pagu_bas
WHERE
    (paguBasKeterangan LIKE %s
    OR
    paguBasKode LIKE %s)
    AND paguBasParentId = 0
    AND paguBasStatusAktif ='Y'
";

// get combo type bas
$sql['get_combo_type_bas']   = "
SELECT
    `paguBasTipeId` AS id,
    CONCAT(`paguBasTipeKode`, ' - ', `paguBasTipeNama`) AS `name`
FROM `finansi_ref_pagu_bas_tipe`
";

// get last mak id from pagu bas
$sql['get_last_pagu_bas_id'] = "
SELECT MAX(paguBasId) AS last_id FROM finansi_ref_pagu_bas
";

$sql['cek_coa_mak_by_bas_id']   = "
SELECT
    COUNT(`coaId`) AS count_mak
FROM `finansi_coa_mak`
WHERE paguBasId = '%s'
";

$sql['count_bas_tipe_bas_by_bas_id'] = "
SELECT
    COUNT(`paguBasTipeId`) AS count_tipe
FROM `finansi_ref_pagu_bas_tipe_bas`
WHERE
	paguBasId = '%s'
";

// DO
$sql['insert_mak_into_pagu_bas'] = "
INSERT INTO `finansi_ref_pagu_bas`
            (`paguBasId`,
             `paguBasKode`,
             `paguBasParentId`,
             `paguBasNilaiDefault`,
             `paguBasStatusAktif`,
             `paguBasKeterangan`)
VALUES (NULL,
        '%s',
        '%s',
        '%s',
        '%s',
        '%s');
";

$sql['insert_into_coa_mak'] = "
INSERT INTO `finansi_coa_mak`
            (`coaId`,
             `paguBasId`)
VALUES ('%s',
        '%s')
";
$sql['insert_into_bas_tipe'] = "
INSERT INTO `finansi_ref_pagu_bas_tipe_bas`
            (`paguBasTipeId`,
             `paguBasId`)
VALUES ('%s',
        '%s')
";
$sql['update_coa_mak_by_bas_id'] = "
UPDATE `finansi_coa_mak`
SET `coaId` = '%s',
    `paguBasId` = '%s'
WHERE
	`paguBasId` = '%s'
";

$sql['update_bas_tipe_bas_by_bas_id'] = "
UPDATE `finansi_ref_pagu_bas_tipe_bas`
SET `paguBasTipeId` = '%s',
    `paguBasId` = '%s'
WHERE `paguBasId` = '%s'
";

$sql['update_mak_by_bas_id'] = "
UPDATE `finansi_ref_pagu_bas`
SET `paguBasKode` = '%s',
    `paguBasParentId` = '%s',
    `paguBasNilaiDefault` = '%s',
    `paguBasStatusAktif` = '%s',
    `paguBasKeterangan` = '%s'
WHERE `paguBasId` = '%s'
";

$sql['delete_mak_pagu_bas_by_bas_id'] = "
DELETE
FROM `finansi_ref_pagu_bas`
WHERE `paguBasId` = '%s'
";

$sql['delete_bas_tipe_bas_by_bas_id'] = "
DELETE
FROM `finansi_ref_pagu_bas_tipe_bas`
WHERE `paguBasId` = '%s'
";

$sql['delete_coa_mak_by_bas_id'] = "
DELETE
FROM `finansi_coa_mak`
WHERE `paguBasId` = '%s'
";
?>