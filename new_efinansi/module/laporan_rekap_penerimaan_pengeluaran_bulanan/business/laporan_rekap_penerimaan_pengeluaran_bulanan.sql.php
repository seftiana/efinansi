<?php
/**
 * @package SQL-FILE
 */
$sql['get_periode_tahun']     = "
SELECT
   thanggarId AS id,
   thanggarNama AS `name`
FROM tahun_anggaran
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
ORDER BY thanggarNama
";

$sql['count']              = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_realisasi_pengeluaran'] = "
SELECT   
  thanggarId AS taId,
  unitkerjaId AS unitId,
  programId AS programId,
  subprogId AS kegiatanId,
  kegrefId AS subKegiatanId,
  MONTH(pengrealTanggal) AS bulan,
  pengrealNominal AS nominalUsulan,
  IF(
    UPPER(pengrealIsApprove) = 'YA' 
    AND pengrealIsApprove IS NOT NULL,
    pengrealNominalAprove,
    0
  ) AS nominalSetuju
FROM
  pengajuan_realisasi 
  JOIN kegiatan_detail 
    ON pengrealKegdetId = kegdetId 
  LEFT JOIN kegiatan_ref 
    ON kegdetKegrefId = kegrefId 
  LEFT JOIN sub_program 
    ON kegrefSubprogId = subprogId 
  LEFT JOIN program_ref 
    ON subprogProgramId = programId 
  LEFT JOIN jenis_kegiatan_ref 
    ON subprogJeniskegId = jeniskegId 
  LEFT JOIN kegiatan 
    ON kegdetKegId = kegId 
  JOIN unit_kerja_ref 
    ON kegUnitkerjaId = unitkerjaId 
  JOIN tahun_anggaran 
    ON thanggarId = programThanggarId 
WHERE 1 = 1 
  AND UPPER(pengrealIsApprove) = 'YA' 
  AND programThanggarId = '%s' 
  AND (programId = %s OR 1 = %s)
  AND (
        SUBSTR(`unitkerjaKodeSistem`,1, (
        SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) 
      = 
     (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) 
     OR unitkerjaId = %s
 )
";

$sql['get_data_penerimaan_pengeluaran_bulanan'] = "
SELECT
   SQL_CALC_FOUND_ROWS kegdetId AS id,
   kegId,
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   kegProgramId AS programId,
   programNomor AS programKode,
   programNama AS programNama,
   subprogId AS kegiatanId,
   subprogNomor AS kegiatanKode,
   subprogNama AS kegiatanNama,
   kegrefId AS subKegiatanId,
   kegrefNomor AS subKegiatanKode,
   kegrefNama AS subKegiatanNama,
   UPPER(IFNULL(jeniskegNama, 'rutin')) AS jenisKegiatan,
   UPPER(kegdetIsAprove) AS `approval`,
   kegdetPrioritasId AS prioritasId,
   prioritasNama AS prioritas,
   kegLatarBelakang AS latarBelakang,
   thanggarIsAktif AS taAktif,
   thanggarIsOpen AS taOpen,
   IF(kegdetDeskripsi IS NULL OR kegdetDeskripsi = '','-',kegdetDeskripsi) AS deskripsi,
   SUM(CONVERT(IFNULL(rp.nominal, 0), DECIMAL (20, 2))) AS nominal,
   IF(rp.id IS NULL, 'NO', 'YES') AS rkat,
   EXTRACT(MONTH FROM kegdetWaktuMulaiPelaksanaan) AS bulan,
   EXTRACT(YEAR FROM kegdetWaktuMulaiPelaksanaan) AS tahun
FROM
   kegiatan_detail   
   JOIN kegiatan
      ON kegId = kegdetKegId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   JOIN unit_kerja_ref
      ON unitkerjaId = kegUnitkerjaId
   JOIN (
      SELECT unitkerjaId AS id,
      CASE
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN unitkerjaKodeSistem
      END AS kode
      FROM unit_kerja_ref
   ) AS tmp_unit ON tmp_unit.id = unitkerjaId
   JOIN program_ref
      ON programId = kegProgramId
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN sub_program
      ON subprogId = kegrefSubProgId
   LEFT JOIN jenis_kegiatan_ref
      ON jeniskegId = subprogJeniskegId
   LEFT JOIN prioritas_ref
      ON kegdetPrioritasId = prioritasId
   LEFT JOIN
      (SELECT
         rncnpengeluaranKegdetId AS id,
         SUM(rncnpengeluaranSatuan * rncnpengeluaranKomponenNominal * IF(kompFormulaHasil = '0' OR kompFormulaHasil IS NULL, 1, kompFormulaHasil)) AS nominal
      FROM
         rencana_pengeluaran
         LEFT JOIN komponen
            ON kompKode = rncnpengeluaranKomponenKode
      GROUP BY rncnpengeluaranKegdetId) AS rp
      ON rp.id = kegdetId
WHERE 1 = 1
   AND kegThanggarId = %s
   AND (programId = %s OR 1 = %s)
   AND kegUnitkerjaId IN
   (SELECT
      unitkerjaId
   FROM
      unit_kerja_ref
   WHERE 1 = 1
      AND SUBSTR(`unitkerjaKodeSistem`,1,
         (SELECT
            LENGTH(CONCAT(`unitkerjaKodeSistem`, '.'))
         FROM
            unit_kerja_ref
         WHERE `unitkerjaId` = '%s')
      ) =
      (SELECT
         CONCAT(`unitkerjaKodeSistem`, '.')
      FROM
         unit_kerja_ref
      WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
   AND (EXTRACT(MONTH FROM kegdetWaktuMulaiPelaksanaan) = EXTRACT(MONTH FROM %s) OR 1 = 1)
   AND (EXTRACT(YEAR FROM kegdetWaktuMulaiPelaksanaan) = EXTRACT(YEAR FROM %s) OR 1 = 1)
GROUP BY programId,
subprogId, kegrefId, kegUnitkerjaId,
EXTRACT(MONTH FROM kegdetWaktuMulaiPelaksanaan),
EXTRACT(YEAR FROM kegdetWaktuMulaiPelaksanaan)
ORDER BY programId, subprogId, kegrefId, EXTRACT(MONTH FROM kegdetWaktuMulaiPelaksanaan),
EXTRACT(YEAR FROM kegdetWaktuMulaiPelaksanaan)
LIMIT %s, %s
";
?>