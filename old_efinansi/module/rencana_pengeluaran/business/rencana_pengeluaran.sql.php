<?php
$sql['get_periode_tahun']   = "
SELECT
   thanggarId AS id,
   thanggarNama AS `name`
FROM tahun_anggaran
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
ORDER BY thanggarNama ASC
";

//===GET===
$sql['get_count']="
SELECT
   COUNT(DISTINCT kegdetId) AS `count`
FROM
   kegiatan_detail
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN sub_program
      ON subprogId = kegrefSubprogId
   JOIN program_ref
      ON programId = subprogProgramId
   JOIN kegiatan
      ON kegId = kegdetKegId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   JOIN unit_kerja_ref
      ON unitkerjaId = kegUnitkerjaId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   LEFT JOIN rencana_pengeluaran
      ON rncnpengeluaranKegdetId = kegdetId
   LEFT JOIN komponen
      ON kompKode = rncnpengeluaranKomponenKode
WHERE 1 = 1
AND kegThanggarId = '%s'
AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
AND (jeniskegId = '%s' OR 1 = %s)
AND kegrefNomor LIKE '%s'
AND kegrefNama LIKE '%s'
AND (UPPER(IFNULL(kompIsPengadaan, 'T')) = '%s' OR 1 = %s)
AND (MONTH(kegdetWaktuMulaiPelaksanaan) = %s OR %s ) 
";

$sql['get_data']="
SELECT
   rkat.*,
   kdstatus.userId AS userId,
   gu.RealName AS userNama,
   kompId,
   kompKode,
   kompNama,
   kompNamaSatuan,
   kompDeskripsi,
   bas.paguBasid AS basId,
   bas.paguBasKode AS basKode,
   bas.paguBasKeterangan AS basNama,
   mak.paguBasId AS makId,
   mak.paguBasKode AS makKode,
   mak.paguBasKeterangan AS makNama,
   coaId AS akunId,
   IFNULL(coaKodeAkun, kompKode) AS akunKode,
   IFNULL(coaNamaAkun, kompNama) AS akunNama,
   IFNULL(detail_belanja.count, 0) AS detailBelanja,
   IFNULL(kompIsPengadaan, 'T') AS pengadaan,
   rncnpengeluaranIsAprove AS statusKomponen,
   IF(UPPER(IFNULL(kompIsPengadaan, 'T')) = 'T',
      0,
      rncnpengeluaranSatuan
   ) * rncnpengeluaranKomponenNominal * IF(
      kompFormulaHasil = '0',
      1,
      IFNULL(kompFormulaHasil, 1)
   ) AS nominalPengadaan,
   IF(UPPER(IFNULL(kompIsPengadaan, 'T')) = 'Y',
      0,
      rncnpengeluaranSatuan
   ) * rncnpengeluaranKomponenNominal * IF(
      kompFormulaHasil = '0',
      1,
      IFNULL(kompFormulaHasil, 1)
   ) AS nominalNonPengadaan,
   rncnpengeluaranSatuan * rncnpengeluaranKomponenNominal * IF(
      kompFormulaHasil = '0',
      1,
      IFNULL(kompFormulaHasil, 1)
   ) AS nominal,
   IF(
      UPPER(rncnpengeluaranIsAprove) = 'YA',
      1,
      0
   ) * IF(
      kompIsPengadaan IS NULL
      OR UPPER(kompIsPengadaan) = 'T',
      0,
      rncnpengeluaranSatuanAprove
   ) * rncnpengeluaranKomponenNominalAprove * IF(
      kompFormulaHasil = '0',
      1,
      IFNULL(kompFormulaHasil, 1)
   ) AS nominalApprovePengadaan,
   IF(
      UPPER(rncnpengeluaranIsAprove) = 'YA',
      1,
      0
   ) * IF(
      kompIsPengadaan IS NULL
      OR UPPER(kompIsPengadaan) = 'T',
      rncnpengeluaranKomponenTotalAprove,0) * IF(
      kompFormulaHasil = '0',
      1,
      IFNULL(kompFormulaHasil, 1)
   ) AS nominalApproveNonPengadaan,
   IF(
      UPPER(rncnpengeluaranIsAprove) = 'YA',
      1,
      0
   ) * rncnpengeluaranKomponenTotalAprove * IF(
      kompFormulaHasil = '0',
      1,
      IFNULL(kompFormulaHasil, 1)
   ) AS nominalApprove
FROM (SELECT
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaNama AS unitNama,
   programId AS programId,
   programNomor AS programNomor,
   programNama AS programNama,
   subprogId AS kegiatanId,
   subprogNomor AS kegiatanNomor,
   subprogNama AS kegiatanNama,
   subprogJeniskegId AS jenigKegiatan,
   kegrefId AS subkegiatanId,
   kegrefNomor AS subkegiatanNomor,
   kegrefNama AS subkegiatanNama,
   kegdetId AS id,
   UPPER(IF(getSts.statusApproveBelum > 0 AND (getSts.statusApproveYa > 0  OR getSts.statusApproveTidak > 0 ),
    'Parsial',
    IF(getSts.statusApproveBelum > 0 AND (getSts.statusApproveYa = 0  OR getSts.statusApproveTidak = 0 ),'Belum',
    kegdetIsAprove)
   )) AS statusApprove,
   IF(kegdetDeskripsi IS NULL OR kegdetDeskripsi = '', '-', kegdetDeskripsi) AS deskripsi,
   jeniskegId AS jenisKegiatanId,
   jeniskegNama AS jenisKegiatanNama,
   kegdetRABFile,
   kegThanggarId,
   kegUnitkerjaId,
   MONTH(kegdetWaktuMulaiPelaksanaan) AS bulan
FROM kegiatan_detail
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN sub_program
      ON subprogId = kegrefSubprogId
   JOIN program_ref prog
      ON programId = subprogProgramId
   JOIN kegiatan
      ON kegId = kegdetKegId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   JOIN unit_kerja_ref
      ON unitkerjaId = kegUnitkerjaId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   LEFT JOIN (
        SELECT
         rpeng_sts.rncnpengeluaranKegdetId AS kdId,
         SUM(IF(rpeng_sts.rncnpengeluaranIsAprove ='Ya',1,0)) AS statusApproveYa,
         SUM(IF(rpeng_sts.rncnpengeluaranIsAprove ='Belum',1,0)) AS statusApproveBelum,
         SUM(IF(rpeng_sts.rncnpengeluaranIsAprove ='Tidak',1,0)) AS statusApproveTidak
      FROM
         rencana_pengeluaran rpeng_sts
      GROUP BY rpeng_sts.`rncnpengeluaranKegdetId`
   ) getSts ON getSts.kdId = kegdetId
WHERE 1 = 1
AND kegThanggarId = '%s'
AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
AND (jeniskegId = '%s' OR 1 = %s)
AND kegrefNomor LIKE '%s'
AND kegrefNama LIKE '%s'
AND (MONTH(kegdetWaktuMulaiPelaksanaan) = %s OR %s ) 
LIMIT %s, %s) AS rkat
   LEFT JOIN rencana_pengeluaran
      ON rncnpengeluaranKegdetId = rkat.id
   LEFT JOIN komponen
      ON kompKode = rncnpengeluaranKomponenKode
   LEFT JOIN (
      SELECT
         rncnpengeluaranKegdetId AS id,
         COUNT(rncnpengeluaranId) AS `count`
      FROM rencana_pengeluaran
      GROUP BY rncnpengeluaranKegdetId
   ) AS detail_belanja ON detail_belanja.id = rkat.id
   LEFT JOIN (
      SELECT kds_b.kegdetstatusId AS id,
      kds_b.`kegdetstatusKegdetId` AS kdId,
      kds_b.`kegdetstatusRncnpengeluaranId` AS rpengId,
      kds_b.`kegdetstatusLogAktifitas` AS keterangan,
      kds_b.`kegdetstatusTanggal` AS tanggal,
      kds_b.`kegdetstatusUserId` AS userId
   FROM(SELECT
      kegdetstatusRncnpengeluaranId AS rpengId,
      MAX(kegdetstatusLogAktifitas) AS kodeaksi
   FROM kegiatan_detail_status
   GROUP BY kegdetstatusRncnpengeluaranId) AS kds_a
   INNER JOIN kegiatan_detail_status AS kds_b 
   ON kds_a.rpengId = kds_b.kegdetstatusRncnpengeluaranId
   AND kds_a.kodeaksi = kds_b.kegdetstatusLogAktifitas) AS kdstatus
      ON kdstatus.kdId = rkat.id
      AND kdstatus.rpengId = rencana_pengeluaran.rncnpengeluaranId
   LEFT JOIN gtfw_user gu
   ON gu.UserId = kdstatus.userId
   LEFT JOIN coa
      ON coaId = kompCoaId
   LEFT JOIN finansi_ref_pagu_bas AS mak
      ON (mak.paguBasId = rncnpengeluaranMakId OR mak.paguBasId = kompMakId)
   LEFT JOIN finansi_ref_pagu_bas AS bas
      ON bas.paguBasId = mak.paguBasParentId
WHERE 1 = 1
AND (UPPER(IFNULL(kompIsPengadaan, 'T')) = '%s' OR 1 = %s)
ORDER BY 
   rkat.bulan ASC,
   rkat.programNomor,
   rkat.kegiatanNomor,
   rkat.subkegiatanNomor,
   rkat.id,
   kompKode
";

/**
 * @description get data resume
 */
$sql['get_data_resume']    = "
SELECT
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaNama AS unitNama,
   programId AS programId,
   programNomor AS programNomor,
   programNama AS programNama,
   SUM(IF(UPPER(IFNULL(kompIsPengadaan, 'T')) = 'T',
      0,
      rncnpengeluaranSatuan
   ) * rncnpengeluaranKomponenNominal * IF(
      kompFormulaHasil = '0',
      1,
      IFNULL(kompFormulaHasil, 1)
   )) AS nominalPengadaan,
   SUM(IF(UPPER(IFNULL(kompIsPengadaan, 'T')) = 'Y',
      0,
      rncnpengeluaranSatuan
   ) * rncnpengeluaranKomponenNominal * IF(
      kompFormulaHasil = '0',
      1,
      IFNULL(kompFormulaHasil, 1)
   )) AS nominalNonPengadaan,
   SUM(rncnpengeluaranSatuan * rncnpengeluaranKomponenNominal * IF(
      kompFormulaHasil = '0',
      1,
      IFNULL(kompFormulaHasil, 1)
   )) AS nominal,
   SUM(IF(
      UPPER(rncnpengeluaranIsAprove) = 'YA',
      1,
      0
   ) * IF(
      kompIsPengadaan IS NULL
      OR UPPER(kompIsPengadaan) = 'T',
      0,
      rncnpengeluaranSatuanAprove
   ) * rncnpengeluaranKomponenNominalAprove * IF(
      kompFormulaHasil = '0',
      1,
      IFNULL(kompFormulaHasil, 1)
   )) AS nominalApprovePengadaan,
   SUM(IF(
      UPPER(rncnpengeluaranIsAprove) = 'YA',
      1,
      0
   ) * IF(
      kompIsPengadaan IS NULL
      OR UPPER(kompIsPengadaan) = 'T',
      rncnpengeluaranKomponenTotalAprove,0) * IF(
      kompFormulaHasil = '0',
      1,
      IFNULL(kompFormulaHasil, 1)
   )) AS nominalApproveNonPengadaan,
   SUM(IF(
      UPPER(rncnpengeluaranIsAprove) = 'YA',
      1,
      0
   ) * rncnpengeluaranKomponenTotalAprove * IF(
      kompFormulaHasil = '0',
      1,
      IFNULL(kompFormulaHasil, 1)
   )) AS nominalApprove
FROM
   kegiatan_detail
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN sub_program
      ON subprogId = kegrefSubprogId
   JOIN program_ref prog
      ON programId = subprogProgramId
   JOIN kegiatan
      ON kegId = kegdetKegId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   JOIN unit_kerja_ref
      ON unitkerjaId = kegUnitkerjaId
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   LEFT JOIN rencana_pengeluaran
      ON rncnpengeluaranKegdetId = kegdetId
   LEFT JOIN komponen
      ON kompKode = rncnpengeluaranKomponenKode
   LEFT JOIN (
      SELECT
         rncnpengeluaranKegdetId AS id,
         COUNT(rncnpengeluaranId) AS `count`
      FROM rencana_pengeluaran
      GROUP BY rncnpengeluaranKegdetId
   ) AS detail_belanja ON detail_belanja.id = kegdetId
WHERE 1 = 1
AND kegThanggarId = '%s'
AND kegrefNomor LIKE '%s'
AND kegrefNama LIKE '%s'
AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
AND (jeniskegId = '%s' OR 1 = %s)
AND (UPPER(IFNULL(kompIsPengadaan, 'T')) = '%s' OR 1 = %s)
AND (MONTH(kegdetWaktuMulaiPelaksanaan) = %s OR %s ) 
GROUP BY programNomor
ORDER BY programNomor
";

$sql['get_sub_kegiatan_detail']     = "
SELECT
   programId,
   programNomor,
   programNama AS programNama,
   subprogId AS kegiatanId,
   subprogNomor AS kegiatanNomor,
   subprogNama AS kegiatanNama,
   kegrefId AS subKegiatanId,
   kegrefNomor AS subKegiatanNomor,
   kegrefNama AS subKegiatanNama,
   IF(kegdetDeskripsi IS NULL OR kegdetDeskripsi = '', '-', kegdetDeskripsi) AS deskripsi,
   IF(kegdetCatatan IS NULL OR kegdetCatatan = '', '-', kegdetCatatan) AS catatan,
   prioritasNama AS prioritasNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   kegdetId AS id,
   unitkerjaNamaPimpinan AS pimpinanNama,
   thanggarId AS taId,
   thanggarNama AS taNama
FROM
   kegiatan_detail
   LEFT JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   LEFT JOIN sub_program
      ON subprogId = kegrefSubprogId
   LEFT JOIN program_ref
      ON programId = subprogProgramId
   LEFT JOIN kegiatan
      ON kegId = kegdetKegId
   LEFT JOIN unit_kerja_ref
      ON unitkerjaId = kegUnitkerjaId
   LEFT JOIN prioritas_ref
      ON prioritasId = kegdetPrioritasId
   LEFT JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
WHERE 1 = 1
AND kegdetId = '%s'
LIMIT 0,1
";

$sql['get_data_detail_belanja']     = "
SELECT
   komponen.kompId AS id,
   IF(rncnpengeluaranId IS NOT NULL, komponen.kompId, NULL) AS dataId,
   IFNULL(komponen.kompKode, rncnpengeluaranKomponenKode) AS kode,
   LEFT(komponen.kompKode,2) AS basIdKomponen,
   IFNULL(komponen.kompNama, rncnpengeluaranKomponenNama) AS nama,
   IFNULL(rncnpengeluaranSatuan, 0) AS jumlah,
   IF(IFNULL(komponen.kompFormula, rncnpengeluaranFormula) IS NULL OR IFNULL(komponen.kompFormula, rncnpengeluaranFormula) = '', 1, IFNULL(komponen.kompFormula, rncnpengeluaranFormula)) AS formula,
   IF(komponen.kompFormulaHasil = '0' ,1, komponen.kompFormulaHasil) AS hasilFormula,
   IFNULL(komponen.kompNamaSatuan, rncnpengeluaranNamaSatuan) AS satuan,
   kompkegBiaya AS biayaMax,
   komponen.kompIsSBU AS isSbu,
   IFNULL(rncnpengeluaranKomponenNominal,kompkegBiaya) AS biaya,
   IF(IFNULL(komponen.kompDeskripsi,rncnpengeluaranKomponenDeskripsi) = '', '-', IFNULL(komponen.kompDeskripsi,rncnpengeluaranKomponenDeskripsi)) AS deskripsi,
   rncnpengeluaranId  AS rencanaPengeluaranId,
   bas.paguBasid AS basId,
   bas.paguBasKode AS basKode,
   bas.paguBasKeterangan AS basNama,
   mak.paguBasId AS makId,
   mak.paguBasKode AS makKode,
   mak.paguBasKeterangan AS makNama,
   coaId AS akunId,
   coaKodeAkun AS akunKode,
   coaNamaAkun AS akunNama,
   IFNULL(finansi_pa_komponen_unit_kerja.kompUnitNominal, 0) AS komponenNominal,
   IFNULL((IFNULL(rncnpengeluaranKomponenNominal,kompkegBiaya)  * rncnpengeluaranSatuan), 0) AS totalBiaya,
   komponen.kompSumberDanaId AS sumberDanaId,
   UPPER(IFNULL(rncnpengeluaranIsAprove, 'BELUM')) AS `status`,
   rncnpengeluaranSatuanAprove AS satuanApprove,
   rncnpengeluaranKomponenNominalAprove AS nominalApprove,
   rncnpengeluaranKomponenTotalAprove AS totalApprove
FROM kegiatan_detail
   LEFT JOIN kegiatan_ref
      ON kegdetKegrefId = kegrefId
   LEFT JOIN kegiatan
      ON kegId = kegdetKegId
   LEFT JOIN komponen_kegiatan
      ON kegrefId = kompkegKegrefId
   JOIN komponen
      ON kompId = kompkegKompId
   LEFT JOIN rencana_pengeluaran
      ON kegdetId = rncnpengeluaranKegdetId
      AND IF(komponen.kompId IS NULL, 1, komponen.kompKode = rncnpengeluaranKomponenKode)
   LEFT JOIN finansi_ref_pagu_bas AS mak
      ON (mak.paguBasId = rncnpengeluaranMakId OR mak.paguBasId = kompMakId)
   LEFT JOIN finansi_ref_pagu_bas AS bas
      ON bas.paguBasId = mak.paguBasParentId
   LEFT JOIN finansi_pa_komponen_unit_kerja
      ON finansi_pa_komponen_unit_kerja.kompUnitKompId = komponen.kompId
      AND finansi_pa_komponen_unit_kerja.kompUnitUnitKerjaId = kegUnitkerjaId
   JOIN unit_kerja_ref
      ON unit_kerja_ref.unitkerjaId = kegUnitkerjaId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   LEFT JOIN coa
      ON coaId = kompCoaId
WHERE 1 = 1
AND kegdetId = %s
";

$sql['get_nominal_total_pengeluaran']  = "
SELECT
   IFNULL(SUM(IFNULL(rncnpengeluaranSatuan, 0)*IFNULL(rncnpengeluaranKomponenNominal, kompkegBiaya)), 0) AS nominal
FROM kegiatan_detail
   LEFT JOIN kegiatan_ref
      ON kegdetKegrefId = kegrefId
   LEFT JOIN kegiatan
      ON kegId = kegdetKegId
   LEFT JOIN komponen_kegiatan
      ON kegrefId = kompkegKegrefId
   LEFT JOIN komponen
      ON kompId = kompkegKompId
   LEFT JOIN rencana_pengeluaran
      ON kegdetId = rncnpengeluaranKegdetId
      AND IF(komponen.kompId IS NULL, 1, komponen.kompKode = rncnpengeluaranKomponenKode)
   LEFT JOIN finansi_ref_pagu_bas
      ON (paguBasId = rncnpengeluaranMakId OR paguBasId = kompMakId)
   LEFT JOIN finansi_pa_komponen_unit_kerja
      ON finansi_pa_komponen_unit_kerja.kompUnitKompId = komponen.kompId
       AND kompUnitUnitKerjaId = kegUnitkerjaId
WHERE 1 = 1
   AND UPPER(rncnpengeluaranIsAprove) != 'TIDAK'
   AND rncnpengeluaranIsAprove IS NOT NULL
   AND kegUnitkerjaId = '%s'
   AND kegThanggarId = '%s'
   AND kegdetId != '%s'
";

$sql['get_nominal_rencana_penerimaan'] = "
SELECT
   CONVERT(IFNULL(SUM(
      IF(renterimaUnitkerjaId = 1,
         IF(renterimaAlokasiPusat > 0,((renterimaAlokasiPusat / 100) * renterimaTotalTerima),renterimaTotalTerima),
         IF(renterimaAlokasiUnit > 0,((renterimaAlokasiUnit / 100) * renterimaTotalTerima),renterimaTotalTerima)
      )
   ), 0), DECIMAL(20,2)) AS nominal
FROM
   rencana_penerimaan
WHERE 1 = 1
   AND renterimaRpstatusId = '2'
   AND renterimaUnitkerjaId = '%s'
   AND renterimaThanggarId = '%s'
";

$sql['do_add_rencana_pengeluaran_rutin']  = "
INSERT INTO rencana_pengeluaran SET rncnpengeluaranKegdetId = '%s',
rncnpengeluaranKomponenKode = '%s',
rncnpengeluaranKomponenNama = '%s',
rncnpengeluaranSatuan = '%s',
rncnpengeluaranNamaSatuan = '%s',
rncnpengeluaranKomponenNominal = '%s',
rncnpengeluaranKomponenTotal = '%s',
rncnpengeluaranKomponenDeskripsi = '%s',
rncnpengeluaranFormula = '%s',

rncnpengeluaranKomponenNominalAprove = '%s',
rncnpengeluaranSatuanAprove = '%s',
rncnpengeluaranKomponenTotalAprove = '%s',

rncnpengeluaranMakId = IF('%s' = '', NULL, %s),
rncnpengeluaranSumberDanaId = IF('%s' IS NULL OR '%s' = '', NULL, '%s'),
rncnpengeluaranTgl = NOW(),
rncnpengeluaranUserId = '%s',
rncnpengeluaranIsAprove  = '%s'
";

$sql['do_delete_rencana_pengeluaran_kegiatan']  = "
DELETE FROM rencana_pengeluaran WHERE rncnpengeluaranKegdetId = '%s'
";

$sql['do_insert_komponen_kegiatan']    = "
INSERT IGNORE INTO komponen_kegiatan
SET kompkegKompId = '%s',
   kompkegKegrefId = '%s',
   kompkegBiaya = '%s'
";
// ========================================= edited =================================== //
$sql['get_data_by_id']="
SELECT
  rp.rncnpengeluaranId AS id,
  rp.rncnpengeluaranKomponenKode AS kode,
  rp.rncnpengeluaranKomponenNama AS nama,
  rp.rncnpengeluaranSatuan AS jumlah,
  rp.rncnpengeluaranNamaSatuan AS satuan,
  rp.rncnpengeluaranKomponenNominal AS biaya,
  rp.rncnpengeluaranKomponenDeskripsi AS deskripsi,
  rp.rncnpengeluaranKegdetId AS kegiatandetail_id,
  kr.kegrefId AS subkegiatan_id,
  kr.kegrefNama AS subkegiatan_nama

FROM
  rencana_pengeluaran rp
  JOIN kegiatan_detail kd ON (kd.kegdetId=rp.rncnpengeluaranKegdetId)
  JOIN kegiatan_ref kr ON (kr.kegrefId=kd.kegdetKegrefId)

WHERE
   rp.rncnpengeluaranId=%s
LIMIT 1


";

$sql['get_data_komponen_add'] ="
    SELECT
       komp.kompId AS id,
       komp.kompKode AS kode,
       komp.kompNama AS nama,
      komp.kompDeskripsi AS deskripsi,
      komp.kompNamaSatuan AS satuan,
       kk.kompkegBiaya AS biaya
    FROM
       kegiatan_detail kd INNER JOIN
          (
           kegiatan_ref kr INNER JOIN
             (komponen_kegiatan kk INNER JOIN komponen komp ON komp.kompId = kk.kompkegKompId)
           ON kr.kegrefId =kk.kompkegKegrefId
          )
       ON kd.kegdetKegrefId = kr.kegrefId
    WHERE kd.kegdetId=%s

";

$sql['get_data_komponen_edit'] ="
    SELECT
       rp.rncnpengeluaranKomponenKode  AS kode,
       rp.rncnpengeluaranKomponenNama AS nama,
       rp.rncnpengeluaranSatuan AS jumlah,
      rp.rncnpengeluaranNamaSatuan AS satuan,
       rp.rncnpengeluaranKomponenNominal AS biaya,
      rp.rncnpengeluaranKomponenDeskripsi AS deskripsi,
      rp.rncnpengeluaranId  AS rencanapengeluaran_id
    FROM
       kegiatan_detail kd
       JOIN rencana_pengeluaran rp  ON (kd.kegdetId=rp.rncnpengeluaranKegdetId)

    WHERE rp.rncnpengeluaranKegdetId=%s;

";


$sql['get_data_komponen'] ="
select * FROM (
(
SELECT
   komp.kompId AS id,
   IFNULL(komp.kompKode,rp.rncnpengeluaranKomponenKode) AS kode,
   LEFT(komp.kompKode,2) AS bas_id,
   IFNULL(komp.kompNama,rp.rncnpengeluaranKomponenNama) AS nama,
   rp.rncnpengeluaranSatuan AS jumlah,
   IFNULL(komp.kompFormula,rp.rncnpengeluaranFormula) AS formula,
   IF(komp.kompFormulaHasil = '0' ,1, komp.kompFormulaHasil) AS hasil_formula,
   IFNULL(komp.kompNamaSatuan,rp.rncnpengeluaranNamaSatuan) AS satuan,
   kk.kompkegBiaya AS biaya_max,
   komp.kompIsSBU AS is_sbu,
   IFNULL(rp.rncnpengeluaranKomponenNominal,kk.kompkegBiaya) AS biaya,
   IFNULL(komp.kompDeskripsi,rp.rncnpengeluaranKomponenDeskripsi) AS deskripsi,
   rp.rncnpengeluaranId  AS rencanapengeluaran_id,
   m.paguBasId AS mak_id,
   m.paguBasKode AS mak_kode,
   m.paguBasKeterangan AS mak_nama,
   ku.kompNominal AS komp_nominal,
   (IFNULL(rp.rncnpengeluaranKomponenNominal,kk.kompkegBiaya)  * rp.rncnpengeluaranSatuan ) as total_biaya,
    komp.kompSumberDanaId AS sumber_dana_id
FROM
   kegiatan_detail kd
   LEFT JOIN kegiatan_ref kr ON kd.kegdetKegrefId = kr.kegrefId
   LEFT JOIN kegiatan ON kegiatan.kegId = kd.kegdetKegId
   LEFT JOIN komponen_kegiatan kk ON kr.kegrefId = kk.kompkegKegrefId
   LEFT JOIN komponen komp ON komp.kompId = kk.kompkegKompId
   LEFT JOIN rencana_pengeluaran rp ON kd.kegdetId = rp.rncnpengeluaranKegdetId AND IF(komp.kompId IS NULL, 1, komp.kompKode = rp.rncnpengeluaranKomponenKode)
   LEFT JOIN finansi_ref_pagu_bas m ON (m.paguBasId = rp.rncnpengeluaranMakId OR m.paguBasId=komp.kompMakId)
   RIGHT JOIN finansi_pa_komponen_unit_kerja AS ku ON
         (ku.kompId = komp.kompId AND ku.unitkerjaId = kegiatan.kegUnitkerjaId)
WHERE
   /*(komp.kompId IS NOT NULL OR rp.rncnpengeluaranId IS NOT NULL)*/
   komp.kompId IS NOT NULL
   AND rp.rncnpengeluaranId IS NULL
   AND kd.kegdetId = %s
)
UNION
(
    SELECT
      k.kompId AS id,
      rp.`rncnpengeluaranKomponenKode` AS kode,
       LEFT(k.kompKode, 2)  AS bas_id,
      rp.`rncnpengeluaranKomponenNama` AS nama,
      rp.rncnpengeluaranSatuan AS jumlah,
      rp.rncnpengeluaranFormula AS formula,
       k.kompFormulaHasil AS hasil_formula,
      rp.rncnpengeluaranNamaSatuan AS satuan,
      k.kompHargaSatuan AS biaya_max,
      k.kompIsSBU AS is_sbu,
      rp.rncnpengeluaranKomponenNominal AS biaya,
      rp.`rncnpengeluaranKomponenDeskripsi` AS deskripsi,
      rp.rncnpengeluaranId AS rencanapengeluaran_id,
      rp.`rncnpengeluaranMakId` AS mak_id,
      m.paguBasKode AS mak_kode,
      m.paguBasKeterangan AS mak_nama,
      '0.00' AS komp_nominal ,
      (rp.rncnpengeluaranKomponenNominal * rp.rncnpengeluaranSatuan ) as total_biaya,
       rp.`rncnpengeluaranSumberDanaId` AS sumber_dana_id
    FROM
      `rencana_pengeluaran` rp
      LEFT JOIN finansi_ref_pagu_bas m
        ON m.paguBasId = rp.rncnpengeluaranMakId
       LEFT JOIN komponen k ON k.kompKode =  rp.rncnpengeluaranKomponenKode
WHERE
rp.`rncnpengeluaranKegdetId`= %s
))
a
ORDER BY kode
";

/**
 * get jumlah komponen yang ada di rencana pengeluran
 * berdasarkan kegiatan detail
 */
$sql['get_count_detai_belanja']="
SELECT
COUNT(rpeng.`rncnpengeluaranId`) AS total
FROM rencana_pengeluaran rpeng
WHERE rpeng.`rncnpengeluaranKegdetId` = '%s'
";

$sql['get_jenis_kegiatan']="
   SELECT
      sp.subprogJeniskegId AS jenis_kegiatan
   FROM
      kegiatan_detail kd
      JOIN kegiatan k ON k.kegId=kd.kegdetKegId
      JOIN program_ref pr ON pr.programId = k.kegProgramId
      JOIN sub_program sp ON sp.subprogId = pr.programId
   WHERE kd.kegdetId=%s
   LIMIT 1
";


//== for combo box ==
$sql['get_data_ta']     = "
SELECT
   thanggarId AS id,
   thanggarNama AS name
FROM
  tahun_anggaran
ORDER BY
  thanggarNama ASC
";

$sql['get_data_satuan_komponen'] = "
SELECT
   satkompNama AS id,
   satkompNama AS name
FROM
  satuan_komponen
ORDER BY
  satkompNama ASC
";

$sql['get_ta_aktif']    = "
SELECT
   thanggarId AS id
FROM
  tahun_anggaran
WHERE
  thanggarIsAktif='Y'
LIMIT 1
";

$sql['get_unit_kerja']  = "
SELECT
   unitkerjaId AS unitkerja_id,
   unitkerjaKode AS unitkerja_kode,
   unitkerjaNama AS unitkerja_nama
FROM
  unit_kerja_ref
WHERE
  unitkerjaParentId LIKE %s AND
  unitkerjaNama LIKE %s
ORDER BY
  unitkerjaKode, UnitkerjaNama ASC
LIMIT %s, %s
";

$sql['get_count_unit_kerja']  = "
SELECT
   COUNT(unitkerjaId) AS total
FROM
  unit_kerja_ref
WHERE
  unitkerjaParentId LIKE %s AND
  unitkerjaNama LIKE %s
ORDER BY
  unitkerjaKode, UnitkerjaNama ASC
LIMIT 1
";

$sql['get_jenis_kegiatan']    = "
SELECT
   jeniskegId AS id,
   jeniskegNama AS name
FROM jenis_kegiatan_ref
ORDER BY jeniskegId ASC
";

//untuk cetak
$sql['get_data_cetak']        = "
SELECT
 (SELECT unitkerjaKode FROM unit_kerja_ref WHERE unitkerjaId=uk.unitkerjaId) AS unit_kode,
 (SELECT unitkerjaNama FROM unit_kerja_ref WHERE unitkerjaId=uk.unitkerjaId) AS unit_nama,
(SELECT unitkerjaNamaPimpinan FROM unit_kerja_ref WHERE unitkerjaId=uk.unitkerjaId) AS unit_pimpinan,
 uk.unitkerjaId AS subunit_id,
 uk.unitkerjaParentId AS subunit_parentid,
uk.unitkerjaNamaPimpinan AS subunit_pimpinan,
 uk.unitkerjaKode AS subunit_kode,
 uk.unitkerjaNama AS subunit_nama,
kd.kegdetDeskripsi AS kegiatandetail_deksripsi,


 IFNULL(/*CONCAT(
        CASE WHEN LENGTH(pr.programNomor) = 1 THEN CONCAT('0',pr.programNomor)
        WHEN LENGTH(pr.programNomor) = 2 THEN pr.programNomor END,'.',CASE WHEN LENGTH(sp.subprogNomor)= 1 THEN      CONCAT('0',sp.subprogNomor)
WHEN LENGTH(sp.subprogNomor)= 2 THEN subprogNomor END,'.',CASE WHEN LENGTH(kr.kegrefNomor) = 1 THEN CONCAT('0',kr.kegrefNomor)
WHEN LENGTH(kr.kegrefNomor) = 2 THEN kr.kegrefNomor END)*/kr.kegrefNomor,'')
 AS subkegiatan_kode,

 kr.kegrefNama AS subkegiatan_nama,
 kd.kegdetMasTUK AS masukan_tuk,
 kd.kegdetMasTk AS masukan_tk,
 kd.kegdetKelTUK AS keluaran_tuk,
 kd.kegdetKelTk AS keluaran_tk,

 IFNULL(/*CONCAT(
        CASE WHEN LENGTH(pr.programNomor) = 1 THEN CONCAT('0',pr.programNomor)
             WHEN LENGTH(pr.programNomor) = 2 THEN pr.programNomor END,
       '.',
       CASE WHEN LENGTH(sp.subprogNomor)= 1 THEN CONCAT('0',sp.subprogNomor)
       WHEN LENGTH(sp.subprogNomor)= 2 THEN sp.subprogNomor END,'.00')*/sp.subprogNomor,'')
 AS kegiatan_kode,

 sp.subprogNama AS kegiatan_nama,
 IF(kompIsLangsung=0,'Biaya Tidak Langsung','Biaya Langsung') AS IsBiayaLangsung,
 IF(kompIsTetap=0,'Biaya Variabel','Biaya Tetap')AS IsBiayaRelatif,

 /*CONCAT(CASE WHEN LENGTH(pr.programNomor) = 1 THEN CONCAT('0',pr.programNomor)
      WHEN LENGTH(pr.programNomor) = 2 THEN programNomor END,'.00.00')*/
pr.programNomor AS program_kode,

 pr.programNama AS program_nama,

IFNULL(CONCAT((SELECT unitkerjaKode FROM unit_kerja_ref WHERE unitkerjaId=uk.unitkerjaParentId),'.',uk.unitkerjaKode,'.',
        /*CASE WHEN LENGTH(pr.programNomor) = 1 THEN CONCAT('0',pr.programNomor)
        WHEN LENGTH(pr.programNomor) = 2 THEN pr.programNomor END*/pr.programNomor,'.',
        /*CASE WHEN LENGTH(sp.subprogNomor)= 1 THEN      CONCAT('0',sp.subprogNomor)
         WHEN LENGTH(sp.subprogNomor)= 2 THEN subprogNomor END*/sp.subprogNomor
         ,'.',/*CASE WHEN LENGTH(kr.kegrefNomor) = 1 THEN CONCAT('0',kr.kegrefNomor)
WHEN LENGTH(kr.kegrefNomor) = 2 THEN kr.kegrefNomor END*/kr.kegrefNomor),'')
 AS kode_mata_anggaran,

 ta.thanggarNama AS ta_nama,
 rp.rncnpengeluaranKomponenKode  AS kode,
 rp.rncnpengeluaranKomponenNama AS uraian,
 rp.rncnpengeluaranNamaSatuan AS satuan,
 IF (rp.rncnpengeluaranIsAprove = 'Ya', rp.rncnpengeluaranSatuanAprove, rp.rncnpengeluaranSatuan) AS nilai_satuan,
 IF (rp.rncnpengeluaranIsAprove = 'Ya', rncnpengeluaranKomponenNominalAprove, rp.rncnpengeluaranKomponenNominal) AS kuantitas,
 IF (rp.rncnpengeluaranIsAprove = 'Ya',(rp.rncnpengeluaranSatuanAprove * rp.rncnpengeluaranKomponenNominalAprove * IF(komponen.kompFormulaHasil = '0',1, komponen.kompFormulaHasil)), (rp.rncnpengeluaranSatuan * rp.rncnpengeluaranKomponenNominal * IF(komponen.kompFormulaHasil = '0',1,komponen.kompFormulaHasil))) AS jumlah
FROM
rencana_pengeluaran rp
JOIN kegiatan_detail kd  ON (kd.kegdetId=rp.rncnpengeluaranKegdetId)
JOIN kegiatan k ON k.kegId=kd.kegdetKegId
JOIN unit_kerja_ref uk ON uk.unitkerjaId  = k.kegUnitkerjaId
JOIN kegiatan_ref kr ON kr.kegrefId=kd.kegdetKegrefId
LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
JOIN sub_program sp ON sp.subprogId = kr.kegrefSubprogId
JOIN program_ref pr ON pr.programId = sp.subprogProgramId
JOIN tahun_anggaran ta ON ta.thanggarId = pr.programThanggarId
WHERE rp.rncnpengeluaranKegdetId=%s
";

$sql['get_data_cetak_approved'] ="
SELECT
   (SELECT unitkerjaKode FROM unit_kerja_ref WHERE unitkerjaId=uk.unitkerjaParentId) AS unit_kode,
   (SELECT unitkerjaNama FROM unit_kerja_ref WHERE unitkerjaId=uk.unitkerjaParentId) AS unit_nama,
   (SELECT unitkerjaNamaPimpinan FROM unit_kerja_ref WHERE unitkerjaId=uk.unitkerjaParentId) AS unit_pimpinan,
    uk.unitkerjaId AS subunit_id,
    uk.unitkerjaParentId AS subunit_parentid,
   uk.unitkerjaNamaPimpinan AS subunit_pimpinan,
    uk.unitkerjaKode AS subunit_kode,
    uk.unitkerjaNama AS subunit_nama,
   kd.kegdetDeskripsi AS kegiatandetail_deksripsi,


    IFNULL(CONCAT(
           CASE WHEN LENGTH(pr.programNomor) = 1 THEN CONCAT('0',pr.programNomor)
           WHEN LENGTH(pr.programNomor) = 2 THEN pr.programNomor END,'.',CASE WHEN LENGTH(sp.subprogNomor)= 1 THEN      CONCAT('0',sp.subprogNomor)
WHEN LENGTH(sp.subprogNomor)= 2 THEN subprogNomor END,'.',CASE WHEN LENGTH(kr.kegrefNomor) = 1 THEN CONCAT('0',kr.kegrefNomor)
WHEN LENGTH(kr.kegrefNomor) = 2 THEN kr.kegrefNomor END),'')
 AS subkegiatan_kode,
 kr.kegrefNama AS subkegiatan_nama,
 kd.kegdetMasTUK AS masukan_tuk,
 kd.kegdetMasTk AS masukan_tk,
 kd.kegdetKelTUK AS keluaran_tuk,
 kd.kegdetKelTk AS keluaran_tk,
 IFNULL(CONCAT(
        CASE WHEN LENGTH(pr.programNomor) = 1 THEN CONCAT('0',pr.programNomor)
             WHEN LENGTH(pr.programNomor) = 2 THEN pr.programNomor END,
       '.',
       CASE WHEN LENGTH(sp.subprogNomor)= 1 THEN CONCAT('0',sp.subprogNomor)
       WHEN LENGTH(sp.subprogNomor)= 2 THEN sp.subprogNomor END,'.00'),'')
 AS kegiatan_kode,

 sp.subprogNama AS kegiatan_nama,
 IF(kompIsLangsung=0,'BTL','BL') AS IsBiayaLangsung,
 IF(kompIsTetap=0,'BV','BT')AS IsBiayaRelatif,

 CONCAT(CASE WHEN LENGTH(pr.programNomor) = 1 THEN CONCAT('0',pr.programNomor)
      WHEN LENGTH(pr.programNomor) = 2 THEN programNomor END,'.00.00')
AS program_kode,

 pr.programNama AS program_nama,

IFNULL(CONCAT((SELECT unitkerjaKode FROM unit_kerja_ref WHERE unitkerjaId=uk.unitkerjaParentId),'.',uk.unitkerjaKode,'.',
        CASE WHEN LENGTH(pr.programNomor) = 1 THEN CONCAT('0',pr.programNomor)
        WHEN LENGTH(pr.programNomor) = 2 THEN pr.programNomor END,'.',CASE WHEN LENGTH(sp.subprogNomor)= 1 THEN      CONCAT('0',sp.subprogNomor)
WHEN LENGTH(sp.subprogNomor)= 2 THEN subprogNomor END,'.',CASE WHEN LENGTH(kr.kegrefNomor) = 1 THEN CONCAT('0',kr.kegrefNomor)
WHEN LENGTH(kr.kegrefNomor) = 2 THEN kr.kegrefNomor END),'')
 AS kode_mata_anggaran,

 ta.thanggarNama AS ta_nama,
 rp.rncnpengeluaranKomponenKode  AS kode,
 rp.rncnpengeluaranKomponenNama AS uraian,
 rp.rncnpengeluaranNamaSatuan AS satuan,
 rp.rncnpengeluaranSatuanAprove AS nilai_satuan,
 rp.rncnpengeluaranKomponenNominalAprove AS kuantitas,
 (rp.rncnpengeluaranSatuanAprove * rp.rncnpengeluaranKomponenNominalAprove) AS jumlah
FROM
 rencana_pengeluaran rp
 JOIN kegiatan_detail kd  ON (kd.kegdetId=rp.rncnpengeluaranKegdetId)
 JOIN kegiatan k ON k.kegId=kd.kegdetKegId
 JOIN unit_kerja_ref uk ON uk.unitkerjaId  = k.kegUnitkerjaId
 JOIN kegiatan_ref kr ON kr.kegrefId=kd.kegdetKegrefId
 LEFT JOIN komponen ON kompKode = rncnpengeluaranKomponenKode
 JOIN sub_program sp ON sp.subprogId = kr.kegrefSubprogId
 JOIN program_ref pr ON pr.programId = sp.subprogProgramId
 JOIN tahun_anggaran ta ON ta.thanggarId = pr.programThanggarId

WHERE rp.rncnpengeluaranKegdetId=%s
";
//===DO===

$sql['do_add']       = "
INSERT INTO rencana_pengeluaran
   (
   `rncnpengeluaranKegdetId`,
   `rncnpengeluaranKomponenKode`,
   `rncnpengeluaranKomponenNama`,
   `rncnpengeluaranSatuan`,
   `rncnpengeluaranNamaSatuan`,
   `rncnpengeluaranKomponenNominal`,
   `rncnpengeluaranKomponenTotal`,
   `rncnpengeluaranKomponenDeskripsi`,
   `rncnpengeluaranFormula`,
   `rncnpengeluaranKomponenNominalAprove`,
   `rncnpengeluaranSatuanAprove`,
   `rncnpengeluaranKomponenTotalAprove`,
   `rncnpengeluaranMakId`,
   `rncnpengeluaranSumberDanaId`,
   `rncnpengeluaranTgl`,
   `rncnpengeluaranUserId`)
VALUES
   ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',IFNULL(%s,NULL),NOW(),'%s')
";

$sql['do_delete']    = "
DELETE FROM `rencana_pengeluaran`
WHERE `rncnpengeluaranId`=%s
";

$sql['do_update_rutin']    = "
UPDATE `rencana_pengeluaran`
SET
   `rncnpengeluaranKegdetId` = '%s',
   `rncnpengeluaranKomponenKode` = '%s',
   `rncnpengeluaranKomponenNama` = '%s',
   `rncnpengeluaranSatuan` = '%s',
   `rncnpengeluaranNamaSatuan` = '%s',
   `rncnpengeluaranKomponenNominal` = '%s',
   `rncnpengeluaranKomponenTotal` = '%s',
   `rncnpengeluaranKomponenDeskripsi` = '%s',
   `rncnpengeluaranFormula` = '%s',
   `rncnpengeluaranKomponenNominalAprove` = '%s',
   `rncnpengeluaranSatuanAprove` = '%s',
   `rncnpengeluaranKomponenTotalAprove` = '%s',
   `rncnpengeluaranMakId` = '%s',
   `rncnpengeluaranTgl` = NOW() ,
   `rncnpengeluaranIsAprove` = 'Belum',
   `rncnpengeluaranSumberDanaId` = IFNULL(%s,NULL),
   `rncnpengeluaranUserId` ='%s'
WHERE `rncnpengeluaranId`=%s
";

$sql['do_update_non_rutin']="
UPDATE `rencana_pengeluaran`
SET
`rncnpengeluaranKomponenKode`=%s,
`rncnpengeluaranKomponenNama`=%s,
`rncnpengeluaranSatuan`=%s,
`rncnpengeluaranNamaSatuan`=%s,
`rncnpengeluaranKomponenNominal`=%s,
`rncnpengeluaranKomponenDeskripsi`=%s
WHERE
`rncnpengeluaranId`=%s
";

//cek pagu
$sql['get_posisi_rencana_pengeluaran_sekarang'] = "
SELECT
   paguBasKode AS bas,
   paguBasKeterangan AS bas_nama,
   SUM(rncnpengeluaranKomponenNominal * rncnpengeluaranSatuan) AS nominal_rencana
FROM rencana_pengeluaran
   JOIN kegiatan_detail ON rncnpengeluaranKegdetId = kegdetId
   JOIN kegiatan ON kegdetKegId = kegId
   JOIN komponen ON rncnpengeluaranKomponenKode = kompKode
   JOIN finansi_ref_pagu_bas ON paguBasId = (SELECT paguBasParentId FROM finansi_ref_pagu_bas WHERE paguBasId = kompMakId)
   JOIN finansi_pagu_anggaran_unit ON paguAnggUnitPaguBasId = paguBasId
WHERE
   paguBasKode = '%s' AND
   kegThanggarId = '%s' AND
   kegUnitkerjaId='%s'
GROUP BY
   paguBasKode
";

$sql['get_pagu']     = "
SELECT
   paguBasId as bas_id,
   paguBasKode AS bas,
   paguBasKeterangan as bas_nama,
   paguAnggUnitNominal AS nominal
FROM
   finansi_pagu_anggaran_unit
JOIN
   finansi_ref_pagu_bas ON paguBasId=paguAnggUnitPaguBasId
WHERE
   paguBasKode = '%s' AND
   paguAnggUnitUnitKerjaId = '%s' AND
   paguAnggUnitThAnggaranId ='%s'
";

$sql['get_bas_pengeluaran'] = "
SELECT DISTINCT
   paguBasKode AS bas_id
FROM
   kegiatan_detail kd
   LEFT JOIN kegiatan_ref kr ON kd.kegdetKegrefId = kr.kegrefId
   LEFT JOIN komponen_kegiatan kk ON kr.kegrefId = kk.kompkegKegrefId
   LEFT JOIN komponen komp ON komp.kompId = kk.kompkegKompId
   JOIN finansi_ref_pagu_bas ON paguBasId = (SELECT paguBasParentId FROM finansi_ref_pagu_bas WHERE paguBasId = kompMakId)
   LEFT JOIN rencana_pengeluaran rp ON kd.kegdetId = rp.rncnpengeluaranKegdetId AND IF(komp.kompId IS NULL, 1, komp.kompKode = rp.rncnpengeluaranKomponenKode)
WHERE
   (komp.kompId IS NOT NULL OR rp.rncnpengeluaranId IS NOT NULL)
   AND komp.kompMakId IS NOT NULL
   AND kd.kegdetId = '%s'
";

$sql['get_jumlah_pengeluaran_perkegiatan_per_bas'] = "
SELECT
   SUM(rncnpengeluaranKomponenNominal * rncnpengeluaranSatuan) AS jumlah_sekarang,
   paguBasKode AS bas_id
FROM rencana_pengeluaran
   JOIN kegiatan_detail ON rncnpengeluaranKegdetId = kegdetId
   JOIN kegiatan ON kegdetKegId = kegId
   JOIN komponen ON rncnpengeluaranKomponenKode = kompKode
   JOIN finansi_ref_pagu_bas ON paguBasId = (SELECT paguBasParentId FROM finansi_ref_pagu_bas WHERE paguBasId = kompMakId)
WHERE
   kegdetId = '%s'
GROUP BY paguBasKode
";

/**
 * untuk mendapatkan jumlah sub unit
 * @since 3 Januari 2012
 */
$sql['get_total_sub_unit_kerja']="
SELECT
   count(unitkerjaId) as total
FROM unit_kerja_ref
WHERE unitkerjaParentId = %s
";

/**
 * untuk mendapatkan komponen non rutin
 * @since 7 Februari 2012
 */
$sql['get_data_komponen_non_rutin']="
SELECT
   rp.rncnpengeluaranKomponenNama AS nama,
   rp.rncnpengeluaranSatuan AS jumlah,
   rp.rncnpengeluaranFormula AS formula,
   rp.rncnpengeluaranNamaSatuan AS satuan,
   rp.rncnpengeluaranKomponenNominal AS biaya,
   rp.rncnpengeluaranKomponenDeskripsi AS deskripsi,
   rp.rncnpengeluaranId  AS rencanapengeluaran_id
FROM
   kegiatan_detail kd
   LEFT JOIN kegiatan_ref kr ON kd.kegdetKegrefId = kr.kegrefId
   LEFT JOIN kegiatan ON kegiatan.kegId = kd.kegdetKegId
   LEFT JOIN rencana_pengeluaran rp ON kd.kegdetId = rp.rncnpengeluaranKegdetId
  WHERE
      kd.kegdetId = %s
";

/**
 * untuk mendapatkan nominal total rencana penerimaan yang telah di approve
 * @since 8 maret 2012
 */
$sql['get_max_rencana_penerimaan_approved']="
SELECT
   SUM(
   IF(`rencana_penerimaan`.`renterimaUnitkerjaId` = 1,
      ( IF(`rencana_penerimaan`.`renterimaAlokasiPusat` > 0,
         ((`rencana_penerimaan`.`renterimaAlokasiPusat`/100) * `rencana_penerimaan`.`renterimaTotalTerima`),
         `rencana_penerimaan`.`renterimaTotalTerima`
      ))

   ,
   IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
      ((`rencana_penerimaan`.`renterimaAlokasiUnit`/100) * `rencana_penerimaan`.`renterimaTotalTerima`),
      `rencana_penerimaan`.`renterimaTotalTerima`
      )
   )
   )
    AS max_nominal
FROM
   `rencana_penerimaan`
WHERE
   `rencana_penerimaan`.`renterimaRpstatusId` = '2'
   AND
   `rencana_penerimaan`.`renterimaUnitkerjaId` = '%s'
   AND
   `rencana_penerimaan`.`renterimaThanggarId` = '%s'
";

/**
 * untuk mendapatkan maksimum pengeluaran
 * @since 8 Maret 2012
 */
$sql['get_max_rencana_pengeluaran']="
SELECT
   SUM(rncnpengeluaranKomponenNominal * rncnpengeluaranSatuan) AS max_nominal
FROM
   rencana_pengeluaran
   LEFT JOIN kegiatan_detail ON  kegdetId = rncnpengeluaranKegdetId
   LEFT JOIN kegiatan ON kegId = kegdetKegId
   LEFT JOIN `unit_kerja_ref` ON `unit_kerja_ref`.`unitkerjaId` = `kegiatan`.`kegUnitkerjaId`
WHERE
    rncnpengeluaranIsAprove NOT IN('Tidak' )
    AND
   `unit_kerja_ref`.`unitkerjaId` = '%s'
   AND
   `kegiatan`.`kegThanggarId` = '%s'
";

/**
 * untuk mendapatkan jumlah pengeluaran komponen yang sedang di edit
 * @since 14 Maret 2012
 */
$sql['get_total_pengeluaran_komponen_edit']="
SELECT
   SUM(rp.rncnpengeluaranSatuan * (
   IFNULL(rp.rncnpengeluaranKomponenNominal,kk.kompkegBiaya))) AS max_nominal
FROM
   kegiatan_detail kd
   LEFT JOIN kegiatan_ref kr ON kd.kegdetKegrefId = kr.kegrefId
   LEFT JOIN kegiatan ON kegiatan.kegId = kd.kegdetKegId
   LEFT JOIN komponen_kegiatan kk ON kr.kegrefId = kk.kompkegKegrefId
   LEFT JOIN komponen komp ON komp.kompId = kk.kompkegKompId
   LEFT JOIN rencana_pengeluaran rp ON kd.kegdetId = rp.rncnpengeluaranKegdetId AND IF(komp.kompId IS NULL, 1, komp.kompKode = rp.rncnpengeluaranKomponenKode)
   LEFT JOIN finansi_ref_pagu_bas m ON (m.paguBasId = rp.rncnpengeluaranMakId OR m.paguBasId=komp.kompMakId)
   RIGHT JOIN finansi_pa_komponen_unit_kerja AS ku ON
         (ku.kompId = komp.kompId AND ku.unitkerjaId = kegiatan.kegUnitkerjaId)
WHERE
   (komp.kompId IS NOT NULL OR rp.rncnpengeluaranId IS NOT NULL)
   AND kd.kegdetId = '%s'
";

$sql['get_tipe_unit']="
SELECT
   `unitkerjaTipeunitId`  as tipe
FROM `unit_kerja_ref`
WHERE
   `unitkerjaId` = %s
";
?>