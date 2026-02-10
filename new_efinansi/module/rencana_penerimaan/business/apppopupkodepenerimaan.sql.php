<?php
$sql['count']        = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']     = "
SELECT
   SQL_CALC_FOUND_ROWS unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   penerimaanUnitAlokasiIdPusatUnitKerja AS alokasiPusatId,
   penerimaanUnitAlokasiId AS alokasiId,
   kodeterimaId AS id,
   kodeterimaKode AS kode,
   kodeterimaNama AS nama,
   satkompId,
   IF(satkompNama IS NULL OR satkompNama = '', '-', satkompNama) AS satuan,
   IF(
      LOWER(kodeterimaTipe) = 'header',
      NULL,
      penerimaanUnitAlokasiUnit
   ) AS alokasiUnit,
   IF(
      LOWER(kodeterimaTipe) = 'header',
      NULL,
      penerimaanUnitAlokasiPusat
   ) AS alokasiPusat,
   sumberdanaId,
   sumberdanaNama,
   kodeterimaTipe AS tipe,
   IFNULL(tmp_kp.count, 0) AS child
FROM
   kode_penerimaan_ref
   LEFT JOIN (SELECT COUNT(kodeterimaId) AS `count`,
      kodeterimaParentId AS id
   FROM kode_penerimaan_ref
   GROUP BY kodeterimaParentId
   ) AS tmp_kp ON tmp_kp.id = kodeterimaId
   LEFT JOIN finansi_pa_kode_penerimaan_ref_unit_alokasi
      ON penerimaanUnitAlokasiIdKdPenRef = kodeterimaId
   LEFT JOIN unit_kerja_ref
      ON unitkerjaId = penerimaanUnitAlokasiIdUnitKerja
   LEFT JOIN finansi_ref_sumber_dana
      ON sumberDanaId = kodeterimaSumberDanaId
   LEFT JOIN
      (SELECT
         unitkerjaId AS id,
         CASE
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$'
            THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$'
            THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$'
            THEN CONCAT(unitkerjaKodeSistem, '.0.0')
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$'
            THEN CONCAT(unitkerjaKodeSistem, '.0')
            WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$'
            THEN unitkerjaKodeSistem
         END AS kode
      FROM
         unit_kerja_ref) AS tmp_unit
      ON tmp_unit.id = unitkerjaId
   LEFT JOIN satuan_komponen
      ON satkompId = kodeterimaSatKompId
WHERE 1 = 1
   AND kodeterimaIsAktif = 'Y'
   AND (penerimaanUnitAlokasiIdUnitKerja = %s OR 1 = %s)
   AND kodeterimaKode LIKE '%s'
   AND kodeterimaNama LIKE '%s'
ORDER BY SUBSTRING_INDEX(tmp_unit.kode, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 5), '.', -1)+0,
SUBSTRING_INDEX(kodeterimaKode, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(kodeterimaKode, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(kodeterimaKode, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(kodeterimaKode, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(kodeterimaKode, '.', 5), '.', -1)+0,
SUBSTRING_INDEX(kodeterimaKode, '-', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(kodeterimaKode, '-', 2), '-', -1)+0
LIMIT %s, %s
";
?>