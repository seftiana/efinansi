<?php

/**
 * popupindikatorprogramref.sql.php
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
 $sql['get_data']="
 SELECT
    ref_ip.`ipId` AS id,
	ref_ip.`ipKode` AS kode,
	ref_ip.`ipNama` AS nama
 FROM 
	`finansi_pa_ref_ip` ref_ip
 WHERE 
	ref_ip.`ipKode` LIKE '%s'
	AND 
	ref_ip.`ipNama` LIKE '%s'
 LIMIT %s,%s;       
 ";
 
 $sql['get_data_count']="
 SELECT 
	COUNT(ref_ip.`ipId`) AS total
 FROM 
	`finansi_pa_ref_ip` ref_ip
 WHERE 
	ref_ip.`ipKode` LIKE '%s'
	AND 
	ref_ip.`ipNama` LIKE '%s'        
 ";
