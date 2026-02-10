<?php
/**
 * @package SQL-FILE
 */


$sql['get_tahun_anggaran_aktif_id'] ="
SELECT
  `thanggarId` AS `id`
FROM
  `tahun_anggaran`
WHERE
    thanggarIsAktif = 'Y'
";

$sql['get_tahun_anggaran'] ="
SELECT
  `thanggarId` AS `id`,
  `thanggarNama` AS `name`
FROM
  `tahun_anggaran`
WHERE
    thanggarIsAktif = 'Y' OR thanggarIsOpen = 'Y'
ORDER BY id DESC
";

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_tipe_unit']   = "
SELECT
   tipeunitId AS `id`,
   tipeunitNama AS `name`
FROM tipe_unit_kerja_ref
WHERE 1 = 1
ORDER BY tipeunitNama ASC
";

$sql['get_data_unit']   = "
SELECT
   SQL_CALC_FOUND_ROWS
   unitkerjaId AS id,
   unitkerjaKode AS kode,
   unitkerjaNama AS nama,
   unitkerjaNamaPimpinan AS namaPimpinan,
   unitkerjaKodeSistem AS kodeSistem,
   unitkerjaParentId AS parentId,
   tipeunitId AS tipeId,
   tipeunitNama AS tipeNama
FROM
   `unit_kerja_ref`
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
   ) AS tmp ON tmp.id = unitkerjaId
   LEFT JOIN tipe_unit_kerja_ref
      ON tipeunitId = unitkerjaTipeunitId
WHERE 1 = 1
AND unitkerjaKode LIKE '%s'
AND unitkerjaNama LIKE '%s'
AND (tipeunitId = '%s' OR 1 = %s)
AND SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s'
ORDER BY SUBSTRING_INDEX(tmp.kode, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 5), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 6), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 7), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 8), '.', -1)+0
LIMIT %s, %s
";

$sql['get_data_realisasi']    = "
SELECT
   SQL_CALC_FOUND_ROWS CONCAT_WS('|', kegdetId, pengrealId) AS id,
   kegdetId,
   pengrealId,
   pengrealId AS pId,
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   kegrefNomor AS kode,
   kegrefNama AS nama,
  `pengrealNomorPengajuan` AS no_pengajuan,
   SUM(pengrealNominalAprove) AS nominalApprove,
   IFNULL(spj.nominal, 0) AS nominalSpj,
   IFNULL(pencairan.realisasiNominal, 0) AS nominalRealisasi,
   SUM(pengrealNominalAprove) - IFNULL(pencairan.realisasiNominal, 0) AS sisaDana,
   spp.sppu AS sppu
FROM
   kegiatan_detail
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN kegiatan
      ON kegId = kegdetKegId
   JOIN pengajuan_realisasi
      ON pengrealKegdetId = kegdetId
   JOIN unit_kerja_ref
      ON unitkerjaid = kegUnitkerjaId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   LEFT JOIN(SELECT
      transdtspjKegdetId AS id,
      SUM(transNilai) AS nominal
   FROM transaksi_detail_spj
      JOIN transaksi
         ON transid = transdtspjTransId
   GROUP BY transdtspjKegdetId) AS spj ON spj.id = kegdetId
   LEFT JOIN (SELECT
      realisasi.kegiatanId AS kegId,
      realisasi.realisasiId AS realId,
      SUM(realisasi.nominal) AS realisasiNominal
   FROM (SELECT
      kegdetId AS kegiatanId,
      pengrealId AS realisasiId,
      SUM(IFNULL(transNilai,0)) AS nominal
   FROM transaksi
   JOIN transaksi_detail_anggaran
      ON transdtanggarTransId = transId
   JOIN kegiatan_detail
      ON kegdetId = transdtanggarKegdetId
   JOIN pengajuan_realisasi
      ON pengrealKegdetId = kegdetId
      AND pengrealid = transdtanggarPengrealId
   GROUP BY kegdetId, pengrealId
   UNION
   SELECT
      kegdetId AS kegiatanId,
      pengrealId AS realisasiId,
      SUM(IFNULL(transNilai,0)) AS nominal
   FROM transaksi
   JOIN transaksi_detail_pencairan
      ON transdtpencairanTransId = transid
   JOIN kegiatan_detail
      ON kegdetId = transdtpencairanKegdetId
   JOIN pengajuan_realisasi
      ON pengrealKegdetId = kegdetId
      AND pengrealid = transdtpencairanPengrealId
   GROUP BY kegdetId, pengrealId
   UNION
   SELECT
      kegdetId AS kegiatanId,
      pengrealId AS realisasiId,
      SUM(IFNULL(transNilai,0)) AS nominal
   FROM transaksi
   JOIN transaksi_detail_pengembalian
      ON transdtpengembalianTransId = transId
   JOIN kegiatan_detail
      ON kegdetid = transdtpengembalianKegdetId
   JOIN pengajuan_realisasi
      ON pengrealKegdetId = kegdetId
      AND pengrealid = transdtpengembalianPengrealId
   GROUP BY kegdetId, pengrealId
   UNION
   SELECT
      kegdetId AS kegiatanId,
      pengrealId AS realisasiId,
      SUM(IFNULL(transNilai,0)) AS nominal
   FROM transaksi
   JOIN transaksi_detail_realisasi
      ON transdtrealisasiTransId = transId
   JOIN kegiatan_detail
      ON kegdetid = transdtrealisasiKegdetId
   JOIN pengajuan_realisasi
      ON pengrealKegdetId = kegdetId
   GROUP BY kegdetId, pengrealId
   UNION
   SELECT
      kegdetId AS kegiatanId,
      pengrealId AS realisasiId,
      SUM(IFNULL(transNilai,0)) AS nominal
   FROM transaksi
   JOIN transaksi_detail_spj
      ON transdtspjTransId = transid
   JOIN kegiatan_detail
      ON kegdetId = transdtspjKegdetId
   JOIN pengajuan_realisasi
      ON pengrealKegdetId = kegdetId
   GROUP BY kegdetId, pengrealId) AS realisasi
   GROUP BY kegiatanId, realisasiId) AS pencairan ON pencairan.kegId = kegdetId
   AND pencairan.realId = pengrealId
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
WHERE 1 = 1
   AND thanggarId = '%s'
   AND (thanggarIsAktif = 'Y' OR thanggarIsOpen = 'Y')
   AND UPPER(pengrealIsApprove) = 'YA'
   AND UPPER(kegdetIsAprove) = 'YA'
   AND (kegUnitkerjaId = %s OR 1 = 0)
   AND pengrealNomorPengajuan LIKE '%s'
   -- AND kegrefNama LIKE '%s'
   and pengrealId not in(
      select
         pr.pengrealId as pengrealId  
      from
         finansi_pa_transaksi_bank tpb
      join finansi_pa_sppu sppu on
         sppu.sppuId = tpb.transaksiBankSppuId
      join finansi_pa_sppu_det sppu_det on
         sppu_det.sppuDetSppuId = sppu.sppuId
      join pengajuan_realisasi_detil pr_det on
         pr_det.pengrealdetId = sppu_det.sppuDetPengrealDetId
      join pengajuan_realisasi pr on
         pr.pengrealId = pr_det.pengrealdetPengRealId
      join kegiatan_detail kd on kd.kegdetId = pr.pengrealKegdetId 
      join kegiatan k on k.kegId = kd.kegdetKegId  
      where
         tpb.transaksiBankTipe = 'pengeluaran'
         and
         k.kegThanggarId = '%s'
      group by pr.pengrealId  
   ) 
GROUP BY pengrealId 
HAVING (sppu > 0 OR nominalApprove <= 500000  ) AND sisaDana > 0
ORDER BY kegdetId,
pengrealId,
kegrefNomor
LIMIT %s, %s
";


//untuk select detail belanja (komponen anggaran)

$sql['get_komponen_anggaran'] = "
SELECT
  peng_real.`pengrealId` AS pId, 
  rpeng.`rncnpengeluaranKegdetId` AS kegdetId,  
  peng_real_det.`pengrealdetId` AS pdId,
  rpeng.`rncnpengeluaranKomponenKode` AS kompKode,
  rpeng.`rncnpengeluaranKomponenNama` AS kompNama,
  peng_real_det.`pengrealdetDeskripsi` AS deskripsi,
  c.`coaId` AS coaId,
  c.`coaKodeAkun` AS coaKode,
  -- (peng_real_det.`pengrealdetNominalApprove` - SUM(IFNULL(trd_komp.`transdtpencairanKompBelanjaNominal`,0)))AS nominal,
  SUM(peng_real_det.`pengrealdetNominalApprove`) - IFNULL(pencairan.realisasiNominal, 0) AS nominal
FROM 
pengajuan_realisasi_detil peng_real_det 
JOIN rencana_pengeluaran rpeng 
  ON rpeng.`rncnpengeluaranId` = peng_real_det.`pengrealdetRncnpengeluaranId` 
JOIN pengajuan_realisasi peng_real
  ON peng_real.`pengrealId` = peng_real_det.`pengrealdetPengRealId`
LEFT JOIN komponen komp 
  ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode` 
LEFT JOIN coa c 
  ON c.`coaId` = komp.`kompCoaId` 
LEFT JOIN kegiatan_detail kd
  ON kd.`kegdetId` = rpeng.`rncnpengeluaranKegdetId`  
LEFT JOIN kegiatan k
  ON k.`kegId` = kd.`kegdetId`  
LEFT JOIN `transaksi_detail_pencairan_komponen_belanja` trd_komp
  ON trd_komp.`transdtpencairanKompBelanjaPengrealDetId` = peng_real_det.`pengrealdetId`  
  LEFT JOIN 
    (SELECT 
      realisasi.kegiatanId AS kegId,
      realisasi.realisasiId AS realId,
      SUM(realisasi.nominal) AS realisasiNominal 
    FROM
      (SELECT 
        kegdetId AS kegiatanId,
        pengrealId AS realisasiId,
        SUM(IFNULL(transNilai, 0)) AS nominal 
      FROM
        transaksi 
        JOIN transaksi_detail_anggaran 
          ON transdtanggarTransId = transId 
        JOIN kegiatan_detail 
          ON kegdetId = transdtanggarKegdetId 
        JOIN pengajuan_realisasi 
          ON pengrealKegdetId = kegdetId 
          AND pengrealid = transdtanggarPengrealId 
      GROUP BY kegdetId,
        pengrealId 
      UNION
      SELECT 
        kegdetId AS kegiatanId,
        pengrealId AS realisasiId,
        SUM(IFNULL(transNilai, 0)) AS nominal 
      FROM
        transaksi 
        JOIN transaksi_detail_pencairan 
          ON transdtpencairanTransId = transid 
        JOIN kegiatan_detail 
          ON kegdetId = transdtpencairanKegdetId 
        JOIN pengajuan_realisasi 
          ON pengrealKegdetId = kegdetId 
          AND pengrealid = transdtpencairanPengrealId 
      GROUP BY kegdetId,
        pengrealId 
      UNION
      SELECT 
        kegdetId AS kegiatanId,
        pengrealId AS realisasiId,
        SUM(IFNULL(transNilai, 0)) AS nominal 
      FROM
        transaksi 
        JOIN transaksi_detail_pengembalian 
          ON transdtpengembalianTransId = transId 
        JOIN kegiatan_detail 
          ON kegdetid = transdtpengembalianKegdetId 
        JOIN pengajuan_realisasi 
          ON pengrealKegdetId = kegdetId 
          AND pengrealid = transdtpengembalianPengrealId 
      GROUP BY kegdetId,
        pengrealId 
      UNION
      SELECT 
        kegdetId AS kegiatanId,
        pengrealId AS realisasiId,
        SUM(IFNULL(transNilai, 0)) AS nominal 
      FROM
        transaksi 
        JOIN transaksi_detail_realisasi 
          ON transdtrealisasiTransId = transId 
        JOIN kegiatan_detail 
          ON kegdetid = transdtrealisasiKegdetId 
        JOIN pengajuan_realisasi 
          ON pengrealKegdetId = kegdetId 
      GROUP BY kegdetId,
        pengrealId 
      UNION
      SELECT 
        kegdetId AS kegiatanId,
        pengrealId AS realisasiId,
        SUM(IFNULL(transNilai, 0)) AS nominal 
      FROM
        transaksi 
        JOIN transaksi_detail_spj 
          ON transdtspjTransId = transid 
        JOIN kegiatan_detail 
          ON kegdetId = transdtspjKegdetId 
        JOIN pengajuan_realisasi 
          ON pengrealKegdetId = kegdetId 
      GROUP BY kegdetId,
        pengrealId) AS realisasi 
    GROUP BY kegiatanId,
      realisasiId) AS pencairan 
    ON pencairan.kegId = kegdetId 
    AND pencairan.realId = pengrealId 
WHERE
k.`kegThanggarId` = '%s'
AND 
k.`kegUnitkerjaId` = %s
AND
peng_real.`pengrealIsApprove` = 'Ya'
AND 
peng_real_det.`pengrealdetNominalApprove` > 0
GROUP BY pdId
ORDER BY peng_real.`pengrealId` ASC
";

?>
