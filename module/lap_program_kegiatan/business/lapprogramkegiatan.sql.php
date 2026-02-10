<?php

/**
 * Kumpulan query LapProgramKegiatan
 * @package lap_program_kegiatan
 * @subpackage business
 * @todo untuk menjalankan perintah query
 * @since mei 2012
 * @copyright 2012 Gamatechno Indonesia
 * @author noor hadi <noor.hadi@gamatechno.com>
 */

//== for combo box ==
$sql['get_data_ta'] =
   "SELECT
      thanggarId AS id,
	  thanggarNama AS name
	FROM
	  tahun_anggaran
	ORDER BY
	  thanggarNama ASC
   ";


$sql['get_ta_aktif']=
   "SELECT
      thanggarId AS id
	FROM
	  tahun_anggaran
	WHERE
	  thanggarIsAktif='Y'
	LIMIT 1
   ";

$sql['get_data_ta_nama'] =
   "SELECT
	  thanggarNama AS nama
	FROM
	  tahun_anggaran
    WHERE
	  thanggarId = %s      
   ";   
$sql['get_unit_kerja']=
   "SELECT
      unitkerjaId AS unitkerja_id,
      unitkerjaKode AS unitkerja_kode,
      unitkerjaNama AS unitkerja_nama
	FROM
	  unit_kerja_ref
	WHERE
	  unitkerjaParentId LIKE %s AND
	  unitkerjaNama LIKE %s
	ORDER BY
	  unitkerjaKode, UnitkerjaNama ASC
	LIMIT %s, %s
   ";

$sql['get_count_unit_kerja']=
   "SELECT
      COUNT(unitkerjaId) AS total
	FROM
	  unit_kerja_ref
	WHERE
	  unitkerjaParentId LIKE %s AND
	  unitkerjaNama LIKE %s
	ORDER BY
	  unitkerjaKode, UnitkerjaNama ASC
	LIMIT 1
   ";
/**
 * untuk mendapatkan jumlah sub unit
 * @since 3 Januari 2012
 */
$sql['get_total_sub_unit_kerja']="
SELECT 
	count(unitkerjaId) as total
FROM unit_kerja_ref
WHERE unitkerjaParentId = %s
";

$sql['get_list_lap_program_kegiatan']="
SELECT 
 *
FROM 

((SELECT 
	p.`programId` AS id,
	'0' AS parent,
	'0' AS up_parent,
	'0' AS top_parent,
	'' AS kode_parent,
	'' AS kode_up_parent,
	'' AS kode_top_parent,
	'' AS nama_parent,
	'' AS nama_up_parent,
	'' AS nama_top_parent,
	p.`programNomor` AS kode,
	p.`programNama` AS nama,
        '' AS jumlah,
        '' AS formula,
        '' AS biaya,
        '' AS biaya_langsung,
        '' AS biaya_tetap,
        IF(rp.`rncnpengeluaranIsAprove`='Ya','Y','T') AS approve,
        jk.`jeniskegId` AS id_jenis,
	jk.`jeniskegNama` AS jenis_kegiatan,
	'' AS unit_nama,
	'' AS unit_id,
    '' AS unit_kode_sistem,
	'1' AS tipe,
	k.`kegThanggarId` AS th_anggar
FROM
        `rencana_pengeluaran` rp 
        LEFT JOIN `komponen` komp ON komp.`kompKode` = rp.`rncnpengeluaranKomponenKode`
        LEFT JOIN `kegiatan_detail` kd ON kd.`kegdetId` = rp.`rncnpengeluaranKegdetId`
	LEFT JOIN `kegiatan_ref` kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
	LEFT JOIN `sub_program` sp ON sp.`subprogId` = kr.`kegrefSubprogId`
	LEFT JOIN `kegiatan` k ON k.`kegId` = kd.`kegdetKegId`
	LEFT JOIN `program_ref` p ON p.`programId` = k.`kegProgramId`
	LEFT JOIN `unit_kerja_ref` uk ON k.`kegUnitkerjaId` = uk.`unitkerjaId`
	LEFT JOIN `jenis_kegiatan_ref` jk ON jk.`jeniskegId` = sp.`subprogJeniskegId`

/**GROUP BY kode*/)
UNION
(SELECT 
	CONCAT(p.`programId`,sp.`subprogId`) AS id,
	p.`programId` AS parent,
	'0' AS up_parent,
	'0' AS top_parent,
	p.`programNomor` AS kode_parent,
	'' AS kode_up_parent,
	'' AS kode_top_parent,
	p.`programNama` AS nama_parent,
	'' AS nama_up_parent,
	'' AS nama_top_parent,
	sp.`subprogNomor` AS kode,
	sp.`subprogNama` AS nama,
        '' AS jumlah,
        '' AS formula,
        '' AS biaya,
        '' AS biaya_langsung,
        '' AS biaya_tetap,
        IF(rp.`rncnpengeluaranIsAprove`='Ya','Y','T') AS approve,
        jk.`jeniskegId` AS id_jenis,
	jk.`jeniskegNama` AS jenis_kegiatan,
	'' AS unit_nama,
	'' AS unit_id,
	'' AS unit_kode_sistem,
	'2' AS tipe,
	k.`kegThanggarId` AS th_anggar
FROM
        `rencana_pengeluaran` rp 
        LEFT JOIN `komponen` komp ON komp.`kompKode` = rp.`rncnpengeluaranKomponenKode`
        LEFT JOIN `kegiatan_detail` kd ON kd.`kegdetId` = rp.`rncnpengeluaranKegdetId`
	LEFT JOIN `kegiatan_ref` kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
	LEFT JOIN `sub_program` sp ON sp.`subprogId` = kr.`kegrefSubprogId`
	LEFT JOIN `kegiatan` k ON k.`kegId` = kd.`kegdetKegId`
	LEFT JOIN `program_ref` p ON p.`programId` = k.`kegProgramId`
	LEFT JOIN `unit_kerja_ref` uk ON k.`kegUnitkerjaId` = uk.`unitkerjaId`
	LEFT JOIN `jenis_kegiatan_ref` jk ON jk.`jeniskegId` = sp.`subprogJeniskegId`

/*GROUP BY kode*/)
UNION
(SELECT 
	CONCAT(uk.`unitkerjaId`,p.`programId`,sp.`subprogId`,kr.`kegrefId`) AS id,
	CONCAT(p.`programId`,sp.`subprogId`) AS parent,
	p.`programId` AS up_parent,
	'0' AS top_parent,
	sp.`subprogNomor` AS kode_parent,
	p.`programNomor` AS kode_up_parent,
	'' AS kode_top_parent,
	sp.`subprogNama` AS nama_parent,
	p.`programNama` AS nama_up_parent,
	'' AS nama_top_parent,
	kr.`kegrefNomor` AS kode,
	kr.`kegrefNama` AS nama,
        '' AS jumlah,
        '' AS formula,
        '' AS biaya,
        '' AS biaya_langsung,
        '' AS biaya_tetap,
        IF(rp.`rncnpengeluaranIsAprove`='Ya','Y','T') AS approve,
        jk.`jeniskegId` AS id_jenis,
	jk.`jeniskegNama` AS jenis_kegiatan,
	uk.`unitkerjaNama` AS unit_nama,
	uk.`unitkerjaId` AS unit_id,
	uk.`unitkerjaKodeSistem` AS unit_kode_sistem,
	'3' AS tipe,
	k.`kegThanggarId` AS th_anggar
FROM
        `rencana_pengeluaran` rp 
        LEFT JOIN `komponen` komp ON komp.`kompKode` = rp.`rncnpengeluaranKomponenKode`
        LEFT JOIN `kegiatan_detail` kd ON kd.`kegdetId` = rp.`rncnpengeluaranKegdetId`
	LEFT JOIN `kegiatan_ref` kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
	LEFT JOIN `sub_program` sp ON sp.`subprogId` = kr.`kegrefSubprogId`
	LEFT JOIN `kegiatan` k ON k.`kegId` = kd.`kegdetKegId`
	LEFT JOIN `program_ref` p ON p.`programId` = k.`kegProgramId`
	LEFT JOIN `unit_kerja_ref` uk ON k.`kegUnitkerjaId` = uk.`unitkerjaId`
	LEFT JOIN `jenis_kegiatan_ref` jk ON jk.`jeniskegId` = sp.`subprogJeniskegId`

GROUP BY uk.`unitkerjaId`,p.`programId`,sp.`subprogId`	)
UNION
(SELECT 
	CONCAT(uk.`unitkerjaId`,p.`programId`,sp.`subprogId`,kr.`kegrefId`,rp.`rncnpengeluaranKomponenKode`) AS id,
	CONCAT(uk.`unitkerjaId`,p.`programId`,sp.`subprogId`,kr.`kegrefId`) AS parent,	
	CONCAT(p.`programId`,sp.`subprogId`) AS up_parent,
	p.`programId` AS top_parent,
	kr.`kegrefNomor` AS kode_parent,
	sp.`subprogNomor` AS kode_up_parent,
	p.`programNomor` AS kode_top_parent,
	kr.`kegrefNama` AS nama_parent,
	sp.`subprogNama` AS nama_up_parent,
	p.`programNama` AS nama_top_parent,
        rp.`rncnpengeluaranKomponenKode` AS kode,
        rp.`rncnpengeluaranKomponenNama` AS nama,
        rp.rncnpengeluaranSatuan AS jumlah,
        rp.rncnpengeluaranFormula AS formula,
        rp.rncnpengeluaranKomponenNominal AS biaya,
        komp.`kompIsLangsung` AS biaya_langsung,
        komp.`kompIsTetap` AS biaya_tetap,
        IF(rp.`rncnpengeluaranIsAprove`='Ya','Y','T') AS approve,
        jk.`jeniskegId` AS id_jenis,
	jk.`jeniskegNama` AS jenis_kegiatan,
	uk.`unitkerjaNama` AS unit_nama,
	uk.`unitkerjaId` AS unit_id,
	uk.`unitkerjaKodeSistem` AS unit_kode_sistem,
	'4' AS tipe,
	k.`kegThanggarId` AS th_anggar
FROM
        `rencana_pengeluaran` rp 
        LEFT JOIN `komponen` komp ON komp.`kompKode` = rp.`rncnpengeluaranKomponenKode`
        LEFT JOIN `kegiatan_detail` kd ON kd.`kegdetId` = rp.`rncnpengeluaranKegdetId`
	LEFT JOIN `kegiatan_ref` kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
	LEFT JOIN `sub_program` sp ON sp.`subprogId` = kr.`kegrefSubprogId`
	LEFT JOIN `kegiatan` k ON k.`kegId` = kd.`kegdetKegId`
	LEFT JOIN `program_ref` p ON p.`programId` = k.`kegProgramId`
	LEFT JOIN `unit_kerja_ref` uk ON k.`kegUnitkerjaId` = uk.`unitkerjaId`
	LEFT JOIN `jenis_kegiatan_ref` jk ON jk.`jeniskegId` = sp.`subprogJeniskegId`
)) pk
WHERE   
(pk.approve = 'Y') 
AND  
 pk.th_anggar = '%s' 
 AND
 pk.parent = '%s'    
AND
 (
	pk.unit_kode_sistem LIKE '%s'
	OR 
	pk.unit_kode_sistem LIKE '%s'
	OR 
	pk.unit_kode_sistem =''
 ) 
 ";

$sql['count_lap_program_kegiatan']="
SELECT 
 count(*) as total
FROM 

((SELECT 
	p.`programId` AS id,
	'0' AS parent,
	'0' AS up_parent,
	'0' AS top_parent,
	'' AS kode_parent,
	'' AS kode_up_parent,
	'' AS kode_top_parent,
	'' AS nama_parent,
	'' AS nama_up_parent,
	'' AS nama_top_parent,
	p.`programNomor` AS kode,
	p.`programNama` AS nama,
        '' AS jumlah,
        '' AS formula,
        '' AS biaya,
        '' AS biaya_langsung,
        '' AS biaya_tetap,
        IF(rp.`rncnpengeluaranIsAprove`='Ya','Y','T') AS approve,
        jk.`jeniskegId` AS id_jenis,
	jk.`jeniskegNama` AS jenis_kegiatan,
	'' AS unit_nama,
	'' AS unit_id,
    '' AS unit_kode_sistem,
	'1' AS tipe,
	k.`kegThanggarId` AS th_anggar
FROM
        `rencana_pengeluaran` rp 
        LEFT JOIN `komponen` komp ON komp.`kompKode` = rp.`rncnpengeluaranKomponenKode`
        LEFT JOIN `kegiatan_detail` kd ON kd.`kegdetId` = rp.`rncnpengeluaranKegdetId`
	LEFT JOIN `kegiatan_ref` kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
	LEFT JOIN `sub_program` sp ON sp.`subprogId` = kr.`kegrefSubprogId`
	LEFT JOIN `kegiatan` k ON k.`kegId` = kd.`kegdetKegId`
	LEFT JOIN `program_ref` p ON p.`programId` = k.`kegProgramId`
	LEFT JOIN `unit_kerja_ref` uk ON k.`kegUnitkerjaId` = uk.`unitkerjaId`
	LEFT JOIN `jenis_kegiatan_ref` jk ON jk.`jeniskegId` = sp.`subprogJeniskegId`

GROUP BY kode)
UNION
(SELECT 
	CONCAT(p.`programId`,sp.`subprogId`) AS id,
	p.`programId` AS parent,
	'0' AS up_parent,
	'0' AS top_parent,
	p.`programNomor` AS kode_parent,
	'' AS kode_up_parent,
	'' AS kode_top_parent,
	p.`programNama` AS nama_parent,
	'' AS nama_up_parent,
	'' AS nama_top_parent,
	sp.`subprogNomor` AS kode,
	sp.`subprogNama` AS nama,
        '' AS jumlah,
        '' AS formula,
        '' AS biaya,
        '' AS biaya_langsung,
        '' AS biaya_tetap,
        IF(rp.`rncnpengeluaranIsAprove`='Ya','Y','T') AS approve,
        jk.`jeniskegId` AS id_jenis,
	jk.`jeniskegNama` AS jenis_kegiatan,
	'' AS unit_nama,
	'' AS unit_id,
	'' AS unit_kode_sistem,
	'2' AS tipe,
	k.`kegThanggarId` AS th_anggar
FROM
        `rencana_pengeluaran` rp 
        LEFT JOIN `komponen` komp ON komp.`kompKode` = rp.`rncnpengeluaranKomponenKode`
        LEFT JOIN `kegiatan_detail` kd ON kd.`kegdetId` = rp.`rncnpengeluaranKegdetId`
	LEFT JOIN `kegiatan_ref` kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
	LEFT JOIN `sub_program` sp ON sp.`subprogId` = kr.`kegrefSubprogId`
	LEFT JOIN `kegiatan` k ON k.`kegId` = kd.`kegdetKegId`
	LEFT JOIN `program_ref` p ON p.`programId` = k.`kegProgramId`
	LEFT JOIN `unit_kerja_ref` uk ON k.`kegUnitkerjaId` = uk.`unitkerjaId`
	LEFT JOIN `jenis_kegiatan_ref` jk ON jk.`jeniskegId` = sp.`subprogJeniskegId`

GROUP BY kode)
UNION
(SELECT 
	CONCAT(uk.`unitkerjaId`,p.`programId`,sp.`subprogId`,kr.`kegrefId`) AS id,
	CONCAT(p.`programId`,sp.`subprogId`) AS parent,
	p.`programId` AS up_parent,
	'0' AS top_parent,
	sp.`subprogNomor` AS kode_parent,
	p.`programNomor` AS kode_up_parent,
	'' AS kode_top_parent,
	sp.`subprogNama` AS nama_parent,
	p.`programNama` AS nama_up_parent,
	'' AS nama_top_parent,
	kr.`kegrefNomor` AS kode,
	kr.`kegrefNama` AS nama,
        '' AS jumlah,
        '' AS formula,
        '' AS biaya,
        '' AS biaya_langsung,
        '' AS biaya_tetap,
        IF(rp.`rncnpengeluaranIsAprove`='Ya','Y','T') AS approve,
        jk.`jeniskegId` AS id_jenis,
	jk.`jeniskegNama` AS jenis_kegiatan,
	uk.`unitkerjaNama` AS unit_nama,
	uk.`unitkerjaId` AS unit_id,
	uk.`unitkerjaKodeSistem` AS unit_kode_sistem,
	'3' AS tipe,
	k.`kegThanggarId` AS th_anggar
FROM
        `rencana_pengeluaran` rp 
        LEFT JOIN `komponen` komp ON komp.`kompKode` = rp.`rncnpengeluaranKomponenKode`
        LEFT JOIN `kegiatan_detail` kd ON kd.`kegdetId` = rp.`rncnpengeluaranKegdetId`
	LEFT JOIN `kegiatan_ref` kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
	LEFT JOIN `sub_program` sp ON sp.`subprogId` = kr.`kegrefSubprogId`
	LEFT JOIN `kegiatan` k ON k.`kegId` = kd.`kegdetKegId`
	LEFT JOIN `program_ref` p ON p.`programId` = k.`kegProgramId`
	LEFT JOIN `unit_kerja_ref` uk ON k.`kegUnitkerjaId` = uk.`unitkerjaId`
	LEFT JOIN `jenis_kegiatan_ref` jk ON jk.`jeniskegId` = sp.`subprogJeniskegId`

GROUP BY uk.`unitkerjaId`,p.`programId`,sp.`subprogId`	)
UNION
(SELECT 
	CONCAT(uk.`unitkerjaId`,p.`programId`,sp.`subprogId`,kr.`kegrefId`,rp.`rncnpengeluaranKomponenKode`) AS id,
	CONCAT(uk.`unitkerjaId`,p.`programId`,sp.`subprogId`,kr.`kegrefId`) AS parent,	
	CONCAT(p.`programId`,sp.`subprogId`) AS up_parent,
	p.`programId` AS top_parent,
	kr.`kegrefNomor` AS kode_parent,
	sp.`subprogNomor` AS kode_up_parent,
	p.`programNomor` AS kode_top_parent,
	kr.`kegrefNama` AS nama_parent,
	sp.`subprogNama` AS nama_up_parent,
	p.`programNama` AS nama_top_parent,
        rp.`rncnpengeluaranKomponenKode` AS kode,
        rp.`rncnpengeluaranKomponenNama` AS nama,
        rp.rncnpengeluaranSatuan AS jumlah,
        rp.rncnpengeluaranFormula AS formula,
        rp.rncnpengeluaranKomponenNominal AS biaya,
        komp.`kompIsLangsung` AS biaya_langsung,
        komp.`kompIsTetap` AS biaya_tetap,
        IF(rp.`rncnpengeluaranIsAprove`='Ya','Y','T') AS approve,
        jk.`jeniskegId` AS id_jenis,
	jk.`jeniskegNama` AS jenis_kegiatan,
	uk.`unitkerjaNama` AS unit_nama,
	uk.`unitkerjaId` AS unit_id,
	uk.`unitkerjaKodeSistem` AS unit_kode_sistem,
	'4' AS tipe,
	k.`kegThanggarId` AS th_anggar
FROM
        `rencana_pengeluaran` rp 
        LEFT JOIN `komponen` komp ON komp.`kompKode` = rp.`rncnpengeluaranKomponenKode`
        LEFT JOIN `kegiatan_detail` kd ON kd.`kegdetId` = rp.`rncnpengeluaranKegdetId`
	LEFT JOIN `kegiatan_ref` kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
	LEFT JOIN `sub_program` sp ON sp.`subprogId` = kr.`kegrefSubprogId`
	LEFT JOIN `kegiatan` k ON k.`kegId` = kd.`kegdetKegId`
	LEFT JOIN `program_ref` p ON p.`programId` = k.`kegProgramId`
	LEFT JOIN `unit_kerja_ref` uk ON k.`kegUnitkerjaId` = uk.`unitkerjaId`
	LEFT JOIN `jenis_kegiatan_ref` jk ON jk.`jeniskegId` = sp.`subprogJeniskegId`
)) pk
WHERE   
(pk.approve = 'Y') 
AND  
 pk.th_anggar = '%s' 
 AND
 pk.parent = '%s'    
AND
 (
	pk.unit_kode_sistem LIKE '%s'
	OR 
	pk.unit_kode_sistem LIKE '%s'
	OR 
	pk.unit_kode_sistem =''
 ) 
";

/**
 * total nominal sub kegiatan
 */
$sql['count_total_nominal_sub_kegiatan']="
SELECT 
	IFNULL(SUM(total_biaya),0) AS total_biaya
FROM 
(SELECT 
	CONCAT(uk.`unitkerjaId`,p.`programId`,sp.`subprogId`,kr.`kegrefId`) AS parent,	
        komp.`kompIsLangsung` AS biaya_langsung,
        komp.`kompIsTetap` AS biaya_tetap,
	(rp.rncnpengeluaranKomponenNominal * rp.rncnpengeluaranSatuan) AS total_biaya,
	k.`kegThanggarId` AS th_anggar,
	uk.`unitkerjaKodeSistem` AS unit_kode_sistem
FROM
        `rencana_pengeluaran` rp 
        LEFT JOIN `komponen` komp ON komp.`kompKode` = rp.`rncnpengeluaranKomponenKode`
        LEFT JOIN `kegiatan_detail` kd ON kd.`kegdetId` = rp.`rncnpengeluaranKegdetId`
	LEFT JOIN `kegiatan_ref` kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
	LEFT JOIN `sub_program` sp ON sp.`subprogId` = kr.`kegrefSubprogId`
	LEFT JOIN `kegiatan` k ON k.`kegId` = kd.`kegdetKegId`
	LEFT JOIN `program_ref` p ON p.`programId` = k.`kegProgramId`
	LEFT JOIN `unit_kerja_ref` uk ON k.`kegUnitkerjaId` = uk.`unitkerjaId`
	LEFT JOIN `jenis_kegiatan_ref` jk ON jk.`jeniskegId` = sp.`subprogJeniskegId`
WHERE 
    rp.`rncnpengeluaranIsAprove`='Ya'    
  )pk
WHERE
  pk.th_anggar = '%s'
  AND pk.parent = '%s'
  AND (pk.biaya_langsung ='%s' AND pk.biaya_tetap = '%s')
  AND (pk.unit_kode_sistem LIKE '%s' OR pk.unit_kode_sistem LIKE '%s' OR pk.unit_kode_sistem ='')
";
/**
 * end
 */
 
/**
 * count total nominal program
 */
$sql['count_total_nominal_program']=
"SELECT 
       IFNULL( SUM(rp.rncnpengeluaranKomponenNominal * rp.rncnpengeluaranSatuan),0) AS total_biaya 
FROM
        `rencana_pengeluaran` rp 
        LEFT JOIN `komponen` komp 
                ON komp.`kompKode` = rp.`rncnpengeluaranKomponenKode` 
        LEFT JOIN `kegiatan_detail` kd 
                ON kd.`kegdetId` = rp.`rncnpengeluaranKegdetId` 
        LEFT JOIN `kegiatan_ref` kr 
                ON kr.`kegrefId` = kd.`kegdetKegrefId` 
        LEFT JOIN `sub_program` sp 
                ON sp.`subprogId` = kr.`kegrefSubprogId` 
        LEFT JOIN `kegiatan` k 
                ON k.`kegId` = kd.`kegdetKegId` 
        LEFT JOIN `program_ref` p 
                ON p.`programId` = k.`kegProgramId` 
        LEFT JOIN `unit_kerja_ref` uk 
                ON k.`kegUnitkerjaId` = uk.`unitkerjaId` 
        LEFT JOIN `jenis_kegiatan_ref` jk 
                ON jk.`jeniskegId` = sp.`subprogJeniskegId` 
WHERE 
    rp.`rncnpengeluaranIsAprove` = 'Ya'
    AND k.`kegThanggarId` = '%s'
    AND p.`programId` = '%s'
    AND (`komp`.`kompIsLangsung` = '%s' AND `komp`.`kompIsTetap` = '%s')
    AND ( uk.`unitkerjaKodeSistem` LIKE '%s' OR uk.`unitkerjaKodeSistem` LIKE '%s' OR uk.`unitkerjaKodeSistem` = '')
";
/**
 * end
 */

/** 
 * total nominal kegitan
 */
$sql['count_total_nominal_kegiatan']="
SELECT 
	IFNULL(SUM(total_biaya),0) AS total_biaya
FROM 
(SELECT 
	CONCAT(p.`programId`,sp.`subprogId`) AS up_parent,	
        komp.`kompIsLangsung` AS biaya_langsung,
        komp.`kompIsTetap` AS biaya_tetap,
	(rp.rncnpengeluaranKomponenNominal * rp.rncnpengeluaranSatuan) AS total_biaya,
	k.`kegThanggarId` AS th_anggar,
	uk.`unitkerjaKodeSistem` AS unit_kode_sistem
FROM
        `rencana_pengeluaran` rp 
        LEFT JOIN `komponen` komp ON komp.`kompKode` = rp.`rncnpengeluaranKomponenKode`
        LEFT JOIN `kegiatan_detail` kd ON kd.`kegdetId` = rp.`rncnpengeluaranKegdetId`
	LEFT JOIN `kegiatan_ref` kr ON kr.`kegrefId` = kd.`kegdetKegrefId`
	LEFT JOIN `sub_program` sp ON sp.`subprogId` = kr.`kegrefSubprogId`
	LEFT JOIN `kegiatan` k ON k.`kegId` = kd.`kegdetKegId`
	LEFT JOIN `program_ref` p ON p.`programId` = k.`kegProgramId`
	LEFT JOIN `unit_kerja_ref` uk ON k.`kegUnitkerjaId` = uk.`unitkerjaId`
	LEFT JOIN `jenis_kegiatan_ref` jk ON jk.`jeniskegId` = sp.`subprogJeniskegId`
WHERE 
    rp.`rncnpengeluaranIsAprove`='Ya'    
  )pk
WHERE
    pk.th_anggar = '%s'
    AND pk.up_parent = '%s'
    AND (pk.biaya_langsung = '%s' AND pk.biaya_tetap ='%s')
    AND (pk.unit_kode_sistem LIKE '%s' OR pk.unit_kode_sistem LIKE '%s' OR pk.unit_kode_sistem ='')
";
/**
 * end
 */
$sql['get_unit_kerja_kode_sistem']="
SELECT	
	unitkerjaKodeSistem  as kode_sistem
FROM 
    unit_kerja_ref 
WHERE 
	unit_kerja_ref.unitkerjaId='%s'
";