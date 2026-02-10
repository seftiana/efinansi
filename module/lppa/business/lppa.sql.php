<?php

/**
 * @package SQL-FILE
 */

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

$sql['get_count_list_lppa'] = "
SELECT FOUND_ROWS() AS total
";

$sql['get_lppa_by_id'] = "
SELECT
  lppa.`lapLppaId` AS lppa_id,
  lppa.`lapLppaTahunAnggaranId` AS tahun_anggaran_id,
  lppa.`lapLppaTahunAnggaranId` AS tahun_anggaran_id_old,
  ta.`thanggarNama` AS tahun_anggaran_nama,
  lppa.`lapLppaRealisasiId` AS realisasi_id,
  lppa.`lapLppaUnitKerjaId` AS unit_kerja_id,
  lppa.`lapLppaUnitKerjaId` AS unit_kerja_id_old,
  lppa.`lapLppaTanggal` AS tgl_lppa,
  lppa.`lapLppaFile` AS lppa_file,
  uk.`unitkerjaKode` AS unit_kerja_kode,
  uk.`unitkerjaNama` AS unit_kerja_nama,
  peng_real.`pengrealNomorPengajuan` AS realisasi_no,
  lppa.`lapLppaPenanggungJawab` AS penanggung_jawab,
  lppa.`lapLppaMengetahui` AS mengetahui,  
  lppa.`lapLppaUraian` AS uraian,
   IFNULL(lppa.`lapLppaIsApprove`,'B') AS is_approve,
   lppa.lapLppaNoBukti AS no_lppa
FROM 
`finansi_pa_lap_lppa` lppa
JOIN `unit_kerja_ref` uk
ON uk.`unitkerjaId` = lppa.`lapLppaUnitKerjaId`
JOIN pengajuan_realisasi peng_real
ON peng_real.`pengrealId` = lppa.`lapLppaRealisasiId`
JOIN tahun_anggaran ta
ON ta.`thanggarId` = lppa.`lapLppaTahunAnggaranId`
WHERE
lppa.`lapLppaId` = %s
";


$sql['get_list_lppa'] ="
SELECT
  SQL_CALC_FOUND_ROWS
  lppa.`lapLppaId` AS lppa_id,
  lppa.`lapLppaRealisasiId` AS realisasi_id,
  lppa.`lapLppaUnitKerjaId` AS unit_kerja_id,
  uk.`unitkerjaKode` AS unit_kerja_kode,
  uk.`unitkerjaNama` AS unit_kerja_nama,
  kref.`kegrefNama` AS sub_kegiatan_nama,  
  peng_real.`pengrealNomorPengajuan` AS no_pengajuan,
  lppa.`lapLppaTanggal` AS tanggal,
  lppa.`lapLppaNoBukti` AS no_bukti,
  lppa.`lapLppaUraian` AS uraian,
  lppa.`lapLppaPenanggungJawab` AS penanggung_jawab,
  lppa.`lapLppaMengetahui` AS mengetahui,
  IFNULL(lppa.`lapLppaIsApprove`,'B') AS is_approve,
  peng_real.`pengrealNominalAprove` AS nominal_approve,
  SUM(lppa_d.`lapLppaDetailNominal`) AS nominal_lppa,
  (peng_real.`pengrealNominalAprove`  - SUM(lppa_d.`lapLppaDetailNominal`)) AS sisa
FROM 
`finansi_pa_lap_lppa` lppa
JOIN `unit_kerja_ref` uk
ON uk.`unitkerjaId` = lppa.`lapLppaUnitKerjaId`
JOIN pengajuan_realisasi peng_real
ON peng_real.`pengrealId` = lppa.`lapLppaRealisasiId`
JOIN kegiatan_detail kd
ON kd.`kegdetId` = peng_real.`pengrealKegdetId`
JOIN kegiatan k
ON k.`kegId` = kd.`kegdetKegId`
JOIN kegiatan_ref kref
ON kref.`kegrefId` = kd.`kegdetKegrefId`
#JOIN transaksi_detail_pencairan tr_cair
#ON tr_cair.`transdtpencairanPengrealId` = peng_real.`pengrealId`
#JOIN transaksi tr
#ON tr.`transId` = tr_cair.`transdtpencairanTransId`
JOIN `pengajuan_realisasi_detil` peng_real_det 
ON peng_real_det.`pengrealdetPengRealId` = peng_real.`pengrealId`
JOIN `finansi_pa_lap_lppa_detail` lppa_d 
  ON lppa_d.`lapLppaDetailLppaId` = lppa.`lapLppaId` 
  AND lppa_d.`lapLppaDetailRealDetailId` = peng_real_det.`pengrealdetId` 
WHERE
1=1
AND k.`kegThanggarId` = %s
AND (
  SUBSTR(
    `unitkerjaKodeSistem`,
    1,
    (SELECT 
      LENGTH(
        CONCAT(`unitkerjaKodeSistem`, '.')
      ) 
    FROM
      unit_kerja_ref 
    WHERE `unitkerjaId` =  %s)
  ) = 
  (SELECT 
    CONCAT(`unitkerjaKodeSistem`, '.') 
  FROM
    unit_kerja_ref 
  WHERE `unitkerjaId` =  %s) 
  OR unitkerjaId =  %s
)  
GROUP BY lppa.`lapLppaId`
LIMIT  %s, %s
";

$sql['get_laporan_lppa'] ="
SELECT
    lppa.`lapLppaId` AS lppa_id,  
    c.`coaKodeAkun` AS kode_akun,
    c.`coaNamaAkun` AS nama_akun,
    peng_real.`pengrealNomorPengajuan` AS no_pengajuan,
    kref.`kegrefNama` AS sub_kegiatan,
    rpeng.`rncnpengeluaranKomponenNama` AS detail_pengeluaran,
    peng_real_detail.`pengrealdetDeskripsi` AS deskripsi,
    lppa_d.`lapLppaDetailDeskripsi` AS komponen_deskripsi,
    uk.`unitkerjaKode` AS unit_kerja_kode,
    uk.`unitkerjaNama` AS unit_kerja_nama,
    lppa.`lapLppaPenanggungJawab` AS penanggung_jawab,
    lppa.`lapLppaMengetahui` AS mengetahui,    
    lppa.`lapLppaTanggal` AS tgl_lppa,
    peng_real_detail.`pengrealdetNominalPencairan` AS nominal_pencairan,
    peng_real_detail.`pengrealdetNominalApprove` AS nominal_approve,     
    lppa_d.`lapLppaDetailNominal` AS nominal_lppa,
    (peng_real.`pengrealNominalAprove`) AS total_fpa,
    SUM(tr.`transNilai`) AS total_fpa_realisasi,
    ((peng_real.`pengrealNominalAprove`) - SUM(tr.`transNilai`)) AS sisa
FROM 
    `finansi_pa_lap_lppa` lppa
    JOIN `unit_kerja_ref` uk
        ON uk.`unitkerjaId` = lppa.`lapLppaUnitKerjaId`
    JOIN pengajuan_realisasi peng_real
        ON peng_real.`pengrealId` = lppa.`lapLppaRealisasiId`
    JOIN pengajuan_realisasi_detil peng_real_detail
        ON peng_real_detail.`pengrealdetPengRealId` = peng_real.`pengrealId`
    JOIN rencana_pengeluaran rpeng
        ON rpeng.`rncnpengeluaranId` = peng_real_detail.`pengrealdetRncnpengeluaranId`
    JOIN kegiatan_detail kd
        ON kd.`kegdetId` = peng_real.`pengrealKegdetId`
    JOIN kegiatan_ref kref
        ON kref.`kegrefId` = kd.`kegdetKegrefId`
    JOIN transaksi_detail_pencairan tr_cair
        ON tr_cair.`transdtpencairanPengrealId` = peng_real.`pengrealId`
    JOIN transaksi tr
        ON tr.`transId` = tr_cair.`transdtpencairanTransId`
    JOIN pembukuan_referensi pref
        ON pref.`prTransId` = tr.`transId`
    JOIN pembukuan_detail pdet
        ON pdet.`pdPrId` = pref.`prId`
    JOIN coa c
        ON c.`coaId` = pdet.`pdCoaId`
  JOIN `finansi_pa_lap_lppa_detail` lppa_d
    ON lppa_d.`lapLppaDetailLppaId` = lppa.`lapLppaId` AND lppa_d.`lapLppaDetailRealDetailId` = peng_real_detail.`pengrealdetId`            
WHERE
   lppa.`lapLppaId` = %s
   AND
   pdet.`pdStatus` = 'K'
GROUP BY rpeng.`rncnpengeluaranKomponenKode`     
ORDER BY pdet.`pdStatus` ASC
";

$sql['get_max_id']="
   SELECT
      MAX(lapLppaId) AS max_id
   FROM
      finansi_pa_lap_lppa
   LIMIT 0, 1
";

$sql['do_update_file'] = "
UPDATE finansi_pa_lap_lppa
SET lapLppaFile = '%s'
WHERE lapLppaId = %s
";

///
$sql['add_lppa'] = "
INSERT INTO `finansi_pa_lap_lppa` (
  `lapLppaNoBukti`,
  `lapLppaTahunAnggaranId`,
  `lapLppaRealisasiId`,
  `lapLppaUnitKerjaId`,
  `lapLppaTanggal`,
  `lapLppaUraian`,
  `lapLppaPenanggungJawab`,
  `lapLppaMengetahui`
) 
VALUES
  (
    '%s',
    '%s',
    '%s',
    '%s',
    NOW(),
    '%s',
    '%s',
    '%s'
  ) ;
";

$sql['add_lppa_detail'] = "
INSERT INTO `finansi_pa_lap_lppa_detail` (
  `lapLppaDetailLppaId`,
  `lapLppaDetailRealDetailId`,
  `lapLppaDetailNominal`,
  `lapLppaDetailDeskripsi`
) 
VALUES
  ('%s', '%s', '%s', '%s')
";

$sql['delete_lppa'] ="
DELETE
FROM `finansi_pa_lap_lppa`
WHERE `lapLppaId` = '%s'
";

$sql['delete_lppa_detail'] ="
DELETE FROM `finansi_pa_lap_lppa_detail` 
WHERE `lapLppaDetailLppaId` = '%s' 
";

$sql['get_total_lppa_detail'] = "
SELECT 
  COUNT(`lapLppaDetailId`) AS total 
FROM
  `finansi_pa_lap_lppa_detail` 
WHERE `lapLppaDetailLppaId` = %s
";

$sql['update_lppa'] = "
UPDATE `finansi_pa_lap_lppa`
SET 
  `lapLppaTahunAnggaranId` = '%s',
  `lapLppaRealisasiId` = '%s',
  `lapLppaUnitKerjaId` = '%s',
  `lapLppaTanggal` = NOW(),
  `lapLppaUraian` = '%s',
  `lapLppaPenanggungJawab` = '%s',
  `lapLppaMengetahui` = '%s'
WHERE `lapLppaId` = '%s';
";

$sql['get_range_tanggal']  = "
SELECT
   MIN(tahun_anggaran.`thanggarBuka`) AS tanggal_awal,
   MAX(tahun_anggaran.`thanggarTutup`) AS tanggal_akhir
FROM tahun_anggaran
";

$sql['get_periode_tahun']  = "
SELECT
   thanggarId AS id,
   thanggarNama AS `name`,
   thanggarIsAktif AS `active`,
   thanggarIsOpen AS `open`,
   renstraTanggalAwal AS `start`,
   renstraTanggalAkhir AS `end`
FROM tahun_anggaran
JOIN renstra
   ON renstraId = thanggarRenstraId
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
ORDER BY thanggarNama
";

?>