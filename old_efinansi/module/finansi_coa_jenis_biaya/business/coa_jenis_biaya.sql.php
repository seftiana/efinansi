<?php

$sql['add_coa_jenis_biaya'] = "
INSERT INTO `finansi_coa_jenis_biaya`
SET
    `coaJenisBiayaKodeId` = '%s', 
    `coaJenisBiayaNama` = '%s',
    `coaJenisBiayaPembayaranCoaId` = '%s',
    `coaJenisBiayaPembayaranDK` = '%s',
    `coaJenisBiayaPotonganCoaId` = '%s',
    `coaJenisBiayaPotonganDK` = '%s',
    `coaJenisBiayaDepositCoaId` = '%s',
    `coaJenisBiayaDepositDK` = '%s',
    `coaJenisBiayaPiutangCoaId` = '%s',
    `coaJenisBiayaPiutangDK` = '%s'
";

$sql['update_coa_jenis_biaya'] = "
UPDATE `finansi_coa_jenis_biaya`
SET
    `coaJenisBiayaKodeId` = '%s', 
    `coaJenisBiayaNama` = '%s',
    `coaJenisBiayaPembayaranCoaId` = '%s',
    `coaJenisBiayaPembayaranDK` = '%s',
    `coaJenisBiayaPotonganCoaId` = '%s',
    `coaJenisBiayaPotonganDK` = '%s',
    `coaJenisBiayaDepositCoaId` = '%s',
    `coaJenisBiayaDepositDK` = '%s',
    `coaJenisBiayaPiutangCoaId` = '%s',
    `coaJenisBiayaPiutangDK` = '%s'
WHERE `coaJenisBiayaId` = '%s'
";

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
";

//===GET===
$sql['get_count_data'] = "
SELECT 
      count(*) AS total
   FROM 
      jurnal_kode
	WHERE 
		(jurkodeNama LIKE '%s'  OR
      jurkodeKode LIKE '%s')
";

$sql['get_data'] = "
   SELECT 
      jurkodeId as id,
      jurkodeKode as kode,
      jurkodeNama as nama,
      jurkodeStatusAktif as status_aktif
   FROM 
      jurnal_kode
	WHERE 
		(jurkodeNama LIKE '%s'  OR
      jurkodeKode LIKE '%s')
   ORDER BY 
   		jurkodeStatusAktif, jurkodeKode
   LIMIT %s, %s";

$sql['get_data_by_id'] = "
SELECT
  cjb.`coaJenisBiayaId` AS id,
  cjb.`coaJenisBiayaKodeId` AS jenis_biaya_id,
  cjb.`coaJenisBiayaNama` AS jenis_biaya_nama,
  cjb.`coaJenisBiayaPembayaranCoaId` AS jenis_biaya_pembayaran_coa_id,
  cpem.`coaKodeAkun` AS jenis_biaya_pembayaran_coa_kode,
  cpem.`coaNamaAkun` AS jenis_biaya_pembayaran_coa_nama,
  cjb.`coaJenisBiayaPembayaranDK` AS jenis_biaya_pembayaran_coa_dk,
    
  cjb.`coaJenisBiayaPotonganCoaId` AS jenis_biaya_potongan_coa_id,
  cpot.`coaKodeAkun` AS jenis_biaya_potongan_coa_kode,
  cpot.`coaNamaAkun` AS jenis_biaya_potongan_coa_nama,
  cjb.`coaJenisBiayaPotonganDK` AS jenis_biaya_potongan_coa_dk,

  cjb.`coaJenisBiayaDepositCoaId` AS jenis_biaya_deposit_coa_id,
  cdep.`coaKodeAkun` AS jenis_biaya_deposit_coa_kode,
  cdep.`coaNamaAkun` AS jenis_biaya_deposit_coa_nama,    
  cjb.`coaJenisBiayaDepositDK` AS jenis_biaya_deposit_coa_dk,
  
  cjb.`coaJenisBiayaPiutangCoaId` AS jenis_biaya_piutang_coa_id,
  cpu.`coaKodeAkun` AS jenis_biaya_piutang_coa_kode,
  cpu.`coaNamaAkun` AS jenis_biaya_piutang_coa_nama,
  cjb.`coaJenisBiayaPiutangDK` AS jenis_biaya_piutang_coa_dk
FROM 
   `finansi_coa_jenis_biaya` cjb
   LEFT JOIN coa cpem ON cpem.`coaId` = cjb.`coaJenisBiayaPembayaranCoaId`
   LEFT JOIN coa cpot ON cpot.`coaId` = cjb.`coaJenisBiayaPotonganCoaId`
   LEFT JOIN coa cdep ON cdep.`coaId` = cjb.`coaJenisBiayaDepositCoaId`
   LEFT JOIN coa cpu ON cpu.`coaId` = cjb.`coaJenisBiayaPiutangCoaId`
WHERE
   cjb.`coaJenisBiayaId`='%s'
";

$sql['do_delete_data_by_id'] = "
DELETE
    FROM `finansi_coa_jenis_biaya`
WHERE 
    `coaJenisBiayaId` IN (%s)
";
?>