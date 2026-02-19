<?php

$sql['get_range_year']  = "
SELECT
   EXTRACT(YEAR FROM IFNULL(MIN(transTanggal), DATE_ADD(DATE(NOW()),INTERVAL -DAY(DATE(NOW()))+1 DAY))) AS minYear,
   EXTRACT(YEAR FROM IFNULL(MAX(transTanggal), DATE(LAST_DAY(DATE(NOW()))))) AS maxYear,
   IFNULL(MIN(transTanggal), DATE_ADD(DATE(NOW()),INTERVAL -DAY(DATE(NOW()))+1 DAY)) AS tanggalAwal,
   IFNULL(MAX(transTanggal), DATE(LAST_DAY(DATE(NOW())))) AS tanggalAkhir
FROM transaksi
WHERE 1 = 1
";

$sql['count']        = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']     = "
SELECT
   SQL_CALC_FOUND_ROWS
   	COALESCE(coa_prodi_ada.mpcoaId, coa_prodi_kosong.mpcoaId)AS id,
	COALESCE(coa_prodi_ada.mpcoaCoaId, coa_prodi_kosong.mpcoaCoaId) AS coa_id,
	'0' AS coa_is_debet_positif,
	SUM(aa.pembyrnPotongan) AS 'potongan',
	SUM(aa.pembyrnNominal)-SUM(aa.pembyrnPotongan) AS 'bayar',
	SUM(aa.pembyrnDariDeposit) AS deposit,
	(SUM(aa.pembyrnNominal)-SUM(aa.pembyrnPotongan))-SUM(aa.pembyrnDariDeposit) AS 'real_bayar',
	MONTHNAME(a.pembyrnTglBayar)AS bulan,
	YEAR(a.pembyrnTglBayar) AS tahun
FROM pm_pembayaran a
JOIN pm_pembayaran_det aa ON a.pembyrnId  = aa.pembyrnDetMstId
LEFT JOIN pm_jenis_biaya  ON aa.pembyrnJnsBiayaId = jenisBiayaId
LEFT JOIN pm_data_mahasiswa ON pembyrnMhsId=mahasiswaId
LEFT JOIN finansi_mapping_coa coa_prodi_ada ON coa_prodi_ada.mpcoaJenisBiayaId=jenisBiayaId AND coa_prodi_ada.mpcoaProdiId=mahasiswaProdiId
LEFT JOIN finansi_mapping_coa coa_prodi_kosong ON coa_prodi_kosong.mpcoaJenisBiayaId=jenisBiayaId AND coa_prodi_kosong.mpcoaProdiId=0
WHERE COALESCE(coa_prodi_ada.mpcoaCoaId, coa_prodi_kosong.mpcoaCoaId)!=''
AND a.pembyrnTglBayar BETWEEN '%s' AND '%s'
GROUP BY COALESCE(coa_prodi_ada.mpcoaCoaId, coa_prodi_kosong.mpcoaCoaId)
HAVING (SUM(aa.pembyrnNominal)-SUM(aa.pembyrnPotongan))-SUM(aa.pembyrnDariDeposit)>0
ORDER BY a.pembyrnId ASC
LIMIT %s, %s
";
?>