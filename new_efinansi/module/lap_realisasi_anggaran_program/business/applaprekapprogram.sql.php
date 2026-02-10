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

$sql['get_unit_kerja_id'] = "
SELECT
   unitkerjaNama,
   unitkerjaNamaPimpinan
FROM
   unit_kerja_ref
WHERE
   unitkerjaId = '%s'
";

//===GET===
$sql['get_data'] = "
SELECT SQL_CALC_FOUND_ROWS
   kegdetId AS id,
   kegdetIsAprove AS `status`,
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   programId,
   programNomor AS programKode,
   programNama AS programNama,
   subprogId AS kegiatanId,
   IFNULL(subprogNomor, '') AS kegiatanKode,
   CONCAT(IFNULL(subprogNama, ''),' (',IFNULL(jeniskegNama, '-'),')') AS kegiatanNama,
   kegrefId AS subKegiatanId,
   IFNULL(kegrefNomor, '') AS subKegiatanKode,
   IFNULL(kegrefNama, '') AS subKegiatanNama,
   IF(kegdetDeskripsi = '', '-', kegdetDeskripsi) AS deskripsi,
   IF(rencana_pengeluaran.nominalUsulan > 0,rencana_pengeluaran.nominalUsulan, 0) AS nominalUsulan,
   IF(rencana_pengeluaran.nominalSetuju > 0,rencana_pengeluaran.nominalSetuju,0) AS nominalSetuju,
   IFNULL(pencairan.nominalPencairan, 0) AS nominalPencairan,
   IFNULL(realisasi.nominal, 0) AS nominalRealisasi,
   IF(rencana_pengeluaran.nominalSetuju > 0,rencana_pengeluaran.nominalSetuju,0) - IFNULL(realisasi.nominal, 0) AS sisaDana
FROM
   kegiatan_detail
   LEFT JOIN kegiatan
      ON kegdetKegId = kegId
   LEFT JOIN kegiatan_ref
      ON kegdetKegrefId = kegrefId
   LEFT JOIN sub_program
      ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref
      ON subprogProgramId = programId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
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
   LEFT JOIN
      (SELECT
         rncnpengeluaranKegdetId,
         SUM(rncnpengeluaranKomponenNominal * rncnpengeluaranSatuan * IF(kompFormulaHasil > 0, kompFormulaHasil, 1)) AS nominalUsulan,
         SUM(
            CASE
               WHEN rncnpengeluaranIsAprove = 'Ya'
               THEN (
                  rncnpengeluaranKomponenNominalAprove * rncnpengeluaranSatuanAprove * IF(
                     kompFormulaHasil > 0,
                     kompFormulaHasil,
                     1
                  )
               )
               WHEN rncnpengeluaranIsAprove <> 'Ya'
               THEN 0
            END
         ) AS nominalSetuju
      FROM
         rencana_pengeluaran
         LEFT JOIN komponen
            ON kompKode = rncnpengeluaranKomponenKode
      GROUP BY rncnpengeluaranKegdetId) AS rencana_pengeluaran
      ON rencana_pengeluaran.rncnpengeluaranKegdetId = kegdetId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   LEFT JOIN (
      SELECT
         pengrealKegdetId AS id,
         SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL, IFNULL(pengrealdetNominalApprove, 0), 0)) AS nominalPencairan
      FROM pengajuan_realisasi
      JOIN pengajuan_realisasi_detil
         ON pengrealdetPengRealId = pengrealId
      GROUP BY pengrealKegdetId
   ) AS pencairan ON pencairan.id = kegdetId
   LEFT JOIN(
      SELECT
         transdtanggarKegdetId AS id,
         SUM(transNilai) AS nominal
      FROM transaksi
      JOIN transaksi_detail_anggaran
         ON transdtanggarTransId = transId
      JOIN pengajuan_realisasi
         ON pengrealId = transdtanggarPengrealId
      WHERE 1 = 1
      GROUP BY transdtanggarKegdetId
   ) AS realisasi ON realisasi.id = kegdetId
WHERE 1 = 1
   AND kegThanggarId = %s
   AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
   AND (programId = '%s' OR 1 = %s)
   AND (jeniskegId = '%s' OR 1 = %s)
    AND (MONTH(`kegdetWaktuMulaiPelaksanaan`) = '%s' OR 1 = %s)
ORDER BY programId,
   subprogId,
   SUBSTRING_INDEX(tmp_unit.kode, '.', 1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 2), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 3), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 4), '.', -1)+0,
   SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 5), '.', -1)+0,
   kegrefId
LIMIT %s, %s
";

$sql['get_count_data'] = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_resume']    = "
SELECT SQL_CALC_FOUND_ROWS
   programId,
   programNomor AS programKode,
   programNama AS programNama,
   SUM(IF(rencana_pengeluaran.nominalUsulan > 0,rencana_pengeluaran.nominalUsulan, 0)) AS nominalUsulan,
   SUM(IF(rencana_pengeluaran.nominalSetuju > 0,rencana_pengeluaran.nominalSetuju,0)) AS nominalSetuju,
   SUM(IFNULL(pencairan.nominalPencairan, 0)) AS nominalPencairan,
   SUM(IFNULL(realisasi.nominal, 0)) AS nominalRealisasi,
   SUM(IF(rencana_pengeluaran.nominalSetuju > 0,rencana_pengeluaran.nominalSetuju,0) - IFNULL(realisasi.nominal, 0)) AS sisaDana
FROM
   kegiatan_detail
   LEFT JOIN kegiatan
      ON kegdetKegId = kegId
   LEFT JOIN kegiatan_ref
      ON kegdetKegrefId = kegrefId
   LEFT JOIN sub_program
      ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref
      ON subprogProgramId = programId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId
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
   LEFT JOIN
      (SELECT
         rncnpengeluaranKegdetId,
         SUM(rncnpengeluaranKomponenNominal * rncnpengeluaranSatuan * IF(kompFormulaHasil > 0, kompFormulaHasil, 1)) AS nominalUsulan,
         SUM(
            CASE
               WHEN rncnpengeluaranIsAprove = 'Ya'
               THEN (
                  rncnpengeluaranKomponenNominalAprove * rncnpengeluaranSatuanAprove * IF(
                     kompFormulaHasil > 0,
                     kompFormulaHasil,
                     1
                  )
               )
               WHEN rncnpengeluaranIsAprove <> 'Ya'
               THEN 0
            END
         ) AS nominalSetuju
      FROM
         rencana_pengeluaran
         LEFT JOIN komponen
            ON kompKode = rncnpengeluaranKomponenKode
      GROUP BY rncnpengeluaranKegdetId) AS rencana_pengeluaran
      ON rencana_pengeluaran.rncnpengeluaranKegdetId = kegdetId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   LEFT JOIN (
      SELECT
         pengrealKegdetId AS id,
         SUM(IF(UPPER(pengrealIsApprove) = 'YA' AND pengrealIsApprove IS NOT NULL, IFNULL(pengrealdetNominalApprove, 0), 0)) AS nominalPencairan
      FROM pengajuan_realisasi
      JOIN pengajuan_realisasi_detil
         ON pengrealdetPengRealId = pengrealId
      GROUP BY pengrealKegdetId
   ) AS pencairan ON pencairan.id = kegdetId
   LEFT JOIN(
      SELECT
         transdtanggarKegdetId AS id,
         SUM(transNilai) AS nominal
      FROM transaksi
      JOIN transaksi_detail_anggaran
         ON transdtanggarTransId = transId
      JOIN pengajuan_realisasi
         ON pengrealId = transdtanggarPengrealId
      WHERE 1 = 1
      GROUP BY transdtanggarKegdetId
   ) AS realisasi ON realisasi.id = kegdetId
WHERE 1 = 1
   AND kegThanggarId = %s
   AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
   AND (programId = '%s' OR 1 = %s)
   AND (jeniskegId = '%s' OR 1 = %s)
    AND (MONTH(`kegdetWaktuMulaiPelaksanaan`) = '%s' OR 1 = %s)
GROUP BY programId
ORDER BY programId
";

$sql['get_resume'] = "
SELECT
   programId as id,
   /*
   CONCAT(CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
         WHEN LENGTH(programNomor) = 2 THEN programNomor END, '.',
         CASE WHEN jeniskegNama='Rutin' THEN '1'
         WHEN jeniskegNama='Pengembangan' THEN '2' END,'.00.00')*/
         programNomor AS kode,
   programNama AS nama,
   ifnull(SUM(h.nominalUsulan),0) AS nominal_usulan,
   ifnull(SUM(h.nominalSetuju),0) AS nominal_setuju,
   SUM(pengrealNominalAprove) AS nominal_pencairan,
   SUM(ifnull((
      SELECT
         SUM(transNilai)
      FROM
         transaksi a
         JOIN transaksi_detail_pencairan b ON transdtpencairanTransId = transId
      WHERE
         transdtpencairanKegdetId = kegdetId
      GROUP BY transdtpencairanKegdetId
         ) ,0))
      as nominal_pencairan_pr,
   SUM(ifnull((
      SELECT
         SUM(transNilai)
      FROM
         transaksi a
         JOIN transaksi_detail_anggaran b ON transdtanggarTransId = transId
      WHERE
         transdtanggarKegdetId = kegdetId
      GROUP BY transdtanggarKegdetId
         ) ,0))
      as nominal_realisasi,
   (SUM(ifnull(h.nominalSetuju,0)) - SUM(ifnull((SELECT
         SUM(transNilai)
      FROM
         transaksi a
         JOIN transaksi_detail_anggaran b ON transdtanggarTransId = transId
      WHERE
         transdtanggarKegdetId = kegdetId
      GROUP BY transdtanggarKegdetId
         ),0) )) as sisa
FROM kegiatan a
   LEFT JOIN  kegiatan_detail b  ON b.kegdetKegId = a.kegId
   LEFT JOIN  kegiatan_ref c   ON b.kegdetKegrefId = c.kegrefId
   LEFT JOIN  sub_program d  ON c.kegrefSubprogId = d.subprogId
   LEFT JOIN  program_ref e  ON d.subprogProgramId = e.programId
   LEFT JOIN unit_kerja_ref g ON kegUnitkerjaId = g.unitkerjaId
   LEFT JOIN (
      SELECT
         rncnpengeluaranKegdetId,
         sum(rncnpengeluaranKomponenNominal * rncnpengeluaranSatuan*IFNULL(kompFormulaHasil,1))
            AS nominalUsulan,
         sum(rncnpengeluaranKomponenNominalAprove*rncnpengeluaranSatuanAprove*IFNULL(kompFormulaHasil,1))
            AS nominalSetuju
      FROM rencana_pengeluaran
        LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
      GROUP BY rncnpengeluaranKegdetId) h ON h.rncnpengeluaranKegdetId = b.kegdetId
   LEFT JOIN jenis_kegiatan_ref i ON d.subprogJeniskegId = i.jeniskegId
   LEFT JOIN (
                SELECT
                    pengrealKegdetId,SUM(pengrealNominal) AS pengrealNominal,
                    SUM(pengrealNominalAprove) AS pengrealNominalAprove
                FROM pengajuan_realisasi
                WHERE (
                            MONTH(pengrealTanggal) = '%s'
                            OR 'all' = '%s'
                ) GROUP BY pengrealKegdetId) j ON j.pengrealKegdetId = kegdetId
   WHERE a.kegThanggarId=%s
    AND kegdetIsAprove = 'Ya'
      %s
      %s
        %s
   GROUP BY programId, jeniskegId
   ORDER BY kode, jeniskegId
";
$sql['get_resume_kegiatan'] = "
SELECT
   /*CONCAT(CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
            WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.00.00') AS kode,  */
    subProgId as id,
   ifnull(CONCAT(CASE WHEN LENGTH(programNomor) = 1 THEN CONCAT('0',programNomor)
                  WHEN LENGTH(programNomor) = 2 THEN programNomor END,'.',
                  CASE WHEN jeniskegNama='Rutin' THEN '1'
                  WHEN jeniskegNama='Pengembangan' THEN '2' END,'.',
                  CASE WHEN LENGTH(subprogNomor)= 1 THEN CONCAT('0',subprogNomor)
                  WHEN LENGTH(subprogNomor)= 2 THEN subprogNomor END,'.00'),'') AS kode,
   CONCAT(ifnull(subprogNama,''),' (',IFNULL(jeniskegNama, '-'),')') AS namaKegiatan,
   ifnull(SUM(h.nominalUsulan),0) AS nominal_usulan,
   ifnull(SUM(h.nominalSetuju),0) AS nominal_setuju,
    SUM(ifnull((
        SELECT
            SUM(transNilai)
        FROM
            transaksi a
            JOIN transaksi_detail_anggaran b ON transdtanggarTransId = transId
        WHERE
            transdtanggarKegdetId = kegdetId
        GROUP BY transdtanggarKegdetId
         ) ,0)) AS nominal_realisasi,
   (SUM(ifnull(h.nominalSetuju,0)) - SUM(ifnull((SELECT
         SUM(transNilai)
      FROM
         transaksi a
         JOIN transaksi_detail_anggaran b ON transdtanggarTransId = transId
      WHERE
         transdtanggarKegdetId = kegdetId
      GROUP BY transdtanggarKegdetId
         ),0) )) as sisa
FROM kegiatan_detail b
   LEFT JOIN kegiatan a ON b.kegdetKegId = a.kegId
   LEFT JOIN kegiatan_ref c ON b.kegdetKegrefId = c.kegrefId
   LEFT JOIN sub_program d ON c.kegrefSubprogId = d.subprogId
   LEFT JOIN program_ref e ON d.subprogProgramId = e.programId
   LEFT JOIN unit_kerja_ref g ON kegUnitkerjaId = g.unitkerjaId

   LEFT JOIN (
      SELECT
         rncnpengeluaranKegdetId,
         sum(rncnpengeluaranKomponenNominal * rncnpengeluaranSatuan*IFNULL(kompFormulaHasil,1))
            AS nominalUsulan,
         sum(rncnpengeluaranKomponenNominalAprove*rncnpengeluaranSatuanAprove*IFNULL(kompFormulaHasil,1))
            AS nominalSetuju
      FROM rencana_pengeluaran
        LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
      GROUP BY rncnpengeluaranKegdetId) h ON h.rncnpengeluaranKegdetId = b.kegdetId
   LEFT JOIN jenis_kegiatan_ref i ON d.subprogJeniskegId = i.jeniskegId
   LEFT JOIN pengajuan_realisasi j ON j.pengrealKegdetId = kegdetId
WHERE
    a.kegThanggarId=%s
   AND
   kegdetIsAprove = 'Ya'
   AND
   (MONTH(pengrealTanggal) = '%s' OR 'all' = '%s')
      %s
      %s
        %s
   GROUP BY subProgId, jeniskegId
   ORDER BY kode, jeniskegId
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
$sql['get_tahun_anggaran_aktif']="
   SELECT
      thanggarId as id,
      thanggarNama as name
   FROM
      tahun_anggaran
   WHERE
      thanggarIsAktif='Y'
";
$sql['get_combo_jenis_kegiatan']="
   SELECT
      jeniskegId as id,
      jeniskegNama as name
   FROM
      jenis_kegiatan_ref

   ORDER BY jeniskegId
";

$sql['get_tahun_anggaran_by_id']="
   SELECT
      thanggarId as id,
      thanggarNama as nama
   FROM
      tahun_anggaran
   WHERE thanggarId=%s
";

$sql['get_program_by_id'] =
   "SELECT
      programId as id,
     programNomor as kode,
     programNama as nama
   FROM
      program_ref
   WHERE
   programId=%s
";

   $sql['get_unitkerja_by_id'] = "
   SELECT
      (if(tempUnitId IS NULL,unitkerjaId,unitkerjaId)) AS `id`,
      (if(tempUnitKode IS NULL,unitkerjaKode,unitkerjaKode)) AS `kode`,
      (if(tempUnitNama IS NULL,unitkerjaNama,CONCAT_WS('/ ',tempUnitNama, unitkerjaNama))) AS `nama`
   FROM
      unit_kerja_ref
      LEFT JOIN
         (SELECT
            unitkerjaId AS tempUnitId,
            unitkerjaKode AS tempUnitKode,
            unitkerjaNama AS tempUnitNama,
            unitkerjaParentId AS tempParentId
         FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUnitKerja ON(unitkerjaParentId=tempUnitId)
   WHERE
      unitkerjaId=%s
   ";
$sql['get_jenis_kegiatan_by_id']="
   SELECT
      jeniskegId as id,
      jeniskegNama as nama
   FROM
      jenis_kegiatan_ref
   WHERE jeniskegId=%s
";
?>