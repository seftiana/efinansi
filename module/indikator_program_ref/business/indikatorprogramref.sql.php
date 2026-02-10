<?php

/**
 * indikatorprogramref.sql.php
 * @package indikator_program_ref
 * @todo Untuk mengumpulkan perintah-perintah query
 * @subpackage business
 * @since 21 Juni 2012
 * @SystemAnalyst Dyan Galih Nugroho Wicaksi <galih@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
 /**
  * untuk proses akses data
  */
 $sql['get_data']="
 SELECT
	ref_ip.`ipId` AS ipId,
	ref_ip.`ipKode` AS ipKode,
	ref_ip.`ipNama` AS ipNama,
	ref_ik.`ikId` AS ikId,
	ref_ik.`ikKode` AS ikKode,
	ref_ik.`ikNama` AS ikNama,
    ref_ik.`ikValue` as ikValue
	
 FROM 
	`finansi_pa_ref_ip` ref_ip
	LEFT JOIN `finansi_pa_ref_ik` ref_ik ON ref_ip.`ipId` = ref_ik.`ikIpId`
    AND `ref_ik`.`ikKode` LIKE '%s' AND `ref_ik`.`ikNama` LIKE '%s' 
 WHERE
	`ref_ip`.`ipId` LIKE '%s'
 ORDER BY ipId,ikKode ASC    
 LIMIT %s,%s
 ";
 
 $sql['get_data_count']="
 SELECT 
	COUNT(ref_ip.`ipId`) AS total
 FROM 
	`finansi_pa_ref_ip` ref_ip
	LEFT JOIN `finansi_pa_ref_ik` ref_ik ON ref_ip.`ipId` = ref_ik.`ikIpId`
    AND `ref_ik`.`ikKode` LIKE '%s' AND `ref_ik`.`ikNama` LIKE '%s' 
 WHERE
	`ref_ip`.`ipId` LIKE '%s'    
 ";
 
 $sql['get_data_by_id']="
 SELECT 
	ref_ip.`ipId` AS id,
	ref_ip.`ipKode` AS kode,
	ref_ip.`ipNama` AS nama
 FROM 
	`finansi_pa_ref_ip` ref_ip
 WHERE 
	ref_ip.`ipId` = '%s'
 ";

$sql['get_count_kode']="
SELECT 
	COUNT(ref_ip.`ipKode`) AS total
 FROM 
	`finansi_pa_ref_ip` ref_ip
 WHERE 
	ref_ip.`ipKode` = '%s'
";  
/**
 * end
 */
   
 /**
  * untuk proses manipulasi data
  */
 $sql['add']="
 INSERT INTO `finansi_pa_ref_ip`
 (
	ipKode,
	ipNama,
	ipTglUbah,
	ipUserId
 )
 VALUES ('%s','%s',NOW(),'%s')
 ";
 $sql['update']="
 UPDATE `finansi_pa_ref_ip`
 SET
	ipKode ='%s',
	ipNama ='%s',
	ipTglUbah =NOW(),
	ipUserId ='%s'

 WHERE 
	ipId = '%s' 
 ";
 $sql['delete']="
 DELETE FROM `finansi_pa_ref_ip`
 WHERE 
	ipId IN(%s)
 ";
 /**
  * end
  */ 