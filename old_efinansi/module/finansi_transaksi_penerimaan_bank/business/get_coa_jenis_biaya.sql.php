<?php

$sql['get_coa_jenis_biaya'] ="
SELECT
  cjb.`coaJenisBiayaId` AS id,
  cjb.`coaJenisBiayaKodeId` AS jb_id,
  cjb.`coaJenisBiayaNama` AS jb_nama,
  cjb.`coaJenisBiayaPembayaranCoaId` AS jb_pembayaran_coa_id,
  cpem.`coaKodeAkun` AS jb_pembayaran_coa_kode,
  cpem.`coaNamaAkun` AS jb_pembayaran_coa_nama,
  cjb.`coaJenisBiayaPembayaranDK` AS jb_pembayaran_dk,
    
  cjb.`coaJenisBiayaPotonganCoaId` AS jb_potongan_coa_id,
  cpot.`coaKodeAkun` AS jb_potongan_coa_kode,
  cpot.`coaNamaAkun` AS jb_potongan_coa_nama,
  cjb.`coaJenisBiayaPotonganDK` AS jb_potongan_dk,

  cjb.`coaJenisBiayaDepositCoaId` AS jb_deposit_coa_id,
  cdep.`coaKodeAkun` AS jb_deposit_coa_kode,
  cdep.`coaNamaAkun` AS jb_deposit_coa_nama,    
  cjb.`coaJenisBiayaDepositDK` AS jb_deposit_dk,
  
  cjb.`coaJenisBiayaPiutangCoaId` AS jb_piutang_coa_id,
  cpu.`coaKodeAkun` AS jb_piutang_coa_kode,
  cpu.`coaNamaAkun` AS jb_piutang_coa_nama,
  cjb.`coaJenisBiayaPiutangDK` AS jb_piutang_dk
FROM 
   `finansi_coa_jenis_biaya` cjb
   LEFT JOIN coa cpem ON cpem.`coaId` = cjb.`coaJenisBiayaPembayaranCoaId`
   LEFT JOIN coa cpot ON cpot.`coaId` = cjb.`coaJenisBiayaPotonganCoaId`
   LEFT JOIN coa cdep ON cdep.`coaId` = cjb.`coaJenisBiayaDepositCoaId`
   LEFT JOIN coa cpu ON cpu.`coaId` = cjb.`coaJenisBiayaPiutangCoaId`
WHERE
  cjb.`coaJenisBiayaKodeId` IN (%s)   
";


$sql['get_coa_deposit_masuk'] ="
SELECT
   c.`coaId` AS coa_id,
   c.`coaKodeAkun` AS coa_kode,
   c.`coaNamaAkun` AS coa_nama,
   c.`coaIsDebetPositif` AS coa_is_d_pos,
   IF(c.`coaIsDebetPositif`= 0,'K','D') AS dk
FROM
    coa c
WHERE 
    c.`coaIsDepMasuk` = 1
LIMIT 1
";