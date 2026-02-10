<?php

/**
 * @package lap_realisasi_penerimaan_pnbp_unit
 * Query
 * @since 27 Maret 2012
 * @copyright 2012 Gamatechno
 */



$sql['get_count_data'] = "
	SELECT
		count(renterimaId) AS total
	FROM rencana_penerimaan
	LEFT JOIN unit_kerja_ref ON renterimaUnitkerjaId = unitkerjaId AND renterimaThanggarId ='%s'
	LEFT JOIN realisasi_penerimaan ON realrenterimaId = renterimaId
	LEFT JOIN kode_penerimaan_ref ON renterimaKodeterimaId = kodeterimaId
	LEFT JOIN (
                SELECT 
                    renterimaUnitkerjaId AS totalUnitkerjaId, 
                    SUM(renterimaTotalTerima) AS totalTotalTerima, 
                    SUM(renterimaJumlah) AS totalterima 
                FROM 
                    rencana_penerimaan 
                WHERE renterimaThanggarId ='%s' 
                GROUP BY totalUnitkerjaId) AS total ON totalUnitkerjaId=unitkerjaId
	WHERE renterimaRpstatusId = 2 AND
	   (unit_kerja_ref.unitkerjaKodeSistem LIKE 
	   CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') 
	   OR 
	   unit_kerja_ref.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'))
";

$sql['get_data_realisasi_pnbp'] = "
	SELECT
		unitkerjaId           AS idunit,
		unitkerjaKode 			 AS kode_satker,
		unitkerjaNama 			 AS nama_satker,
		unitkerjaKode 			 AS kode_unit,
		unitkerjaNama 			 AS nama_unit,
		unitkerjaParentId 	 AS parentId,
		renterimaId 			 AS idrencana,
		kodeterimaId 			 AS idkode,
		kodeterimaKode 		 AS kode,
		kodeterimaNama 		 AS nama,
        IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            ((IF (renterimaTotalTerima IS NULL,0,renterimaTotalTerima))))
	      ,((IF (renterimaTotalTerima IS NULL,0,renterimaTotalTerima))) )AS target_pnbp,
          
        IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM( IF(realterimaJmlJan IS NULL,0,realterimaJmlJan)  )))
	      ,(SUM( IF(realterimaJmlJan IS NULL,0,realterimaJmlJan)  ) ) ) AS realJan,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(  IF(realterimaJmlFeb IS NULL,0,realterimaJmlFeb)   )))
	      ,(SUM(IF(realterimaJmlFeb IS NULL,0,realterimaJmlFeb)  ) ) ) AS realFeb,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlMar IS NULL,0,realterimaJmlMar)  )))
	      ,(SUM(IF(realterimaJmlMar IS NULL,0,realterimaJmlMar) ) ) ) AS realMar,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlApr IS NULL,0,realterimaJmlApr)  )))
	      ,(SUM(IF(realterimaJmlApr IS NULL,0,realterimaJmlApr)  ) ) ) AS realApr,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlMei IS NULL,0,realterimaJmlMei) )))
	      ,(SUM(IF(realterimaJmlMei IS NULL,0,realterimaJmlMei)  ) ) ) AS realMei,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlJun IS NULL,0,realterimaJmlJun) )))
	      ,(SUM(IF(realterimaJmlJun IS NULL,0,realterimaJmlJun) ) ) ) AS realJun,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlJul IS NULL,0,realterimaJmlJul) )))
	      ,(SUM(IF(realterimaJmlJul IS NULL,0,realterimaJmlJul)  ) ) ) AS realJul,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlAgt IS NULL,0,realterimaJmlAgt)  )))
	      ,(SUM(IF(realterimaJmlAgt IS NULL,0,realterimaJmlAgt ) ) ) ) AS realAgs,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlSep IS NULL,0,realterimaJmlSep)  )))
	      ,(SUM(IF(realterimaJmlSep IS NULL,0,realterimaJmlSep)  ) ) )  AS realSep,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlOkt IS NULL,0,realterimaJmlOkt) )))
	      ,(SUM(IF(realterimaJmlOkt IS NULL,0,realterimaJmlOkt) ) ) )  AS realOkt,
        
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlNov IS NULL,0,realterimaJmlNov) )))
	      ,(SUM(IF(realterimaJmlNov IS NULL,0,realterimaJmlNov) ) ) )  AS realNov,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlDes IS NULL,0,realterimaJmlDes) )))
	      ,(SUM(IF(realterimaJmlDes IS NULL,0,realterimaJmlDes) ) ) ) AS realDes,
        
        IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaTotalTerima IS NULL,0,realterimaTotalTerima) )))
	      ,(SUM(IF(realterimaTotalTerima IS NULL,0,realterimaTotalTerima)) ) ) AS total_realisasi
    
	FROM rencana_penerimaan
	LEFT JOIN unit_kerja_ref ON renterimaUnitkerjaId = unitkerjaId AND renterimaThanggarId ='%s'
	LEFT JOIN realisasi_penerimaan ON realrenterimaId = renterimaId
	LEFT JOIN kode_penerimaan_ref ON renterimaKodeterimaId = kodeterimaId
    
	WHERE
    renterimaRpstatusId = 2 AND
	(unit_kerja_ref.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') 
	OR 
	unit_kerja_ref.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'))
	GROUP BY unitkerjaKode, kodeterimaKode
   ORDER BY unitkerjaId,kodeterimaKode
   LIMIT %s,%s
";

$sql['get_data_realisasi_pnbp_cetak'] = "
	SELECT
		unitkerjaId           AS idunit,
		unitkerjaKode 			 AS kode_satker,
		unitkerjaNama 			 AS nama_satker,
		unitkerjaKode 			 AS kode_unit,
		unitkerjaNama 			 AS nama_unit,
		unitkerjaParentId 	 AS parentId,
		renterimaId 			 AS idrencana,
		kodeterimaId 			 AS idkode,
		kodeterimaKode 		 AS kode,
		kodeterimaNama 		 AS nama,
        IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            ((IF (renterimaTotalTerima IS NULL,0,renterimaTotalTerima))))
	      ,((IF (renterimaTotalTerima IS NULL,0,renterimaTotalTerima))) )AS target_pnbp,
          
        IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM( IF(realterimaJmlJan IS NULL,0,realterimaJmlJan)  )))
	      ,(SUM( IF(realterimaJmlJan IS NULL,0,realterimaJmlJan)  ) ) ) AS realJan,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(  IF(realterimaJmlFeb IS NULL,0,realterimaJmlFeb)   )))
	      ,(SUM(IF(realterimaJmlFeb IS NULL,0,realterimaJmlFeb)  ) ) ) AS realFeb,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlMar IS NULL,0,realterimaJmlMar)  )))
	      ,(SUM(IF(realterimaJmlMar IS NULL,0,realterimaJmlMar) ) ) ) AS realMar,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlApr IS NULL,0,realterimaJmlApr)  )))
	      ,(SUM(IF(realterimaJmlApr IS NULL,0,realterimaJmlApr)  ) ) ) AS realApr,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlMei IS NULL,0,realterimaJmlMei) )))
	      ,(SUM(IF(realterimaJmlMei IS NULL,0,realterimaJmlMei)  ) ) ) AS realMei,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlJun IS NULL,0,realterimaJmlJun) )))
	      ,(SUM(IF(realterimaJmlJun IS NULL,0,realterimaJmlJun) ) ) ) AS realJun,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlJul IS NULL,0,realterimaJmlJul) )))
	      ,(SUM(IF(realterimaJmlJul IS NULL,0,realterimaJmlJul)  ) ) ) AS realJul,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlAgt IS NULL,0,realterimaJmlAgt)  )))
	      ,(SUM(IF(realterimaJmlAgt IS NULL,0,realterimaJmlAgt ) ) ) ) AS realAgs,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlSep IS NULL,0,realterimaJmlSep)  )))
	      ,(SUM(IF(realterimaJmlSep IS NULL,0,realterimaJmlSep)  ) ) )  AS realSep,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlOkt IS NULL,0,realterimaJmlOkt) )))
	      ,(SUM(IF(realterimaJmlOkt IS NULL,0,realterimaJmlOkt) ) ) )  AS realOkt,
        
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlNov IS NULL,0,realterimaJmlNov) )))
	      ,(SUM(IF(realterimaJmlNov IS NULL,0,realterimaJmlNov) ) ) )  AS realNov,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlDes IS NULL,0,realterimaJmlDes) )))
	      ,(SUM(IF(realterimaJmlDes IS NULL,0,realterimaJmlDes) ) ) ) AS realDes,
        
        IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaTotalTerima IS NULL,0,realterimaTotalTerima) )))
	      ,(SUM(IF(realterimaTotalTerima IS NULL,0,realterimaTotalTerima)) ) ) AS total_realisasi
    
	FROM rencana_penerimaan
	LEFT JOIN unit_kerja_ref ON renterimaUnitkerjaId = unitkerjaId AND renterimaThanggarId ='%s'
	LEFT JOIN realisasi_penerimaan ON realrenterimaId = renterimaId
	LEFT JOIN kode_penerimaan_ref ON renterimaKodeterimaId = kodeterimaId
    
	WHERE
    renterimaRpstatusId = 2 AND
	(unit_kerja_ref.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') 
	OR 
	unit_kerja_ref.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'))
	GROUP BY unitkerjaKode, kodeterimaKode
   ORDER BY unitkerjaId,kodeterimaKode
";

$sql['get_total_data_realisasi_pnbp_per_bulan']="
SELECT
	SUM(rp.target_pnbp) AS t_target_pnbp,
	SUM(rp.realJan) AS t_realJan,
	SUM(rp.realFeb) AS t_realFeb,
	SUM(rp.realMar) AS t_realMar,
	SUM(rp.realApr) AS t_realApr,
	SUM(rp.realMei) AS t_realMei,
	SUM(rp.realJun) AS t_realJun,
	SUM(rp.realJul) AS t_realJul,
	SUM(rp.realAgs) AS t_realAgs,
	SUM(rp.realSep) AS t_realSep,
	SUM(rp.realOkt) AS t_realOkt,
	SUM(rp.realNov) AS t_realNov,
	SUM(rp.realDes) AS t_realDes,
	SUM(rp.total_realisasi) AS t_total_realisasi

FROM (
	SELECT
        IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            ((IF (renterimaTotalTerima IS NULL,0,renterimaTotalTerima))))
	      ,((IF (renterimaTotalTerima IS NULL,0,renterimaTotalTerima))) )AS target_pnbp,
          
        IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM( IF(realterimaJmlJan IS NULL,0,realterimaJmlJan)  )))
	      ,(SUM( IF(realterimaJmlJan IS NULL,0,realterimaJmlJan)  ) ) ) AS realJan,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(  IF(realterimaJmlFeb IS NULL,0,realterimaJmlFeb)   )))
	      ,(SUM(IF(realterimaJmlFeb IS NULL,0,realterimaJmlFeb)  ) ) ) AS realFeb,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlMar IS NULL,0,realterimaJmlMar)  )))
	      ,(SUM(IF(realterimaJmlMar IS NULL,0,realterimaJmlMar) ) ) ) AS realMar,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlApr IS NULL,0,realterimaJmlApr)  )))
	      ,(SUM(IF(realterimaJmlApr IS NULL,0,realterimaJmlApr)  ) ) ) AS realApr,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlMei IS NULL,0,realterimaJmlMei) )))
	      ,(SUM(IF(realterimaJmlMei IS NULL,0,realterimaJmlMei)  ) ) ) AS realMei,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlJun IS NULL,0,realterimaJmlJun) )))
	      ,(SUM(IF(realterimaJmlJun IS NULL,0,realterimaJmlJun) ) ) ) AS realJun,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlJul IS NULL,0,realterimaJmlJul) )))
	      ,(SUM(IF(realterimaJmlJul IS NULL,0,realterimaJmlJul)  ) ) ) AS realJul,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlAgt IS NULL,0,realterimaJmlAgt)  )))
	      ,(SUM(IF(realterimaJmlAgt IS NULL,0,realterimaJmlAgt ) ) ) ) AS realAgs,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlSep IS NULL,0,realterimaJmlSep)  )))
	      ,(SUM(IF(realterimaJmlSep IS NULL,0,realterimaJmlSep)  ) ) )  AS realSep,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlOkt IS NULL,0,realterimaJmlOkt) )))
	      ,(SUM(IF(realterimaJmlOkt IS NULL,0,realterimaJmlOkt) ) ) )  AS realOkt,
        
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlNov IS NULL,0,realterimaJmlNov) )))
	      ,(SUM(IF(realterimaJmlNov IS NULL,0,realterimaJmlNov) ) ) )  AS realNov,
          
		IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaJmlDes IS NULL,0,realterimaJmlDes) )))
	      ,(SUM(IF(realterimaJmlDes IS NULL,0,realterimaJmlDes) ) ) ) AS realDes,
        
        IF(`rencana_penerimaan`.`renterimaAlokasiUnit` > 0,
	       (( `rencana_penerimaan`.`renterimaAlokasiUnit` / 100 ) * 
            (SUM(IF(realterimaTotalTerima IS NULL,0,realterimaTotalTerima) )))
	      ,(SUM(IF(realterimaTotalTerima IS NULL,0,realterimaTotalTerima)) ) ) AS total_realisasi
    
	FROM rencana_penerimaan
	LEFT JOIN unit_kerja_ref ON renterimaUnitkerjaId = unitkerjaId AND renterimaThanggarId ='%s'
	LEFT JOIN realisasi_penerimaan ON realrenterimaId = renterimaId
	LEFT JOIN kode_penerimaan_ref ON renterimaKodeterimaId = kodeterimaId
    
	WHERE
    renterimaRpstatusId = 2 AND
	(unit_kerja_ref.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') 
	OR 
	unit_kerja_ref.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'))
	GROUP BY unitkerjaKode, kodeterimaKode
) rp
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
		thanggarId 		AS id,
		thanggarNama 	AS name
	FROM
		tahun_anggaran
	WHERE
		thanggarId='%s'
";

?>