<?php
$sql['get_count_data']="
SELECT 
  count(uk.`unitkerjaId`) AS total
FROM
  `finansi_pa_kode_penerimaan_ref_unit_alokasi` al 
  LEFT JOIN `kode_penerimaan_ref` kp
    ON (al.`penerimaanUnitAlokasiIdKdPenRef` = kp.`kodeterimaId`) 
  LEFT JOIN `unit_kerja_ref` uk
    ON (al.`penerimaanUnitAlokasiIdUnitKerja` = uk.`unitkerjaId`) 
WHERE
  kp.`kodeterimaTipe` NOT IN ('header') 
  AND 
  kp.`kodeterimaIsAktif` = 'Y' 
  AND (kp.`kodeterimaKode` LIKE '%s' OR kp.`kodeterimaNama` LIKE '%s'
  ) 
  AND (uk.`unitkerjaKode` LIKE '%s' OR uk.`unitkerjaNama` LIKE '%s') 
  

";

$sql['get_data']="
SELECT 
  uk.`unitkerjaId` AS unit_kerja_id,
  uk.`unitkerjaKode` AS unit_kerja_kode,
  uk.`unitkerjaNama` AS unit_kerja_nama,
  kp.`kodeterimaKode` AS kode_penerimaan_kode,
  kp.`kodeterimaNama` AS kode_penerimaan_nama,
  al.`penerimaanUnitAlokasiNilaiBatas` AS alokasi_nilai_batas,
  al.`penerimaanUnitAlokasiUnit` AS alokasi_unit,
  al.`penerimaanUnitAlokasiPusat` AS alokasi_pusat,
  al.`penerimaanUnitAlokasiId` AS alokasi_id,
  al.`penerimaanUnitAlokasiIdUnitKerja` AS alokasi_unit_id,
  al.`penerimaanUnitAlokasiIdPusatUnitKerja` AS alokasi_pusat_id ,
  uk_p.`unitkerjaKode` AS p_unit_kerja_kode,
  uk_p.`unitkerjaNama` AS p_unit_kerja_nama
FROM
  `finansi_pa_kode_penerimaan_ref_unit_alokasi` al 
  LEFT JOIN `kode_penerimaan_ref` kp
    ON (al.`penerimaanUnitAlokasiIdKdPenRef` = kp.`kodeterimaId`) 
  LEFT JOIN `unit_kerja_ref` uk
    ON (al.`penerimaanUnitAlokasiIdUnitKerja` = uk.`unitkerjaId`) 
  LEFT JOIN unit_kerja_ref uk_p
     ON ( uk_p.`unitkerjaId` = al.`penerimaanUnitAlokasiIdPusatUnitKerja`)      
WHERE
  kp.`kodeterimaTipe` NOT IN ('header') 
  AND 
  kp.`kodeterimaIsAktif` = 'Y' 
  AND (kp.`kodeterimaKode` LIKE '%s' OR kp.`kodeterimaNama` LIKE '%s'
  ) 
  AND (uk.`unitkerjaKode` LIKE '%s' OR uk.`unitkerjaNama` LIKE '%s') 
  
ORDER BY uk.`unitkerjaId` ASC 
LIMIT %s,%s

";

?>