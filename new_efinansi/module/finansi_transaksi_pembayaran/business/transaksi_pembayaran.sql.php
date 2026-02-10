<?php
/**
 * @package SQL-FILE
 */
$sql['get_range_year']  = "
SELECT
   EXTRACT(YEAR FROM IFNULL(MIN(transTanggal), DATE_ADD(DATE(NOW()),INTERVAL -DAY(DATE(NOW()))+1 DAY))) AS minYear,
   EXTRACT(YEAR FROM IFNULL(MAX(transTanggal), DATE(LAST_DAY(DATE(NOW()))))) AS maxYear,
   IFNULL(MIN(transTanggal), DATE_ADD(DATE(NOW()),INTERVAL -DAY(DATE(NOW()))+1 DAY)) AS tanggalAwal,
   IFNULL(MAX(transTanggal), DATE(LAST_DAY(DATE(NOW())))) AS tanggalAkhir
FROM transaksi
WHERE 1 = 1
";

$sql['set_referensi']   = "
SET @referensi = ''
";

$sql['set_tahun_pembukuan']   = "
SET @tahun_pembukuan = ''
";

$sql['set_jenis_transaksi']   = "
SET @jenis_transaksi = ''
";

$sql['set_tipe_transaksi']    = "
SET @tipe_transaksi = ''
";

$sql['set_unit_kerja']     = "
SET @unit_kerja = ''
";

$sql['set_tahun_anggaran'] = "
SET @tahun_anggaran = ''
";

$sql['do_set_tahun_pembukuan']   = "
SELECT
   tppId INTO @tahun_pembukuan
FROM tahun_pembukuan_periode
WHERE 1 = 1
AND tppIsBukaBuku = 'Y'
LIMIT 1
";

$sql['do_set_jenis_transaksi']   = "
SELECT
   transjenId INTO @jenis_transaksi
FROM transaksi_jenis_ref
WHERE 1 = 1
AND UPPER(transjenKode) = 'RG'
LIMIT 1
";

$sql['do_set_unit_kerja']  = "
SELECT
   unitkerjaId INTO @unit_kerja
FROM unit_kerja_ref
WHERE 1 = 1
AND unitkerjaParentId = 0
LIMIT 1
";

$sql['do_set_tahun_anggaran'] = "
SELECT
   thanggarId INTO @tahun_anggaran
FROM tahun_anggaran
WHERE 1 = 1
AND thanggarIsAktif = 'Y'
LIMIT 1
";

$sql['do_set_tipe_transaksi'] = "
SELECT
   ttId INTO @tipe_transaksi
FROM transaksi_tipe_ref
WHERE 1 = 1
AND ttKodeTransaksi = 'PM'
LIMIT 1
";

$sql['do_set_referensi']      = "
SELECT
   CONCAT_WS('/',
      'PM',
      LPAD(IFNULL(MAX(SUBSTRING_INDEX(SUBSTRING_INDEX(transReferensi, '/', 2), '/', -1)+0)+1, 1), 7, 0),
      UPPER('%s'),
      CONCAT(LPAD(EXTRACT(MONTH FROM DATE(NOW())), 2, 0), '.', EXTRACT(YEAR FROM DATE(NOW())))
   ) INTO @referensi
FROM transaksi
WHERE 1 = 1
AND SUBSTRING_INDEX(transReferensi, '/', 1) = 'PM'
AND EXTRACT(YEAR FROM transTanggal) = EXTRACT(YEAR FROM DATE(NOW()))
AND EXTRACT(MONTH FROM transTanggal) = EXTRACT(MONTH FROM DATE(NOW()))
";

$sql['do_insert_transaksi']   = "
INSERT INTO transaksi
SET transTtId = @tipe_transaksi,
   transTransjenId = @jenis_transaksi,
   transUnitkerjaId = @unit_kerja,
   transTppId = @tahun_pembukuan,
   transThanggarId = @tahun_anggaran,
   transReferensi = @referensi,
   transUserId = '%s',
   transTanggal = DATE(NOW()),
   transTanggalEntri = DATE(NOW()),
   transDueDate = DATE(NOW()),
   transCatatan = '%s',
   transNilai = '%s',
   transPenanggungJawabNama = '%s'
";

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_transaksi']    = "
SELECT SQL_CALC_FOUND_ROWS
   tppId AS tpId,
   tppTanggalAwal AS tpTglAwal,
   tppTanggalAkhir AS tpTglAkhir,
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   transId AS id,
   transReferensi AS referensi,
   transNilai AS nominal,
   transPenanggungJawabNama AS penanggungJawab,
   transTanggal AS tanggal,
   transTanggalEntri AS tanggalEntri,
   transDueDate AS dueDate
FROM transaksi
JOIN transaksi_tipe_ref
   ON ttId = transTtId
   AND ttId = 10
JOIN tahun_pembukuan_periode
   ON tppId = transTppId
JOIN tahun_anggaran
   ON thanggarId = transThanggarId
JOIN unit_kerja_ref
   ON unitkerjaId = transUnitkerjaid
JOIN transaksi_jenis_ref
   ON transjenId = transTransJenId
WHERE 1 = 1
AND SUBSTRING_INDEX(transreferensi, '/', 1) = 'PM'
AND transReferensi LIKE '%s'
AND transDueDate BETWEEN '%s' AND '%s'
ORDER BY transTanggal,
SUBSTRING_INDEX(SUBSTRING_INDEX(transReferensi, '/', 2), '/', -1)+0 DESC,
SUBSTRING_INDEX(SUBSTRING_INDEX(transReferensi, '/', 3), '/', -1)+0 DESC
LIMIT %s, %s
";

$sql['get_transaksi_detail']     = "
SELECT SQL_CALC_FOUND_ROWS
   tppId AS tpId,
   tppTanggalAwal AS tpTglAwal,
   tppTanggalAkhir AS tpTglAkhir,
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   transId AS id,
   transReferensi AS referensi,
   transNilai AS nominal,
   transPenanggungJawabNama AS penanggungJawab,
   transTanggal AS tanggal,
   transTanggalEntri AS tanggalEntri,
   transDueDate AS dueDate,
   transCatatan AS keterangan
FROM transaksi
JOIN transaksi_tipe_ref
   ON ttId = transTtId
   AND ttId = 10
JOIN tahun_pembukuan_periode
   ON tppId = transTppId
JOIN tahun_anggaran
   ON thanggarId = transThanggarId
JOIN unit_kerja_ref
   ON unitkerjaId = transUnitkerjaid
JOIN transaksi_jenis_ref
   ON transjenId = transTransJenId
WHERE 1 = 1
AND SUBSTRING_INDEX(transreferensi, '/', 1) = 'PM'
AND transId = 11
ORDER BY transTanggal,
SUBSTRING_INDEX(SUBSTRING_INDEX(transReferensi, '/', 2), '/', -1)+0 DESC,
SUBSTRING_INDEX(SUBSTRING_INDEX(transReferensi, '/', 3), '/', -1)+0 DESC
";
?>