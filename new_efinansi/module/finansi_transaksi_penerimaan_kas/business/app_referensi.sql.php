<?php
/**
 * @package SQL-FILE
 */

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";


$sql['get_data_coa']    = "
SELECT SQL_CALC_FOUND_ROWS
   coaId AS id,
   coaKodeAkun AS kode,
   coaNamaAkun AS nama,
   IF(tmp.count IS NOT NULL, 'parent', 'child') AS `status`,
   tmp_coa.kodeSistem
FROM coa
LEFT JOIN (SELECT
   COUNT(coaId) AS `count`,
   coaParentAkun AS id
FROM coa
GROUP BY coaParentAkun
) AS tmp ON tmp.id = coaId
JOIN (SELECT
   coaId AS id,
   CASE
      WHEN coaKodeSistem REGEXP '^([0-9]{1,3})$' THEN CONCAT(coaKodeSistem, '.0.0.0.0.0.0.0')
      WHEN coaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(coaKodeSistem, '.0.0.0.0.0.0')
      WHEN coaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(coaKodeSistem, '.0.0.0.0.0')
      WHEN coaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(coaKodeSistem, '.0.0.0.0')
      WHEN coaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(coaKodeSistem, '.0.0.0')
      WHEN coaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(coaKodeSistem, '.0.0')
      WHEN coaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(coaKodeSistem, '.0')
      WHEN coaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN coaKodeSistem
   END AS kodeSistem
FROM coa) AS tmp_coa ON tmp_coa.id = coaId
WHERE 1 = 1
AND (tmp.count IS NULL OR tmp.count = 0)
AND coaKodeAkun LIKE '%s'
AND coaNamaAkun LIKE '%s'
ORDER BY SUBSTRING_INDEX(tmp_coa.kodeSistem, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_coa.kodeSistem, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_coa.kodeSistem, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_coa.kodeSistem, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_coa.kodeSistem, '.', 5), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_coa.kodeSistem, '.', 6), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_coa.kodeSistem, '.', 7), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_coa.kodeSistem, '.', 8), '.', -1)+0
LIMIT %s, %s
";


$sql['get_data_referensi_komponen'] = "
SELECT
  SQL_CALC_FOUND_ROWS
  `sppuId` AS id,
  `sppuTanggal` AS tanggal,
  `sppuNomor` AS kode,
  `sppuBPKBCr` AS bpkbCr,
  `sppuNomorBukti` AS nama,
  `sppuNominal` AS nominal
FROM `finansi_pa_sppu`
WHERE
`sppuIsTransaksiKas` ='Belum'
AND (`sppuTanggal` BETWEEN 
    (SELECT MIN(`thanggarBuka`) FROM `tahun_anggaran` WHERE `thanggarIsAktif`  = 'Y' OR `thanggarIsOpen` = 'Y')
AND 
    (SELECT MAX(`thanggarTutup`) FROM `tahun_anggaran` WHERE `thanggarIsAktif`  = 'Y' OR `thanggarIsOpen` = 'Y')
)
AND `sppuBankPayment` = 'Y'
AND `sppuCashPayment` = 'Y'
AND `sppuNomor` LIKE %s
LIMIT %s,%s
";

/*
$sql['get_sppu_id'] ="
SELECT 
    `sppuId` as id  
FROM `finansi_pa_sppu`
WHERE 
    `sppuIsTransaksiKas` ='Belum' 
    AND `sppuNomor` LIKE %s
    AND `sppuBankPayment` = 'Y'
    AND `sppuCashPayment` = 'Y'
    LIMIT %s,%s
";
*/

$sql['get_detail_sppu_item'] ="
SELECT
   SQL_CALC_FOUND_ROWS
   sppuId AS id,
   sppuDetId AS dId,    
   IFNULL(kompNama,'-') AS nama,
   IFNULL(pengrealKeterangan,'-') AS keterangan,
   (sppuDetNominal) AS nominal
FROM finansi_pa_sppu_det
   JOIN finansi_pa_sppu
      ON sppuId = sppuDetSppuId
   JOIN pengajuan_realisasi_detil
      ON pengrealdetId = sppuDetPengrealDetId
   JOIN pengajuan_realisasi
      ON pengrealId = pengrealdetPengrealId
   JOIN rencana_pengeluaran
      ON rncnpengeluaranId = pengrealdetRncnpengeluaranId
   LEFT JOIN komponen
      ON kompKode = rncnpengeluaranKomponenKode
WHERE
sppuId IN (%s)      
";

$sql['get_data_referensi_komponen_old'] = "
SELECT 
   SQL_CALC_FOUND_ROWS
   kompId AS id,
   kompKode AS kode,
   kompNama AS nama,
   coaId AS akunId,
   coaKodeAkun AS akunKode,
   coaNamaAkun AS akunNama,
   paguBasId AS makId,
   paguBasKode AS makKode,
   paguBasKeterangan AS makNama,
   kompHargaSatuan AS nominal
FROM komponen
LEFT JOIN finansi_ref_pagu_bas
   ON paguBasId = kompMakId
LEFT JOIN coa
   ON coaId = kompCoaId
WHERE 1 = 1
AND kompKode LIKE '%s'
AND kompNama LIKE '%s'
ORDER BY kompKode
LIMIT %s, %s
";

?>