<?php

/**
 * @package SQL-FILE
 */

$sql['get_nominal_belanja_per_bulan'] = "
SELECT
  CONCAT(YEAR(kegdetWaktuMulaiPelaksanaan),'-',MONTH(kegdetWaktuMulaiPelaksanaan)) AS bulan,
  kegProgramId AS programId,
  subprogId AS kegiatanId,
  kegrefId AS subKegiatanId,
  `kegdetId` AS kegiatanDetailId,
  rencana_pengeluaran.`rncnpengeluaranKomponenKode` AS detailBelanjaKode,
  SUM(IF(rencana_pengeluaran.`rncnpengeluaranIsAprove` ='Ya',
  (rencana_pengeluaran.rncnpengeluaranKomponenTotalAprove * 
    IF(kompFormulaHasil = '0' OR kompFormulaHasil IS NULL, 1, kompFormulaHasil)
  ),
  (rencana_pengeluaran.rncnpengeluaranSatuan * 
    rencana_pengeluaran.rncnpengeluaranKomponenNominal * 
    IF(kompFormulaHasil = '0' OR kompFormulaHasil IS NULL, 1, kompFormulaHasil)
  )
  )) AS detailBelanjaNominal 
FROM
  rencana_pengeluaran 
  JOIN kegiatan_detail 
    ON kegiatan_detail.`kegdetId` = rencana_pengeluaran.`rncnpengeluaranKegdetId` 
  JOIN kegiatan 
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
  LEFT JOIN komponen 
    ON kompKode = rncnpengeluaranKomponenKode 
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
    AND (rencana_pengeluaran.`rncnpengeluaranIsAprove` = '%s' OR %s)
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

$sql['get_nominal_belanja_per_bulan_x'] = "
SELECT  
   CONCAT(YEAR(kegdetWaktuMulaiPelaksanaan),'-',MONTH(kegdetWaktuMulaiPelaksanaan)) AS bulan,
   SUM(ifnull(rencana_pengeluaran.nominal,0)) AS nominalSetelahRevisi
FROM
   kegiatan_detail
   LEFT JOIN kegiatan
      ON kegdetKegId = kegId
   LEFT JOIN kegiatan_ref
      ON kegdetKegrefId = kegrefId
   #left join komponen_kegiatan on kegrefId = kompkegKegrefId 
   #LEFT JOIN komponen ON kompkegKompId = kompId 
   LEFT JOIN sub_program
      ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref
      ON subprogProgramId = programId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   JOIN unit_kerja_ref
      ON kegUnitkerjaId = unitkerjaId 
   LEFT JOIN
      (SELECT kompNama,rncnpengeluaranKomponenKode,
         rncnpengeluaranKegdetId,
         SUM(rncnpengeluaranKomponenNominal * rncnpengeluaranSatuan * IF(kompFormulaHasil > 0, kompFormulaHasil, 1)) AS nominalUsulan,
         SUM(rncnpengeluaranKomponenNominalAprove * rncnpengeluaranSatuanAprove * IF(
            kompFormulaHasil > 0,
            kompFormulaHasil,
            1
         )) as nominalSetuju,       
         SUM(
            if(rncnpengeluaranIsAprove = 'Ya',
              (rncnpengeluaranKomponenTotalAprove * IF(kompFormulaHasil > 0,kompFormulaHasil,1)),#nominal setelah revisi
              (rncnpengeluaranKomponenNominal * rncnpengeluaranSatuan * IF(kompFormulaHasil > 0, kompFormulaHasil, 1))
            )
         ) as nominal
      FROM
         rencana_pengeluaran
         LEFT JOIN komponen
            ON kompKode = rncnpengeluaranKomponenKode
      WHERE (rncnpengeluaranIsAprove = '%s' OR 1=%s)
      GROUP BY rncnpengeluaranKegdetId) AS rencana_pengeluaran
      ON rencana_pengeluaran.rncnpengeluaranKegdetId = kegdetId 
WHERE 1 = 1
   AND kegThanggarId = '%s'
   AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
   FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
   AND (programId = '%s' OR 1 = %s) 
GROUP BY  bulan 
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
   kegiatan_detail.`kegdetId` AS kegiatanDetailId,   
  rencana_pengeluaran.`rncnpengeluaranKomponenKode` AS detailBelanjaKode,
   rencana_pengeluaran.`rncnpengeluaranKomponenNama` AS detailBelanjaNama,   
   thanggarIsAktif AS taAktif,
   thanggarIsOpen AS taOpen,
   rencana_pengeluaran.`rncnpengeluaranKomponenKode` AS detailBelanjaKode, 
   IF(kegdetDeskripsi IS NULL OR kegdetDeskripsi = '','-',kegdetDeskripsi) AS deskripsi,
  -- SUM(rencana_pengeluaran.rncnpengeluaranKomponenTotalAprove * IF(
  --         kompFormulaHasil = '0' 
  --         OR kompFormulaHasil IS NULL,
  --         1,
  --         kompFormulaHasil
  --       )) AS detailBelanjaNominal

  SUM(IF(rencana_pengeluaran.`rncnpengeluaranIsAprove` ='Ya',
  (rencana_pengeluaran.rncnpengeluaranKomponenTotalAprove * 
    IF(kompFormulaHasil = '0' OR kompFormulaHasil IS NULL, 1, kompFormulaHasil)
  ),
  (rencana_pengeluaran.rncnpengeluaranSatuan * 
    rencana_pengeluaran.rncnpengeluaranKomponenNominal * 
    IF(kompFormulaHasil = '0' OR kompFormulaHasil IS NULL, 1, kompFormulaHasil)
  )
  )) AS detailBelanjaNominal 
FROM
   rencana_pengeluaran   
   JOIN kegiatan_detail 
      ON kegiatan_detail.`kegdetId` =  rencana_pengeluaran.`rncnpengeluaranKegdetId`
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
   LEFT JOIN komponen 
        ON kompKode = rncnpengeluaranKomponenKode 
   
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
   AND (rencana_pengeluaran.`rncnpengeluaranIsAprove` = '%s' OR %s)
GROUP BY programId,
subprogId, kegrefId, rencana_pengeluaran.`rncnpengeluaranKomponenKode`
ORDER BY programId, 
subprogId, kegrefId, rencana_pengeluaran.`rncnpengeluaranKomponenKode`
LIMIT %s, %s
"; 

$sql['get_data_realisasi']    = "
SELECT 
   CONCAT(YEAR(pengrealTanggal),'-',MONTH(pengrealTanggal)) AS bulan,   
   SUM(IFNULL(realisasi.nominal, transPengBank.trNilai )) AS nominalRealisasi    
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
   LEFT JOIN jenis_kegiatan_ref
      ON subprogJeniskegId = jeniskegId
   JOIN pengajuan_realisasi 
      ON pengrealKegdetId= kegdetId
   JOIN (
      SELECT
         kd.kegdetId as fa_id
      FROM rencana_pengeluaran rp 
         JOIN kegiatan_detail kd ON kd.kegdetId = rp.rncnpengeluaranKegdetId 
         JOIN kegiatan k ON k.kegId = kd.kegdetKegId 
      WHERE 
         (rp.rncnpengeluaranIsAprove  ='%s' OR 1=%s)
         AND
         k.kegThanggarId = %s
      GROUP BY fa_id
   ) as filter_approval on filter_approval.fa_id = kegdetId
   left JOIN(
      SELECT
         transdtanggarKegdetId AS id,
         pengrealId  as realisasiPengrealId,
         SUM(transNilai) AS nominal
      FROM transaksi
      JOIN transaksi_detail_anggaran
         ON transdtanggarTransId = transId
      JOIN pengajuan_realisasi pr
         ON pr.pengrealId = transdtanggarPengrealId
      WHERE 1 = 1
      GROUP BY transdtanggarKegdetId
   ) AS realisasi ON realisasi.id = kegdetId
   and  realisasi.realisasiPengrealId = pengrealId
   left JOIN (
      select 
         kd.kegdetId as trBankKegdetId,
         pr.pengrealId as trBankPengrealId,
         sum(pr_det.pengrealdetNominalApprove) as trNilai,  
         tpb.transaksiBankNominal as totalNominalBank,
         tpb.transaksiBankId AS transId, 
         tpb.transaksiBankBpkb AS trReferensi,
         tpb.transaksiBankTanggal trTanggal
      from
         finansi_pa_transaksi_bank tpb
         join finansi_pa_sppu sppu on sppu.sppuId = tpb.transaksiBankSppuId 
         join finansi_pa_sppu_det sppu_det on sppu_det.sppuDetSppuId = sppu.sppuId 
         join pengajuan_realisasi_detil pr_det on pr_det.pengrealdetId  = sppu_det.sppuDetPengrealDetId 
         join pengajuan_realisasi pr on pr.pengrealId = pr_det.pengrealdetPengRealId
         join kegiatan_detail kd on kd.kegdetId = pr.pengrealKegdetId          
         join rencana_pengeluaran rp on rp.rncnpengeluaranId = pr_det.pengrealdetRncnpengeluaranId  
      group by kd.kegdetId 
   ) transPengBank 
      on  transPengBank.trBankKegdetId = kegiatan_detail.`kegdetId`
      and transPengBank.trBankPengrealId = pengrealId
WHERE 1 = 1
   AND kegThanggarId = %s
   AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
   FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')      
   AND (programId = '%s' OR 1 = %s)
group by bulan
";

?>