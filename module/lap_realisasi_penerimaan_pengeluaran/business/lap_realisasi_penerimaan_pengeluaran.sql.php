<?php

$sql['get_total_pengeluaran_per_bulan'] ="
SELECT * FROM ((
SELECT 
  0 AS sectionId,
  CONCAT(YEAR(`transTanggalEntri`),'-',MONTH(`transTanggalEntri`)) AS bulan,  
  SUM(rpen.renterimaTotalTerima) AS nominalTotalUsulan,
  SUM(rpen.renterimaTotalTerima) AS nominalUsulan,
  SUM(real_pen.realterimaTotalTerima) AS nominalRealisasi
FROM
  rencana_penerimaan rpen
  JOIN unit_kerja_ref uk
    ON rpen.renterimaUnitkerjaId = uk.unitkerjaId 
  JOIN realisasi_penerimaan real_pen
    ON real_pen.realrenterimaId = rpen.renterimaId 
  JOIN kode_penerimaan_ref  kref
    ON rpen.renterimaKodeterimaId = kref.kodeterimaId
  JOIN transaksi tr
    ON tr.`transId` = real_pen.`realterimaTransId`
  LEFT JOIN   kode_penerimaan_ref kref_h
    ON kref_h.`kodeterimaId` = kref.`kodeterimaParentId`  
WHERE 
  rpen.renterimaRpstatusId = 2
  AND
  rpen.`renterimaThanggarId` = '%s' 
  AND 
  (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))
    FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') 
        FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
  AND
  tr.`transTanggalEntri` BETWEEN '%s' AND '%s'
GROUP BY  bulan
)
UNION
(SELECT 
   1 AS sectionId,
   CONCAT(YEAR( pengrealTanggal),'-',MONTH(pengrealTanggal)) AS bulan,
   SUM(pengrealNominal) AS nominalTotalUsulan,
   SUM(pengrealNominal) AS nominalUsulan,
   SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL, pengrealNominalAprove, 0)) AS nominalRealisasi   
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
   LEFT JOIN(
   SELECT
      pengrealdetPengRealId AS id,
      COUNT(sppDetId) AS `count`,
      sppId
   FROM pengajuan_realisasi_detil
   LEFT JOIN finansi_pa_spp_det
      ON sppDetRealdetId = pengrealdetId
   LEFT JOIN finansi_pa_spp
      ON sppId = sppDetSppId
   GROUP BY pengrealdetPengRealId
   ) AS spp ON spp.id = pengrealId
   JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
   JOIN tahun_anggaran
      ON thanggarId = programThanggarId
WHERE 1 = 1
AND UPPER(pengrealIsApprove) = 'YA'
AND programThanggarId = %s
AND (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
AND pengrealTanggal BETWEEN '%s' AND '%s'
GROUP BY bulan
)) AS tp
";

$sql['get_penerimaan_pengeluaran_per_bulan'] ="
SELECT * FROM (
(
SELECT 
  0 AS sectionId,
  CONCAT(YEAR(`transTanggalEntri`),'-',MONTH(`transTanggalEntri`)) AS bulan,  
  IFNULL(kref_h.`kodeterimaId`,kref.`kodeterimaId`) AS idKode,
  SUM(rpen.renterimaTotalTerima) AS nominalTotalUsulan,
  SUM(rpen.renterimaTotalTerima) AS nominalUsulan,
  SUM(real_pen.realterimaTotalTerima) AS nominalRealisasi
FROM
  rencana_penerimaan rpen
  JOIN unit_kerja_ref uk
    ON rpen.renterimaUnitkerjaId = uk.unitkerjaId 
  JOIN realisasi_penerimaan real_pen
    ON real_pen.realrenterimaId = rpen.renterimaId 
  JOIN kode_penerimaan_ref  kref
    ON rpen.renterimaKodeterimaId = kref.kodeterimaId
  JOIN transaksi tr
    ON tr.`transId` = real_pen.`realterimaTransId`
  LEFT JOIN   kode_penerimaan_ref kref_h
    ON kref_h.`kodeterimaId` = kref.`kodeterimaParentId`  
WHERE 
  rpen.renterimaRpstatusId = 2
  AND
  rpen.`renterimaThanggarId` = '%s' 
  AND 
  (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))
    FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') 
        FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
  AND
  tr.`transTanggalEntri` BETWEEN '%s' AND '%s'
GROUP BY  bulan,kref_h.kodeterimaKode 
) 
UNION
(
SELECT 
   1 AS sectionId,
   CONCAT(YEAR( pengrealTanggal),'-',MONTH(pengrealTanggal)) AS bulan,
   unitkerjaId AS idKode,
   SUM(pengrealNominal) AS nominalTotalUsulan,
   SUM(pengrealNominal) AS nominalUsulan,
   SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL, pengrealNominalAprove, 0)) AS nominalRealisasi   
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
   LEFT JOIN(
   SELECT
      pengrealdetPengRealId AS id,
      COUNT(sppDetId) AS `count`,
      sppId
   FROM pengajuan_realisasi_detil
   LEFT JOIN finansi_pa_spp_det
      ON sppDetRealdetId = pengrealdetId
   LEFT JOIN finansi_pa_spp
      ON sppId = sppDetSppId
   GROUP BY pengrealdetPengRealId
   ) AS spp ON spp.id = pengrealId
   JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
   JOIN tahun_anggaran
      ON thanggarId = programThanggarId
WHERE 1 = 1
AND UPPER(pengrealIsApprove) = 'YA'
AND programThanggarId = %s
AND (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
AND pengrealTanggal BETWEEN '%s' AND '%s'
GROUP BY bulan,unitkerjaId 
)) AS perbulan
";

$sql['get_data_penerimaan_pengeluaran'] = "
SELECT SQL_CALC_FOUND_ROWS
* FROM ((
SELECT 
  0 AS sectionId,
  'PENERIMAAN' AS section,
  IFNULL(kref_h.`kodeterimaId`,kref.`kodeterimaId`) AS idKode,
  IFNULL(kref_h.kodeterimaKode,kref.kodeterimaKode) AS kode,
  IFNULL(kref_h.kodeterimaNama,kref.kodeterimaNama) AS nama,
  SUM(rpen.renterimaTotalTerima) AS nominalTotalUsulan,
  SUM(rpen.`renterimaJmlJan`) AS nominalUsulanJanuari,
  SUM(rpen.`renterimaJmlFeb`) AS nominalUsulanFebruari,
  SUM(rpen.`renterimaJmlMar`) AS nominalUsulanMaret,
  SUM(rpen.`renterimaJmlApr`) AS nominalUsulanApril,
  SUM(rpen.`renterimaJmlMei`) AS nominalUsulanMei,
  SUM(rpen.`renterimaJmlJun`) AS nominalUsulanJuni,
  SUM(rpen.`renterimaJmlJul`) AS nominalUsulanJuli,
  SUM(rpen.`renterimaJmlAgs`) AS nominalUsulanAgustus,
  SUM(rpen.`renterimaJmlSep`) AS nominalUsulanSeptember,
  SUM(rpen.`renterimaJmlOkt`) AS nominalUsulanOktober,
  SUM(rpen.`renterimaJmlNov`) AS nominalUsulanNovember,
  SUM(rpen.`renterimaJmlDes`) AS nominalUsulanDesember,
  SUM(real_pen.realterimaJmlJan) AS nominalRealisasiJanuari,
  SUM(real_pen.realterimaJmlFeb) AS nominalRealisasiFebruari,
  SUM(real_pen.realterimaJmlMar) AS nominalRealisasiMaret,
  SUM(real_pen.realterimaJmlApr) AS nominalRealisasiApril,
  SUM(real_pen.realterimaJmlMei) AS nominalRealisasiMei,
  SUM(real_pen.realterimaJmlJun) AS nominalRealisasiJuni,
  SUM(real_pen.realterimaJmlJul) AS nominalRealisasiJuli,
  SUM(real_pen.realterimaJmlAgt) AS nominalRealisasiAgustus,
  SUM(real_pen.realterimaJmlSep) AS nominalRealisasiSeptember,
  SUM(real_pen.realterimaJmlOkt) AS nominalRealisasiOktober,
  SUM(real_pen.realterimaJmlNov) AS nominalRealisasiNovember,
  SUM(real_pen.realterimaJmlDes) AS nominalRealisasiDesember,
  SUM(real_pen.realterimaTotalTerima) AS nominalTotalRealisasi
FROM
  rencana_penerimaan rpen
  JOIN unit_kerja_ref uk
    ON rpen.renterimaUnitkerjaId = uk.unitkerjaId 
  JOIN realisasi_penerimaan real_pen
    ON real_pen.realrenterimaId = rpen.renterimaId 
  JOIN kode_penerimaan_ref  kref
    ON rpen.renterimaKodeterimaId = kref.kodeterimaId
  JOIN transaksi tr
    ON tr.`transId` = real_pen.`realterimaTransId`
  LEFT JOIN   kode_penerimaan_ref kref_h
    ON kref_h.`kodeterimaId` = kref.`kodeterimaParentId`  
WHERE 
  rpen.renterimaRpstatusId = 2
  AND
  rpen.`renterimaThanggarId` = '%s' 
  AND 
  (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))
    FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') 
        FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
  AND
  tr.`transTanggalEntri` BETWEEN '%s' AND '%s'
GROUP BY kref_h.kodeterimaKode 
ORDER BY kref_h.kodeterimaKode 
)
UNION (
SELECT 
   1 AS sectionId,
   'PENGELUARAN BIAYA OPERASIONAL' AS section,
   unitkerjaId AS idKode,
   unitkerjaKode AS kode,
   unitkerjaNama AS nama,
   SUM(pengrealNominal) AS nominalTotalUsulan,
   SUM(IF(MONTH(pengrealTanggal) = 1,pengrealNominal,0)) AS nominalUsulanJanuari,
   SUM(IF(MONTH(pengrealTanggal) = 2,pengrealNominal,0)) AS nominalUsulanFebruari,
   SUM(IF(MONTH(pengrealTanggal) = 3,pengrealNominal,0)) AS nominalUsulanMaret,
   SUM(IF(MONTH(pengrealTanggal) = 4,pengrealNominal,0)) AS nominalUsulanApril,
   SUM(IF(MONTH(pengrealTanggal) = 5,pengrealNominal,0)) AS nominalUsulanMei,
   SUM(IF(MONTH(pengrealTanggal) = 6,pengrealNominal,0)) AS nominalUsulanJuni,
   SUM(IF(MONTH(pengrealTanggal) = 7,pengrealNominal,0)) AS nominalUsulanJuli,
   SUM(IF(MONTH(pengrealTanggal) = 8,pengrealNominal,0)) AS nominalUsulanAgustus,
   SUM(IF(MONTH(pengrealTanggal) = 9,pengrealNominal,0)) AS nominalUsulanSeptember,
   SUM(IF(MONTH(pengrealTanggal) = 10,pengrealNominal,0)) AS nominalUsulanOktober,
   SUM(IF(MONTH(pengrealTanggal) = 11,pengrealNominal,0)) AS nominalUsulanNovember,
   SUM(IF(MONTH(pengrealTanggal) = 12,pengrealNominal,0)) AS nominalUsulanDesember,
   SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =1),
    pengrealNominalAprove,0)) AS nominalRealisasiJanuari,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =2),
    pengrealNominalAprove,0)) AS nominalRealisasiFebruari,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =3),
    pengrealNominalAprove,0)) AS nominalRealisasiMaret,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =4),
    pengrealNominalAprove,0)) AS nominalRealisasiApril,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =5),
    pengrealNominalAprove,0)) AS nominalRealisasiMei,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =6),
    pengrealNominalAprove,0)) AS nominalRealisasiJuni,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =7),
    pengrealNominalAprove,0)) AS nominalRealisasiJuli,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =8),
    pengrealNominalAprove,0)) AS nominalRealisasiAgustus,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =9),
    pengrealNominalAprove,0)) AS nominalRealisasiSeptember,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =10),
    pengrealNominalAprove,0)) AS nominalRealisasiOktober,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =11),
    pengrealNominalAprove,0)) AS nominalRealisasiNovember,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =12),
    pengrealNominalAprove,0)) AS nominalRealisasiDesember,   
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL, pengrealNominalAprove, 0)) AS nominalTotalRealisasi   
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
   LEFT JOIN(
   SELECT
      pengrealdetPengRealId AS id,
      COUNT(sppDetId) AS `count`,
      sppId
   FROM pengajuan_realisasi_detil
   LEFT JOIN finansi_pa_spp_det
      ON sppDetRealdetId = pengrealdetId
   LEFT JOIN finansi_pa_spp
      ON sppId = sppDetSppId
   GROUP BY pengrealdetPengRealId
   ) AS spp ON spp.id = pengrealId
   JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
   JOIN (SELECT unitkerjaId AS id,
   CASE
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN unitkerjaKodeSistem
   END AS `code`
   FROM unit_kerja_ref) AS tmp_unit
      ON tmp_unit.id = unitkerjaId
   JOIN tahun_anggaran
      ON thanggarId = programThanggarId
WHERE 1 = 1
AND UPPER(pengrealIsApprove) = 'YA'
AND programThanggarId = %s
AND (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
AND pengrealTanggal BETWEEN '%s' AND '%s'
GROUP BY unitkerjaId 
ORDER BY programId, subprogId,
SUBSTRING_INDEX(tmp_unit.code, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 5), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 6), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 7), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 8), '.', -1)+0,
kegrefId,
pengrealTanggal DESC
)) AS pp
LIMIT %s,%s
";

$sql['get_total_penerimaan'] ="
SELECT 
  'PENERIMAAN' AS section,
  SUM(rpen.renterimaTotalTerima) AS nominalTotalUsulan,
  SUM(rpen.`renterimaJmlJan`) AS nominalUsulanJanuari,
  SUM(rpen.`renterimaJmlFeb`) AS nominalUsulanFebruari,
  SUM(rpen.`renterimaJmlMar`) AS nominalUsulanMaret,
  SUM(rpen.`renterimaJmlApr`) AS nominalUsulanApril,
  SUM(rpen.`renterimaJmlMei`) AS nominalUsulanMei,
  SUM(rpen.`renterimaJmlJun`) AS nominalUsulanJuni,
  SUM(rpen.`renterimaJmlJul`) AS nominalUsulanJuli,
  SUM(rpen.`renterimaJmlAgs`) AS nominalUsulanAgustus,
  SUM(rpen.`renterimaJmlSep`) AS nominalUsulanSeptember,
  SUM(rpen.`renterimaJmlOkt`) AS nominalUsulanOktober,
  SUM(rpen.`renterimaJmlNov`) AS nominalUsulanNovember,
  SUM(rpen.`renterimaJmlDes`) AS nominalUsulanDesember,
  SUM(real_pen.realterimaJmlJan) AS nominalRealisasiJanuari,
  SUM(real_pen.realterimaJmlFeb) AS nominalRealisasiFebruari,
  SUM(real_pen.realterimaJmlMar) AS nominalRealisasiMaret,
  SUM(real_pen.realterimaJmlApr) AS nominalRealisasiApril,
  SUM(real_pen.realterimaJmlMei) AS nominalRealisasiMei,
  SUM(real_pen.realterimaJmlJun) AS nominalRealisasiJuni,
  SUM(real_pen.realterimaJmlJul) AS nominalRealisasiJuli,
  SUM(real_pen.realterimaJmlAgt) AS nominalRealisasiAgustus,
  SUM(real_pen.realterimaJmlSep) AS nominalRealisasiSeptember,
  SUM(real_pen.realterimaJmlOkt) AS nominalRealisasiOktober,
  SUM(real_pen.realterimaJmlNov) AS nominalRealisasiNovember,
  SUM(real_pen.realterimaJmlDes) AS nominalRealisasiDesember,
  SUM(real_pen.realterimaTotalTerima) AS nominalTotalRealisasi
FROM
  rencana_penerimaan rpen
  JOIN unit_kerja_ref uk
    ON rpen.renterimaUnitkerjaId = uk.unitkerjaId 
    AND rpen.renterimaThanggarId = '1' 
  JOIN realisasi_penerimaan real_pen
    ON real_pen.realrenterimaId = rpen.renterimaId 
  JOIN kode_penerimaan_ref  kref
    ON rpen.renterimaKodeterimaId = kref.kodeterimaId
  JOIN transaksi tr
    ON tr.`transId` = real_pen.`realterimaTransId`
WHERE 
  rpen.renterimaRpstatusId = 2
  AND
  rpen.`renterimaThanggarId` = '%s' 
  AND 
  (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))
    FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') 
        FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
  AND
  tr.`transTanggalEntri` BETWEEN '%s' AND '%s'
";

$sql['get_range_tanggal']  = "
SELECT
   MIN(tahun_anggaran.`thanggarBuka`) AS tanggalAwal,
   MAX(tahun_anggaran.`thanggarTutup`) AS tanggalAkhir
FROM tahun_anggaran
";

$sql['get_periode_tahun']  = "
SELECT
   thanggarId AS `id`,
   thanggarNama AS `name`
FROM tahun_anggaran
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
";

$sql['count']        = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_total_pengeluaran'] = "
SELECT 
   'PENGELUARAN' AS section,
   SUM(pengrealNominal) AS nominalTotalUsulan,
   SUM(IF(MONTH(pengrealTanggal) = 1,pengrealNominal,0)) AS nominalUsulanJanuari,
   SUM(IF(MONTH(pengrealTanggal) = 2,pengrealNominal,0)) AS nominalUsulanFebruari,
   SUM(IF(MONTH(pengrealTanggal) = 3,pengrealNominal,0)) AS nominalUsulanMaret,
   SUM(IF(MONTH(pengrealTanggal) = 4,pengrealNominal,0)) AS nominalUsulanApril,
   SUM(IF(MONTH(pengrealTanggal) = 5,pengrealNominal,0)) AS nominalUsulanMei,
   SUM(IF(MONTH(pengrealTanggal) = 6,pengrealNominal,0)) AS nominalUsulanJuni,
   SUM(IF(MONTH(pengrealTanggal) = 7,pengrealNominal,0)) AS nominalUsulanJuli,
   SUM(IF(MONTH(pengrealTanggal) = 8,pengrealNominal,0)) AS nominalUsulanAgustus,
   SUM(IF(MONTH(pengrealTanggal) = 9,pengrealNominal,0)) AS nominalUsulanSeptember,
   SUM(IF(MONTH(pengrealTanggal) = 10,pengrealNominal,0)) AS nominalUsulanOktober,
   SUM(IF(MONTH(pengrealTanggal) = 11,pengrealNominal,0)) AS nominalUsulanNovember,
   SUM(IF(MONTH(pengrealTanggal) = 12,pengrealNominal,0)) AS nominalUsulanDesember,
   SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =1),
    pengrealNominalAprove,0)) AS nominalRealisasiJanuari,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =2),
    pengrealNominalAprove,0)) AS nominalRealisasiFebruari,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =3),
    pengrealNominalAprove,0)) AS nominalRealisasiMaret,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =4),
    pengrealNominalAprove,0)) AS nominalRealisasiApril,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =5),
    pengrealNominalAprove,0)) AS nominalRealisasiMei,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =6),
    pengrealNominalAprove,0)) AS nominalRealisasiJuni,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =7),
    pengrealNominalAprove,0)) AS nominalRealisasiJuli,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =8),
    pengrealNominalAprove,0)) AS nominalRealisasiAgustus,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =9),
    pengrealNominalAprove,0)) AS nominalRealisasiSeptember,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =10),
    pengrealNominalAprove,0)) AS nominalRealisasiOktober,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =11),
    pengrealNominalAprove,0)) AS nominalRealisasiNovember,
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL AND (MONTH(pengrealTanggal) =12),
    pengrealNominalAprove,0)) AS nominalRealisasiDesember,   
    SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL, pengrealNominalAprove, 0)) AS nominalTotalRealisasi   
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
   LEFT JOIN(
   SELECT
      pengrealdetPengRealId AS id,
      COUNT(sppDetId) AS `count`,
      sppId
   FROM pengajuan_realisasi_detil
   LEFT JOIN finansi_pa_spp_det
      ON sppDetRealdetId = pengrealdetId
   LEFT JOIN finansi_pa_spp
      ON sppId = sppDetSppId
   GROUP BY pengrealdetPengRealId
   ) AS spp ON spp.id = pengrealId
   JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
   JOIN (SELECT unitkerjaId AS id,
   CASE
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN unitkerjaKodeSistem
   END AS `code`
   FROM unit_kerja_ref) AS tmp_unit
      ON tmp_unit.id = unitkerjaId
   JOIN tahun_anggaran
      ON thanggarId = programThanggarId
WHERE 1 = 1
AND UPPER(pengrealIsApprove) = 'YA'
AND programThanggarId = %s
AND (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
AND pengrealTanggal BETWEEN '%s' AND '%s'
";
?>