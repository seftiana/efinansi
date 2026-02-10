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

$sql['get_data_referensi_transaksi']   = "
SELECT
   SQL_CALC_FOUND_ROWS
    CASE
      WHEN `transTtId` = '1' THEN transid    
      WHEN `transTtId` = '5' THEN transid    
      WHEN `transTtId` = '10' THEN transid    
   END AS transaksiId,
   transId AS id,
   transReferensi AS nomorReferensi,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   IF(
      UPPER(instituteNama) IN ('PERBANAS INSTITUTE', 'UNIVERSITAS') OR
	   UPPER(yayasanNama) IN ('PERBANAS INSTITUTE', 'UNIVERSITAS')
   ,'Y','T') AS isInstitute,
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
   ttNamaTransaksi AS tipeNama,
   ttId AS tipeId,
   ttKodeTransaksi AS kodeTransaksi,
   ttKeterangan AS tipeKeterangan,
   ttNamaJurnal AS tipeJurnal,
   transNilai AS nominal,
   transTanggalEntri AS tanggal,
   transCatatan AS keterangan,
   transTtId AS transTtId,
   c.coaId AS coaId,
   c.coaKodeAkun AS coaKodeAkun,
   c.coaNamaAkun AS coaNamaAkun,
   c.coaIsDebetPositif AS coaIsDebetPositif
FROM
   transaksi
   JOIN transaksi_tipe_ref
      ON ttId = transTtId
   JOIN tahun_pembukuan_periode
      ON tppId = transTppId
   JOIN tahun_anggaran
      ON thanggarId = transThanggarId
   JOIN unit_kerja_ref
      ON unitkerjaId = transUnitkerjaId
   JOIN realisasi_penerimaan real_pen
      ON real_pen.`realterimaTransId` = transaksi.`transId`
   JOIN rencana_penerimaan ren_pen
      ON ren_pen.`renterimaId` =  real_pen.`realrenterimaId`
         AND ren_pen.`renterimaThanggarId` = transThanggarId
   JOIN `finansi_coa_map` kp_coa
       ON kp_coa.`kodeterimaId` =  ren_pen.`renterimaKodeterimaId`
   LEFT JOIN coa c
      ON c.`coaId` =  kp_coa.`coaId`
   LEFT JOIN (
      SELECT
         unitkerjaKodeSistem AS yayasanKode,
         unitkerjaNama AS yayasanNama
      FROM unit_kerja_ref
   ) AS yayasan ON SUBSTRING_INDEX(unitkerjaKodeSistem,'.',1) = yayasanKode
   LEFT JOIN (
      SELECT
         unitkerjaKodeSistem AS instituteKode,
         unitkerjaNama AS instituteNama
      FROM unit_kerja_ref
   ) AS institute ON SUBSTRING_INDEX(unitkerjaKodeSistem,'.',2) = instituteKode
WHERE 1 = 1
AND transIsJurnal = 'T'
AND (transTppId = %s OR 1 = %s)
/* AND (transThanggarId = %s OR 1 = %s) */
AND (MONTH(transTanggalEntri) = %s OR 1 = %s)
AND transReferensi LIKE '%s'
AND transTtId IN (1,5,10)
AND transTransjenId NOT IN(8,9)
HAVING transaksiId IS NOT NULL
ORDER BY transTanggalEntri ASC
LIMIT %s, %s
";


?>