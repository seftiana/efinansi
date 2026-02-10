<?php


$sql['get_data'] = "
SELECT 
  SQL_CALC_FOUND_ROWS
  kp_a.`penerimaanUnitAlokasiIdUnitKerja` AS unit_kerja_sumber_id,
  uk_sumber.`unitkerjaKode` AS unit_kerja_sumber_kode,
  uk_sumber.`unitkerjaNama` AS unit_kerja_sumber_nama,
  concat(kp_a.`penerimaanUnitAlokasiIdUnitKerja`,kp_a.`penerimaanUnitAlokasiIdKdPenRef`) AS kode_penerimaan_id,
  kpr_p.`kodeterimaKode` AS kode_penerimaan_kode,
  kpr_p.`kodeterimaNama` AS kode_penerimaan_nama,
  kp_a.`penerimaanUnitAlokasiId` AS alokasi_id,
  concat(kp_a.`penerimaanUnitAlokasiIdUnitKerja`,kp_a.`penerimaanUnitAlokasiIdKdPenRef`,
  IF( al.`penerimaAlokasiUnitIndukUnitKerjaId` = kp_a.`penerimaanUnitAlokasiIdUnitKerja`,
      2,
  IF( al.`penerimaAlokasiUnitIndukUnitKerjaId` = kp_a.`penerimaanUnitAlokasiIdPusatUnitKerja`,      
      1,0)) ) AS jenis_alokasi,  
   IF( al.`penerimaAlokasiUnitIndukUnitKerjaId` = kp_a.`penerimaanUnitAlokasiIdUnitKerja`,
      2,
  IF( al.`penerimaAlokasiUnitIndukUnitKerjaId` = kp_a.`penerimaanUnitAlokasiIdPusatUnitKerja`,      
      1,0)) AS jenis_alokasi_kode,  
  IF( al.`penerimaAlokasiUnitIndukUnitKerjaId` = kp_a.`penerimaanUnitAlokasiIdUnitKerja`,
      kp_a.`penerimaanUnitAlokasiUnit`,
   IF( al.`penerimaAlokasiUnitIndukUnitKerjaId` = kp_a.`penerimaanUnitAlokasiIdPusatUnitKerja`,      
      kp_a.`penerimaanUnitAlokasiPusat`,0)) AS besar_alokasi_sumber,
    
  al.`penerimaAlokasiUnitIndukUnitKerjaId` AS unit_induk_id,
  uk_induk.`unitkerjaKode` AS unit_induk_kode,
  uk_induk.`unitkerjaNama` AS unit_induk_nama,
  al.`penerimaAlokasiUnitUnitKerjaId` AS unit_kerja_id,
  uk_anak.`unitkerjaKode` AS unit_kerja_kode,
  uk_anak.`unitkerjaNama` AS unit_kerja_nama,
  al.`penerimaAlokasiUnitAlokasiId` AS alokasi_unit_id,
  al.`penerimaAlokasiUnitNilaiAlokasi` AS alokasi_nilai,
  kp_a.`penerimaanUnitAlokasiIdUnitKerja` AS alokasi_unit_id,
  kp_a.`penerimaanUnitAlokasiIdPusatUnitKerja` AS alokasi_pusat_id
FROM
  `finansi_pa_penerima_alokasi_unit` al 
  LEFT JOIN finansi_pa_kode_penerimaan_ref_unit_alokasi kp_a 
    ON kp_a.`penerimaanUnitAlokasiId` = al.`penerimaAlokasiUnitAlokasiId` 
  LEFT JOIN kode_penerimaan_ref kpr_p 
    ON kpr_p.`kodeterimaId` = kp_a.`penerimaanUnitAlokasiIdKdPenRef` 
  LEFT JOIN unit_kerja_ref uk_sumber
    ON uk_sumber.`unitkerjaId` = kp_a.`penerimaanUnitAlokasiIdUnitKerja`  
  LEFT JOIN unit_kerja_ref uk_induk
    ON uk_induk.`unitkerjaId` = al.`penerimaAlokasiUnitIndukUnitKerjaId`
  LEFT JOIN unit_kerja_ref uk_anak
    ON (uk_anak.`unitkerjaId` = al.`penerimaAlokasiUnitUnitKerjaId`)       
WHERE 
  
	(uk_sumber.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	uk_sumber.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	) 
	AND
	(kp_a.`penerimaanUnitAlokasiIdKdPenRef` = '%s' OR %s )
  
ORDER BY unit_kerja_sumber_kode,kode_penerimaan_kode,unit_induk_kode
LIMIT %s,%s
"; 

$sql['get_count_data'] = "
SELECT FOUND_ROWS() AS total
";

$sql['get_data_by_id']="
SELECT 
  kp_a.`penerimaanUnitAlokasiId` AS alokasi_id,
  kp_a.`penerimaanUnitAlokasiIdKdPenRef` AS kode_penerimaan_id,
  kp_a.`penerimaanUnitAlokasiIdUnitKerja` AS unit_kerja_id,
  uk.`unitkerjaNama` AS unit_kerja_nama,
  kpr_p.`kodeterimaKode` AS kode_penerimaan_kode,
  CONCAT(kpr_p.`kodeterimaKode`,' - ',kpr_p.`kodeterimaNama` ) AS kode_penerimaan_nama,
  kp_a.`penerimaanUnitAlokasiIdUnitKerja` AS alokasi_unit_id,
  kp_a.`penerimaanUnitAlokasiIdPusatUnitKerja` AS alokasi_pusat_id,
  kp_a.`penerimaanUnitAlokasiUnit` AS alokasi_unit_nilai,
  kp_a.`penerimaanUnitAlokasiPusat` AS alokasi_pusat_nilai 
FROM
  finansi_pa_kode_penerimaan_ref_unit_alokasi kp_a 
  LEFT JOIN kode_penerimaan_ref kpr_p 
    ON kpr_p.`kodeterimaId` = kp_a.`penerimaanUnitAlokasiIdKdPenRef` 
  LEFT JOIN unit_kerja_ref uk
    ON uk.`unitkerjaId` = kp_a.`penerimaanUnitAlokasiIdUnitKerja`
WHERE 
  kp_a.`penerimaanUnitAlokasiId` = '%s'
";

$sql['get_unit_kerja_penerima']="
SELECT 
 al.`penerimaAlokasiUnitUnitKerjaId` AS unit_kerja_id,
 uk.`unitkerjaKode` AS unit_kerja_kode,
 uk.`unitkerjaNama` AS unit_kerja_nama,
 al.`penerimaAlokasiUnitNilaiAlokasi` AS nilai
FROM 
  finansi_pa_penerima_alokasi_unit al
  LEFT JOIN unit_kerja_ref uk
   ON uk.`unitkerjaId` = al.`penerimaAlokasiUnitUnitKerjaId`
  LEFT  JOIN finansi_pa_kode_penerimaan_ref_unit_alokasi kp_a
    ON kp_a.`penerimaanUnitAlokasiId`= al.`penerimaAlokasiUnitAlokasiId`
WHERE 
  al.`penerimaAlokasiUnitAlokasiId` = '%s'
  AND
  al.`penerimaAlokasiUnitIndukUnitKerjaId` = '%s'
ORDER BY unit_kerja_kode ASC  
";

$sql['get_count_unit_kerja_penerima']="
SELECT 
  COUNT(`penerimaAlokasiUnitUnitKerjaId`) AS total
FROM
  finansi_pa_penerima_alokasi_unit
WHERE 
  `penerimaAlokasiUnitAlokasiId` = '%s'
";

$sql['get_count_kode_penerimaan_alokasi'] ="
SELECT 
	COUNT(penerimaAlokasiUnitId) AS total 
FROM 
	`finansi_pa_penerima_alokasi_unit`
WHERE 
	penerimaAlokasiUnitAlokasiId = '%s'
";

$sql['do_add']="
INSERT INTO `finansi_pa_penerima_alokasi_unit`
  (
  `penerimaAlokasiUnitAlokasiId`,
  `penerimaAlokasiUnitIndukUnitKerjaId`,
  `penerimaAlokasiUnitUnitKerjaId`,
  `penerimaAlokasiUnitNilaiAlokasi`
  )
  VALUE('%s','%s','%s','%s')
";

$sql['do_delete']="
DELETE FROM `finansi_pa_penerima_alokasi_unit`
WHERE penerimaAlokasiUnitAlokasiId = '%s'
";

?>