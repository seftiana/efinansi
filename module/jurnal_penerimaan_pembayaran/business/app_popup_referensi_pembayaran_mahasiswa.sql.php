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

$sql['get_tahun_pembayaran']    = "
	SELECT 
		YEAR(pembyrnTglBayar) AS id,
		YEAR(pembyrnTglBayar) AS name
	FROM pm_pembayaran 
	GROUP BY YEAR(pembyrnTglBayar)
	ORDER BY YEAR(pembyrnTglBayar) DESC
";

$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_referensi_pembayaran']   = "
SELECT 
	COALESCE(coa_prodi_ada.mpcoaId, coa_prodi_kosong.mpcoaId)AS id,
	COALESCE(coa_prodi_ada.mpcoaCoaId, coa_prodi_kosong.mpcoaCoaId) AS coa_id,
	'0' AS coa_is_debet_positif,
	SUM(aa.pembyrnNominal)-SUM(aa.pembyrnPotongan) AS 'bayar',
	SUM(aa.pembyrnDariDeposit) AS deposit,
	(SUM(aa.pembyrnNominal)-SUM(aa.pembyrnPotongan))-SUM(aa.pembyrnDariDeposit) AS 'nominal',
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