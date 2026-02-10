<?php
/**
 * untuk keperluan mengenerate number berdasarkan formula
 * dalam tabel finansi_ref_formula
 */
$sql['get_sql_generate_number']="
SELECT
   formulaFormula as formatNumberFormula
FROM
   finansi_ref_formula
WHERE
   formulaCode = '%s'
AND
   formulaIsAktif = 'Y'
LIMIT 0,1
";
/**
 * end
 */

/**
 * Jika formula query tidak ditemukan, gunakan generate query di bawah ini
 */
$sql['set_generate_number']   = "
SET @GENERATE_NUMBER = '';
";

$sql['do_set_query'] = "
SELECT
   CONCAT_WS(
      '/',
      IF(UPPER('%s') = '', NULL, '%s'), -- IDENTIFIER
      LPAD(
         IFNULL(
            MAX(SUBSTRING_INDEX(SUBSTRING_INDEX(transReferensi, '/', 2),'/',-1)+0)+1,
            1
         ),5,0
      ),
      unitkerjaKode,
      CONCAT(
         LPAD(EXTRACT(MONTH FROM DATE('%s')), 2, 0), -- TANGGAL PEMBUATAN TRANSAKSI
         '.',
         EXTRACT(YEAR FROM DATE('%s')) -- TANGGAL PEMBUATAN TRANSAKSI
      )
   ) INTO @GENERATE_NUMBER
FROM
   transaksi
   LEFT JOIN unit_kerja_ref
      ON unitkerjaId = transUnitkerjaId
WHERE 1 = 1
   AND SUBSTRING_INDEX(transReferensi, '/', 1) = UPPER('%s') -- IDENTIFIER
   AND EXTRACT(MONTH FROM  transTanggalEntri) = EXTRACT(MONTH FROM DATE('%s')) -- TANGGAL PEMBUATAN TRANSAKSI
   AND EXTRACT(YEAR FROM  transTanggalEntri) = EXTRACT(YEAR FROM DATE('%s')) -- TANGGAL PEMBUATAN TRANSAKSI
   AND unitkerjaId = %s
";

/// untuk nomor kode baru
$sql['get_trans_reference_cp'] ="
SELECT
   CONCAT(
      SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) , -- TAHUN
      EXTRACT(MONTH FROM DATE('%s')) , -- BULAN
     '%s' , -- KODE CP,     
    LPAD(IFNULL(MAX(SUBSTR(transReferensi,-4,4)) + 1,1),4,0)
   ) AS nomorCp
FROM
   transaksi
WHERE 1 = 1 
    AND transReferensi LIKE CONCAT(SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) ,EXTRACT(MONTH FROM DATE('%s')) ,'%s','%s')
    AND EXTRACT(MONTH FROM  transTanggalEntri) = EXTRACT(MONTH FROM DATE('%s')) -- TANGGAL PEMBUATAN TRANSAKSI
    AND EXTRACT(YEAR FROM  transTanggalEntri) = EXTRACT(YEAR FROM DATE('%s')) -- TANGGAL PEMBUATAN TRANSAKSI
";

$sql['get_trans_reference_cp_kas_kecil'] ="
SELECT
   CONCAT('KK',
      SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) , -- TAHUN
      EXTRACT(MONTH FROM DATE('%s')) , -- BULAN
    LPAD(IFNULL(MAX(SUBSTR(transReferensi,-4,4)) + 1,1),4,0)
   ) AS nomorCpKasKecil
FROM
   transaksi
WHERE 1 = 1 
    AND transReferensi LIKE CONCAT('KK',SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) ,EXTRACT(MONTH FROM DATE('%s')) ,'%s')
    AND EXTRACT(MONTH FROM  transTanggalEntri) = EXTRACT(MONTH FROM DATE('%s')) -- TANGGAL PEMBUATAN TRANSAKSI
    AND EXTRACT(YEAR FROM  transTanggalEntri) = EXTRACT(YEAR FROM DATE('%s')) -- TANGGAL PEMBUATAN TRANSAKSI
    /* AND (LENGTH(transReferensi) - (
	LENGTH(SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2)) + LENGTH(EXTRACT(MONTH FROM DATE('%s')))
    )) = 4 */
";
//end


$sql['get_trans_reference_br'] ="
SELECT
   CONCAT('BR',
      SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) , -- TAHUN
      EXTRACT(MONTH FROM DATE('%s')) , -- BULAN
     '%s' , -- KODE BR,     
    LPAD(IFNULL(MAX(SUBSTR(transReferensi,-4,4)) + 1,1),4,0)
   ) AS nomorBr
FROM
   transaksi
WHERE 1 = 1 
   AND transReferensi LIKE CONCAT('BR',SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) ,EXTRACT(MONTH FROM DATE('%s')) ,'%s','%s')
    AND EXTRACT(MONTH FROM  transTanggalEntri) = EXTRACT(MONTH FROM DATE('%s')) -- TANGGAL PEMBUATAN TRANSAKSI
    AND EXTRACT(YEAR FROM  transTanggalEntri) = EXTRACT(YEAR FROM DATE('%s')) -- TANGGAL PEMBUATAN TRANSAKSI  
";

$sql['get_trans_reference_bp'] ="
SELECT
   CONCAT(
      SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) , -- TAHUN
      EXTRACT(MONTH FROM DATE('%s')) , -- BULAN
     '%s' , -- KODE BP,     
    LPAD(IFNULL(MAX(SUBSTR(transReferensi,-4,4)) + 1,1),4,0)
   ) AS nomorBp
FROM
   transaksi
WHERE 1 = 1 
   AND transReferensi LIKE CONCAT(SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) ,EXTRACT(MONTH FROM DATE('%s')) ,'%s','%s')
    AND EXTRACT(MONTH FROM  transTanggalEntri) = EXTRACT(MONTH FROM DATE('%s')) -- TANGGAL PEMBUATAN TRANSAKSI
    AND EXTRACT(YEAR FROM  transTanggalEntri) = EXTRACT(YEAR FROM DATE('%s')) -- TANGGAL PEMBUATAN TRANSAKSI
";

$sql['get_trans_reference_cr'] ="
SELECT
   CONCAT(
      SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) , -- TAHUN
      EXTRACT(MONTH FROM DATE('%s')) , -- BULAN
     '%s' , -- KODE CR,     
    LPAD(IFNULL(MAX(SUBSTR(transReferensi,-4,4)) + 1,1),4,0)
   ) AS nomorCr
FROM
   transaksi
WHERE 1 = 1 
   AND transReferensi LIKE CONCAT(SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) ,EXTRACT(MONTH FROM DATE('%s')) ,'%s','%s')
    AND EXTRACT(MONTH FROM  transTanggalEntri) = EXTRACT(MONTH FROM DATE('%s')) -- TANGGAL PEMBUATAN TRANSAKSI
    AND EXTRACT(YEAR FROM  transTanggalEntri) = EXTRACT(YEAR FROM DATE('%s')) -- TANGGAL PEMBUATAN TRANSAKSI  
";



$sql['get_generated_number']  = "
SELECT @GENERATE_NUMBER AS `GENERATED_NUMBER`
";

//generate number dari transaksi_bank
$sql['cek_nomor_bp_bank'] ="
 SELECT
  `transaksiBankBpkb` AS nomorBp
FROM
   `finansi_pa_transaksi_bank`
WHERE 1 = 1 
   AND `transaksiBankBpkb` LIKE CONCAT(SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) ,EXTRACT(MONTH FROM DATE('%s')) ,'%s','%s')
   AND EXTRACT(MONTH FROM `transaksiBankTanggal`) = EXTRACT(MONTH FROM DATE('%s')) -- TANGGAL PEMBUATAN SPPU
   AND EXTRACT(YEAR FROM `transaksiBankTanggal`) = EXTRACT(YEAR FROM DATE('%s')) -- TANGGAL PEMBUATAN SPPU
 ";

//generate bp
$sql['get_nomor_bp_bank'] ="
 SELECT
   CONCAT(
      SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) , -- TAHUN
      EXTRACT(MONTH FROM DATE('%s')) , -- BULAN
     '%s' , -- KODE BP,     
    LPAD(IFNULL(MAX(SUBSTR(`transaksiBankBpkb`,-4,4)) + 1,1),4,0)
   ) AS nomorBp
FROM
   `finansi_pa_transaksi_bank`
WHERE 1 = 1 
   AND `transaksiBankBpkb` LIKE CONCAT(SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) ,EXTRACT(MONTH FROM DATE('%s')) ,'%s','%s')
   AND EXTRACT(MONTH FROM `transaksiBankTanggal`) = EXTRACT(MONTH FROM DATE('%s')) -- TANGGAL PEMBUATAN SPPU
   AND EXTRACT(YEAR FROM `transaksiBankTanggal`) = EXTRACT(YEAR FROM DATE('%s')) -- TANGGAL PEMBUATAN SPPU
 ";
$sql['get_nomor_cr_bank'] ="
SELECT
   CONCAT(
      SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) , -- TAHUN
      EXTRACT(MONTH FROM DATE('%s')) , -- BULAN
     '%s' , -- KODE CR,     
    LPAD(IFNULL(MAX(SUBSTR(`transaksiKasBpkb`,-4,4)) + 1,1),4,0)
   ) AS nomorCr
FROM
  `finansi_pa_transaksi_kas`
WHERE 1 = 1 
   AND `transaksiKasBpkb` LIKE CONCAT(SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) ,EXTRACT(MONTH FROM DATE('%s')) ,'%s','%s')
   AND EXTRACT(MONTH FROM `transaksiKasTanggal`) = EXTRACT(MONTH FROM DATE('%s')) -- TANGGAL PEMBUATAN SPPU
   AND EXTRACT(YEAR FROM `transaksiKasTanggal`) = EXTRACT(YEAR FROM DATE('%s')) -- TANGGAL PEMBUATAN SPPU     
";

//generate number dari sppu
// generate cr dan bp
$sql['cek_nomor_bp_sppu'] ="
SELECT
  `sppuBPKBBp` AS nomorBp
FROM
   `finansi_pa_sppu`
WHERE 1 = 1 
   AND `sppuBPKBBp` LIKE CONCAT(SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) ,EXTRACT(MONTH FROM DATE('%s')) ,'%s','%s')
   AND EXTRACT(MONTH FROM `sppuTanggal`) = EXTRACT(MONTH FROM DATE('%s')) -- TANGGAL PEMBUATAN SPPU
   AND EXTRACT(YEAR FROM `sppuTanggal`) = EXTRACT(YEAR FROM DATE('%s')) -- TANGGAL PEMBUATAN SPPU     
";

$sql['get_nomor_bp'] ="
SELECT
   CONCAT(
      SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) , -- TAHUN
      EXTRACT(MONTH FROM DATE('%s')) , -- BULAN
     '%s' , -- KODE BP,     
    LPAD(IFNULL(MAX(SUBSTR(`sppuBPKBBp`,-4,4)) + 1,1),4,0)
   ) AS nomorBp
FROM
   `finansi_pa_sppu`
WHERE 1 = 1 
   AND `sppuBPKBBp` LIKE CONCAT(SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) ,EXTRACT(MONTH FROM DATE('%s')) ,'%s','%s')
   AND EXTRACT(MONTH FROM `sppuTanggal`) = EXTRACT(MONTH FROM DATE('%s')) -- TANGGAL PEMBUATAN SPPU
   AND EXTRACT(YEAR FROM `sppuTanggal`) = EXTRACT(YEAR FROM DATE('%s')) -- TANGGAL PEMBUATAN SPPU     
";

$sql['get_nomor_cr'] ="
SELECT
   CONCAT(
      SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) , -- TAHUN
      EXTRACT(MONTH FROM DATE('%s')) , -- BULAN
     '%s' , -- KODE CR,     
    LPAD(IFNULL(MAX(SUBSTR(`sppuBPKBCr`,-4,4)) + 1,1),4,0)
   ) AS nomorCr
FROM
   `finansi_pa_sppu`
WHERE 1 = 1 
   AND `sppuBPKBCr` LIKE CONCAT(SUBSTR(EXTRACT(YEAR FROM DATE('%s')),3,2) ,EXTRACT(MONTH FROM DATE('%s')) ,'%s','%s')
   AND EXTRACT(MONTH FROM `sppuTanggal`) = EXTRACT(MONTH FROM DATE('%s')) -- TANGGAL PEMBUATAN SPPU
   AND EXTRACT(YEAR FROM `sppuTanggal`) = EXTRACT(YEAR FROM DATE('%s')) -- TANGGAL PEMBUATAN SPPU    
";

$sql['get_nomor_pengajuan'] = "
SELECT 
	CONCAT('FPA','/',
	LPAD((MAX(CAST((SUBSTRING_INDEX(SUBSTRING_INDEX(IFNULL(pengrealNomorPengajuan,0),'/',2),'/',-1)) AS SIGNED))+1),4,0)
	,'/','KEU','/',
	(CASE LPAD(MONTH('%s'),2,0)
		WHEN '01' THEN 'I'
		WHEN '02' THEN 'II'
		WHEN '03' THEN 'III'
		WHEN '04' THEN 'IV'
		WHEN '05' THEN 'V'
		WHEN '06' THEN 'VI'
		WHEN '07' THEN 'VII'
		WHEN '08' THEN 'VIII'
		WHEN '09' THEN 'IX'
		WHEN '10' THEN 'X'
		WHEN '11' THEN 'XI'
		WHEN '12' THEN 'XII'
	END)	
	,'/',YEAR('%s')) AS nomorPengajuan
FROM
   (SELECT 1 AS dummy) dummy
   LEFT JOIN pengajuan_realisasi ON pengrealNomorPengajuan LIKE CONCAT('FPA','/','%s','/','KEU','/',
	(CASE LPAD(MONTH('%s'),2,0)
		WHEN '01' THEN 'I'
		WHEN '02' THEN 'II'
		WHEN '03' THEN 'III'
		WHEN '04' THEN 'IV'
		WHEN '05' THEN 'V'
		WHEN '06' THEN 'VI'
		WHEN '07' THEN 'VII'
		WHEN '08' THEN 'VIII'
		WHEN '09' THEN 'IX'
		WHEN '10' THEN 'X'
		WHEN '11' THEN 'XI'
		WHEN '12' THEN 'XII'
	END)   
   ,'/',YEAR('%s'))
ORDER BY pengrealId DESC
LIMIT 1;
";
// end



$sql['get_nomor_lppa'] = "
SELECT 
	CONCAT('%s','/',
	LPAD((MAX(CAST((SUBSTRING_INDEX(SUBSTRING_INDEX(IFNULL(lapLppaNoBukti,0),'/',2),'/',-1)) AS SIGNED))+1),4,0)
	,'/','%s','/',
	(CASE LPAD(MONTH('%s'),2,0)
		WHEN '01' THEN 'I'
		WHEN '02' THEN 'II'
		WHEN '03' THEN 'III'
		WHEN '04' THEN 'IV'
		WHEN '05' THEN 'V'
		WHEN '06' THEN 'VI'
		WHEN '07' THEN 'VII'
		WHEN '08' THEN 'VIII'
		WHEN '09' THEN 'IX'
		WHEN '10' THEN 'X'
		WHEN '11' THEN 'XI'
		WHEN '12' THEN 'XII'
	END)	
	,'/',YEAR('%s')) AS nomorLppa
FROM
   (SELECT 1 AS dummy) dummy
   LEFT JOIN finansi_pa_lap_lppa ON lapLppaNoBukti LIKE CONCAT('%s','/','%s','/','%s','/',
	(CASE LPAD(MONTH('%s'),2,0)
		WHEN '01' THEN 'I'
		WHEN '02' THEN 'II'
		WHEN '03' THEN 'III'
		WHEN '04' THEN 'IV'
		WHEN '05' THEN 'V'
		WHEN '06' THEN 'VI'
		WHEN '07' THEN 'VII'
		WHEN '08' THEN 'VIII'
		WHEN '09' THEN 'IX'
		WHEN '10' THEN 'X'
		WHEN '11' THEN 'XI'
		WHEN '12' THEN 'XII'
	END)   
   ,'/',YEAR('%s'))
ORDER BY lapLppaId DESC
LIMIT 1;
";
// end
?>