<?php

$sql['set_tahun_pembukuan']   = "
SET @tahun_pembukuan    = ''
";

$sql['set_tahun_anggaran']    = "
SET @tahun_anggaran     = ''
";

$sql['do_set_tahun_pembukuan']   = "
SELECT
   tppId INTO @tahun_pembukuan
FROM tahun_pembukuan_periode
WHERE 1 = 1
AND tppIsBukaBuku = 'Y'
LIMIT 1
";

$sql['do_set_tahun_anggaran']    = "
SELECT
   thanggarId INTO @tahun_anggaran
FROM tahun_anggaran
WHERE 1 = 1
AND thanggarIsAktif = 'Y'
LIMIT 1
";

$sql['get_pembukuan_jurnal']  = "
SELECT
   bbId AS bb_id,
   prId AS pembukuan_id,
   transId AS transaksi_id,
   pdId AS pembukuan_detail_id,
   coaId AS akun_id,
   coaKodeAkun AS akun_kode,
   coaNamaAkun AS akun_nama,
   pdNilai AS nominal,
   IF(UPPER(pdStatus) = 'D', pdNilai, 0) AS  saldo_mutasi_d,
   IF(UPPER(pdStatus) = 'K', pdNilai, 0) AS saldo_mutasi_k,
   IF(
      UPPER(`coaKelompokNama`) = 'AKTIVA' AND `coaIsDebetPositif` = 0,
      IF(`coaIsDebetPositif` = 1, IF(UPPER(pdStatus) = 'D', pdNilai, pdNilai * -1), IF(UPPER(pdStatus) = 'K', pdNilai, pdNilai * -1))*-1,
      (IF(`coaIsDebetPositif` = 1, IF(UPPER(pdStatus) = 'D', pdNilai, pdNilai * -1), IF(UPPER(pdStatus) = 'K', pdNilai, pdNilai * -1)))
   ) AS saldo_mutasi_dk,
   pdStatus AS `status`,
   coaCoaKelompokId AS akun_kelompok_id,
   UPPER(coaKelompokNama) AS akun_kelompok,
   coaIsDebetPositif AS status_debet,
   CONCAT_WS('-', subaccPertamaKode,
   subaccKeduaKode,
   subaccKetigaKode,
   subaccKeempatKode,
   subaccKelimaKode,
   subaccKeenamKode,
   subaccKetujuhKode) AS sub_account
FROM pembukuan_detail
JOIN pembukuan_referensi
   ON prId = pdPrId
JOIN transaksi
   ON transId = prTransId
JOIN coa
   ON coaId = pdCoaId
LEFT JOIN coa_kelompok
   ON coaKelompokId = coaCoaKelompokId
LEFT JOIN buku_besar
   ON bbTppId = transTppId
   AND bbCoaId = pdCoaId
   AND pdSubaccPertamaKode = bbSubaccPertamaKode
   AND pdSubaccKeduaKode = bbSubaccKeduaKode
   AND pdSubaccKetigaKode = bbSubaccKetigaKode
   AND pdSubaccKeempatKode = bbSubaccKeempatKode
   AND pdSubaccKelimaKode = bbSubaccKelimaKode
   AND pdSubaccKeenamKode = bbSubaccKeenamKode
   AND pdSubaccKetujuhKode = bbSubaccKetujuhKode
LEFT JOIN finansi_keu_ref_subacc_1
   ON subaccPertamaKode = pdSubaccPertamaKode
LEFT JOIN finansi_keu_ref_subacc_2
   ON subaccKeduaKode = pdSubaccKeduaKode
LEFT JOIN finansi_keu_ref_subacc_3
   ON subaccKetigaKode = pdSubaccKetigaKode
LEFT JOIN finansi_keu_ref_subacc_4
   ON subaccKeempatKode = pdSubaccKeempatKode
LEFT JOIN finansi_keu_ref_subacc_5
   ON subaccKelimaKode = pdSubaccKelimaKode
LEFT JOIN finansi_keu_ref_subacc_6
   ON subaccKeenamKode = pdSubaccKeenamKode
LEFT JOIN finansi_keu_ref_subacc_7
   ON subaccKetujuhKode = pdSubaccKetujuhKode
WHERE 1 = 1
AND prIsApproved = 'Y'
AND prIsPosting = 'T'
AND transTppId = @tahun_pembukuan
AND (transTanggalEntri BETWEEN '%s' AND '%s')
HAVING (sub_account LIKE '%s' OR 1 = %s)
ORDER BY coaId ASC, prTanggal DESC
";
?>