<?php
$sql['get_periode_tahun']     = "
SELECT
   thanggarId AS `id`,
   thanggarNama AS `name`
FROM tahun_anggaran
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
ORDER BY thanggarNama
";

$sql['get_data']        = "
SELECT SQL_CALC_FOUND_ROWS
   renterimaId AS id,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   kodeterimaId AS kodeId,
   kodeterimaKode AS kode,
   kodeterimaNama AS nama,
   renterimaRpstatusId AS approval,
   rpstatusNama AS status_nama,
   IF (renterimaTotalTerima IS NULL,0,renterimaTotalTerima) AS nominal,
   IF (penerimaan.totalTotalTerima IS NULL,0,penerimaan.totalTotalTerima) AS nominalTotal
FROM rencana_penerimaan
   JOIN unit_kerja_ref
      ON unitkerjaId = renterimaUnitkerjaId
   JOIN (
      SELECT unitkerjaId AS id,
      CASE
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN unitkerjaKodeSistem
      END AS kode
      FROM unit_kerja_ref
   ) AS tmp_unit ON tmp_unit.id = unitkerjaId
   JOIN tahun_anggaran
      ON thanggarId = renterimaThanggarId
   LEFT JOIN kode_penerimaan_ref
      ON renterimaKodeterimaId = kodeterimaId
   LEFT JOIN finansi_rp_ref_status_rp
      ON rpStatusid = renterimaRpstatusId
   LEFT JOIN(
      SELECT
         renterimaThanggarId AS ta,
         renterimaUnitkerjaId AS unit,
         SUM(renterimaTotalTerima) AS totalTotalTerima
      FROM
         rencana_penerimaan
      GROUP BY renterimaThanggarId,
      renterimaUnitkerjaId
   ) AS penerimaan ON penerimaan.ta = renterimaThanggarId
   AND penerimaan.unit = renterimaUnitkerjaId
WHERE 1 = 1
AND renterimaThanggarId = %s
AND (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
AND kodeterimaKode LIKE '%s'
AND kodeterimaNama LIKE '%s'
AND (renterimaRpstatusId = '%s' OR 1 = %s)
ORDER BY SUBSTRING_INDEX(tmp_unit.kode, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 5), '.', -1)+0,
SUBSTRING_INDEX(kodeterimaKode, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(kodeterimaKode, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(kodeterimaKode, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(kodeterimaKode, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(kodeterimaKode, '.', 5), '.', -1)+0
LIMIT %s, %s
";

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

//===GET===
$sql['get_count_data'] = "
   SELECT
      COUNT(renterimaId)         AS total
   FROM unit_kerja_ref
   LEFT JOIN rencana_penerimaan ON renterimaUnitkerjaId = unitkerjaId AND renterimaThanggarId ='%s'
   LEFT JOIN kode_penerimaan_ref ON renterimaKodeterimaId = kodeterimaId
   LEFT JOIN (SELECT renterimaUnitkerjaId AS totalUnitkerjaId, SUM(renterimaTotalTerima) AS totalTotalTerima FROM rencana_penerimaan WHERE renterimaThanggarId ='%s' GROUP BY totalUnitkerjaId) AS total ON totalUnitkerjaId=unitkerjaId
   WHERE
   (kodeterimaKode = '%s' OR kodeterimaNama LIKE '%s') AND
   unitkerjaKodeSistem
   LIKE CONCAT((SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitkerjaId=(SELECT userunitkerjaUnitkerjaId FROM user_unit_kerja WHERE userunitkerjaUserId = '%s')),'%%') %s
";
$sql['get_data_unitkerja'] = "
   SELECT
      unitkerjaId          AS idunit,
      unitkerjaKode        AS kode_satker,
      unitkerjaNama        AS nama_satker,
      unitkerjaKode        AS kode_unit,
      unitkerjaNama        AS nama_unit,
      unitkerjaParentId       AS parentId,
      if (renterimaTotalTerima IS NULL,0,renterimaTotalTerima)    AS total,
      if (totalTotalTerima IS NULL,0,totalTotalTerima)         AS jumlah_total,
      renterimaId          AS idrencana,
      kodeterimaId         AS idkode,
      kodeterimaKode          AS kode,
      kodeterimaNama          AS nama,
      renterimaRpstatusId     AS approval

   FROM unit_kerja_ref
   LEFT JOIN rencana_penerimaan ON renterimaUnitkerjaId = unitkerjaId AND renterimaThanggarId ='%s'
   LEFT JOIN kode_penerimaan_ref ON renterimaKodeterimaId = kodeterimaId
   LEFT JOIN (SELECT renterimaUnitkerjaId AS totalUnitkerjaId, SUM(renterimaTotalTerima) AS totalTotalTerima FROM rencana_penerimaan WHERE renterimaThanggarId ='%s' GROUP BY totalUnitkerjaId) AS total ON totalUnitkerjaId=unitkerjaId
   WHERE
   (kodeterimaKode = '%s' OR kodeterimaNama LIKE '%s') AND
   unitkerjaKodeSistem
   LIKE CONCAT((SELECT unitkerjaKodeSistem FROM unit_kerja_ref WHERE unitkerjaId=(SELECT userunitkerjaUnitkerjaId FROM user_unit_kerja WHERE userunitkerjaUserId = '%s')),'%%') %s
";

$sql['get_data_rencana_penerimaan_by_id']="
   SELECT
      thanggarId           AS tahun_anggaran_id,
      thanggarNama         AS tahun_anggaran_label,
      unitkerjaId          AS unitkerja_id,
      unitkerjaNama        AS unitkerja_label,
      kodeterimaId         AS penerimaan_id,
      kodeterimaKode          AS kode_penerimaan,
      kodeterimaNama          AS nama_penerimaan,
      renterimaTotalTerima    AS total,
      renterimaJmlJan         AS januari,
      renterimaJmlFeb         AS februari,
      renterimaJmlMar         AS maret,
      renterimaJmlApr         AS april,
      renterimaJmlMei         AS mei,
      renterimaJmlJun         AS juni,
      renterimaJmlJul         AS juli,
      renterimaJmlAgs         AS agustus,
      renterimaJmlSep         AS september,
      renterimaJmlOkt         AS oktober,
      renterimaJmlNov         AS november,
      renterimaJmlDes         AS desember,
      renterimaVolume         AS volume,
      renterimaTarif          AS tarif,
      renterimaJumlah         AS totalterima,
      renterimaPersenPagu     AS pagu,
      renterimaPagu           AS totalpagu,
      renterimaKeterangan     AS keterangan,
      renterimaRpstatusId     AS approval
   FROM
      rencana_penerimaan

      JOIN kode_penerimaan_ref ON (kodeterimaId = renterimaKodeterimaId)
      JOIN tahun_anggaran ON (thanggarId = renterimaThanggarId)
      JOIN unit_kerja_ref ON (unitkerjaId = renterimaUnitkerjaId)
   WHERE
      renterimaId=%s
";

//COMBO
$sql['get_combo_tahun_anggaran']="
   SELECT
      thanggarId     AS id,
      thanggarNama   AS name
   FROM
      tahun_anggaran
   ORDER BY thanggarNama
";
//aktif
$sql['get_tahun_anggaran_aktif']="
   SELECT
      thanggarId     AS id,
      thanggarNama   AS name
   FROM
      tahun_anggaran
   WHERE
      thanggarIsAktif='Y'
";
//aktif
$sql['get_tahun_anggaran']="
   SELECT
      thanggarId     AS id,
      thanggarNama   AS name
   FROM
      tahun_anggaran
   WHERE
      thanggarId='%s'
";

/*$sql['do_add_rencana_penerimaan']="
   INSERT INTO
      rencana_penerimaan(
         renterimaUnitkerjaId,
         renterimaKodeterimaId,
         renterimaTotalTerima,
         renterimaThanggarId,
         renterimaJmlJan,
         renterimaJmlFeb,
         renterimaJmlMar,
         renterimaJmlApr,
         renterimaJmlMei,
         renterimaJmlJun,
         renterimaJmlJul,
         renterimaJmlAgs,
         renterimaJmlSep,
         renterimaJmlOkt,
         renterimaJmlNov,
         renterimaJmlDes,
         renterimaVolume,
         renterimaTarif,
         renterimaJumlah,
         renterimaPersenPagu,
         renterimaPagu,
         renterimaKeterangan
      ) VALUES (
         %s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s
      )
";*/
/*belum keinsert
renterimaDeskripsi,
renterimaTipedistribusiId,
renterimaIsAktif,
*/
$sql['do_update_rencana_penerimaan']="
   UPDATE
      rencana_penerimaan
   SET
      renterimaRpstatusId = '%s'
   WHERE
      renterimaId= %s
";

/*$sql['do_delete_rencana_penerimaan_by_id'] =
   "DELETE from rencana_penerimaan
   WHERE
      renterimaId='%s'";

$sql['do_delete_rencana_penerimaan_by_array_id'] =
   "DELETE from rencana_penerimaan
   WHERE
      renterimaId IN ('%s')";*/

$sql['status_approval'] =
   "SELECT
      rpstatusId   AS id,
      rpstatusNama AS name
   FROM
      finansi_rp_ref_status_rp
   ";

/**
 * untuk mendapatkan jumlah sub unit
 * @since 11 Januari 2012
 */
$sql['get_total_sub_unit_kerja']="
SELECT
   count(unitkerjaId) as total
FROM unit_kerja_ref
WHERE unitkerjaParentId = %s
";
?>
