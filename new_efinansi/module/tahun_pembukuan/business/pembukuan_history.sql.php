<?php

$sql['get_tpp_id_periode_sebelumnya'] ="
SELECT 
    `tppId` AS tpp_id
FROM 
    `tahun_pembukuan_periode`
WHERE 
    tppTanggalAkhir = DATE_SUB((
        SELECT `tppTanggalAwal` FROM `tahun_pembukuan_periode` WHERE `tppIsBukaBuku` = 'Y'
    ),INTERVAL 1 DAY)
";

$sql['get_tpp_id_periode_sebelumnya_by_id'] ="
SELECT 
    `tppId` AS tpp_id
FROM 
    `tahun_pembukuan_periode`
WHERE 
    tppTanggalAkhir = DATE_SUB((
        SELECT `tppTanggalAwal` FROM `tahun_pembukuan_periode` WHERE `tppId` = '%s'
    ),INTERVAL 1 DAY)
";

$sql['get_neraca_saldo_tahun_sebelumnya'] = "
SELECT
  tph.`tphCoaId` AS coa_id,
  SUM(tph.`tphSaldoAkhir`) AS saldo_akhir
FROM `tahun_pembukuan_hist` tph
WHERE
tphUnitkerjaId = %s
AND
tphTppId = %s
GROUP BY coa_id
ORDER BY coa_id
";

$sql['get_neraca_saldo_tahun_sebelumnya_by_coa_id'] = "
SELECT
  tph.`tphCoaId` AS coa_id,
  CONCAT_WS('-',
    tph.`tphSubaccPertamaKode`,
    tph.`tphSubaccPertamaKode`,
    tph.`tphSubaccPertamaKode`,
    tph.`tphSubaccPertamaKode`,
    tph.`tphSubaccPertamaKode`,
    tph.`tphSubaccPertamaKode`,
    tph.`tphSubaccPertamaKode`
  ) AS sub_acc,
  SUM(tph.`tphSaldoAkhir`) AS saldo_akhir
FROM `tahun_pembukuan_hist` tph
WHERE
tphUnitkerjaId = %s
AND
tphTppId = %s
AND
tph.`tphCoaId` = %s
";

$sql['get_range_periode_pembukuan'] ="
SELECT
  `tppTanggalAwal` AS tanggal_awal,
  MONTH(tppTanggalAwal) AS bulan_awal,
  YEAR(tppTanggalAwal) AS tahun_awal,
  `tppTanggalAkhir` AS tanggal_akhir,
  MONTH(tppTanggalAkhir) AS bulan_akhir,
  YEAR(tppTanggalAkhir) AS tahun_akhir
FROM `tahun_pembukuan_periode`
WHERE 
    `tppIsBukaBuku` = 'Y'
";

$sql['get_range_periode_pembukuan_tahun_sebelumnya'] ="
SELECT
  `tppTanggalAwal` AS tanggal_awal,
  MONTH(tppTanggalAwal) AS bulan_awal,
  YEAR(tppTanggalAwal) AS tahun_awal,
  `tppTanggalAkhir` AS tanggal_akhir,
  MONTH(tppTanggalAkhir) AS bulan_akhir,
  YEAR(tppTanggalAkhir) AS tahun_akhir
FROM `tahun_pembukuan_periode`
WHERE 
    tppId ='%s'
";
