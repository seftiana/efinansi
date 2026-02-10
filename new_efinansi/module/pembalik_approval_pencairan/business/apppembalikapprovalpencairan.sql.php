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

$sql['get_data_program'] = "
SELECT
   programThanggarId AS taId,
   programId AS id,
   programNama AS `name`
FROM
  program_ref
WHERE 1 = 1
   AND (programThanggarId = '%s' OR 1 = %s)
ORDER BY
  programNama ASC
";

$sql['get_combo_jenis_kegiatan']="
   SELECT
      jeniskegId as id,
      jeniskegNama as name
   FROM
      jenis_kegiatan_ref
   ORDER BY jeniskegId
";

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_realisasi_pencairan']   = "
SELECT SQL_CALC_FOUND_ROWS
   pengrealId AS id,
   pengrealTanggal AS tanggal,
   MONTH(kegdetWaktuMulaiPelaksanaan) AS bulanAnggaran,
   pengrealNomorPengajuan AS nomorPengajuan,
   IF(pengrealKeterangan IS NULL OR pengrealKeterangan = '', '-', pengrealKeterangan) AS keterangan,
   jeniskegId AS jenisKegiatanId,
   jeniskegNama AS jenisKegiatanNama,
   thanggarId AS taId,
   thanggarNama AS taNama,
   thanggarIsAktif AS taStatus,
   thanggarIsOpen AS taOpen,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   programId AS programId,
   programNomor AS programKode,
   programNama AS programNama,
   subprogId AS kegiatanId,
   IFNULL(subprogNomor, '') AS kegiatanKode,
   CONCAT(IFNULL(subprogNama, ''), IF(jeniskegNama IS NULL, '' , CONCAT(' (',jeniskegNama,')'))) AS kegiatanNama,
   kegrefId AS subKegiatanId,
   IFNULL(kegrefNomor, '') AS subKegiatanKode,
   kegrefNama AS subKegiatanNama,
   pengrealNominal AS nominalUsulan,
   IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL, spp.nominalSetuju, 0) AS nominalSetuju,
   UPPER(IFNULL(pengrealIsApprove, 'Belum')) AS `status`,
   spp.count AS spp,
   spp.sppId,
   spp.countSpm AS spm,
   spp.spmId,
   spp.sppu AS sppu
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
      COUNT(spmDetId) AS countSpm,
      sppId,
      spmId,
      COUNT(DISTINCT sppuDetId) AS sppu,
      SUM(pengrealdetNominalPencairan) AS nominal,
      SUM(pengrealdetNominalApprove) AS nominalSetuju
   FROM pengajuan_realisasi_detil
   LEFT JOIN finansi_pa_spp_det
      ON sppDetRealdetId = pengrealdetId
   LEFT JOIN finansi_pa_spp
      ON sppId = sppDetSppId
   LEFT JOIN finansi_pa_spm_det
      ON spmDetRealDetId = pengrealdetId
   LEFT JOIN finansi_pa_spm
      ON spmDetSpmId = spmId
   LEFT JOIN finansi_pa_sppu_det
      ON sppuDetPengrealDetId = pengrealdetId
   LEFT JOIN finansi_pa_sppu
      ON sppuId = sppuDetSppuId
   GROUP BY pengrealdetPengRealId
   ) AS spp ON spp.id = pengrealId
   JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
   JOIN (SELECT unitkerjaId AS id,
   CASE
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN CONCAT(unitkerjaKodeSistem, '.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}.([0-9]){1,3}$' THEN unitkerjaKodeSistem
   END AS `code`
   FROM unit_kerja_ref) AS tmp_unit
      ON tmp_unit.id = unitkerjaId
   JOIN tahun_anggaran
      ON thanggarId = programThanggarId
WHERE 1 = 1
AND programThanggarId = %s
AND (SUBSTR(`unitkerjaKodeSistem`,1, (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = %s)) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.') FROM unit_kerja_ref WHERE `unitkerjaId` = %s) OR unitkerjaId = %s)
AND (programId = '%s' OR 1 = %s)
AND pengrealNomorPengajuan LIKE '%s'
AND kegrefNomor LIKE '%s'
AND kegrefNama LIKE '%s'
AND (jeniskegId = '%s' OR 1 = %s)
AND (MONTH(pengrealTanggal) = %s OR 1 = %s)
AND (MONTH(kegdetWaktuMulaiPelaksanaan)  = %s OR 1 = %s)
ORDER BY programId, subprogId,
SUBSTRING_INDEX(tmp_unit.code, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.code, '.', 5), '.', -1)+0,
kegrefId,kegrefNomor,pengrealTanggal DESC
LIMIT %s, %s
";

$sql['get_data_detail']    = "
SELECT
   pengrealId AS id,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   thanggarId AS taId,
   thanggarNama AS taNama,
   programId AS programId,
   programNomor AS programKode,
   programNama AS programNama,
   subprogId AS kegiatanId,
   subprogNomor AS kegiatanKode,
   subprogNama AS kegiatanNama,
   kegrefId AS subKegiatanId,
   kegrefNomor AS subKegiatanKode,
   kegrefNama AS subKegiatanNama,
   kegdetId AS kegiatanDetId,
   pengrealNomorPengajuan AS nomorPengajuan,
   pengrealNominal AS nominal,
   pengrealKeterangan AS keterangan,
   pengrealTanggal AS tanggal,
   IF(pengrealIsApprove IS NULL OR pengrealIsApprove = '', 'BELUM', pengrealIsApprove) AS `status`,
   anggaran.nominalAnggaran,
   anggaran.nominalRealisasi,
   anggaran.nominalPencairan,
   IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL, rd.pencairanApprove, 0) AS nominalSetuju,
   (pengrealNominal/anggaran.nominalAnggaran)*100 AS persen
FROM pengajuan_realisasi
   JOIN kegiatan_detail
      ON pengrealKegdetId = kegdetId
   JOIN kegiatan_ref
      ON kegdetKegrefId = kegrefId
   JOIN sub_program
      ON kegrefSubprogId = subprogId
   JOIN program_ref
      ON subprogProgramId = programId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   JOIN kegiatan
      ON kegdetKegId = kegId
   JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
   LEFT JOIN tahun_anggaran
      ON thanggarId = programThanggarId
   LEFT JOIN (
   SELECT
      pengrealdetPengRealId AS id,
      SUM(pengrealdetNominalPencairan) AS nominalPencairan,
      SUM(pengrealdetNominalApprove) AS pencairanApprove
   FROM pengajuan_realisasi_detil
   GROUP BY pengrealdetPengRealId) AS rd
      ON rd.id = pengrealId
   LEFT JOIN (
      SELECT
         kegdetId AS id,
         IFNULL(rp.nominal, 0)-IFNULL(pengajuan.nominal, 0) AS nominalAnggaran,
         IFNULL(pengajuan.nominal, 0) AS nominalRealisasi,
         IFNULL(`real`.nominal, 0) AS nominalPencairan
      FROM
         kegiatan_detail
         LEFT JOIN (
            SELECT
               rncnpengeluaranKegdetId AS id,
               IF(UPPER(rncnpengeluaranIsAprove) = 'YA', SUM(IFNULL(rncnpengeluaranSatuanAprove, 1) * rncnpengeluaranKomponenNominalAprove), 0) AS nominal
            FROM rencana_pengeluaran
            GROUP BY rncnpengeluaranKegdetId
         ) AS rp ON rp.id = kegdetId
         LEFT JOIN (
            SELECT
               realisasi.id,
               SUM(IFNULL(realisasi.nominal, 0)) AS nominal
            FROM ((SELECT
               pengrealKegdetId AS id,
               SUM(pengrealNominalAprove) AS nominal
            FROM
               pengajuan_realisasi
            WHERE 1 = 1
            AND pengrealIsApprove = 'Ya'
            AND (pengrealId != %s OR 1 = %s)
            GROUP BY pengrealKegdetId)
            UNION
            (SELECT
               kegAdjustAmblKegId AS id,
               SUM(kegAdjustNominal) AS nominal
            FROM kegiatan_adjust
            GROUP BY kegAdjustAmblKegId)) AS realisasi
            GROUP BY realisasi.id
         ) AS `real` ON `real`.id = kegdetId
         LEFT JOIN (
            SELECT
               pengrealKegdetId AS id,
               SUM(IFNULL(pengrealNominal, 0)) AS nominal,
               SUM(IF(UPPER(pengrealIsApprove) = 'YA', IFNULL(pengrealNominalAprove, 0), 0)) AS nominalApprove,
               IFNULL(pengrealIsApprove, 'Tidak') AS status_approve
            FROM pengajuan_realisasi
            WHERE 1 = 1
            AND (pengrealId != %s OR 1 = %s)
            GROUP BY pengrealKegdetId
         ) AS pengajuan ON pengajuan.id = kegdetId
      GROUP BY kegdetId
   ) AS anggaran ON anggaran.id = pengrealKegdetId
WHERE pengrealId = %s
LIMIT 0, 1
";

$sql['get_data_komponen_pencairan']    = "
SELECT
   pengrealdetId AS realisasiDetId,
   pengrealdetPengRealId AS realisasiId,
   rncnpengeluaranKegdetId AS kegdetId,
   rncnpengeluaranId AS id,
   rncnpengeluaranKomponenKode AS komponenKode,
   rncnpengeluaranKomponenNama AS komponenNama,
   coaId AS makId,
   IF(UPPER(rncnpengeluaranIsAprove) = 'YA', (IFNULL(rncnpengeluaranSatuanAprove,0)*rncnpengeluaranKomponenNominalAprove), 0) - IFNULL(realisasi.nominal, 0) AS nominal,
   coaKodeAkun AS makKode,
   `pengrealdetDeskripsi` AS deskripsi,
   pengrealdetNominalPencairan AS nominalBudget,
   IF(UPPER(rncnpengeluaranIsAprove) = 'YA' , pengrealdetNominalApprove, 0) AS nominalApprove,
   IF(UPPER(rncnpengeluaranIsAprove) = 'YA', (IFNULL(rncnpengeluaranSatuanAprove,0)*rncnpengeluaranKomponenNominalAprove), 0) - IFNULL(realisasi.nominal, 0) - pengrealdetNominalPencairan AS sisaDana
FROM
   rencana_pengeluaran
   LEFT JOIN finansi_ref_pagu_bas
      ON paguBasId = rncnpengeluaranMakId
   JOIN pengajuan_realisasi_detil
      ON pengrealdetRncnpengeluaranId = rncnpengeluaranId
   LEFT JOIN (
      SELECT
         pengrealdetRncnpengeluaranId AS id,
         SUM(pengrealdetNominalPencairan) AS nominal
      FROM pengajuan_realisasi_detil
      JOIN pengajuan_realisasi
         ON pengrealId = pengrealdetPengRealId
      JOIN rencana_pengeluaran
         ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
      WHERE 1 = 1
      AND (pengrealdetPengRealId != %s OR 1 = %s)
      GROUP BY pengrealdetRncnpengeluaranId
   ) AS realisasi ON realisasi.id = rncnpengeluaranId
LEFT JOIN komponen komp
	ON komp.`kompKode` = `rncnpengeluaranKomponenKode`
   LEFT JOIN coa c
        ON c.`coaId` =  komp.`kompCoaId`   
WHERE 1 = 1
   AND pengrealdetPengRealId = %s
ORDER BY rncnpengeluaranKegdetId ASC,
rncnpengeluaranKomponenKode
";

$sql['do_update_realisasi_detail']  = "
UPDATE
   pengajuan_realisasi_detil
SET
   pengrealdetNominalApprove = '0',
   pengrealdetUserId = %s
WHERE
   pengrealdetPengRealId = %s
";

$sql['do_update'] = "
UPDATE
   pengajuan_realisasi
SET
   pengrealIsApprove = NULL,
   pengrealNominalAprove = '0',
   pengrealUserId = %s
WHERE pengrealId = %s
";

//===GET===
$sql['get_count_data_approval'] = "
SELECT
		COUNT(kegdetId) as total
	FROM
		program_ref
		JOIN sub_program ON (subprogProgramId = programId)
		JOIN kegiatan_ref ON (kegrefSubprogId = subprogId)
		JOIN kegiatan_detail ON (kegdetKegrefId = kegrefId)
		JOIN kegiatan ON (kegId = kegdetKegId)
		JOIN pengajuan_realisasi ON (pengrealkegdetId = kegdetId)
		JOIN unit_kerja_ref ON kegiatan.kegUnitkerjaId = unit_kerja_ref.unitkerjaId
	WHERE
	 (programNomor = '%s' OR
	  subprogNomor = '%s' OR
	  kegrefNomor = '%s' OR
	  programNama LIKE '%s' OR
	  subprogNama LIKE '%s' OR
	  kegrefNama LIKE '%s') AND
		programThanggarId=%s
	  %s
	  %s
	  %s
";

$sql['get_data_approval'] = "
SELECT
	   programId AS program_id,
	   programNomor AS program_nomor,
	   programNama AS program_nama,
	   subprogId  AS subprogram_id,
	   subprogNomor AS kegiatan_nomor,
	   subprogNama AS kegiatan_nama,
	   kegrefId AS subkegiatan_id,
	   kegrefNomor AS subkegiatan_kode,
	   kegrefNama AS subkegiatan_nama,
	   kegdetId as kegiatan_detil_id,
      pengrealId as realisasi_id,
	   pengrealNominal AS nominal,
	   pengrealNominalAprove AS nominal_approve,
      pengrealTanggal as tanggal,
      pengrealIsApprove as approval,
      jeniskegNama as jenis_kegiatan,
	  jeniskegId as jenis_keg_id,
      unitkerjaNama as unit_nama
	FROM
		program_ref
		JOIN sub_program ON (subprogProgramId = programId)
		JOIN kegiatan_ref ON (kegrefSubprogId = subprogId)
		JOIN kegiatan_detail ON (kegdetKegrefId = kegrefId)
		JOIN kegiatan ON (kegId = kegdetKegId)
      LEFT JOIN jenis_kegiatan_ref ON (jeniskegId = subprogJeniskegId)
      JOIN unit_kerja_ref ON (unitkerjaId = kegUnitkerjaId)
		JOIN pengajuan_realisasi ON (pengrealkegdetId = kegdetId)
	WHERE
	 (programNomor = '%s' OR
	  subprogNomor = '%s' OR
	  kegrefNomor = '%s' OR
	  programNama LIKE '%s' OR
	  subprogNama LIKE '%s' OR
	  kegrefNama LIKE '%s') AND
	  programThanggarId=%s
	  %s
	  %s
	  %s
	 GROUP BY pengrealId
	ORDER BY
	   program_nomor, kegiatan_nomor, subkegiatan_kode, kegdetId,pengrealTanggal
   LIMIT %s, %s
";

$sql['get_data_by_id'] = "
SELECT
      pengrealId as realisasi_id,
      thanggarNama as tahun_anggaran_label,
      unitkerjaNama AS unitkerja_label,
    /*(if(tempUnitNama IS NULL,unitkerjaNama,CONCAT_WS('/ ',tempUnitNama, unitkerjaNama))) AS unitkerja_label,*/
	   programNama AS program_label,
	   subprogNama AS kegiatan_label,
	   kegrefNama AS subkegiatan_label,
      pengrealKeterangan as keterangan,
      pengrealTanggal as tanggal,
      pengrealNomorPengajuan as nomor,
	   pengrealNominal AS nominal,
	   pengrealNominalAprove AS nominal_approve,
      pengrealIsApprove as status
	FROM
		program_ref
		JOIN sub_program ON (subprogProgramId = programId)
		JOIN kegiatan_ref ON (kegrefSubprogId = subprogId)
		JOIN kegiatan_detail ON (kegdetKegrefId = kegrefId)
		JOIN kegiatan ON (kegId = kegdetKegId)
      LEFT JOIN jenis_kegiatan_ref ON (jeniskegId = subprogJeniskegId)
		JOIN pengajuan_realisasi ON (pengrealkegdetId = kegdetId)
      JOIN tahun_anggaran ON (thanggarId = kegThanggarId)
      JOIN unit_kerja_ref ON (unitkerjaId = kegUnitkerjaId)
	 /*	LEFT JOIN
			(SELECT
				unitkerjaId AS tempUnitId,
				unitkerjaKode AS tempUnitKode,
				unitkerjaNama AS tempUnitNama,
				unitkerjaParentId AS tempParentId
			FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUnitKerja ON(unitkerjaParentId=tempUnitId)
            */
	WHERE
	  pengrealId=%s
";

$sql['get_data_nominal'] = "
   SELECT
      pengrealNominal as nominal
   FROM
      pengajuan_realisasi
   WHERE
      pengrealId=%s
";
//COMBO
$sql['get_combo_tahun_anggaran']="
	SELECT
		thanggarId as id,
		thanggarNama as name
	FROM
		tahun_anggaran
	ORDER BY thanggarNama
";
//aktif
$sql['get_tahun_anggaran_aktif']="
	SELECT
		thanggarId as id,
		thanggarNama as name
	FROM
		tahun_anggaran
	WHERE
		thanggarIsAktif='Y'
";

//===DO===


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
