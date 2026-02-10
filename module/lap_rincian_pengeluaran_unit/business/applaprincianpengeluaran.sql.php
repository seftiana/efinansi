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

$sql['count']           = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']        = "
SELECT
   SQL_CALC_FOUND_ROWS
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   programId,
   programNomor AS programKode,
   programNama,
   rkaklKegiatanKode,
   IFNULL(rkaklKegiatanNama, '-') AS rkaklKegiatanNama,
   subprogId AS kegiatanId,
   subprogNomor AS kegiatanKode,
   subprogNama AS kegiatanNama,
   rkaklOutputKode,
   IFNULL(rkaklOutputNama, '-') AS rkaklOutputNama,
   kegrefId AS subKegiatanId,
   kegrefNomor AS subKegiatanKode,
   kegrefNama AS subKegiatanNama,
   rkaklSubKegiatanKode,
   IFNULL(rkaklSubKegiatanNama, '-') AS rkaklSubKegiatanNama,
   kegdetId,
   IF(kegdetDeskripsi IS NULL OR kegdetDeskripsi = '', '(-)', kegdetDeskripsi) AS deskripsi,
   paguBasId AS makId,
   paguBasKode AS makKode,
   paguBasKeterangan AS makNama,
   kompId,
   IFNULL(rncnpengeluaranKomponenKode, kompKode) AS kompKode,
   IFNULL(rncnpengeluaranKomponenNama, kompNama) AS kompNama,
   IFNULL(ikkKode, '-') AS ikkKode,
   IFNULL(ikkNama, '-') AS ikkNama,
   IFNULL(ikuKode, '-') AS ikuKode,
   IFNULL(ikuNama, '-') AS ikuNama,
   rncnpengeluaranKomponenNominal * IF(kompFormulaHasil=0,1,kompFormulaHasil) AS usulanNominal,
   rncnpengeluaranSatuan AS usulanSatuan,
   rncnpengeluaranSatuan * rncnpengeluaranKomponenNominal * IF(kompFormulaHasil=0,1,kompFormulaHasil) AS usulanJumlah,
   rncnpengeluaranKomponenNominalAprove * IF(kompFormulaHasil=0,1,kompFormulaHasil) AS setujuNominal,
   rncnpengeluaranSatuanAprove AS setujuSatuan,
   rncnpengeluaranNamaSatuan AS satuanNama,
   CONCAT(rncnpengeluaranSatuanAprove, ' ', rncnpengeluaranNamaSatuan) AS volume,
   rncnpengeluaranSatuanAprove * rncnpengeluaranKomponenNominalAprove * IF(kompFormulaHasil=0,1,kompFormulaHasil) AS setujuJumlah,
   rncnpengeluaranKomponenDeskripsi AS komponenDeskripsi,
   rncnpengeluaranIsAprove AS approval
FROM
   rencana_pengeluaran
   JOIN kegiatan_detail
      ON kegdetId = rncnpengeluaranKegdetId
   JOIN kegiatan
      ON kegdetKegId = kegId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   JOIN unit_kerja_ref
      ON unitkerjaId = kegUnitkerjaId
   JOIN (SELECT unitkerjaId AS id,
      CASE
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN unitkerjaKodeSistem
      END AS `code`
      FROM unit_kerja_ref
   ) AS tmp_unit
      ON tmp_unit.id = unitkerjaId
   LEFT JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   LEFT JOIN sub_program
      ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref
      ON subprogProgramId = programId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   LEFT JOIN finansi_ref_rkakl_subkegiatan
      ON rkaklSubKegiatanId = kegrefRkaklSubKegiatanId
   LEFT JOIN finansi_ref_rkakl_output
      ON rkaklOutputId = subprogRKAKLOutputId
   LEFT JOIN finansi_ref_rkakl_kegiatan
      ON rkaklKegiatanId = programRKAKLKegiatanId
   LEFT JOIN finansi_pa_ref_ikk
      ON kegdetIkkId = ikkId
   LEFT JOIN finansi_pa_ref_iku
      ON kegdetIkuId = ikuId
   LEFT JOIN komponen
      ON kompKode = rncnpengeluaranKomponenKode
   LEFT JOIN finansi_ref_pagu_bas
      ON (paguBasId = rncnpengeluaranMakId)
      OR (paguBasId = kompMakId)
WHERE 1 = 1
AND UPPER(rncnpengeluaranIsAprove) = 'YA'
AND kegThanggarId = %s
AND (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
ORDER BY programId,
subprogId,
kegrefId,
kegdetId,
paguBasId,
SUBSTRING_INDEX(tmp_unit.code, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 5), '.', -1)+0,
kompKode
LIMIT %s, %s
";

$sql['get_unit_kerja']="
   SELECT
     unitkerjaId AS unit_kerja_id,
     unitkerjaKode AS unit_kerja_kode,
     unitkerjaNama AS unit_kerja_nama,
     unitkerjaParentId AS unit_kerja_parent_id,
     unitkerjaParentId AS is_unit_kerja
   FROM
      unit_kerja_ref
   WHERE
      unitkerjaId=%s;
";
?>