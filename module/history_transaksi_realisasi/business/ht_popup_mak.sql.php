<?php
/**
 * @package SQL-FILE
 */
$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
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
   AND thanggarIsAktif = 'Y'
   AND UPPER(pengrealIsApprove) = 'YA'
   AND UPPER(kegdetIsAprove) = 'YA'
   AND pengrealNomorPengajuan LIKE '%s'
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
  (peng_real_det.`pengrealdetNominalApprove` - SUM(trd_komp.`transdtpencairanKompBelanjaNominal`))AS nominal
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
WHERE
k.`kegThanggarId` = (SELECT ta.`thanggarId` FROM tahun_anggaran ta WHERE ta.`thanggarIsAktif` = 'Y')
AND
peng_real.`pengrealIsApprove` = 'Ya'
AND 
peng_real_det.`pengrealdetNominalApprove` > 0
GROUP BY pdId
ORDER BY peng_real.`pengrealId` ASC
";

?>