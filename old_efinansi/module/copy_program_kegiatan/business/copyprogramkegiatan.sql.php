<?php

/**
 * untuk combobox tahun anggaran
 */
 
$sql['get_tahun_anggaran_aktif'] = "
	SELECT
		thanggarId
	FROM
		tahun_anggaran
	WHERE
		thanggarIsAktif = 'Y'
";

$sql['get_tahun_anggaran_by_id'] = "
	SELECT
		thanggarId AS id,
		thanggarNama AS name
	FROM
		tahun_anggaran
	WHERE
		thanggarId = '%s'
";

$sql['get_tahun_anggaran'] = "
	SELECT
		thanggarId AS id,
		thanggarNama AS name
	FROM
		tahun_anggaran
	ORDER BY name DESC
";

/**
 * end
 */
 

$sql['get_program_detail'] ="
SELECT 
        pr.`programNomor` AS kodeProgram,
        pr.`programNama` AS namaProgram,
        ta.`thanggarNama` AS thAnggaran 
FROM
        program_ref pr 
        LEFT JOIN `tahun_anggaran` ta 
                ON ta.`thanggarId` = pr.`programThanggarId` 
WHERE 
	pr.`programId` = '%s'
";

$sql['get_program_kegiatan_by_id']="
SELECT 
        pr.`programNomor` AS kodeProgram,
        IFNULL(sp.`subprogNomor`, '') AS kodeKegiatan,
        IFNULL(kr.`kegrefNomor`, '') AS kodeSubKegiatan,
        IFNULL(k.`kompKode`, '') AS kodeKomponen,
        pr.`programNama` AS namaProgram,
        IFNULL(sp.`subprogNama`, '') AS namaKegiatan,
        IFNULL(kr.`kegrefNama`, '') AS namaSubKegiatan,
        IFNULL(k.`kompNama`, '') AS namaKomponen 
FROM
        `program_ref` pr 
        LEFT JOIN `sub_program` sp 
                ON sp.`subprogProgramId` = pr.`programId` 
        LEFT JOIN `kegiatan_ref` kr 
                ON kr.`kegrefSubprogId` = sp.`subprogId` 
        LEFT JOIN `komponen_kegiatan` kk 
                ON kk.`kompkegKegrefId` = kr.`kegrefId` 
        LEFT JOIN `komponen` k 
                ON k.`kompId` = kk.`kompkegKompId` 
WHERE pr.`programId` = '%s'
LIMIT %s,%s
";  

$sql['get_count_program_kegiatan_by_id']="
SELECT 
       COUNT( pr.`programNomor`) AS total
FROM
        `program_ref` pr 
        LEFT JOIN `sub_program` sp 
                ON sp.`subprogProgramId` = pr.`programId` 
        LEFT JOIN `kegiatan_ref` kr 
                ON kr.`kegrefSubprogId` = sp.`subprogId` 
        LEFT JOIN `komponen_kegiatan` kk 
                ON kk.`kompkegKegrefId` = kr.`kegrefId` 
        LEFT JOIN `komponen` k 
                ON k.`kompId` = kk.`kompkegKompId` 
WHERE pr.`programId` = '%s'
";

$sql['get_program_kegiatan']="
SELECT 
        pr.`programId` AS idProgram,
        pr.`programNomor` AS kodeProgram,
        pr.`programNama` AS namaProgram 
FROM
        `program_ref` pr 
WHERE pr.`programThanggarId` = '%s' 
        AND pr.`programNomor` NOT IN 
        (SELECT 
                pr.`programNomor` AS kode 
        FROM
                `program_ref` pr 
        WHERE pr.`programThanggarId` = '%s' 
        GROUP BY kode)
GROUP BY kodeProgram
LIMIT %s,%s        
";
  
$sql['get_count_program_kegiatan']="
SELECT 
       COUNT(pr.`programId`) AS total 
FROM
        `program_ref` pr 
WHERE pr.`programThanggarId` = '%s' 
        AND pr.`programNomor` NOT IN 
        (SELECT 
                pr.`programNomor` AS kode 
        FROM
                `program_ref` pr 
        WHERE pr.`programThanggarId` = '%s' 
        GROUP BY kode)        
";

$sql['copy_program']="
INSERT IGNORE INTO 
`program_ref` 
(
        programNomor,
        programNama,
        programThanggarId,
        programKodeLabel,
        programRKAKLProgramId,
        programSasaran,
        programIndikator,
        programStrategi,
        programKebijakan
) 
SELECT 
        programNomor,
        programNama,
        '%s' AS programThanggarId,
        programKodeLabel,
        programRKAKLProgramId,
        programSasaran,
        programIndikator,
        programStrategi,
        programKebijakan 
FROM
        `program_ref` 
WHERE `programNomor` IN (%s)
GROUP BY programNomor
";

$sql['copy_kegiatan'] = " 
INSERT IGNORE INTO sub_program (
        subprogProgramId,
        subprogNomor,
        subprogNama,
        subprogJeniskegId,
        subprogKodeLabel,
        subprogRKAKLKegiatanId
) 
SELECT 
        `pr_tujuan`.`programId` AS `subprogProgramId`,
        sp.`subprogNomor`,
        sp.`subprogNama`,
        sp.subprogJeniskegId,
        sp.subprogKodeLabel,
        sp.subprogRKAKLKegiatanId 
FROM
        `sub_program` sp 
        INNER JOIN `program_ref` pr_asal 
                ON `pr_asal`.`programId` = sp.`subprogProgramId` 
                AND `pr_asal`.`programThanggarId` = '%s' 
                AND `pr_asal`.`programNomor` IN(%s)
        INNER JOIN `program_ref` pr_tujuan 
                ON `pr_tujuan`.`programNomor` = `pr_asal`.`programNomor` 
                AND `pr_tujuan`.`programThanggarId` = '%s' 
";

$sql['copy_sub_kegiatan'] = "
INSERT IGNORE INTO 
	`kegiatan_ref`(
		kegrefNomor,
		kegregIkId,
		kegrefSubprogId,
		kegrefNama,
		kegrefLabelKode,
		kegrefRkaklSubKegiatanId,
		kegrefOutputValue,
		kegrefOutputJnsId
	)
SELECT 
        kr.`kegrefNomor`,
        kr.`kegregIkId`,
        sp_tujuan.`subprogId` AS kegrefSubprogId,
        kr.`kegrefNama`,
        kr.`kegrefLabelKode`,
        kr.`kegrefRkaklSubKegiatanId`,
        kr.`kegrefOutputValue`,
        kr.`kegrefOutputJnsId` 
FROM
        `sub_program` sp_asal 
        INNER JOIN `program_ref` pr_asal 
                ON `pr_asal`.`programId` = sp_asal.`subprogProgramId` 
                AND `pr_asal`.`programThanggarId` = '%s' 
                AND `pr_asal`.`programNomor` IN(%s)
        INNER JOIN `kegiatan_ref` kr 
                ON kr.`kegrefSubprogId` = sp_asal.`subprogId` 
        INNER JOIN `program_ref` pr_tujuan 
                ON `pr_tujuan`.`programNomor` = `pr_asal`.`programNomor` 
                AND `pr_tujuan`.`programThanggarId` = '%s' 
        INNER JOIN `sub_program` sp_tujuan 
                ON sp_tujuan.`subprogProgramId` = `pr_tujuan`.`programId` 
                AND `sp_tujuan`.`subprogNomor` =  sp_asal.`subprogNomor`
";

$sql['copy_komponen']="
INSERT IGNORE INTO
	`komponen_kegiatan`(
		kompkegKompId,
		kompkegKegrefId,
		kompkegBiaya 
	)

SELECT 
       kk.`kompkegKompId`,
       kr_tujuan.`kegrefId`,
       kk.`kompkegBiaya`
FROM
        `sub_program` sp_asal 
        INNER JOIN `program_ref` pr_asal 
                ON `pr_asal`.`programId` = sp_asal.`subprogProgramId` 
                AND `pr_asal`.`programThanggarId` = '%s' 
                AND `pr_asal`.`programNomor` IN(%s)
        INNER JOIN `kegiatan_ref` kr_asal
                ON kr_asal.`kegrefSubprogId` = sp_asal.`subprogId` 
        INNER JOIN komponen_kegiatan kk ON kk.`kompkegKegrefId` = kr_asal.`kegrefId`				
        INNER JOIN `program_ref` pr_tujuan 
                ON `pr_tujuan`.`programNomor` = `pr_asal`.`programNomor` 
                AND `pr_tujuan`.`programThanggarId` = '%s' 
        INNER JOIN `sub_program` sp_tujuan 
                ON sp_tujuan.`subprogProgramId` = `pr_tujuan`.`programId` 
                AND `sp_tujuan`.`subprogNomor` =  sp_asal.`subprogNomor`
        INNER JOIN `kegiatan_ref` kr_tujuan
                ON kr_tujuan.`kegrefSubprogId` = sp_tujuan.`subprogId` 
                AND kr_tujuan.`kegrefNomor` = kr_asal.`kegrefNomor`
";

$sql['copy_kegiatan_unit']="
INSERT  IGNORE INTO
	`finansi_pa_kegiatan_ref_unit_kerja`
	(
		`kegrefId`,
		`unitkerjaId`
	)
SELECT 
        kr_tujuan.`kegrefId`,
        kr_uk.`unitkerjaId`
FROM
        `sub_program` sp_asal 
        INNER JOIN `program_ref` pr_asal 
                ON `pr_asal`.`programId` = sp_asal.`subprogProgramId` 
                AND `pr_asal`.`programThanggarId` = '%s' 
                AND `pr_asal`.`programNomor` IN(%s)
        INNER JOIN `kegiatan_ref` kr_asal
                ON kr_asal.`kegrefSubprogId` = sp_asal.`subprogId` 
        INNER JOIN `finansi_pa_kegiatan_ref_unit_kerja` kr_uk 
		ON kr_uk.`kegrefId` = kr_asal.`kegrefId`
        INNER JOIN `program_ref` pr_tujuan 
                ON `pr_tujuan`.`programNomor` = `pr_asal`.`programNomor` 
                AND `pr_tujuan`.`programThanggarId` = '%s' 
        INNER JOIN `sub_program` sp_tujuan 
                ON sp_tujuan.`subprogProgramId` = `pr_tujuan`.`programId` 
                AND `sp_tujuan`.`subprogNomor` =  sp_asal.`subprogNomor`
        INNER JOIN `kegiatan_ref` kr_tujuan
                ON kr_tujuan.`kegrefSubprogId` = sp_tujuan.`subprogId` 
                AND kr_tujuan.`kegrefNomor` = kr_asal.`kegrefNomor`                
";

$sql['copy_sub_kegiatan_indikator_kegiatan']="
INSERT IGNORE INTO `finansi_pa_kegiatan_ik`
(
	    `kegiatanIkIkId`,
        `kegiatanIkKegrefId`,
        `kegiatanIkTglUbah`,
        `kegiatanIkUserId`
)

SELECT 
        kr_ik.`kegiatanIkIkId`,
        kr_tujuan.`kegrefId`,
        NOW(),
        '%s'
FROM
        `sub_program` sp_asal 
        INNER JOIN `program_ref` pr_asal 
                ON `pr_asal`.`programId` = sp_asal.`subprogProgramId` 
                AND `pr_asal`.`programThanggarId` = '%s' 
                AND `pr_asal`.`programNomor` IN(%s)
        INNER JOIN `kegiatan_ref` kr_asal
                ON kr_asal.`kegrefSubprogId` = sp_asal.`subprogId` 
        INNER JOIN `finansi_pa_kegiatan_ik` kr_ik 
		ON kr_ik.`kegiatanIkKegrefId` = kr_asal.`kegrefId`
        INNER JOIN `program_ref` pr_tujuan 
                ON `pr_tujuan`.`programNomor` = `pr_asal`.`programNomor` 
                AND `pr_tujuan`.`programThanggarId` = '%s' 
        INNER JOIN `sub_program` sp_tujuan 
                ON sp_tujuan.`subprogProgramId` = `pr_tujuan`.`programId` 
                AND `sp_tujuan`.`subprogNomor` =  sp_asal.`subprogNomor`
        INNER JOIN `kegiatan_ref` kr_tujuan
                ON kr_tujuan.`kegrefSubprogId` = sp_tujuan.`subprogId` 
                AND kr_tujuan.`kegrefNomor` = kr_asal.`kegrefNomor`
";

/**
 * untuk proses pengkopian program ke
 * finansi_pa_mst_program_kegiatan
 */
$sql['get_kode_sistem']    = "
SELECT 
        IF(
                (SELECT 
                        LOWER(`programKegiatanLevelNama`) 
                FROM
                        `finansi_pa_mst_program_kegiatan` 
                        LEFT JOIN `finansi_pa_ref_program_kegiatan_level` 
                                ON `programKegiatanLevelId` = `programKegiatanMstLevelId` 
                WHERE `programKegiatanMstId` = '%s') = 'program',
                (SELECT 
                        COUNT(`programKegiatanMstId`) + 1 
                FROM
                        `finansi_pa_mst_program_kegiatan` 
                WHERE `programKegiatanParentProgramKegaitanId` = 0),
                CONCAT(
                        programKegiatanMstKodeSistem,
                        '.',
                        (SELECT 
                                COUNT(`programKegiatanMstId`) 
                        FROM
                                `finansi_pa_mst_program_kegiatan` 
                        WHERE `programKegiatanParentProgramKegaitanId` = '%s')
                )
        ) AS kode_sistem 
FROM
        `finansi_pa_mst_program_kegiatan` 
WHERE `programKegiatanMstId` = '%s' 
";
 
$sql['get_data_program_to_mst']="
SELECT 
        (SELECT 
                programKegiatanLevelId 
        FROM
                `finansi_pa_ref_program_kegiatan_level` 
        WHERE programKegiatanLevelKode = '01') AS level_id,
        '0' AS parent_id,
        pr.`programNomor` AS kode,
        pr.`programNama` AS nama,
        NOW() AS tanggal
FROM
        `program_ref` pr 
WHERE pr.`programNomor` IN (%s) 
        AND pr.`programNomor` NOT IN 
        (SELECT 
                pk.`programKegiatanMstKode` 
        FROM
                `finansi_pa_mst_program_kegiatan` pk 
        WHERE pk.`programKegiatanMstLevelId` = 1) 
GROUP BY kode
ORDER BY kode ASC 
"; 

$sql['get_data_kegiatan_to_mst']="
SELECT 
	(SELECT 
		programKegiatanLevelId 
	 FROM 
		`finansi_pa_ref_program_kegiatan_level` 
	 WHERE programKegiatanLevelKode = '02') AS level_id,
	(SELECT 
		`programKegiatanMstId` 
	 FROM 
		`finansi_pa_mst_program_kegiatan`
        WHERE `programKegiatanMstKode` = pr.`programNomor`
                AND `programKegiatanMstLevelId` = 
                (SELECT 
                        programKegiatanLevelId 
                FROM
                        `finansi_pa_ref_program_kegiatan_level` 
                WHERE programKegiatanLevelKode = '01')) AS parent_id,
	
	sp.`subprogNomor` AS kode,
	sp.`subprogNama` AS nama,
	NOW() AS tanggal
FROM
        `sub_program` sp
         LEFT JOIN `program_ref` pr ON pr.`programId` = sp.`subprogProgramId`
WHERE  pr.`programNomor` IN (%s)  
        AND sp.`subprogNomor` NOT IN 
        (SELECT 
                pk.`programKegiatanMstKode` 
        FROM
                `finansi_pa_mst_program_kegiatan` pk 
        WHERE pk.`programKegiatanMstLevelId` = 2) 
GROUP BY kode
ORDER BY kode ASC
";

$sql['get_data_sub_kegiatan_to_mst']="
SELECT 
        (SELECT 
                programKegiatanLevelId 
        FROM
                `finansi_pa_ref_program_kegiatan_level` 
        WHERE programKegiatanLevelKode = '03') AS level_id,
        (SELECT 
                `programKegiatanMstId` 
        FROM
                `finansi_pa_mst_program_kegiatan` 
        WHERE `programKegiatanMstKode` = sp.`subprogNomor` 
                AND `programKegiatanMstLevelId` = 
                (SELECT 
                        programKegiatanLevelId 
                FROM
                        `finansi_pa_ref_program_kegiatan_level` 
                WHERE programKegiatanLevelKode = '02')) AS parent_id,
        kr.`kegrefNomor` AS kode,
        kr.`kegrefNama` AS nama,
        NOW() AS tanggal
FROM
        `kegiatan_ref` kr 
        LEFT JOIN `sub_program` sp 
                ON sp.`subprogId` = kr.`kegrefSubprogId` 
        LEFT JOIN `program_ref` pr 
                ON pr.`programId` = sp.`subprogProgramId` 
WHERE  pr.`programNomor` IN (%s)  
        AND kr.`kegrefNomor` NOT IN  
        (SELECT 
                pk.`programKegiatanMstKode` 
        FROM
                `finansi_pa_mst_program_kegiatan` pk 
        WHERE pk.`programKegiatanMstLevelId` = 3) 
                
GROUP BY kode 
ORDER BY kode ASC 
";

$sql['get_data_komponen_to_mst'] ="
SELECT 
        (SELECT 
                programKegiatanLevelId 
        FROM
                `finansi_pa_ref_program_kegiatan_level` 
        WHERE programKegiatanLevelKode = '06') AS level_id,
        (SELECT 
                `programKegiatanMstId` 
        FROM
                `finansi_pa_mst_program_kegiatan` 
        WHERE `programKegiatanMstKode` = kr.`kegrefNomor`
                AND `programKegiatanMstLevelId` = 
                (SELECT 
                        programKegiatanLevelId 
                FROM
                        `finansi_pa_ref_program_kegiatan_level` 
                WHERE programKegiatanLevelKode = '03')) AS parent_id,        
	k.`kompKode` AS kode,
	k.`kompNama` AS nama,
	NOW() AS tanggal
FROM
	`komponen_kegiatan` kk
	LEFT JOIN `komponen` k ON k.`kompId` = kk.`kompkegKompId`
	LEFT JOIN `kegiatan_ref` kr ON kr.`kegrefId` = kk.`kompkegKegrefId`
	LEFT JOIN `sub_program` sp ON sp.`subprogId` = kr.`kegrefSubprogId`
	LEFT JOIN `program_ref` pr ON pr.`programId` = sp.`subprogProgramId`
WHERE  pr.`programNomor` IN (%s)  
        AND k.`kompKode` NOT IN  
        (SELECT 
                pk.`programKegiatanMstKode` 
        FROM
                `finansi_pa_mst_program_kegiatan` pk 
        WHERE pk.`programKegiatanMstLevelId` = 6) 
                
GROUP BY kode 
ORDER BY kode ASC 
";

$sql['copy_program_master']="
INSERT INTO `finansi_pa_mst_program_kegiatan` 
(
        `programKegiatanMstLevelId`,
        `programKegiatanParentProgramKegaitanId`,
        `programKegiatanMstKodeSistem`,
        `programKegiatanMstKode`,
        `programKegiatanMstNama`,
        `programKegiatanMstTglUbah`,
        `programKegiatanMstUserId`
)
VALUES (
        '%s',
        '%s',
        (SELECT 
                (IFNULL(MAX(pk.`programKegiatanMstKodeSistem` + 0),0) +1) AS kodeSistem
        FROM
                `finansi_pa_mst_program_kegiatan` pk 
        WHERE pk.`programKegiatanMstLevelId` = 1),
        '%s',
        '%s',
        '%s',
        '%s'
)  
";

$sql['copy_kegiatan_master']="
INSERT INTO `finansi_pa_mst_program_kegiatan` 
(
        `programKegiatanMstLevelId`,
        `programKegiatanParentProgramKegaitanId`,
        `programKegiatanMstKodeSistem`,
        `programKegiatanMstKode`,
        `programKegiatanMstNama`,
        `programKegiatanMstTglUbah`,
        `programKegiatanMstUserId`
)
VALUES (
        '%s',
        '%s',
        (
       SELECT
            CONCAT( 
            (SELECT 
		      pk.`programKegiatanMstKodeSistem`
	           FROM
		      `finansi_pa_mst_program_kegiatan` pk 
	           WHERE pk.`programKegiatanMstLevelId` = '%s' AND pk.`programKegiatanMstId` = '%s') 
            ,'.',
            (IFNULL(MAX(
		(SUBSTR(pk_child.`programKegiatanMstKodeSistem`,
		@a:=(SELECT 
		      LENGTH(pk.`programKegiatanMstKodeSistem`)
	           FROM
		      `finansi_pa_mst_program_kegiatan` pk 
	           WHERE pk.`programKegiatanMstLevelId` = '%s' AND pk.`programKegiatanMstId` = '%s')+2
			,(LENGTH(pk_child.`programKegiatanMstKodeSistem`) - @a)+2
			)+0)
            ),0) + 1)
            )
             AS kodeSistem 
            FROM
                `finansi_pa_mst_program_kegiatan` pk_child
                WHERE pk_child.`programKegiatanMstLevelId` = '%s'     
                AND `pk_child`.`programKegiatanParentProgramKegaitanId` = '%s'
        ),
        '%s',
        '%s',
        '%s',
        '%s'
)  
";
$sql['copy_program_master_old']="
INSERT IGNORE INTO `finansi_pa_mst_program_kegiatan` (
        `programKegiatanMstLevelId`,
        `programKegiatanParentProgramKegaitanId`,
        `programKegiatanMstKodeSistem`,
        `programKegiatanMstKode`,
        `programKegiatanMstNama`,
        `programKegiatanMstTglUbah`,
        `programKegiatanMstUserId`
) 
SELECT 
	(SELECT 
		programKegiatanLevelId 
	 FROM 
		`finansi_pa_ref_program_kegiatan_level` 
	 WHERE programKegiatanLevelKode = '01') AS level_id,
	'0' AS parent_id,
	'0' AS kodeSistem,
        pr.`programNomor` AS kodeProgram,
        pr.`programNama` AS namaProgram ,
	NOW() AS tanggal,
	'%s' AS user_id
FROM
        `program_ref` pr 
WHERE pr.`programNomor` IN (%s) 
        AND pr.`programNomor` NOT IN 
        (SELECT 
                pk.`programKegiatanMstKode` 
        FROM
                `finansi_pa_mst_program_kegiatan` pk 
        WHERE pk.`programKegiatanMstLevelId` = 1) 
GROUP BY kodeProgram 
";