<?php

/**
 * indikatorkegiatanref.sql.php
 * @package indikator_program_ref
 * @todo Untuk mengumpulkan perintah-perintah query
 * @subpackage business
 * @since 22 Juni 2012
 * @SystemAnalyst Dyan Galih Nugroho Wicaksi <galih@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
 /**
  * untuk proses akses data
  */
 
 $sql['get_data_by_id']="
 SELECT
	`ref_ik`.`ikId` AS id,
	`ref_ik`.`ikKode` AS kode,
	`ref_ik`.`ikNama` AS nama,
	`ref_ik`.`ikValue` AS value,
	`ref_ip`.`ipId` AS ipId,
	`ref_ip`.`ipNama` AS ipNama,
	`ref_ip`.`ipKode` AS ipKode
 FROM 
	`finansi_pa_ref_ik` ref_ik
	LEFT JOIN `finansi_pa_ref_ip` ref_ip ON `ref_ip`.`ipId` = `ref_ik`.`ikIpId`
 WHERE 
	`ref_ik`.`ikId` ='%s'
 ";

$sql['get_count_kode']="
SELECT 
	COUNT(ref_ik.`ikKode`) AS total
 FROM 
	`finansi_pa_ref_ik` ref_ik
 WHERE 
	ref_ik.`ikKode` = '%s'
";  
/**
 * end
 */
   
 /**
  * untuk proses manipulasi data
  */
 $sql['add']="
 INSERT INTO `finansi_pa_ref_ik`
 (
    ikIpId,
	ikKode,
	ikNama,
    ikValue,
	ikTglUbah,
	ikUserUbahId
 )
 VALUES ('%s','%s','%s','%s',NOW(),'%s')
 ";
 $sql['update']="
 UPDATE `finansi_pa_ref_ik`
 SET
    ikIpId = '%s',
	ikKode = '%s',
	ikNama = '%s',
    ikValue = '%s',
	ikTglUbah = NOW(),
	ikUserUbahId = '%s'
 WHERE 
	ikId = '%s' 
 ";
 $sql['delete']="
 DELETE FROM `finansi_pa_ref_ik`
 WHERE 
	ikId IN(%s)
 ";
 /**
  * end
  */ 