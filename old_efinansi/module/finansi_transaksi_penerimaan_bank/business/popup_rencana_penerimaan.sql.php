<?php

$sql['get_count_data'] = "
   SELECT FOUND_ROWS() as total
";

$sql['get_data'] = "
SELECT
    SQL_CALC_FOUND_ROWS 
    renterimaId           AS id,
    kodeTerimaKode        AS kode,
    kodeTerimaNama        AS nama,
    renterimaKeterangan   AS keterangan,
    renterimaTotalTerima  AS nominal_aprove	
FROM rencana_penerimaan
    LEFT JOIN kode_penerimaan_ref
        ON kodeterimaId = renterimaKodeterimaId
    LEFT JOIN unit_kerja_ref
        ON unitkerjaid = renterimaUnitkerjaId
WHERE 
    unitkerjaId = '%s'
    AND kodeTerimaKode LIKE '%s' 
    AND kodeterimaNama LIKE '%s'
    AND renterimaRpstatusId='2'
    AND `renterimaThanggarId` = '%s'
GROUP BY renterimaId
LIMIT %s, %s	
";

$sql['get_periode_tahun']     = "
SELECT
   thanggarId AS `id`,
   thanggarNama AS `name`
FROM tahun_anggaran
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR thanggarIsOpen = 'Y')
ORDER BY thanggarNama DESC
";

$sql['get_periode_tahun_aktif_open']   = "
SELECT
   thanggarId AS `id`,
   thanggarNama AS `name`
FROM
   tahun_anggaran
WHERE 1 = 1
   AND thanggarIsAktif = 'Y'
   OR thanggarIsOpen = 'Y'
ORDER BY thanggarNama
";
?>