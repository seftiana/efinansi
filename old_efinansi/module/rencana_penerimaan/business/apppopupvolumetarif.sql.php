<?php

/**
 * query untuk menipulasi data volume tarif
 * @package rencana_penerimaan
 * @since 6 februari 2012
 * @copyright 2012 gamatechno
 */
 
$sql['get_data_volume_tarif'] ="
SELECT 
	`finansi_pa_ref_penerimaan_tarif`.`penerimaanTarifNama` AS nama,
	`finansi_pa_ref_penerimaan_tarif`.`penerimaanTarifNilai` AS tarif,
	CONCAT(`pm_fakultas_ref`.`fakultasNamaFakultas`,' - ',`pm_program_studi_ref`.`prodiNamaProdi`) AS  fak,
	`pm_program_studi_ref`.`prodiKodeProdi` AS prodi_kode
FROM 
	`finansi_pa_ref_penerimaan_tarif`
	LEFT JOIN
	`pm_program_studi_ref` ON `pm_program_studi_ref`.`prodiId` = `finansi_pa_ref_penerimaan_tarif`.`penerimaanTarifProdiId`
	LEFT JOIN
	`pm_fakultas_ref` ON `pm_fakultas_ref`.`fakultasId` = `pm_program_studi_ref`.`prodiFakultasId`
	
WHERE
	(`pm_fakultas_ref`.`fakultasNamaFakultas` LIKE '%s' OR `pm_program_studi_ref`.`prodiNamaProdi` LIKE '%s')
	AND 
	`finansi_pa_ref_penerimaan_tarif`.`penerimaanTarifNama`  LIKE '%s'
ORDER BY 
	`pm_program_studi_ref`.`prodiKodeProdi` ASC
LIMIT %s,%s
";

$sql['get_count_data_volume_tarif']="
SELECT 
	count(`finansi_pa_ref_penerimaan_tarif`.`penerimaanTarifNama`) AS total
FROM 
	`finansi_pa_ref_penerimaan_tarif`
	LEFT JOIN
	`pm_program_studi_ref` ON `pm_program_studi_ref`.`prodiId` = `finansi_pa_ref_penerimaan_tarif`.`penerimaanTarifProdiId`
	LEFT JOIN
	`pm_fakultas_ref` ON `pm_fakultas_ref`.`fakultasId` = `pm_program_studi_ref`.`prodiFakultasId`
WHERE
	(`pm_fakultas_ref`.`fakultasNamaFakultas` LIKE '%s' OR `pm_program_studi_ref`.`prodiNamaProdi` LIKE '%s')
	AND 
	`finansi_pa_ref_penerimaan_tarif`.`penerimaanTarifNama`  LIKE '%s'

";

/**
 * query untuk mendapatan volume
 * dari aplikasi lain
 */
 
 $sql['get_volume']="
 	SELECT 
		formulaFormula as volume_query
	FROM 
		finansi_ref_formula 
	WHERE 
		formulaCode = 'GET_VOLUME_TARIF' 
	AND 
		formulaIsAktif = 'Y' 
	LIMIT 0,1
 ";
 
$sql['get_volume_old']="
SELECT 
	COUNT(`mahasiswaId`) AS volume 
FROM 
	`pm_data_mahasiswa`
	LEFT JOIN  `pm_program_studi_ref` ON `pm_program_studi_ref`.`prodiId` = `pm_data_mahasiswa`.`mahasiswaProdiId`
WHERE 
	`pm_data_mahasiswa`.`mahasiswaStatusMhsId` = 2 AND `pm_program_studi_ref`.`prodiKodeProdi` = %s
GROUP BY 
	`pm_data_mahasiswa`.`mahasiswaProdiId`
";
/**
 * end
 */