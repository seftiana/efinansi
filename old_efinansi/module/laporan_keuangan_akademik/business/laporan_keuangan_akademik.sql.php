<?php
/**
 * @package SQL-FILE
 */
 

$sql['get_coa_alokasi_akademik'] ="
SELECT 
`coaAlokasiAkademikCoaId` AS coaId 
FROM
  `finansi_keu_coa_alokasi_akademik` 
";

$sql['get_limit'] ="
LIMIT %s,%s
";

$sql['get_jumlah_kelas_per_unit'] = "
SELECT
  jku.`jmlKelasUnitKerjaId` AS unitKerjaId,
  jku.`jmlKelasTotal` AS jumlahKelas,
  IFNULL(jku.`jmlKelasTotalSmGasal`,jku.`jmlKelasTotal`) AS jumlahKelasGasal,
  IFNULL(jku.`jmlKelasTotalSmGenap`,jku.`jmlKelasTotal`) AS jumlahKelasGenap
FROM
 `finansi_pa_jumlah_kelas_per_unit` jku
WHERE
   `jmlKelasTahunAnggaranId` = '%s'
GROUP BY jku.`jmlKelasUnitKerjaId`
";

$sql['get_nominal_per_item_pengeluaran'] ="
SELECT 
'2' AS identitas,
c.`coaId` AS kelompokId,
rpeng.`rncnpengeluaranKomponenKode` AS subKelompokId,
SUM(peng_real_d.`pengrealdetNominalApprove`) AS nominal
FROM 
pengajuan_realisasi_detil peng_real_d
INNER JOIN pengajuan_realisasi peng_real
ON peng_real.`pengrealId` =  peng_real_d.`pengrealdetPengRealId`
INNER JOIN transaksi_detail_pencairan td_cair 
ON td_cair.`transdtpencairanPengrealId` = peng_real.`pengrealId`
INNER JOIN transaksi tr
ON tr.`transId` = td_cair.`transdtpencairanTransId`
LEFT JOIN rencana_pengeluaran rpeng 
ON rpeng.`rncnpengeluaranId` = peng_real_d.`pengrealdetRncnpengeluaranId`
INNER JOIN komponen komp 
ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN coa c
ON c.`coaId` = komp.`kompCoaId`
WHERE
tr.`transTanggalEntri`  BETWEEN '%s' AND '%s'
GROUP BY identitas,kelompokId,subKelompokId
ORDER BY identitas,kelompokId,subKelompokId
";

$sql['get_nominal_per_unit_pengeluaran'] ="
SELECT 
tr.`transUnitkerjaId` AS unitKerjaId,
'2' AS identitas,
c.`coaId` AS kelompokId,
rpeng.`rncnpengeluaranKomponenKode` AS subKelompokId,
SUM(peng_real_d.`pengrealdetNominalApprove`) AS nominal
FROM 
pengajuan_realisasi_detil peng_real_d
INNER JOIN pengajuan_realisasi peng_real
ON peng_real.`pengrealId` =  peng_real_d.`pengrealdetPengRealId`
INNER JOIN transaksi_detail_pencairan td_cair 
ON td_cair.`transdtpencairanPengrealId` = peng_real.`pengrealId`
INNER JOIN transaksi tr
ON tr.`transId` = td_cair.`transdtpencairanTransId`
LEFT JOIN rencana_pengeluaran rpeng 
ON rpeng.`rncnpengeluaranId` = peng_real_d.`pengrealdetRncnpengeluaranId`
INNER JOIN komponen komp 
ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN coa c
ON c.`coaId` = komp.`kompCoaId`
WHERE
tr.`transTanggalEntri`  BETWEEN '%s' AND '%s'
GROUP BY unitKerjaId,identitas,kelompokId,subKelompokId
ORDER BY unitKerjaId,identitas,kelompokId,subKelompokId
";

$sql['get_nominal_per_kelompok_unit_pengeluaran'] ="
SELECT 
tr.`transUnitkerjaId` AS unitKerjaId,
'2' AS identitas,
c.`coaId` AS kelompokId,
SUM(peng_real_d.`pengrealdetNominalApprove`) AS nominal
FROM 
pengajuan_realisasi_detil peng_real_d
INNER JOIN pengajuan_realisasi peng_real
ON peng_real.`pengrealId` =  peng_real_d.`pengrealdetPengRealId`
INNER JOIN transaksi_detail_pencairan td_cair 
ON td_cair.`transdtpencairanPengrealId` = peng_real.`pengrealId`
INNER JOIN transaksi tr
ON tr.`transId` = td_cair.`transdtpencairanTransId`
LEFT JOIN rencana_pengeluaran rpeng 
ON rpeng.`rncnpengeluaranId` = peng_real_d.`pengrealdetRncnpengeluaranId`
INNER JOIN komponen komp 
ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN coa c
ON c.`coaId` = komp.`kompCoaId`
WHERE
tr.`transTanggalEntri`  BETWEEN '%s' AND '%s'
GROUP BY unitKerjaId,identitas,kelompokId
ORDER BY unitKerjaId,identitas,kelompokId
";

$sql['get_total_per_kelompok_pengeluaran'] ="
SELECT 
c.`coaId` AS kelompokId,
SUM(peng_real_d.`pengrealdetNominalApprove`) AS nominal
FROM 
pengajuan_realisasi_detil peng_real_d
INNER JOIN pengajuan_realisasi peng_real
ON peng_real.`pengrealId` =  peng_real_d.`pengrealdetPengRealId`
INNER JOIN transaksi_detail_pencairan td_cair 
ON td_cair.`transdtpencairanPengrealId` = peng_real.`pengrealId`
INNER JOIN transaksi tr
ON tr.`transId` = td_cair.`transdtpencairanTransId`
LEFT JOIN rencana_pengeluaran rpeng 
ON rpeng.`rncnpengeluaranId` = peng_real_d.`pengrealdetRncnpengeluaranId`
INNER JOIN komponen komp 
ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN coa c
ON c.`coaId` = komp.`kompCoaId`
WHERE
tr.`transTanggalEntri`  BETWEEN '%s' AND '%s'
GROUP BY kelompokId
ORDER BY kelompokId
";

$sql['get_nominal_per_pengeluaran'] ="
SELECT 
tr.`transUnitkerjaId` AS unitKerjaId,
'2' AS identitas,
SUM(peng_real_d.`pengrealdetNominalApprove`) AS nominal
FROM 
pengajuan_realisasi_detil peng_real_d
INNER JOIN pengajuan_realisasi peng_real
ON peng_real.`pengrealId` =  peng_real_d.`pengrealdetPengRealId`
INNER JOIN transaksi_detail_pencairan td_cair 
ON td_cair.`transdtpencairanPengrealId` = peng_real.`pengrealId`
INNER JOIN transaksi tr
ON tr.`transId` = td_cair.`transdtpencairanTransId`
LEFT JOIN rencana_pengeluaran rpeng 
ON rpeng.`rncnpengeluaranId` = peng_real_d.`pengrealdetRncnpengeluaranId`
INNER JOIN komponen komp 
ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN coa c
ON c.`coaId` = komp.`kompCoaId`
WHERE
tr.`transTanggalEntri`  BETWEEN '%s' AND '%s'
GROUP BY unitKerjaId,identitas
ORDER BY unitKerjaId,identitas
";

$sql['get_total_per_pengeluaran'] ="
SELECT 
SUM(peng_real_d.`pengrealdetNominalApprove`) AS nominal
FROM 
pengajuan_realisasi_detil peng_real_d
INNER JOIN pengajuan_realisasi peng_real
ON peng_real.`pengrealId` =  peng_real_d.`pengrealdetPengRealId`
INNER JOIN transaksi_detail_pencairan td_cair 
ON td_cair.`transdtpencairanPengrealId` = peng_real.`pengrealId`
INNER JOIN transaksi tr
ON tr.`transId` = td_cair.`transdtpencairanTransId`
LEFT JOIN rencana_pengeluaran rpeng 
ON rpeng.`rncnpengeluaranId` = peng_real_d.`pengrealdetRncnpengeluaranId`
INNER JOIN komponen komp 
ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN coa c
ON c.`coaId` = komp.`kompCoaId`
WHERE
tr.`transTanggalEntri`  BETWEEN '%s' AND '%s'
";

$sql['get_nominal_per_item_penerimaan'] ="
SELECT 
'1' AS identitas,
IFNULL(kref_parent.`kodeterimaId`,kref.`kodeterimaId`) AS kelompokId,
IFNULL(c.`coaId`,0) AS subKelompokId,
IFNULL(kref_parent.`kodeterimaKode`,kref.`kodeterimaKode`) AS kelompokKode,
c.`coaKodeAkun` AS subKelompokKode,
SUM(real_pen.`realterimaTotalTerima`) AS nominal
FROM 
realisasi_penerimaan real_pen
INNER JOIN transaksi tr ON tr.`transId` = real_pen.`realterimaTransId`
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = tr.`transUnitkerjaId`
LEFT JOIN rencana_penerimaan rpen 
ON rpen.`renterimaId`  = real_pen.`realrenterimaId`
LEFT JOIN kode_penerimaan_ref kref
ON kref.`kodeterimaId` = rpen.`renterimaKodeterimaId`
INNER JOIN finansi_coa_map cmap
ON cmap.`kodeterimaId` = kref.`kodeterimaId`
INNER JOIN coa c
ON c.`coaId` = cmap.`coaId`
LEFT JOIN kode_penerimaan_ref kref_parent
ON kref_parent.`kodeterimaId` = kref.`kodeterimaParentId`
WHERE
tr.`transTanggalEntri`  BETWEEN '%s' AND '%s'
GROUP BY subKelompokId
ORDER BY identitas,kelompokId,subKelompokId,subKelompokKode
";

$sql['get_nominal_per_unit_penerimaan'] ="
SELECT 
tr.`transUnitkerjaId` AS unitKerjaId,
'1' AS identitas,
IFNULL(IFNULL(kref_parent.`kodeterimaId`,kref.`kodeterimaId`),0) AS kelompokId,
IFNULL(c.`coaId`,0) AS subKelompokId,
SUM(real_pen.`realterimaTotalTerima`) AS nominal
FROM 
realisasi_penerimaan real_pen
INNER JOIN transaksi tr ON tr.`transId` = real_pen.`realterimaTransId`
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = tr.`transUnitkerjaId`
LEFT JOIN rencana_penerimaan rpen 
ON rpen.`renterimaId`  = real_pen.`realrenterimaId`
LEFT JOIN kode_penerimaan_ref kref
ON kref.`kodeterimaId` = rpen.`renterimaKodeterimaId`
INNER JOIN finansi_coa_map cmap
ON cmap.`kodeterimaId` = kref.`kodeterimaId`
INNER JOIN coa c
ON c.`coaId` = cmap.`coaId`
LEFT JOIN kode_penerimaan_ref kref_parent
ON kref_parent.`kodeterimaId` = kref.`kodeterimaParentId`

WHERE
tr.`transTanggalEntri`  BETWEEN '%s' AND '%s'
GROUP BY unitKerjaId,identitas,kelompokId,subKelompokId
";

$sql['get_nominal_per_kelompok_unit_penerimaan'] ="
SELECT 
tr.`transUnitkerjaId` AS unitKerjaId,
'1' AS identitas,
IFNULL(kref_parent.`kodeterimaId`,kref.`kodeterimaId`) AS kelompokId,
c.`coaId` AS coaId,
SUM(real_pen.`realterimaTotalTerima`) AS nominal
FROM 
realisasi_penerimaan real_pen
INNER JOIN transaksi tr ON tr.`transId` = real_pen.`realterimaTransId`
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = tr.`transUnitkerjaId`
LEFT JOIN rencana_penerimaan rpen 
ON rpen.`renterimaId`  = real_pen.`realrenterimaId`
LEFT JOIN kode_penerimaan_ref kref
ON kref.`kodeterimaId` = rpen.`renterimaKodeterimaId`
INNER JOIN finansi_coa_map cmap
ON cmap.`kodeterimaId` = kref.`kodeterimaId`
INNER JOIN coa c
ON c.`coaId` = cmap.`coaId`
LEFT JOIN kode_penerimaan_ref kref_parent
ON kref_parent.`kodeterimaId` = kref.`kodeterimaParentId`
WHERE
tr.`transTanggalEntri`  BETWEEN '%s' AND '%s'
GROUP BY unitKerjaId,identitas,kelompokId
";

$sql['get_total_per_kelompok_penerimaan'] ="
SELECT 
IFNULL(kref_parent.`kodeterimaId`,kref.`kodeterimaId`) AS kelompokId,
SUM(real_pen.`realterimaTotalTerima`) AS nominal
FROM 
realisasi_penerimaan real_pen
INNER JOIN transaksi tr ON tr.`transId` = real_pen.`realterimaTransId`
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = tr.`transUnitkerjaId`
LEFT JOIN rencana_penerimaan rpen 
ON rpen.`renterimaId`  = real_pen.`realrenterimaId`
LEFT JOIN kode_penerimaan_ref kref
ON kref.`kodeterimaId` = rpen.`renterimaKodeterimaId`
INNER JOIN finansi_coa_map cmap
ON cmap.`kodeterimaId` = kref.`kodeterimaId`
INNER JOIN coa c
ON c.`coaId` = cmap.`coaId`
LEFT JOIN kode_penerimaan_ref kref_parent
ON kref_parent.`kodeterimaId` = kref.`kodeterimaParentId`
WHERE
tr.`transTanggalEntri`  BETWEEN '%s' AND '%s'
GROUP BY kelompokId
";

$sql['get_nominal_per_penerimaan'] ="
SELECT 
tr.`transUnitkerjaId` AS unitKerjaId,
'1' AS identitas,
c.`coaId` AS coaId,
SUM(real_pen.`realterimaTotalTerima`) AS nominal
FROM 
realisasi_penerimaan real_pen
INNER JOIN transaksi tr ON tr.`transId` = real_pen.`realterimaTransId`
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = tr.`transUnitkerjaId`
LEFT JOIN rencana_penerimaan rpen 
ON rpen.`renterimaId`  = real_pen.`realrenterimaId`
LEFT JOIN kode_penerimaan_ref kref
ON kref.`kodeterimaId` = rpen.`renterimaKodeterimaId`
INNER JOIN finansi_coa_map cmap
ON cmap.`kodeterimaId` = kref.`kodeterimaId`
INNER JOIN coa c
ON c.`coaId` = cmap.`coaId`
LEFT JOIN kode_penerimaan_ref kref_parent
ON kref_parent.`kodeterimaId` = kref.`kodeterimaParentId`
WHERE
tr.`transTanggalEntri`  BETWEEN '%s' AND '%s'
GROUP BY unitKerjaId,identitas
";


$sql['get_total_per_penerimaan'] ="
SELECT 
SUM(real_pen.`realterimaTotalTerima`) AS nominal
FROM 
realisasi_penerimaan real_pen
INNER JOIN transaksi tr ON tr.`transId` = real_pen.`realterimaTransId`
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = tr.`transUnitkerjaId`
LEFT JOIN rencana_penerimaan rpen 
ON rpen.`renterimaId`  = real_pen.`realrenterimaId`
LEFT JOIN kode_penerimaan_ref kref
ON kref.`kodeterimaId` = rpen.`renterimaKodeterimaId`
INNER JOIN finansi_coa_map cmap
ON cmap.`kodeterimaId` = kref.`kodeterimaId`
INNER JOIN coa c
ON c.`coaId` = cmap.`coaId`
LEFT JOIN kode_penerimaan_ref kref_parent
ON kref_parent.`kodeterimaId` = kref.`kodeterimaParentId`
WHERE
tr.`transTanggalEntri`  BETWEEN '%s' AND '%s'
";

$sql['get_unit_kerja']= "
SELECT 
uk.`unitkerjaId` AS unitKerjaId,
uk.`unitkerjaKode` AS unitKerjaKode,
uk.`unitkerjaNama` AS unitKerjaNama,
jku.`jmlKelasTotal` AS unitKerjaKelas,
  IFNULL(jku.`jmlKelasTotalSmGasal`,jku.`jmlKelasTotal`) AS jumlahKelasGasal,
  IFNULL(jku.`jmlKelasTotalSmGenap`,jku.`jmlKelasTotal`) AS jumlahKelasGenap
FROM 
unit_kerja_ref uk
JOIN `finansi_pa_jumlah_kelas_per_unit` jku
ON jku.`jmlKelasUnitKerjaId` = uk.`unitkerjaId`
WHERE
jku.`jmlKelasTahunAnggaranId` = %s
ORDER BY unitKerjaNama
";

$sql['get_data_laporan_keuangan_akademik'] = "
SELECT SQL_CALC_FOUND_ROWS 
tbl.identitas,
tbl.kelompokId,
tbl.kelompokKode,
tbl.kelompokNama,
tbl.subKelompokId,
tbl.subKelompokKode,
tbl.subKelompokNama ,
tbl.nominal
FROM ((
SELECT 
'1' AS identitas,
IFNULL(kref_parent.`kodeterimaId`,kref.`kodeterimaId`) AS kelompokId,
IFNULL(c.`coaId`,0) AS subKelompokId,
IFNULL(kref_parent.`kodeterimaKode`,kref.`kodeterimaKode`) AS kelompokKode,
IFNULL(kref_parent.`kodeterimaNama`,kref.`kodeterimaNama`) AS kelompokNama,
c.`coaKodeAkun` AS subKelompokKode,
c.`coaNamaAkun` AS subKelompokNama,
SUM(real_pen.`realterimaTotalTerima`) AS nominal
FROM 
realisasi_penerimaan real_pen
INNER JOIN transaksi tr ON tr.`transId` = real_pen.`realterimaTransId`
LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = tr.`transUnitkerjaId`
LEFT JOIN rencana_penerimaan rpen 
ON rpen.`renterimaId`  = real_pen.`realrenterimaId`
LEFT JOIN kode_penerimaan_ref kref
ON kref.`kodeterimaId` = rpen.`renterimaKodeterimaId`
INNER JOIN finansi_coa_map cmap
ON cmap.`kodeterimaId` = kref.`kodeterimaId`
INNER JOIN coa c
ON c.`coaId` = cmap.`coaId`
LEFT JOIN kode_penerimaan_ref kref_parent
ON kref_parent.`kodeterimaId` = kref.`kodeterimaParentId`
GROUP BY subKelompokId
ORDER BY identitas,kelompokId,subKelompokId,subKelompokKode
) 
UNION
(
SELECT 
'2' AS identitas,
c.`coaId` AS kelompokId,
rpeng.`rncnpengeluaranKomponenKode` AS subKelompokId,
c.`coaKodeAkun` AS kelompokKode,
c.`coaNamaAkun` AS kelompokNama,
rpeng.`rncnpengeluaranKomponenKode` AS  subKelompokKode,
rpeng.`rncnpengeluaranKomponenNama` AS subKelompokNama,
SUM(peng_real_d.`pengrealdetNominalApprove`) AS nominal
FROM 
pengajuan_realisasi_detil peng_real_d
INNER JOIN pengajuan_realisasi peng_real
ON peng_real.`pengrealId` =  peng_real_d.`pengrealdetPengRealId`
INNER JOIN transaksi_detail_pencairan td_cair 
ON td_cair.`transdtpencairanPengrealId` = peng_real.`pengrealId`
INNER JOIN transaksi tr
ON tr.`transId` = td_cair.`transdtpencairanTransId`
LEFT JOIN rencana_pengeluaran rpeng 
ON rpeng.`rncnpengeluaranId` = peng_real_d.`pengrealdetRncnpengeluaranId`
INNER JOIN komponen komp 
ON komp.`kompKode` = rpeng.`rncnpengeluaranKomponenKode`
LEFT JOIN coa c
ON c.`coaId` = komp.`kompCoaId`

GROUP BY identitas,kelompokId,subKelompokId
ORDER BY identitas,kelompokId,kelompokKode
)) tbl
ORDER BY identitas,kelompokId,kelompokKode
";


$sql['get_periode_tahun']     = "
SELECT
   thanggarId AS id,
   thanggarNama AS `name`
FROM tahun_anggaran
WHERE 1 = 1
AND (thanggarIsAktif = 'Y' OR 1 = %s)
AND (thanggarIsOpen = 'Y' OR 1 = %s)
ORDER BY thanggarNama
";

$sql['count']              = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_periode_tahun_anggaran'] = "
SELECT 
ta.`thanggarId` AS id
FROM tahun_anggaran ta
WHERE
ta.`thanggarBuka` <= '%s' 
AND 
ta.`thanggarTutup` >= '%s' 
LIMIT 1
";

$sql['get_tahun_anggaran_detail_by_id'] ="
SELECT 
  thanggarId AS id,
  thanggarNama AS `name` ,
  thanggarBuka AS tanggal_awal,
  MONTH(thanggarBuka) AS bulan_awal,
  YEAR(thanggarBuka) AS tahun_awal,
  thanggarTutup AS tanggal_tutup,
  MONTH(thanggarTutup) AS bulan_akhir,
  YEAR(thanggarTutup) AS tahun_akhir
FROM
  tahun_anggaran 
WHERE
thanggarId = %s
";

?>