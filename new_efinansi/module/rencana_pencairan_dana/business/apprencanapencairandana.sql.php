<?php
$sql['get_periode_tahun']   = "
SELECT
   thanggarId AS id,
   thanggarNama AS `name`
FROM tahun_anggaran
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
ORDER BY thanggarNama
";

$sql['count']         = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']      = "
SELECT
   SQL_CALC_FOUND_ROWS
   rncnpengeluaranId AS rp_id,
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
   rncnpengeluaranKomponenNominal * IF(kompFormulaHasil=0,1,kompFormulaHasil) AS nominalUsulan,
   rncnpengeluaranSatuan AS satuanUsulan,
   rncnpengeluaranSatuan * rncnpengeluaranKomponenNominal * IF(kompFormulaHasil=0,1,kompFormulaHasil) AS totalUsulan,
   IF(rncnpengeluaranIsAprove IS NULL OR UPPER(rncnpengeluaranIsAprove) != 'YA', 0, rncnpengeluaranKomponenNominalAprove * IF(kompFormulaHasil=0,1,kompFormulaHasil)) AS nominalApprove,
   IF(rncnpengeluaranIsAprove IS NULL OR UPPER(rncnpengeluaranIsAprove) != 'YA', 0, rncnpengeluaranSatuanAprove) AS satuanApprove,
   rncnpengeluaranNamaSatuan AS satuanNama,
   CONCAT(IF(rncnpengeluaranIsAprove IS NULL OR UPPER(rncnpengeluaranIsAprove) != 'YA', 0, rncnpengeluaranSatuanAprove), ' ', rncnpengeluaranNamaSatuan) AS volume,
   IF(rncnpengeluaranIsAprove IS NULL OR UPPER(rncnpengeluaranIsAprove) != 'YA', 0, rncnpengeluaranSatuanAprove) * rncnpengeluaranKomponenNominalAprove * IF(kompFormulaHasil=0,1,kompFormulaHasil) AS totalApprove,
   rncnpengeluaranKomponenDeskripsi AS komponenDeskripsi,
   rncnpengeluaranIsAprove AS approval,
   IFNULL((SELECT
   SUM(IF(pengrealIsApprove IS NULL OR UPPER(pengrealIsApprove) = 'BELUM', 0, pengrealdetNominalApprove)) AS nominalApprove
FROM pengajuan_realisasi_detil
JOIN pengajuan_realisasi
   ON pengrealId = pengrealdetPengRealId
JOIN rencana_pengeluaran
   ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
WHERE 1 = 1
AND UPPER(pengrealIsApprove) = 'YA'
AND MONTH(pengrealdetTanggal) = 1
AND pengrealdetRncnpengeluaranId = rp_id), 0) AS nominalJanuari,
   IFNULL((SELECT
   SUM(IF(pengrealIsApprove IS NULL OR UPPER(pengrealIsApprove) = 'BELUM', 0, pengrealdetNominalApprove)) AS nominalApprove
FROM pengajuan_realisasi_detil
JOIN pengajuan_realisasi
   ON pengrealId = pengrealdetPengRealId
JOIN rencana_pengeluaran
   ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
WHERE 1 = 1
AND UPPER(pengrealIsApprove) = 'YA'
AND MONTH(pengrealdetTanggal) = 2
AND pengrealdetRncnpengeluaranId = rp_id), 0) AS NominalFebruari,
   IFNULL((SELECT
   SUM(IF(pengrealIsApprove IS NULL OR UPPER(pengrealIsApprove) = 'BELUM', 0, pengrealdetNominalApprove)) AS nominalApprove
FROM pengajuan_realisasi_detil
JOIN pengajuan_realisasi
   ON pengrealId = pengrealdetPengRealId
JOIN rencana_pengeluaran
   ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
WHERE 1 = 1
AND UPPER(pengrealIsApprove) = 'YA'
AND MONTH(pengrealdetTanggal) = 3
AND pengrealdetRncnpengeluaranId = rp_id), 0) AS nominalMaret,
   IFNULL((SELECT
   SUM(IF(pengrealIsApprove IS NULL OR UPPER(pengrealIsApprove) = 'BELUM', 0, pengrealdetNominalApprove)) AS nominalApprove
FROM pengajuan_realisasi_detil
JOIN pengajuan_realisasi
   ON pengrealId = pengrealdetPengRealId
JOIN rencana_pengeluaran
   ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
WHERE 1 = 1
AND UPPER(pengrealIsApprove) = 'YA'
AND MONTH(pengrealdetTanggal) = 4
AND pengrealdetRncnpengeluaranId = rp_id), 0) AS nominalApril,
   IFNULL((SELECT
   SUM(IF(pengrealIsApprove IS NULL OR UPPER(pengrealIsApprove) = 'BELUM', 0, pengrealdetNominalApprove)) AS nominalApprove
FROM pengajuan_realisasi_detil
JOIN pengajuan_realisasi
   ON pengrealId = pengrealdetPengRealId
JOIN rencana_pengeluaran
   ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
WHERE 1 = 1
AND UPPER(pengrealIsApprove) = 'YA'
AND MONTH(pengrealdetTanggal) = 5
AND pengrealdetRncnpengeluaranId = rp_id), 0) AS nominalMei,
   IFNULL((SELECT
   SUM(IF(pengrealIsApprove IS NULL OR UPPER(pengrealIsApprove) = 'BELUM', 0, pengrealdetNominalApprove)) AS nominalApprove
FROM pengajuan_realisasi_detil
JOIN pengajuan_realisasi
   ON pengrealId = pengrealdetPengRealId
JOIN rencana_pengeluaran
   ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
WHERE 1 = 1
AND UPPER(pengrealIsApprove) = 'YA'
AND MONTH(pengrealdetTanggal) = 6
AND pengrealdetRncnpengeluaranId = rp_id), 0) AS nominalJuni,
   IFNULL((SELECT
   SUM(IF(pengrealIsApprove IS NULL OR UPPER(pengrealIsApprove) = 'BELUM', 0, pengrealdetNominalApprove)) AS nominalApprove
FROM pengajuan_realisasi_detil
JOIN pengajuan_realisasi
   ON pengrealId = pengrealdetPengRealId
JOIN rencana_pengeluaran
   ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
WHERE 1 = 1
AND UPPER(pengrealIsApprove) = 'YA'
AND MONTH(pengrealdetTanggal) = 7
AND pengrealdetRncnpengeluaranId = rp_id), 0) AS nominalJuli,
   IFNULL((SELECT
   SUM(IF(pengrealIsApprove IS NULL OR UPPER(pengrealIsApprove) = 'BELUM', 0, pengrealdetNominalApprove)) AS nominalApprove
FROM pengajuan_realisasi_detil
JOIN pengajuan_realisasi
   ON pengrealId = pengrealdetPengRealId
JOIN rencana_pengeluaran
   ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
WHERE 1 = 1
AND UPPER(pengrealIsApprove) = 'YA'
AND MONTH(pengrealdetTanggal) = 8
AND pengrealdetRncnpengeluaranId = rp_id), 0) AS nominalAgustus,
   IFNULL((SELECT
   SUM(IF(pengrealIsApprove IS NULL OR UPPER(pengrealIsApprove) = 'BELUM', 0, pengrealdetNominalApprove)) AS nominalApprove
FROM pengajuan_realisasi_detil
JOIN pengajuan_realisasi
   ON pengrealId = pengrealdetPengRealId
JOIN rencana_pengeluaran
   ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
WHERE 1 = 1
AND UPPER(pengrealIsApprove) = 'YA'
AND MONTH(pengrealdetTanggal) = 9
AND pengrealdetRncnpengeluaranId = rp_id), 0) AS nominalSeptember,
   IFNULL((SELECT
   SUM(IF(pengrealIsApprove IS NULL OR UPPER(pengrealIsApprove) = 'BELUM', 0, pengrealdetNominalApprove)) AS nominalApprove
FROM pengajuan_realisasi_detil
JOIN pengajuan_realisasi
   ON pengrealId = pengrealdetPengRealId
JOIN rencana_pengeluaran
   ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
WHERE 1 = 1
AND UPPER(pengrealIsApprove) = 'YA'
AND MONTH(pengrealdetTanggal) = 10
AND pengrealdetRncnpengeluaranId = rp_id), 0) AS nominalOktober,
   IFNULL((SELECT
   SUM(IF(pengrealIsApprove IS NULL OR UPPER(pengrealIsApprove) = 'BELUM', 0, pengrealdetNominalApprove)) AS nominalApprove
FROM pengajuan_realisasi_detil
JOIN pengajuan_realisasi
   ON pengrealId = pengrealdetPengRealId
JOIN rencana_pengeluaran
   ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
WHERE 1 = 1
AND UPPER(pengrealIsApprove) = 'YA'
AND MONTH(pengrealdetTanggal) = 11
AND pengrealdetRncnpengeluaranId = rp_id), 0) AS nominalNovember,
   IFNULL((SELECT
   SUM(IF(pengrealIsApprove IS NULL OR UPPER(pengrealIsApprove) = 'BELUM', 0, pengrealdetNominalApprove)) AS nominalApprove
FROM pengajuan_realisasi_detil
JOIN pengajuan_realisasi
   ON pengrealId = pengrealdetPengRealId
JOIN rencana_pengeluaran
   ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
WHERE 1 = 1
AND UPPER(pengrealIsApprove) = 'YA'
AND MONTH(pengrealdetTanggal) = 12
AND pengrealdetRncnpengeluaranId = rp_id), 0) AS nominalDesember
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

//===GET===
$sql['get_count_rencana_pencairan_dana'] = "
SELECT
    COUNT(programId) AS total
   FROM
      pengajuan_realisasi_detil
   JOIN
      rencana_pengeluaran ON pengrealdetRncnpengeluaranId = rncnpengeluaranId
   LEFT JOIN
      kegiatan_detail ON kegdetId = rncnpengeluaranKegdetId
   LEFT JOIN
      kegiatan ON  kegdetKegId = kegId
   LEFT JOIN
      unit_kerja_ref uk ON unitkerjaId= kegUnitkerjaId
   LEFT JOIN
      kegiatan_ref ON kegrefId = kegdetKegrefId
   LEFT JOIN
      sub_program ON kegrefSubprogId = subprogId
   LEFT JOIN
      program_ref ON subprogProgramId = programId
   LEFT JOIN
      jenis_kegiatan_ref ON subprogJeniskegId = jeniskegId
   LEFT JOIN
      komponen ON kompKode = rncnpengeluaranKomponenKode
   LEFT JOIN
      finansi_ref_pagu_bas ON (paguBasId = rncnpengeluaranMakId) OR (paguBasId = kompMakId)
WHERE
		kegThanggarId=%s
        AND
        (uk.unitkerjaKodeSistem LIKE
	   CONCAT((
			SELECT
				unitkerjaKodeSistem
			FROM
				unit_kerja_ref
			WHERE
				unit_kerja_ref.unitkerjaId='%s'),'.','%s')
        OR
	   uk.unitkerjaKodeSistem =
			(SELECT
				unitkerjaKodeSistem
			FROM
				unit_kerja_ref
			WHERE
				unit_kerja_ref.unitkerjaId='%s')
	   )
    AND rncnpengeluaranIsAprove= 'Ya'
LIMIT 1
";

$sql['get_rencana_pencairan_dana'] = "
   SELECT
      uk.unitkerjaId as unit_id,
      uk.unitkerjaNama AS unit_nama,
      programId AS program_id,
      programNomor AS program_nomor,
      programNama AS program_nama,
      subprogId AS subprogram_id,
      subprogNomor AS kegiatan_nomor,
      subprogNama AS kegiatan_nama,
      kegrefId AS subkegiatan_id,
      kegrefNomor AS subkegiatan_nomor,
      kegrefNama AS subkegiatan_nama,
      kegdetId AS kegiatan_detil_id,
      jeniskegNama AS jenis_kegiatan,
      jeniskegId AS jenis_keg_id,
      rncnpengeluaranId AS id,
      rncnpengeluaranKomponenKode AS komponen_kode,
      rncnpengeluaranKomponenNama AS komponen_nama,
      rncnpengeluaranSatuanAprove AS satuan_setuju,
      rncnpengeluaranNamaSatuan AS nama_satuan,
      rncnpengeluaranSatuanAprove * rncnpengeluaranKomponenNominalAprove * IFNULL(kompFormulaHasil,1) AS jumlah_setuju,
      rncnpengeluaranIsAprove AS approval,
      rncnpengeluaranKomponenTotalAprove AS alokasi,
      uk.unitkerjaNama as unit_subunit,
      uk.unitkerjaId as unit_id,
      IFNULL(
        (SELECT
                SUM(pengrealdetNominalPencairan)
        FROM
                pengajuan_realisasi_detil
                JOIN rencana_pengeluaran
                        ON pengrealdetRncnpengeluaranId = rncnpengeluaranId
        WHERE MONTH(pengrealdetTanggal) = '01'
                AND rncnpengeluaranKomponenKode = komponen_kode
        GROUP BY rncnpengeluaranKomponenKode),
        0
      ) AS jan,
      IFNULL(
        (SELECT
                SUM(pengrealdetNominalPencairan)
        FROM
                pengajuan_realisasi_detil
                JOIN rencana_pengeluaran
                        ON pengrealdetRncnpengeluaranId = rncnpengeluaranId
        WHERE MONTH(pengrealdetTanggal) = '02'
                AND rncnpengeluaranKomponenKode = komponen_kode
        GROUP BY rncnpengeluaranKomponenKode),
        0
      ) AS feb,
      IFNULL(
        (SELECT
                SUM(pengrealdetNominalPencairan)
        FROM
                pengajuan_realisasi_detil
                JOIN rencana_pengeluaran
                        ON pengrealdetRncnpengeluaranId = rncnpengeluaranId
        WHERE MONTH(pengrealdetTanggal) = '03'
                AND rncnpengeluaranKomponenKode = komponen_kode
        GROUP BY rncnpengeluaranKomponenKode),
        0
      ) AS mar,
      IFNULL(
        (SELECT
                SUM(pengrealdetNominalPencairan)
        FROM
                pengajuan_realisasi_detil
                JOIN rencana_pengeluaran
                        ON pengrealdetRncnpengeluaranId = rncnpengeluaranId
        WHERE MONTH(pengrealdetTanggal) = '04'
                AND rncnpengeluaranKomponenKode = komponen_kode
        GROUP BY rncnpengeluaranKomponenKode),
        0
      ) AS apr,
      IFNULL(
        (SELECT
                SUM(pengrealdetNominalPencairan)
        FROM
                pengajuan_realisasi_detil
                JOIN rencana_pengeluaran
                        ON pengrealdetRncnpengeluaranId = rncnpengeluaranId
        WHERE MONTH(pengrealdetTanggal) = '05'
                AND rncnpengeluaranKomponenKode = komponen_kode
        GROUP BY rncnpengeluaranKomponenKode),
        0
      ) AS mei,
      IFNULL(
        (SELECT
                SUM(pengrealdetNominalPencairan)
        FROM
                pengajuan_realisasi_detil
                JOIN rencana_pengeluaran
                        ON pengrealdetRncnpengeluaranId = rncnpengeluaranId
        WHERE MONTH(pengrealdetTanggal) = '06'
                AND rncnpengeluaranKomponenKode = komponen_kode
        GROUP BY rncnpengeluaranKomponenKode),
        0
      ) AS jun,
      IFNULL(
        (SELECT
                SUM(pengrealdetNominalPencairan)
        FROM
                pengajuan_realisasi_detil
                JOIN rencana_pengeluaran
                        ON pengrealdetRncnpengeluaranId = rncnpengeluaranId
        WHERE MONTH(pengrealdetTanggal) = '07'
                AND rncnpengeluaranKomponenKode = komponen_kode
        GROUP BY rncnpengeluaranKomponenKode),
        0
      ) AS jul,
      IFNULL(
        (SELECT
                SUM(pengrealdetNominalPencairan)
        FROM
                pengajuan_realisasi_detil
                JOIN rencana_pengeluaran
                        ON pengrealdetRncnpengeluaranId = rncnpengeluaranId
        WHERE MONTH(pengrealdetTanggal) = '08'
                AND rncnpengeluaranKomponenKode = komponen_kode
        GROUP BY rncnpengeluaranKomponenKode),
        0
      ) AS agt,
      IFNULL(
        (SELECT
                SUM(pengrealdetNominalPencairan)
        FROM
                pengajuan_realisasi_detil
                JOIN rencana_pengeluaran
                        ON pengrealdetRncnpengeluaranId = rncnpengeluaranId
        WHERE MONTH(pengrealdetTanggal) = '09'
                AND rncnpengeluaranKomponenKode = komponen_kode
        GROUP BY rncnpengeluaranKomponenKode),
        0
      ) AS sep,
      IFNULL(
        (SELECT
                SUM(pengrealdetNominalPencairan)
        FROM
                pengajuan_realisasi_detil
                JOIN rencana_pengeluaran
                        ON pengrealdetRncnpengeluaranId = rncnpengeluaranId
        WHERE MONTH(pengrealdetTanggal) = '10'
                AND rncnpengeluaranKomponenKode = komponen_kode
        GROUP BY rncnpengeluaranKomponenKode),
        0
       ) AS okt,
      IFNULL(
        (SELECT
                SUM(pengrealdetNominalPencairan)
        FROM
                pengajuan_realisasi_detil
                JOIN rencana_pengeluaran
                        ON pengrealdetRncnpengeluaranId = rncnpengeluaranId
        WHERE MONTH(pengrealdetTanggal) = '11'
                AND rncnpengeluaranKomponenKode = komponen_kode
        GROUP BY rncnpengeluaranKomponenKode),
        0
      ) AS nov,
      IFNULL(
        (SELECT
                SUM(pengrealdetNominalPencairan)
        FROM
                pengajuan_realisasi_detil
                JOIN rencana_pengeluaran
                        ON pengrealdetRncnpengeluaranId = rncnpengeluaranId
        WHERE MONTH(pengrealdetTanggal) = '12'
                AND rncnpengeluaranKomponenKode = komponen_kode
        GROUP BY rncnpengeluaranKomponenKode),
        0
      ) AS des,
      IFNULL(rncnpengeluaranMakId,paguBasId) AS mak_id,
      paguBasKode as makKode,
      paguBasKeterangan as makNama
   FROM
      pengajuan_realisasi_detil
   JOIN
      rencana_pengeluaran ON pengrealdetRncnpengeluaranId = rncnpengeluaranId
   LEFT JOIN
      kegiatan_detail ON kegdetId = rncnpengeluaranKegdetId
   LEFT JOIN
      kegiatan ON  kegdetKegId = kegId
   LEFT JOIN
      unit_kerja_ref uk ON unitkerjaId= kegUnitkerjaId
   LEFT JOIN
      kegiatan_ref ON kegrefId = kegdetKegrefId
   LEFT JOIN
      sub_program ON kegrefSubprogId = subprogId
   LEFT JOIN
      program_ref ON subprogProgramId = programId
   LEFT JOIN
      jenis_kegiatan_ref ON subprogJeniskegId = jeniskegId
   LEFT JOIN
      komponen ON kompKode = rncnpengeluaranKomponenKode
   LEFT JOIN
      finansi_ref_pagu_bas ON (paguBasId = rncnpengeluaranMakId) OR (paguBasId = kompMakId)
   WHERE
      kegThanggarId=%s
      AND
      (uk.unitkerjaKodeSistem LIKE
	CONCAT((
			SELECT
				unitkerjaKodeSistem
			FROM
				unit_kerja_ref
			WHERE
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR
	uk.unitkerjaKodeSistem =
			(SELECT
				unitkerjaKodeSistem
			FROM
				unit_kerja_ref
			WHERE
				unit_kerja_ref.unitkerjaId='%s')
	)
   AND rncnpengeluaranIsAprove= 'Ya'
   GROUP BY program_nomor , kegiatan_nomor, subkegiatan_nomor, rncnpengeluaranKomponenKode,mak_id,komponen_kode
   ORDER BY program_nomor , kegiatan_nomor, subkegiatan_nomor, rncnpengeluaranKomponenKode,mak_id,komponen_kode
   LIMIT %s, %s
";

//COMBO
$sql['get_combo_tahun_anggaran']="
	SELECT
		thanggarId as id,
		thanggarNama as name
	FROM
		tahun_anggaran
	ORDER BY thanggarNama DESC
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

$sql['get_tahun_anggaran_cetak']="
	SELECT
		thanggarId as id,
		thanggarNama as name
	FROM
		tahun_anggaran
	WHERE
      thanggarId = %s
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

$sql['get_data_cetak'] = "
SELECT
	programId AS program_id,
	programNomor AS program_nomor,
	programNama AS program_nama,
	rkaklProgramNama AS program_nama_rkakl,
	subprogId AS subprogram_id,
	subprogNomor AS kegiatan_nomor,
	subprogNama AS kegiatan_nama,
	rkaklKegiatanNama AS kegiatan_nama_rkakl,
	kegrefId AS subkegiatan_id,
	kegrefNomor AS subkegiatan_nomor,
	kegrefNama AS subkegiatan_nama,
	kegdetId AS kegiatan_detil_id,
	rkaklSubKegiatanNama AS subkegiatan_nama_rkakl,
	jeniskegNama AS jenis_kegiatan,
	jeniskegId AS jenis_keg_id,
	rncnpengeluaranId AS id,
	rncnpengeluaranKomponenKode AS komponen_kode,
	rncnpengeluaranKomponenNama AS komponen_nama,
	rncnpengeluaranKomponenNominal * IFNULL(kompFormulaHasil,1) AS nominal_usulan,
	rncnpengeluaranSatuan AS satuan_usulan,
	rncnpengeluaranSatuan * rncnpengeluaranKomponenNominal * IFNULL(kompFormulaHasil,1) AS jumlah_usulan,
	rncnpengeluaranKomponenNominalAprove * IFNULL(kompFormulaHasil,1) AS nominal_setuju,
	rncnpengeluaranSatuanAprove AS satuan_setuju,
	rncnpengeluaranNamaSatuan AS nama_satuan,
	rncnpengeluaranSatuanAprove * rncnpengeluaranKomponenNominalAprove * IFNULL(kompFormulaHasil,1) AS jumlah_setuju,
	rncnpengeluaranKomponenDeskripsi AS deskripsi,
	rncnpengeluaranIsAprove AS approval,
	rncnpengeluaranMakId as mak_id,
	paguBasKode as makKode,
	paguBasKeterangan as makNama,
   ikkKode,
   ikkNama AS ikk,
   ikuKode,
   ikuNama AS iku,
   rkaklOutputKode,
   rkaklOutputNama AS output
FROM
	rencana_pengeluaran
LEFT JOIN kegiatan_detail ON kegdetId = rncnpengeluaranKegdetId
LEFT JOIN kegiatan ON  kegdetKegId = kegId
LEFT JOIN unit_kerja_ref uk ON unitkerjaId= kegUnitkerjaId
LEFT JOIN kegiatan_ref ON kegrefId = kegdetKegrefId
LEFT JOIN sub_program ON kegrefSubprogId = subprogId
LEFT JOIN program_ref ON subprogProgramId = programId
LEFT JOIN jenis_kegiatan_ref ON subprogJeniskegId = jeniskegId
LEFT JOIN finansi_ref_rkakl_subkegiatan ON rkaklSubKegiatanId = subprogRKAKLKegiatanId
LEFT JOIN finansi_ref_rkakl_kegiatan ON rkaklKegiatanId = kegrefRkaklSubKegiatanId
LEFT JOIN finansi_ref_rkakl_prog ON rkaklProgramId = programRKAKLProgramId
LEFT JOIN finansi_pa_ref_ikk ON kegdetIkkId = ikkId
LEFT JOIN finansi_pa_ref_iku ON kegdetIkuId = ikuId
LEFT JOIN finansi_ref_rkakl_output ON kegdetRkaklOutputId = rkaklOutputId
LEFT JOIN finansi_ref_pagu_bas ON paguBasId = rncnpengeluaranMakId
LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
	WHERE
	  kegThanggarId=%s AND
     (kegUnitkerjaId=%s OR unitkerjaParentId = %s) AND
     rncnpengeluaranIsAprove= 'Ya'
	ORDER BY program_nomor , kegiatan_nomor, subkegiatan_nomor, rncnpengeluaranKomponenKode
";

?>
