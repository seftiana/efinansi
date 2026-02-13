<?php
/**
 * @package SQL-FILE
 */

$sql['get_data_laporan']     = "
SELECT 
	aa.pembyrnDetId as id_pembayaran_dtl,
	a.pembyrnId as id_pembayaran,
	a.pembyrnTglBayar as tgl_bayar,
	mahasiswaNoTest as mahasiswanotest,
	mahasiswaNIM as nim,
	mahasiswaNama as nama,
	CONCAT(jenjangKode,' ',prd.prodiNamaProdi) AS prodi,
	COALESCE(coa_prodi_ada.mpcoaCoaId, coa_prodi_kosong.mpcoaCoaId) AS coa_id,
	jenisBiayaNama AS 'jenis_biaya',
	SUM(aa.pembyrnNominal) AS 'nominal',
	SUM(aa.pembyrnPotongan) AS 'potongan',
	SUM(aa.pembyrnDariDeposit) AS deposit,
	(SUM(aa.pembyrnNominal)-SUM(aa.pembyrnPotongan))-SUM(aa.pembyrnDariDeposit) AS 'real_bayar'
FROM pm_pembayaran_det aa
JOIN  pm_pembayaran a ON a.pembyrnId  = aa.pembyrnDetMstId
LEFT JOIN pm_jenis_biaya  ON aa.pembyrnJnsBiayaId = jenisBiayaId
LEFT JOIN pm_data_mahasiswa ON pembyrnMhsId=mahasiswaId
LEFT JOIN pm_program_studi_ref prd ON mahasiswaProdiId=prd.prodiId
LEFT JOIN pm_jenjang ON prd.prodiJenjangId=jenjangId
LEFT JOIN finansi_mapping_coa coa_prodi_ada ON coa_prodi_ada.mpcoaJenisBiayaId=jenisBiayaId AND coa_prodi_ada.mpcoaProdiId=mahasiswaProdiId
LEFT JOIN finansi_mapping_coa coa_prodi_kosong ON coa_prodi_kosong.mpcoaJenisBiayaId=jenisBiayaId AND coa_prodi_kosong.mpcoaProdiId=0
WHERE 1=1
	AND a.pembyrnTglBayar BETWEEN '%s' AND '%s'
GROUP BY aa.pembyrnDetId,a.pembyrnId,a.pembyrnTglBayar,mahasiswaNoTest,mahasiswaNIM,mahasiswaNama,jenisBiayaNama
ORDER BY a.pembyrnTglBayar,aa.pembyrnDetId ASC
LIMIT %s, %s 
";

$sql['count'] = "
SELECT 
	COUNT(aa.pembyrnDetId) AS count
FROM pm_pembayaran_det aa
	JOIN  pm_pembayaran a ON a.pembyrnId  = aa.pembyrnDetMstId
	LEFT JOIN pm_jenis_biaya  ON aa.pembyrnJnsBiayaId = jenisBiayaId
	LEFT JOIN pm_data_mahasiswa ON pembyrnMhsId=mahasiswaId
	LEFT JOIN finansi_mapping_coa coa_prodi_ada ON coa_prodi_ada.mpcoaJenisBiayaId=jenisBiayaId AND coa_prodi_ada.mpcoaProdiId=mahasiswaProdiId
	LEFT JOIN finansi_mapping_coa coa_prodi_kosong ON coa_prodi_kosong.mpcoaJenisBiayaId=jenisBiayaId AND coa_prodi_kosong.mpcoaProdiId=0
WHERE 1=1
	AND a.pembyrnTglBayar BETWEEN '%s' AND '%s'
";

$sql['get_data_laporan_sum_all'] = "
SELECT 
	(SUM(aa.pembyrnNominal)-SUM(aa.pembyrnPotongan))-SUM(aa.pembyrnDariDeposit) AS 'total_real_bayar'
FROM pm_pembayaran_det aa
	JOIN  pm_pembayaran a ON a.pembyrnId  = aa.pembyrnDetMstId
	LEFT JOIN pm_jenis_biaya  ON aa.pembyrnJnsBiayaId = jenisBiayaId
	LEFT JOIN pm_data_mahasiswa ON pembyrnMhsId=mahasiswaId
	LEFT JOIN finansi_mapping_coa coa_prodi_ada ON coa_prodi_ada.mpcoaJenisBiayaId=jenisBiayaId AND coa_prodi_ada.mpcoaProdiId=mahasiswaProdiId
	LEFT JOIN finansi_mapping_coa coa_prodi_kosong ON coa_prodi_kosong.mpcoaJenisBiayaId=jenisBiayaId AND coa_prodi_kosong.mpcoaProdiId=0
WHERE 1=1
	AND a.pembyrnTglBayar BETWEEN '%s' AND '%s'
";
?>