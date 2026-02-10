<?php

/**
 * @package SQL-FILE
 */
$sql['get_nominal_belanja_per_bulan'] = "
SELECT
  CONCAT(YEAR(kegdetWaktuMulaiPelaksanaan),'-',MONTH(kegdetWaktuMulaiPelaksanaan)) AS bulan,
  Pinjaman AS pinjaman,
  kegProgramId AS programId,
  subprogId AS kegiatanId,
  kegrefId AS subKegiatanId,
  `kegdetId` AS kegiatanDetailId,
  rencana_pengeluaran2.`rncnpengeluaranKomponenKode` AS detailBelanjaKode,
  SUM(IF(rencana_pengeluaran2.`rncnpengeluaranIsAprove` ='Ya',
  (rencana_pengeluaran2.rncnpengeluaranKomponenTotalAprove * 
    IF(kompFormulaHasil = '0' OR kompFormulaHasil IS NULL, 1, kompFormulaHasil)
  ),
  (rencana_pengeluaran2.rncnpengeluaranSatuan * 
    rencana_pengeluaran2.rncnpengeluaranKomponenNominal * 
    IF(kompFormulaHasil = '0' OR kompFormulaHasil IS NULL, 1, kompFormulaHasil)
  )
  )) AS detailBelanjaNominal 
  
FROM
  rencana_pengeluaran2
  JOIN bulan_angsuran_detail 
    ON bulan_angsuran_detail.`kegdetId` = rencana_pengeluaran2.`rncnpengeluaranKegdetId` 
  JOIN bulan_angsuran 
    ON kegId = kegdetKegId 
  JOIN tahun_anggaran 
    ON thanggarId = kegThanggarId 
  JOIN unit_kerja_ref 
    ON unitkerjaId = kegUnitkerjaId 
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
   LEFT JOIN komponen1 
        ON komponen1.kompKode = rncnpengeluaranKomponenKode 
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
    AND (rencana_pengeluaran2.`rncnpengeluaranIsAprove` = '%s' OR %s)
GROUP BY bulan, `rncnpengeluaranKomponenKode`
ORDER BY programId,subprogId,kegrefId,`rncnpengeluaranKomponenKode`
";

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

$sql['get_range_tanggal']  = "
SELECT
   MIN(tahun_anggaran.`thanggarBuka`) AS tanggalAwal,
   MAX(tahun_anggaran.`thanggarTutup`) AS tanggalAkhir
FROM tahun_anggaran
WHERE
   thanggarId = '%s'
";

$sql['count']              = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_anggaran_belanja_bulanan'] = "
SELECT
   SQL_CALC_FOUND_ROWS kegdetId AS id,
   kegId,
   komponen1.Pinjaman AS pinjaman,
   SisaPinjaman AS sisapinjaman,
   Bayar AS bayar,
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
   bulan_angsuran_detail.`kegdetId` AS kegiatanDetailId,   
  rencana_pengeluaran2.`rncnpengeluaranKomponenKode` AS detailBelanjaKode,
   rencana_pengeluaran2.`rncnpengeluaranKomponenNama` AS detailBelanjaNama,   
   thanggarIsAktif AS taAktif,
   thanggarIsOpen AS taOpen,
   rencana_pengeluaran2.`rncnpengeluaranKomponenKode` AS detailBelanjaKode, 
   
   IF(kegdetDeskripsi IS NULL OR kegdetDeskripsi = '','-',kegdetDeskripsi) AS deskripsi,
  -- SUM(rencana_pengeluaran2.rncnpengeluaranKomponenTotalAprove * IF(
  --         kompFormulaHasil = '0' 
  --         OR kompFormulaHasil IS NULL,
  --         1,
  --         kompFormulaHasil
  --       )) AS detailBelanjaNominal,
  SUM(IF(rencana_pengeluaran2.`rncnpengeluaranIsAprove` ='Ya',
  (rencana_pengeluaran2.rncnpengeluaranKomponenTotalAprove * 
    IF(kompFormulaHasil = '0' OR kompFormulaHasil IS NULL, 1, kompFormulaHasil)
  ),
  (rencana_pengeluaran2.rncnpengeluaranSatuan * 
    rencana_pengeluaran2.rncnpengeluaranKomponenNominal * 
    IF(kompFormulaHasil = '0' OR kompFormulaHasil IS NULL, 1, kompFormulaHasil)
  )
  )) AS detailBelanjaNominal

FROM
   rencana_pengeluaran2
   JOIN bulan_angsuran_detail 
      ON bulan_angsuran_detail.`kegdetId` =  rencana_pengeluaran2.`rncnpengeluaranKegdetId`
   JOIN bulan_angsuran
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
   LEFT JOIN view_sisa_pinjaman
    ON view_sisa_pinjaman.kompKode = rncnpengeluaranKomponenKode
  LEFT JOIN komponen1 
        ON komponen1.kompKode = rncnpengeluaranKomponenKode   
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
   AND (rencana_pengeluaran2.`rncnpengeluaranIsAprove` = '%s' OR %s)
GROUP BY programId,
subprogId, kegrefId, rencana_pengeluaran2.`rncnpengeluaranKomponenKode`
ORDER BY programId, 
subprogId, kegrefId, rencana_pengeluaran2.`rncnpengeluaranKomponenKode`
LIMIT %s, %s
";
?>