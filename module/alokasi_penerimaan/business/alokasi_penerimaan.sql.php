<?php

/**
 * Query
 * @package alokasi_penerimaan
 * @since 27 Maret 2012
 * @copyright 2012 Gamatechno
 */ 
 

$sql['get_count_data']="
SELECT 
       count( `kode_penerimaan_ref`.`kodeterimaKode`) as total 
FROM
        `finansi_pa_kode_penerimaan_ref_unit_alokasi` 
        INNER JOIN `kode_penerimaan_ref` 
                ON (
                        `finansi_pa_kode_penerimaan_ref_unit_alokasi`.`penerimaanUnitAlokasiIdKdPenRef` = `kode_penerimaan_ref`.`kodeterimaId`
                ) 
        INNER JOIN `unit_kerja_ref` 
                ON (
                        `finansi_pa_kode_penerimaan_ref_unit_alokasi`.`penerimaanUnitAlokasiIdUnitKerja` = `unit_kerja_ref`.`unitkerjaId`
                ) 

WHERE 
	`kode_penerimaan_ref`.`kodeterimaTipe` NOT IN('header') 
	AND
	`kode_penerimaan_ref`.`kodeterimaIsAktif` = 'Y'
    AND
    `kode_penerimaan_ref`.`kodeterimaKode` LIKE '%s' 
    AND
    `kode_penerimaan_ref`.`kodeterimaNama` LIKE '%s'
    AND
    	(unit_kerja_ref.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	unit_kerja_ref.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)

";

$sql['get_alokasi_penerimaan']="
SELECT 
        `unit_kerja_ref`.`unitkerjaId` as id_unit,
        `unit_kerja_ref`.`unitkerjaKode` as kode_unit,
        `unit_kerja_ref`.`unitkerjaNama` as nama_unit,
        `kode_penerimaan_ref`.`kodeterimaKode` as kode_terima,
        `kode_penerimaan_ref`.`kodeterimaNama` as nama_terima,
        `finansi_pa_kode_penerimaan_ref_unit_alokasi`.`penerimaanUnitAlokasiNilaiBatas` as alokasi_nilai_batas,
        `finansi_pa_kode_penerimaan_ref_unit_alokasi`.`penerimaanUnitAlokasiUnit` as alokasi_unit,
        `finansi_pa_kode_penerimaan_ref_unit_alokasi`.`penerimaanUnitAlokasiPusat` as alokasi_pusat,
        `finansi_pa_kode_penerimaan_ref_unit_alokasi`.`penerimaanUnitAlokasiId` as alokasi_id,
        `finansi_pa_kode_penerimaan_ref_unit_alokasi`.`penerimaanUnitAlokasiOperan` as alokasi_operan ,
         REPLACE(`unit_kerja_ref`.`unitkerjaKode`,'.','') AS kunit,
         REPLACE(`kode_penerimaan_ref`.`kodeterimaKode`,'.','') AS kterima
FROM
        `finansi_pa_kode_penerimaan_ref_unit_alokasi` 
        INNER JOIN `kode_penerimaan_ref` 
                ON (
                        `finansi_pa_kode_penerimaan_ref_unit_alokasi`.`penerimaanUnitAlokasiIdKdPenRef` = `kode_penerimaan_ref`.`kodeterimaId`
                ) 
        INNER JOIN `unit_kerja_ref` 
                ON (
                        `finansi_pa_kode_penerimaan_ref_unit_alokasi`.`penerimaanUnitAlokasiIdUnitKerja` = `unit_kerja_ref`.`unitkerjaId`
                ) 

WHERE 
	`kode_penerimaan_ref`.`kodeterimaTipe` NOT IN('header') 
	AND
	`kode_penerimaan_ref`.`kodeterimaIsAktif` = 'Y'
    AND
    `kode_penerimaan_ref`.`kodeterimaKode` LIKE '%s' 
    AND 
    `kode_penerimaan_ref`.`kodeterimaNama` LIKE '%s'
    AND
	(unit_kerja_ref.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	unit_kerja_ref.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)
    
ORDER BY `kunit`,`kterima` ASC
LIMIT %s,%s

";
$sql['get_alokasi_penerimaan_by_id']="
SELECT 
   au.`penerimaanUnitAlokasiIdKdPenRef` AS id_kode_terima,
   kpr.`kodeterimaNama` AS nama_terima,
  au.`penerimaanUnitAlokasiIdUnitKerja` AS id_unit,
  uk.`unitkerjaNama` AS nama_unit,
  au.`penerimaanUnitAlokasiUnit` AS alokasi_unit,
  au.`penerimaanUnitAlokasiPusat` alokasi_pusat,
  au.`penerimaanUnitAlokasiOperan` AS alokasi_operan,
  au.`penerimaanUnitAlokasiNilaiBatas` AS alokasi_nilai_batas ,
  au.`penerimaanUnitAlokasiIdPusatUnitKerja` AS id_unit_pusat,
  uk_p.`unitkerjaNama` AS nama_unit_pusat
FROM
  `finansi_pa_kode_penerimaan_ref_unit_alokasi` au
  LEFT JOIN `unit_kerja_ref` uk
    ON uk.`unitkerjaId` = au.`penerimaanUnitAlokasiIdUnitKerja` 
  LEFT JOIN `kode_penerimaan_ref` kpr
    ON kpr.`kodeterimaId` = au.`penerimaanUnitAlokasiIdKdPenRef` 
  LEFT JOIN unit_kerja_ref uk_p
    ON uk_p.`unitkerjaId` = au.`penerimaanUnitAlokasiIdPusatUnitKerja`  
WHERE
	au.`penerimaanUnitAlokasiId` = '%s'
";

$sql['is_alokasi_exist']="
SELECT
        count(`penerimaanUnitAlokasiId`) as total
FROM 
	`finansi_pa_kode_penerimaan_ref_unit_alokasi`
WHERE
	`penerimaanUnitAlokasiIdUnitKerja` = '%s' 
    AND
    `penerimaanUnitAlokasiIdKdPenRef` = '%s'
    %s
";
$sql['insert_alokasi']="
INSERT INTO 
`finansi_pa_kode_penerimaan_ref_unit_alokasi` 
(
        `penerimaanUnitAlokasiIdKdPenRef`,
        `penerimaanUnitAlokasiIdUnitKerja`,
        `penerimaanUnitAlokasiUnit`,
        `penerimaanUnitAlokasiPusat`,
        `penerimaanUnitAlokasiOperan`,
        `penerimaanUnitAlokasiNilaiBatas`,
        `penerimaanUnitAlokasiIdPusatUnitKerja` 
) 
VALUES
        ('%s', '%s', '%s', '%s', '%s', '%s','%s') 
";
$sql['update_alokasi']="
UPDATE 
	`finansi_pa_kode_penerimaan_ref_unit_alokasi`
SET 
        `penerimaanUnitAlokasiIdKdPenRef` = '%s',
        `penerimaanUnitAlokasiIdUnitKerja` = '%s',
        `penerimaanUnitAlokasiUnit` = '%s',
        `penerimaanUnitAlokasiPusat` = '%s',
        `penerimaanUnitAlokasiOperan` = '%s',
        `penerimaanUnitAlokasiNilaiBatas` = '%s',
        `penerimaanUnitAlokasiIdPusatUnitKerja` = '%s'
WHERE 
	`penerimaanUnitAlokasiId` = '%s'
";

$sql['delete_alokasi']="
DELETE
FROM `finansi_pa_kode_penerimaan_ref_unit_alokasi`
WHERE `penerimaanUnitAlokasiId` = '%s'
";

$sql['delete_alokasi_array']="
DELETE
FROM `finansi_pa_kode_penerimaan_ref_unit_alokasi`
WHERE `penerimaanUnitAlokasiId` IN (%s)
";

?>