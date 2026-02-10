<?php
#get count data 
$sql['get_count_data'] = "
SELECT
    COUNT(transId) AS transaksi_id
FROM
    transaksi
    JOIN transaksi_tipe_ref ON transTtId = ttId
WHERE
    1 = 1
    AND (transReferensi LIKE '%s' OR transCatatan LIKE '%s')
    AND transTanggalEntri  BETWEEN '%s' AND '%s'
    AND (ttId = '%s' OR %s)
    AND transUnitkerjaId = %s
LIMIT 1
";

#get list data
$sql['get_total_transaksi_nilai'] = "
SELECT
    SUM(transNilai) AS total
FROM
    transaksi
    JOIN transaksi_tipe_ref ON transTtId = ttId
WHERE
    1 = 1
    AND (transReferensi LIKE '%s' OR transCatatan LIKE '%s')
    AND transTanggalEntri  BETWEEN '%s' AND '%s'
    AND (ttId = '%s' OR %s)
    AND transUnitkerjaId = %s
";

$sql['get_data'] = "
SELECT
    transId AS transaksi_id,
    transTanggal AS transaksi_tanggal_entri,
    transTanggalEntri AS transaksi_tanggal,
    transReferensi AS transaksi_referensi,
    transCatatan AS transaksi_catatan,
    transNilai AS transaksi_nilai,
    ttId AS transaksi_id,
    ttNamaTransaksi AS transaksi_tipe,
    transIsJurnal AS transaksi_is_jurnal
FROM
    transaksi
    JOIN transaksi_tipe_ref ON transTtId = ttId
    WHERE
    1 = 1
    AND (transReferensi LIKE '%s' OR transCatatan LIKE '%s')
    AND transTanggalEntri  BETWEEN '%s' AND '%s'
    AND (ttId = '%s' OR %s)
    AND transUnitkerjaId = %s
LIMIT %s, %s
";

$sql['get_tipe_transaksi'] = "
   SELECT
      ttId AS id,
      ttNamaTransaksi AS name
   FROM
      transaksi_tipe_ref
";

$sql['get_data_cetak'] = "
SELECT
    transId AS transaksi_id,
    transTanggal AS transaksi_tanggal_entri,
    transTanggalEntri AS transaksi_tanggal,
    transReferensi AS transaksi_referensi,
    transCatatan AS transaksi_catatan,
    transNilai AS transaksi_nilai,
    ttId AS transaksi_id,
    ttNamaTransaksi AS transaksi_tipe,
    transIsJurnal AS transaksi_is_jurnal
FROM
    transaksi
    JOIN transaksi_tipe_ref ON transTtId = ttId
WHERE
    1 = 1
    AND (transReferensi LIKE '%s' OR transCatatan LIKE '%s')
    AND transTanggalEntri  BETWEEN '%s' AND '%s'
    AND (ttId = '%s' OR %s)
    AND transUnitkerjaId = %s
ORDER BY transTanggalEntri ASC
";

#get min-max tahun
$sql['get_minmax_tahun_transaksi'] = "
	SELECT
 		YEAR(MIN(transTanggalEntri)) - 5 AS minTahun,
 		YEAR(MAX(transTanggalEntri)) + 5 AS maxTahun
	FROM
 		transaksi
";

$sql['get_unitkerja_for_idUser'] = "
   SELECT
      userunitkerjaUnitkerjaId AS unit_kerja
   FROM
      user_unit_kerja
   WHERE
      userunitkerjaUserId = %s
";

?>