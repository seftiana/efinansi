<?php
/**
 * @package SQL-FILE
 */
$sql['get_tahun_pembukuan']   = "
SELECT
   tppId AS id,
   tppTanggalAwal AS tanggalAwal,
   tppTanggalAkhir AS tanggalAkhir
FROM tahun_pembukuan_periode
WHERE 1 = 1
AND (tppIsBukaBuku = 'Y' OR 1 = %s)
";

$sql['get_tahun_anggaran']    = "
SELECT
   thanggarId AS id,
   thanggarNama AS `name`
FROM tahun_anggaran
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
";

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

$sql['get_data_referensi_transaksi']   = "
SELECT
   SQL_CALC_FOUND_ROWS CASE
      WHEN transdtanggarTransId IS NOT NULL THEN transId
      WHEN transdtpencairanTransId IS NOT NULL THEN transId
      WHEN transdtrealisasiTransId IS NOT NULL THEN transId
      WHEN transdtspjTransId IS NOT NULL THEN transid      
      WHEN `transTtId` = '2' THEN transid    
   END AS transaksiId,
   transId AS id,
   transReferensi AS nomorReferensi,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   tppId AS tpId,
   tppTanggalAwal AS tpAwal,
   tppTanggalAkhir AS tpAkhir,
   CONCAT_WS(
      '/',
      tppTanggalAwal,
      tppTanggalAkhir
   ) AS tpNama,
   thanggarId AS taId,
   thanggarNama AS taNama,
   transdtspjId AS spjId,
   transdtrealisasiId AS realisasiId,
   transdtanggarId AS anggaranId,
   ttNamaTransaksi AS tipeNama,
   ttId AS tipeId,
   ttKodeTransaksi AS kodeTransaksi,
   ttKeterangan AS tipeKeterangan,
   ttNamaJurnal AS tipeJurnal,
   transNilai AS nominal,
   transTanggal AS tanggal,
   transCatatan AS keterangan,
   transTtId AS transTtId
FROM
   transaksi
   LEFT JOIN transaksi_detail_anggaran
      ON transdtanggarTransId = transId
      AND transdtanggarPenerimaanId IS NULL
   LEFT JOIN transaksi_detail_pencairan
      ON transdtpencairanTransId = transId
   LEFT JOIN transaksi_detail_realisasi
      ON transdtrealisasiTransId = transId
   LEFT JOIN transaksi_detail_spj
      ON transdtspjTransId = transId
   JOIN transaksi_tipe_ref
      ON ttId = transTtId
   JOIN tahun_pembukuan_periode
      ON tppId = transTppId
   JOIN tahun_anggaran
      ON thanggarId = transThanggarId
   JOIN unit_kerja_ref
      ON unitkerjaId = transUnitkerjaId
WHERE 1 = 1
AND transIsJurnal = 'T'
AND (transTppId = %s OR 1 = %s)
AND (transThanggarId = %s OR 1 = %s)
AND transReferensi LIKE '%s'
HAVING transaksiId IS NOT NULL
ORDER BY transTanggal DESC,
   SUBSTRING_INDEX(SUBSTRING_INDEX(transReferensi, '/', 2), '/', -1)+0 DESC,
   SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(transReferensi, '/', 4),'/', -1), '.', -1)+0 DESC,
   SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(transReferensi, '/', 4),'/', -1), '.', 1)+0 DESC
LIMIT %s, %s
";

$sql['get_data_coa_sppu']    = "
SELECT 
  sppu.`sppuId` AS sppuId,
  c.`coaId` AS coaId,
  c.`coaKodeAkun` AS coaKode,
  c.`coaNamaAkun` AS coaNama,
  SUM(sppu_det.`sppuDetNominal`) AS nominal 
FROM
  `finansi_pa_sppu` sppu 
  JOIN finansi_pa_sppu_det sppu_det 
    ON sppu_det.`sppuDetSppuId` = sppu.`sppuId` 
  JOIN pengajuan_realisasi_detil peng_real_d 
    ON peng_real_d.`pengrealdetId` = sppu_det.`sppuDetPengrealDetId`
  JOIN rencana_pengeluaran rpeng 
    ON rpeng.`rncnpengeluaranId` = peng_real_d.`pengrealdetRncnpengeluaranId` 
  JOIN komponen komp 
    ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode` 
  JOIN coa c 
    ON c.`coaId` = komp.`kompCoaId` 
WHERE 
`sppuIsTransaksiKas` = 'Belum' 
AND
 sppu.`sppuId`  NOT IN (
SELECT
  IFNULL(tbank.`transaksiBankSppuId`,0)
FROM 
`finansi_pa_transaksi_bank` tbank
WHERE 
tbank.`transaksiBankTipe` ='pengeluaran'
)
GROUP BY sppu.`sppuId`,c.`coaId`
ORDER BY sppu.`sppuId`,c.`coaKodeAkun`
";


$sql['get_coa_transaksi_pengeluaran'] = "
SELECT
  tr.`transId` AS transId,
  c.`coaId`  AS coaId,
  c.`coaKodeAkun` AS coaKode,
  c.`coaNamaAkun` AS coaNama,
  SUM(trdt_komp_b.`transdtpencairanKompBelanjaNominal`) AS nominal
  
FROM `transaksi_detail_pencairan_komponen_belanja` trdt_komp_b
   JOIN `transaksi_detail_pencairan` trdt_p
   ON trdt_p.`transdtpencairanId` = trdt_komp_b.`transdtpencairanKompBelanjaTransDtPencairanId`
   JOIN `transaksi` tr
   ON tr.`transId` = trdt_p.`transdtpencairanTransId`
   JOIN pengajuan_realisasi_detil peng_real_d
   ON peng_real_d.`pengrealdetId`= trdt_komp_b.`transdtpencairanKompBelanjaPengrealDetId`
   JOIN rencana_pengeluaran rpeng
   ON rpeng.`rncnpengeluaranId` = peng_real_d.`pengrealdetRncnpengeluaranId`
   AND peng_real_d.`pengrealdetPengRealId` = trdt_p.`transdtpencairanPengrealId`
   JOIN komponen komp
   ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
   LEFT JOIN coa c
   ON c.`coaId` = komp.`kompCoaId`
 WHERE 
 trdt_komp_b.`transdtpencairanKompBelanjaNominal` > 0
 GROUP BY tr.`transId`,c.`coaKodeAkun`
 ORDER BY tr.`transId`,c.`coaKodeAkun` ASC
";


$sql['get_coa_transaksi_pengeluaran_by_id'] = "
SELECT
  c.`coaId`  AS coaId,
  c.`coaKodeAkun` AS coaKode,
  c.`coaNamaAkun` AS coaNama,
  SUM(trdt_komp_b.`transdtpencairanKompBelanjaNominal`) AS nominal  
FROM `transaksi_detail_pencairan_komponen_belanja` trdt_komp_b
   JOIN `transaksi_detail_pencairan` trdt_p
   ON trdt_p.`transdtpencairanId` = trdt_komp_b.`transdtpencairanKompBelanjaTransDtPencairanId`
   JOIN `transaksi` tr
   ON tr.`transId` = trdt_p.`transdtpencairanTransId`
   JOIN pengajuan_realisasi_detil peng_real_d
   ON peng_real_d.`pengrealdetId`= trdt_komp_b.`transdtpencairanKompBelanjaPengrealDetId`
   JOIN rencana_pengeluaran rpeng
   ON rpeng.`rncnpengeluaranId` = peng_real_d.`pengrealdetRncnpengeluaranId`
   AND peng_real_d.`pengrealdetPengRealId` = trdt_p.`transdtpencairanPengrealId`
   JOIN komponen komp
   ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
   LEFT JOIN coa c
   ON c.`coaId` = komp.`kompCoaId`
 WHERE 
 tr.`transId` = %s
 AND
 trdt_komp_b.`transdtpencairanKompBelanjaNominal` > 0
 GROUP BY c.`coaId`,c.`coaKodeAkun`
 ORDER BY tr.`transId`,c.`coaKodeAkun` ASC
";


//query untuk data sppu
$sql['get_data_referensi_sppu'] = "
SELECT
  SQL_CALC_FOUND_ROWS
  `sppuId` AS id,
  `sppuTanggal` AS tanggal,
  `sppuNomor` AS kode,
  `sppuBPKBCr` AS bpkbCr,
  `sppuBPKBBp` AS bpkbBp,
  `sppuNomorBukti` AS nama,
  #`sppuNominal` AS nominal,
  sum(sppu_det.sppuDetNominal) as nominal,
  `sppuKeterangan` AS keterangan,
  transaksiBankId,
  transaksiBankBpkb AS bpkbBank,
  `transaksiBankTanggal` AS tanggalTbank,
  CONCAT(IF(LENGTH(  DAY(`transaksiBankTanggal`)) = 1,'0',''),  DAY(`transaksiBankTanggal`)) AS hari,
  CONCAT(IF(LENGTH(MONTH(`transaksiBankTanggal`)) = 1,'0',''),MONTH(`transaksiBankTanggal`)) AS bulan,
  YEAR(`transaksiBankTanggal`) AS tahun
FROM `finansi_pa_sppu`
JOIN finansi_pa_transaksi_bank bank ON transaksiBankSppuId = sppuId
join finansi_pa_sppu_det sppu_det on sppu_det.sppuDetSppuId = sppuId
join pengajuan_realisasi_detil prd on prd.pengrealdetId = sppu_det.sppuDetPengrealDetId 
join pengajuan_realisasi pr on pr.pengrealId = prd.pengrealdetPengRealId 
WHERE
`sppuIsTransaksiKas` ='Belum'
AND (`sppuTanggal` BETWEEN 
    (SELECT MIN(`thanggarBuka`) FROM `tahun_anggaran` WHERE `thanggarIsAktif`  = 'Y' OR `thanggarIsOpen` = 'Y')
AND 
    (SELECT MAX(`thanggarTutup`) FROM `tahun_anggaran` WHERE `thanggarIsAktif`  = 'Y' OR `thanggarIsOpen` = 'Y')
)
AND
 `transaksiBankBpkb` LIKE %s
AND 
`sppuId`  IN (
      SELECT
         IFNULL(tbank.`transaksiBankSppuId`,0)
      FROM 
         `finansi_pa_transaksi_bank` tbank
      WHERE 
         tbank.`transaksiBankTipe` ='pengeluaran'
   )
AND 
 `transaksiBankId` NOT IN (
   SELECT
      IFNULL(tbankdetil.`transaksiBankDetilTransaksiBankId`,0)
   FROM 
      `finansi_pa_transaksi_bank_detil` tbankdetil
) 
AND 
pr.pengrealId not in (
	select 
		tdp.transdtpencairanPengrealId 
	from transaksi_detail_pencairan tdp 
	join transaksi tr on tr.transId = tdp.transdtpencairanTransId 
	where 
		tr.transTanggalEntri between  
			(SELECT MIN(`thanggarBuka`) FROM `tahun_anggaran` WHERE `thanggarIsAktif`  = 'Y' OR `thanggarIsOpen` = 'Y')
		and
		  (SELECT MAX(`thanggarTutup`) FROM `tahun_anggaran` WHERE `thanggarIsAktif`  = 'Y' OR `thanggarIsOpen` = 'Y')
)
AND `sppuBankPayment` = 'Y'
AND `sppuCashPayment` = 'T'
group by sppuId
LIMIT %s,%s
";


?>