<?php
$sql['get_periode_tahun']     = "
SELECT
   thanggarId AS `id`,
   thanggarNama AS `name`
FROM tahun_anggaran
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
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

$sql['count']     = "
SELECT SQL_CALC_FOUND_ROWS
   COUNT(kodeterimaId) AS count
FROM
   rencana_penerimaan
   JOIN tahun_anggaran
      ON thanggarId = renterimaThanggarId
   JOIN unit_kerja_ref
      ON unitkerjaId = renterimaUnitkerjaId
   JOIN
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
   JOIN kode_penerimaan_ref
      ON kodeterimaId = renterimaKodeterimaId
   LEFT JOIN finansi_pa_kode_penerimaan_ref_unit_alokasi
      ON penerimaanUnitAlokasiIdKdPenRef = kodeterimaId
   LEFT JOIN finansi_rp_ref_status_rp
      ON rpstatusId = renterimaRpstatusId
WHERE 1 = 1
   AND renterimaThanggarId = %s
   AND (kodeterimaKode LIKE '%s' OR kodeterimaNama LIKE '%s')
   AND renterimaUnitkerjaId IN
   (SELECT
      unitkerjaId
   FROM
      unit_kerja_ref
   WHERE 1 = 1
      AND SUBSTR(`unitkerjaKodeSistem`, 1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`, '.'))
         FROM
            unit_kerja_ref
         WHERE `unitkerjaId` = '%s')
      ) =
      (SELECT
         CONCAT(`unitkerjaKodeSistem`, '.')
      FROM
         unit_kerja_ref
      WHERE `unitkerjaId` = '%s')
      OR unitkerjaId = '%s')
";

$sql['get_data']  = "
SELECT SQL_CALC_FOUND_ROWS
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   kodeterimaId AS id,
   kodeterimaKode AS kode,
   kodeterimaNama AS nama,
   renterimaId AS rencanaPenerimaanId,
   penerimaanUnitAlokasiUnit AS alokasiUnit,
   penerimaanUnitAlokasiPusat AS alokasiPusat,
   IF(renterimaDeskripsi IS NULL OR renterimaDeskripsi = '', '-', renterimaDeskripsi) AS deskripsi,
   IF(renterimaKeterangan IS NULL OR renterimaKeterangan = '', '-', renterimaKeterangan) AS keterangan,
   renterimaTotalTerima AS nominal,
   UPPER(rpstatusNama) AS `status`
FROM
   rencana_penerimaan
   JOIN tahun_anggaran
      ON thanggarId = renterimaThanggarId
   JOIN unit_kerja_ref
      ON unitkerjaId = renterimaUnitkerjaId
   JOIN
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
   JOIN kode_penerimaan_ref
      ON kodeterimaId = renterimaKodeterimaId
   LEFT JOIN finansi_pa_kode_penerimaan_ref_unit_alokasi
      ON penerimaanUnitAlokasiIdKdPenRef = kodeterimaId
   LEFT JOIN finansi_rp_ref_status_rp
      ON rpstatusId = renterimaRpstatusId
WHERE 1 = 1
   AND renterimaThanggarId = %s
   AND (kodeterimaKode LIKE '%s' OR kodeterimaNama LIKE '%s')
   AND renterimaUnitkerjaId IN
   (SELECT
      unitkerjaId
   FROM
      unit_kerja_ref
   WHERE 1 = 1
      AND SUBSTR(`unitkerjaKodeSistem`, 1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`, '.'))
         FROM
            unit_kerja_ref
         WHERE `unitkerjaId` = '%s')
      ) =
      (SELECT
         CONCAT(`unitkerjaKodeSistem`, '.')
      FROM
         unit_kerja_ref
      WHERE `unitkerjaId` = '%s')
      OR unitkerjaId = '%s')
ORDER BY thanggarId DESC,
SUBSTRING_INDEX(tmp_unit.kode, '.', 1)+0,
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

$sql['get_total_perunit']  = "
SELECT SQL_CALC_FOUND_ROWS
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   kodeterimaId AS id,
   kodeterimaKode AS kode,
   kodeterimaNama AS nama,
   renterimaId AS rencanaPenerimaanId,
   penerimaanUnitAlokasiUnit AS alokasiUnit,
   penerimaanUnitAlokasiPusat AS alokasiPusat,
   IF(renterimaDeskripsi IS NULL OR renterimaDeskripsi = '', '-', renterimaDeskripsi) AS deskripsi,
   IF(renterimaKeterangan IS NULL OR renterimaKeterangan = '', '-', renterimaKeterangan) AS keterangan,
   IFNULL(SUM(renterimaTotalTerima),0) AS nominal,
   UPPER(rpstatusNama) AS `status`
FROM
   rencana_penerimaan
   JOIN tahun_anggaran
      ON thanggarId = renterimaThanggarId
   JOIN unit_kerja_ref
      ON unitkerjaId = renterimaUnitkerjaId
   JOIN
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
   JOIN kode_penerimaan_ref
      ON kodeterimaId = renterimaKodeterimaId
   LEFT JOIN finansi_pa_kode_penerimaan_ref_unit_alokasi
      ON penerimaanUnitAlokasiIdKdPenRef = kodeterimaId
   LEFT JOIN finansi_rp_ref_status_rp
      ON rpstatusId = renterimaRpstatusId
WHERE 1 = 1
   AND renterimaThanggarId = %s
   AND (kodeterimaKode LIKE '%s' OR kodeterimaNama LIKE '%s')
   AND renterimaUnitkerjaId IN
   (SELECT
      unitkerjaId
   FROM
      unit_kerja_ref
   WHERE 1 = 1
      AND SUBSTR(`unitkerjaKodeSistem`, 1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`, '.'))
         FROM
            unit_kerja_ref
         WHERE `unitkerjaId` = '%s')
      ) =
      (SELECT
         CONCAT(`unitkerjaKodeSistem`, '.')
      FROM
         unit_kerja_ref
      WHERE `unitkerjaId` = '%s')
      OR unitkerjaId = '%s')
GROUP BY unitkerjaId
ORDER BY thanggarId DESC,
SUBSTRING_INDEX(tmp_unit.kode, '.', 1)+0,
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
";

$sql['do_save_rencana_penerimaan']     = "
INSERT INTO rencana_penerimaan
SET renterimaUnitkerjaId = '%s',
   renterimaKodeterimaId = '%s',
   renterimaTotalTerima = '%s',
   renterimaDeskripsi = '%s',
   renterimaTipedistribusiId = NULL,
   renterimaThanggarId = '%s',
   renterimaVolume = '%s',
   renterimaTarif = '%s',
   renterimaJumlah = '%s',
   renterimaPersenPagu = '%s',
   renterimaPagu = '%s',
   renterimaKeterangan = '%s',
   renterimaSumberDanaId = '%s',
   renterimaCatatan = '%s',
   renterimaAlokasiUnit = IF(%s = '' OR %s IS NULL, NULL, %s),
   renterimaAlokasiPusat = IF(%s = '' OR %s IS NULL, NULL, %s),
   renterimaNamaSatuan = '%s',
   renterimaAlokasiPusatId = '%s',
   renterimaJmlJan = 0,
   renterimaJmlFeb = 0,
   renterimaJmlMar = 0,
   renterimaJmlApr = 0,
   renterimaJmlMei = 0,
   renterimaJmlJun = 0,
   renterimaJmlJul = 0,
   renterimaJmlAgs = 0,
   renterimaJmlSep = 0,
   renterimaJmlOkt = 0,
   renterimaJmlNov = 0,
   renterimaJmlDes = 0,
   rterimaPersenJan = 0,
   rterimaPersenFeb = 0,
   rterimaPersenMar = 0,
   rterimaPersenApr = 0,
   rterimaPersenMei = 0,
   rterimaPersenJun = 0,
   rterimaPersenJul = 0,
   rterimaPersenAgs = 0,
   rterimaPersenSep = 0,
   rterimaPersenOkt = 0,
   rterimaPersenNov = 0,
   rterimaPersenDes = 0
";

$sql['do_set_rincian_bulan_rencana_penerimaan'] = "
UPDATE rencana_penerimaan
SET renterimaJmlJan = '%s',
   renterimaJmlFeb = '%s',
   renterimaJmlMar = '%s',
   renterimaJmlApr = '%s',
   renterimaJmlMei = '%s',
   renterimaJmlJun = '%s',
   renterimaJmlJul = '%s',
   renterimaJmlAgs = '%s',
   renterimaJmlSep = '%s',
   renterimaJmlOkt = '%s',
   renterimaJmlNov = '%s',
   renterimaJmlDes = '%s',
   rterimaPersenJan = '%s',
   rterimaPersenFeb = '%s',
   rterimaPersenMar = '%s',
   rterimaPersenApr = '%s',
   rterimaPersenMei = '%s',
   rterimaPersenJun = '%s',
   rterimaPersenJul = '%s',
   rterimaPersenAgs = '%s',
   rterimaPersenSep = '%s',
   rterimaPersenOkt = '%s',
   rterimaPersenNov = '%s',
   rterimaPersenDes = '%s'
WHERE renterimaId = '%s'
";

$sql['get_Data_detail']    = "
SELECT
   renterimaId AS id,
   renterimaThanggarId AS taId,
   thanggarNama AS taNama,
   renterimaUnitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   renterimaKodeterimaId AS kodePenerimaanId,
   kodeterimaKode AS kodePenerimaanKode,
   kodeterimaNama AS kodePenerimaanNama,
   sumberdanaId AS sumberDanaId,
   sumberdanaNama AS sumberDanaNama,
   renterimaDeskripsi AS deskripsi,
   renterimaKeterangan AS keterangan,
   renterimaCatatan AS catatan,
   renterimaPersenPagu AS realisasiPagu,
   renterimaPagu AS nominalPagu,
   renterimaVolume AS volume,
   renterimaTarif AS tarif,
   renterimaNamaSatuan AS satuan,
   renterimaJumlah AS nominal,
   renterimaTotalTerima AS totalPenerimaan,
   penerimaanUnitAlokasiIdPusatUnitKerja AS alokasiPusatId,
   penerimaanUnitAlokasiId AS alokasiId,
   renterimaAlokasiUnit AS alokasiUnit,
   renterimaAlokasiPusat AS alokasiPusat,
   UPPER(rpstatusNama) AS `status`
FROM
   rencana_penerimaan
   JOIN tahun_anggaran
      ON thanggarId = renterimaThanggarId
   JOIN unit_kerja_ref
      ON unitkerjaId = renterimaUnitkerjaId
   JOIN kode_penerimaan_ref
      ON kodeterimaId = renterimaKodeterimaId
   LEFT JOIN finansi_pa_kode_penerimaan_ref_unit_alokasi
      ON penerimaanUnitAlokasiIdKdPenRef = kodeterimaId
   LEFT JOIN finansi_ref_sumber_dana
      ON sumberdanaId = renterimaSumberDanaId
   LEFT JOIN satuan_komponen
      ON satkompId = kodeterimaSatKompId
   LEFT JOIN finansi_rp_ref_status_rp
      ON rpstatusId = renterimaRpstatusId
WHERE 1 = 1
   AND renterimaId = %s
LIMIT 1
";

$sql['get_rincian_penerimaan_bulan']   = "
SELECT
   renterimaJmlJan AS januariNominal,
   renterimaJmlFeb AS februariNominal,
   renterimaJmlMar AS maretNominal,
   renterimaJmlApr AS aprilNominal,
   renterimaJmlMei AS meiNominal,
   renterimaJmlJun AS juniNominal,
   renterimaJmlJul AS juliNominal,
   renterimaJmlAgs AS agustusNominal,
   renterimaJmlSep AS septemberNominal,
   renterimaJmlOkt AS oktoberNominal,
   renterimaJmlNov AS novemberNominal,
   renterimaJmlDes AS desemberNominal,
   rterimaPersenJan AS januariPersen,
   rterimaPersenFeb AS februariPersen,
   rterimaPersenMar AS maretPersen,
   rterimaPersenApr AS aprilPersen,
   rterimaPersenMei AS meiPersen,
   rterimaPersenJun AS juniPersen,
   rterimaPersenJul AS juliPersen,
   rterimaPersenAgs AS agustusPersen,
   rterimaPersenSep AS septemberPersen,
   rterimaPersenOkt AS oktoberPersen,
   rterimaPersenNov AS novemberPersen,
   rterimaPersenDes AS desemberPersen
FROM rencana_penerimaan
WHERE 1 = 1
AND renterimaId = %s
LIMIT 1
";

$sql['do_update_rencana_penerimaan']      = "
UPDATE rencana_penerimaan
SET renterimaUnitkerjaId = '%s',
   renterimaKodeterimaId = '%s',
   renterimaTotalTerima = '%s',
   renterimaDeskripsi = '%s',
   renterimaThanggarId = '%s',
   renterimaVolume = '%s',
   renterimaTarif = '%s',
   renterimaJumlah = '%s',
   renterimaPersenPagu = '%s',
   renterimaPagu = '%s',
   renterimaKeterangan = '%s',
   renterimaSumberDanaId = '%s',
   renterimaCatatan = '%s',
   renterimaAlokasiUnit = IF(%s = '' OR %s IS NULL, NULL, %s),
   renterimaAlokasiPusat = IF(%s = '' OR %s IS NULL, NULL, %s),
   renterimaNamaSatuan = '%s',
   renterimaAlokasiPusatId = '%s'
WHERE renterimaId = %s
";

/**
 * input detail alokasi rencana penerimaan
 */
$sql['do_insert_detail_alokasi_unit'] ="
INSERT INTO rencana_penerimaan_detil(
  `renterimadtRenterimaId`,
  `renterimadtUnitIndukId`,
  `renterimadtUnitKerjaId`,
  `renterimadtAlokasi`)
SELECT
   '%s' AS renterimaId,
   pau.`penerimaAlokasiUnitIndukUnitKerjaId` AS unit_induk_id,
   pau.`penerimaAlokasiUnitUnitKerjaId` AS unit_kerja_id,
   pau.`penerimaAlokasiUnitNilaiAlokasi` AS alokasi
   FROM finansi_pa_penerima_alokasi_unit pau
WHERE
   pau.`penerimaAlokasiUnitAlokasiId` = '%s'
";

$sql['do_delete_rencana_penerimaan_detail']  = "
DELETE FROM rencana_penerimaan_detil WHERE renterimadtRenterimaId = %s
";

$sql['do_delete_rencana_penerimaan']   = "
DELETE
FROM
   rencana_penerimaan
WHERE renterimaId = '%s'
";
?>