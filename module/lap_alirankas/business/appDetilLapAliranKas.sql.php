<?php

//===GET===
$sql['get_data_detil_klp_laporan'] = "
SELECT 
    coaNamaAkun AS coa_nama,
	coaKodeAkun AS coa_kode,
	 IFNULL(CASE WHEN coakellapDK='D' THEN 
		  (SELECT SUM(bbDebet) AS nilai FROM buku_besar_his LEFT JOIN pembukuan_referensi ON bbPembukuanRefId = prId 
		  WHERE bbTanggal BETWEEN '%s' AND '%s' AND bbCoaId = coakellapCoaId AND prBentukTransaksi = kelJnsId
		  AND bbIsJurnalBalik='T'
		  GROUP BY bbCoaId
		  )
		  WHEN coakellapDK='K' THEN (SELECT SUM(bbKredit) AS nilai FROM buku_besar_his LEFT JOIN pembukuan_referensi ON bbPembukuanRefId = prId 
		  WHERE bbTanggal BETWEEN '%s' AND '%s' AND bbCoaId = coakellapCoaId AND prBentukTransaksi = kelJnsId
		  AND bbIsJurnalBalik='T'
		  GROUP BY bbCoaId
		  ) END,0) AS coa_nominal
      FROM kelompok_laporan_ref
      LEFT JOIN kelompok_jenis_laporan_ref ON kellapJnsId = kelJnsId
      LEFT JOIN coa_kelompok_laporan_ref ON coakellapIdKellap = kellapId
      JOIN coa ON coaId=coakellapCoaId
	WHERE 
			coakellapIdKellap = '%s'AND
			kelJnsPrntId = '2'
			
      ORDER BY coa_kode";
      
$sql['get_data_detil_klp_laporan_kas_setara_kas'] = "
SELECT 
         
		coaNamaAkun AS coa_nama,
		coaKodeAkun AS coa_kode,
      IFNULL((SELECT bbSaldoAkhir FROM buku_besar_his WHERE bbCoaId = coakellapCoaId AND bbTanggal<='%s' ORDER BY bbhisId DESC LIMIT 0,1),0) AS coa_nominal
      FROM kelompok_laporan_ref
      LEFT JOIN kelompok_jenis_laporan_ref ON kellapJnsId = kelJnsId
      LEFT JOIN coa_kelompok_laporan_ref ON coakellapIdKellap = kellapId
	  LEFT JOIN coa ON coaId=coakellapCoaId
            WHERE 
			coakellapIdKellap = '%s' AND
			kelJnsPrntId = '2'
			
      ORDER BY coa_kode";
?>
