<?php
$sql['get_minmax_tahun_transaksi'] = "
   SELECT
      YEAR(MIN(transTanggalEntri)) - 5 AS minTahun,
      YEAR(MAX(transTanggalEntri)) + 5 AS maxTahun
   FROM
      transaksi
";

$sql['get_laporan_all'] = "
   SELECT 
         kellapOrderBy,
         kelJnsOrderBy, 
         kellapId,
         kellapJnsId,
         kelJnsNama,
         kellapNama AS nama_kel_lap,     
         kellapIsTambah AS `status`,
			SUM(IFNULL(CASE WHEN coakellapDK='D' THEN 
		  (SELECT SUM(bbDebet) AS nilai FROM buku_besar_his LEFT JOIN pembukuan_referensi ON bbPembukuanRefId = prId 
		  WHERE bbTanggal BETWEEN '%s' AND '%s' AND bbCoaId = coakellapCoaId AND prBentukTransaksi = kelJnsId AND prIsJurnalBalik = '0'
		  AND bbIsJurnalBalik='T'
		  GROUP BY bbCoaId
		  )
		  WHEN coakellapDK='K' THEN (SELECT SUM(bbKredit) AS nilai FROM buku_besar_his LEFT JOIN pembukuan_referensi ON bbPembukuanRefId = prId 
		  WHERE bbTanggal BETWEEN '%s' AND '%s' AND bbCoaId = coakellapCoaId AND prBentukTransaksi = kelJnsId AND prIsJurnalBalik = '0'
		  AND bbIsJurnalBalik='T'
		  GROUP BY bbCoaId
		  ) END,0)) nilai
      FROM kelompok_laporan_ref
      LEFT JOIN kelompok_jenis_laporan_ref ON kellapJnsId = kelJnsId
      LEFT JOIN coa_kelompok_laporan_ref ON coakellapIdKellap = kellapId
	WHERE 
	kelJnsPrntId = '2'
	%s
	GROUP BY kellapId
   ORDER BY kellapJnsId,kelJnsOrderBy,kellapOrderBy
";

$sql['get_laporan_kas_setara_kas'] = "
   SELECT 
      kelJnsOrderBy, 
      kellapId,
      kellapJnsId,
      kelJnsNama,
      nama_kel_lap,     
      `status`,
     IFNULL(SUM(nilai),0) AS nilai
   FROM(
   SELECT 
         kelJnsOrderBy, 
         kellapId,
         kellapJnsId,
         kelJnsNama,
         kellapNama AS nama_kel_lap,     
         kellapIsTambah AS `status`,
      (SELECT bbSaldoAkhir FROM buku_besar_his WHERE bbCoaId = coakellapCoaId AND bbTanggal<='%s' ORDER BY bbhisId DESC LIMIT 0,1) AS nilai
      FROM kelompok_laporan_ref
      LEFT JOIN kelompok_jenis_laporan_ref ON kellapJnsId = kelJnsId
      LEFT JOIN coa_kelompok_laporan_ref ON coakellapIdKellap = kellapId
            WHERE 
            kellapId = '132'
      ORDER BY kellapJnsId,kelJnsOrderBy,kellapOrderBy
   ) a %s
   GROUP BY kellapId
";

$sql['get_laporan_all_old'] = "
   SELECT 
         kelJnsOrderBy, 
         kellapId,
         kellapJnsId,
         kelJnsNama,
         kellapNama AS nama_kel_lap,     
         kellapIsTambah AS `status`,
	 IFNULL((SELECT  
bbSaldoAkhir AS nilai FROM buku_besar_his 
LEFT JOIN pembukuan_referensi ON bbPembukuanRefId = prId WHERE bbTanggal <= '%s' AND prIsKas = 'Y' 
AND bbCoaId = coakellapCoaId AND prBentukTransaksi = kelJnsId ORDER BY bbhisId DESC LIMIT 0,1), 0) AS nilai
      FROM kelompok_laporan_ref
      LEFT JOIN kelompok_jenis_laporan_ref ON kellapJnsId = kelJnsId
      LEFT JOIN coa_kelompok_laporan_ref ON coakellapIdKellap = kellapId
	WHERE 
	kelJnsPrntId = '2'
	%s
	GROUP BY kellapId
        ORDER BY kellapIsTambah, kellapJnsId
";

$sql['get_saldo_coa_aliran_kas'] = "
   SELECT 
      a.bbSaldoAkhir AS saldo_akhir
   FROM
      buku_besar a
      LEFT JOIN coa_tipe_coa b ON a.bbCoaId = b.coatipecoaCoaId
   WHERE
      coatipecoaCtrId = '4'
";
?>
