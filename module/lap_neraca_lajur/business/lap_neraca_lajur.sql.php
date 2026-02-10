<?php

$sql['get_minmax_tahun_transaksi'] = "
   SELECT
      YEAR(MIN(transTanggalEntri)) - 5 AS minTahun,
      YEAR(MAX(transTanggalEntri)) + 5 AS maxTahun
   FROM
      transaksi
";

$sql['get_data_laporan']="
SELECT 
	REPLACE(c.`coaKodeSistem`,'.','') AS ks,
	c.`coaId` AS id_akun,
	c.`coaKodeSistem` AS kode_sistem,
	c.`coaKodeAkun` AS kode_akun,
	c.`coaNamaAkun` AS nama_akun,
	c.`coaLevelAkun` AS level_akun,
	c.`coaCoaKelompokId` AS kelompok_akun,
        c.`coaIsDebetPositif` AS debet_positif,
	SUM(IF(`bbPembukuanRefId` IS NULL AND `bbPdId` IS NULL, IF(c.`coaIsDebetPositif` = 1 , `bbDebet`,0),0)) AS saldo_awal_debet,
	SUM(IF(`bbPembukuanRefId` IS NULL AND `bbPdId` IS NULL, IF(c.`coaIsDebetPositif` = 0 , `bbKredit`,0),0)) AS saldo_awal_kredit,        
	SUM(IF(tr.`transId`,`bbDebet`,0)) AS neraca_debet,
	SUM(IF(tr.`transId`,`bbKredit`,0)) AS neraca_kredit
FROM     
        buku_besar_his bhis
        JOIN coa c ON bhis.`bbCoaId` = c.`coaId`
        JOIN `tahun_pembukuan_periode` tpp ON tpp.`tppId` = bhis.`bbTppId`  AND tpp.`tppIsBukaBuku` = 'Y'
        LEFT JOIN pembukuan_referensi pr ON pr.`prId` = bhis.`bbPembukuanRefId` AND pr.`prIsPosting` = 'Y'		
	LEFT JOIN pembukuan_detail pd 
	   ON pd.`pdPrId` = pr.`prId` AND 
	   pd.`pdId` = bhis.`bbPdId` AND 
	   pd.`pdCoaId` = bhis.`bbCoaId`
        LEFT JOIN transaksi tr 
            ON  tr.`transTppId` = tpp.`tppId` 
            AND tr.`transIsJurnal` = 'Y'
            AND pr.`prTransId` = tr.`transId`	
            AND tr.`transTanggalEntri` BETWEEN '%s' AND '%s'
	           
WHERE 
c.coaId NOT IN(SELECT DISTINCT(coaParentAkun) FROM coa) 
AND
c.`coaId` NOT IN (SELECT `coatipecoaCoaId` FROM `coa_tipe_coa` WHERE `coatipecoaCtrId` = 3)
GROUP BY c.`coaId`	
ORDER BY  kode_akun ASC
";

/*
$sql['get_data_laporan']="
SELECT 
	REPLACE(c.`coaKodeSistem`,'.','') AS ks,
	c.`coaId` AS id_akun,
	c.`coaKodeSistem` AS kode_sistem,
	c.`coaKodeAkun` AS kode_akun,
	c.`coaNamaAkun` AS nama_akun,
	c.`coaLevelAkun` AS level_akun,
	c.`coaCoaKelompokId` as kelompok_akun,
	SUM(IFNULL(tp.`tpDebet`,0)) AS neraca_sa_debet,
	SUM(IFNULL(tp.`tpKredit`,0)) AS neraca_sa_kredit,
	SUM(IF( c.`coaCoaKelompokId` IN (4,5) AND tr.`transIsJurnal` = 'Y', 
			IF(pd.`pdStatus` = 'D'  ,IF(SUBSTR(tr.`transReferensi`,1,2) ='JP',0,pd.`pdNilai`),0),0)
		) AS aktivitas_debet,
	SUM(IF( c.`coaCoaKelompokId` IN (4,5)  AND  tr.`transIsJurnal` = 'Y', 
			IF(pd.`pdStatus` = 'K'  ,IF(SUBSTR(tr.`transReferensi`,1,2) ='JP',0,pd.`pdNilai`),0),0)
		) AS aktivitas_kredit,
	SUM(IF( (c.`coaCoaKelompokId` IN (1,2,3) ) AND  tr.`transIsJurnal` = 'Y', 
			IF(pd.`pdStatus` = 'D'  ,IF(SUBSTR(tr.`transReferensi`,1,2) ='JP',0,pd.`pdNilai`),0),0)
		) AS neraca_debet,
	SUM(IF( (c.`coaCoaKelompokId` IN (1,2,3) ) AND  tr.`transIsJurnal` = 'Y',  
			IF(pd.`pdStatus` = 'K'  ,IF(SUBSTR(tr.`transReferensi`,1,2) ='JP',0,pd.`pdNilai`),0),0)
		) AS neraca_kredit,
	SUM( IF(tr.`transIsJurnal` = 'Y', 
		IF(pd.`pdStatus` = 'D'  ,IF(SUBSTR(tr.`transReferensi`,1,2) ='JP',pd.`pdNilai`,0),0),0)
		) AS jp_debet,
	SUM( IF(tr.`transIsJurnal` = 'Y', 
		IF(pd.`pdStatus` = 'K'  ,IF(SUBSTR(tr.`transReferensi`,1,2) ='JP',pd.`pdNilai`,0),0),0)
		) AS jp_kredit		
FROM coa c
	LEFT JOIN tahun_pembukuan tp ON tp.`tpCoaId` = c.`coaId`
	LEFT JOIN pembukuan_detail pd ON pd.`pdCoaId` = c.`coaId`
	LEFT JOIN pembukuan_referensi pr 
        ON (pr.`prId` = pd.`pdPrId` 
        AND (pr.`prTanggal` BETWEEN '%s' AND '%s'))
	LEFT JOIN transaksi tr ON (
			tr.`transId` = pr.`prTransId`  
			AND 
			tr.`transIsJurnal` = 'Y'
			AND
			tr.`transThanggarId` = '%s'
			AND
            (transTanggalEntri BETWEEN '%s' AND '%s')
			)
  	INNER JOIN `unit_kerja_ref` uk 
	ON (
	uk.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	uk.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	
	)			
# where pd.`pdNilai` > 0
GROUP BY c.`coaId`	
ORDER BY  kode_akun ASC
";
*/
$sql['get_data_laporan_old']="
SELECT 
REPLACE(c.`coaKodeSistem`,'.','') AS ks,
c.`coaId` as id_akun,
c.`coaKodeSistem` AS kode_sistem,
c.`coaKodeAkun` AS kode_akun,
c.`coaNamaAkun` AS nama_akun,
c.`coaLevelAkun` AS level_akun
FROM coa c
ORDER BY ks ASC
";

//COMBO
$sql['get_combo_tahun_anggaran']="
	SELECT
		thanggarId 		AS id,
		thanggarNama 	AS name
	FROM
		tahun_anggaran
	ORDER BY thanggarNama
";
//aktif
$sql['get_tahun_anggaran_aktif']="
	SELECT
		thanggarId 		AS id,
		thanggarNama 	AS name
	FROM
		tahun_anggaran
	WHERE
		thanggarIsAktif='Y'
";
//aktif
$sql['get_tahun_anggaran']="
SELECT 
  `thanggarId` AS `id`,
  `thanggarNama` AS `name`,
  `thanggarBuka` AS tgl_buka,
  `thanggarTutup` AS tgl_tutup,
  (YEAR(`thanggarBuka`)) AS `tahun_buka`,
  (YEAR(`thanggarTutup`)) AS `tahun_tutup` 
FROM
  `tahun_anggaran`
WHERE
  `thanggarId` = '%s'
";

$sql['get_level_coa']="
SELECT	DISTINCT c.`coaLevelAkun` AS level_coa	FROM coa c
";

$sql['get_child_akun'] ="
SELECT	
COUNT(c.`coaId`) AS jml_akun
 FROM coa c
WHERE c.`coaParentAkun`= '%s'
";

$sql['get_header_kolom']="
SELECT 
  cr.`ctrNamaTipe` AS nama ,
  'DEBET' AS debet,
  'KREDIT' AS kredit
FROM
  coa_tipe_ref cr 
";
?>