<?php

/**
$sql['get_data_pagu_old'] = "
	SELECT 
		paguAnggUnitId as id,
		(if(tempUnitId IS NULL,unitkerjaId,tempUnitId)) AS idsatker,
		(if(tempUnitKode IS NULL,unitkerjaKode,tempUnitKode)) AS kodesatker,
		(if(tempUnitNama IS NULL,unitkerjaNama,tempUnitNama)) AS satker,
		(if(tempUnitId IS NULL,'-',unitkerjaId)) AS idunit,
		(if(tempUnitKode IS NULL,'-',unitkerjaKode)) AS kodeunit,
		(if(tempUnitNama IS NULL,'-',unitkerjaNama)) AS unit,
		unitkerjaNama AS subUnitNama,
		unitkerjaParentId AS parentId,
		paguAnggUnitNominal as nominal,
		paguBasKeterangan as bas_nama,
		sumberdanaNama AS sumber_dana, 
		paguAnggUnitNominalTersedia AS nominal_tersedia 
	FROM unit_kerja_ref
		LEFT JOIN 
			(SELECT 
				unitkerjaId AS tempUnitId,
				unitkerjaKode AS tempUnitKode,
				unitkerjaNama AS tempUnitNama,
				unitkerjaParentId AS tempParetnId
			FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUniKerja ON(unitkerjaParentId=tempUnitId)
		JOIN finansi_pagu_anggaran_unit ON (unitkerjaId = paguAnggUnitUnitKerjaId)
		JOIN finansi_ref_pagu_bas ON (paguAnggUnitPaguBasId = paguBasId)
		LEFT JOIN finansi_ref_sumber_dana ON (paguSumberDana = sumberdanaId)
	WHERE 
		1=1
		%s
		%s
	ORDER BY satker,unit
	LIMIT %s, %s
";
$sql['get_count_data_pagu_old'] = "
	SELECT 
		COUNT(paguAnggUnitId) as total
	FROM unit_kerja_ref
		LEFT JOIN 
			(SELECT 
				unitkerjaId AS tempUnitId,
				unitkerjaKode AS tempUnitKode,
				unitkerjaNama AS tempUnitNama,
				unitkerjaParentId AS tempParetnId
			FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUniKerja ON(unitkerjaParentId=tempUnitId)
		JOIN finansi_pagu_anggaran_unit ON (unitkerjaId = paguAnggUnitUnitKerjaId)
		JOIN finansi_ref_pagu_bas ON (paguAnggUnitPaguBasId = paguBasId)
	WHERE 
		1=1
		%s
		%s
";

$sql['get_data_pagu_by_id_old']="
	SELECT 
		paguAnggUnitId AS id,
      paguAnggUnitUnitKerjaId AS unitpagu_id,
      paguAnggUnitThAnggaranId AS tahun_anggaran,
      paguAnggUnitPaguBasId AS bas_id,
		(if(tempUnitId IS NULL,unitkerjaId,tempUnitId)) AS satker,
		(if(tempUnitNama IS NULL,unitkerjaNama,tempUnitNama)) AS satker_label,
		(if(tempUnitId IS NULL,'-',unitkerjaId)) AS unitkerja,
		(if(tempUnitNama IS NULL,'-',unitkerjaNama)) AS unitkerja_label,
		unitkerjaNama AS subUnitNama,
		unitkerjaParentId AS parentId,
		paguAnggUnitNominal AS nominal,
		thanggarNama AS tahun_anggaran_label,
		paguSumberDana AS sumber_dana,
		sumberdanaNama AS sumber_dana_label, 
		paguAnggUnitNominalTersedia AS nominal_tersedia 
	FROM unit_kerja_ref
		LEFT JOIN 
			(SELECT 
				unitkerjaId AS tempUnitId,
				unitkerjaKode AS tempUnitKode,
				unitkerjaNama AS tempUnitNama,
				unitkerjaParentId AS tempParetnId
			FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUniKerja ON(unitkerjaParentId=tempUnitId)
		JOIN finansi_pagu_anggaran_unit ON (unitkerjaId = paguAnggUnitUnitKerjaId)
		JOIN finansi_ref_pagu_bas ON (paguAnggUnitPaguBasId = paguBasId)
		JOIN tahun_anggaran ON (thanggarId = paguAnggUnitThAnggaranId)
		LEFT JOIN finansi_ref_sumber_dana ON paguSumberDana = sumberdanaId
	WHERE 
		paguAnggUnitId=%s
";
*/
$sql['get_data_pagu'] = "
	SELECT 
		paguAnggUnitId AS id,
		IFNULL(p.unitkerjaId,c.unitkerjaId) AS idsatker,
		IFNULL(p.unitkerjaKode,c.unitkerjaKode) AS kodesatker,
		IFNULL(p.unitkerjaNama,c.unitkerjaNama) AS satker,
		c.unitkerjaId AS idunit,
		c.unitkerjaKode AS kodeunit,
		c.unitkerjaNama AS unit,
		c.unitkerjaNama AS subUnitNama,
		c.unitkerjaParentId AS parentId,
		paguAnggUnitNominal AS nominal,
		paguBasKeterangan AS bas_nama,
		sumberdanaNama AS sumber_dana, 
		paguAnggUnitNominalTersedia AS nominal_tersedia 
	FROM unit_kerja_ref c
		LEFT JOIN 
			unit_kerja_ref p ON p.unitkerjaId = c.unitkerjaParentId
		JOIN finansi_pagu_anggaran_unit ON (c.unitkerjaId = paguAnggUnitUnitKerjaId)
		JOIN finansi_ref_pagu_bas ON (paguAnggUnitPaguBasId = paguBasId)
		LEFT JOIN finansi_ref_sumber_dana ON (paguSumberDana = sumberdanaId)
WHERE 
    paguAnggUnitThAnggaranId = '%s'
    AND
   (c.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	c.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)
ORDER BY p.unitkerjaKode ASC		
	
	LIMIT %s, %s
";

$sql['get_count_data_pagu'] = "
	SELECT 
		count(paguAnggUnitId) AS total
	FROM unit_kerja_ref c
		LEFT JOIN 
			unit_kerja_ref p ON p.unitkerjaId = c.unitkerjaParentId
		JOIN finansi_pagu_anggaran_unit ON (c.unitkerjaId = paguAnggUnitUnitKerjaId)
		JOIN finansi_ref_pagu_bas ON (paguAnggUnitPaguBasId = paguBasId)
		LEFT JOIN finansi_ref_sumber_dana ON (paguSumberDana = sumberdanaId)
WHERE 
    paguAnggUnitThAnggaranId = '%s'
    AND
   (c.unitkerjaKodeSistem LIKE 
	CONCAT((
			SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s'),'.','%s') OR 
	c.unitkerjaKodeSistem = 
			(SELECT	
				unitkerjaKodeSistem 
			FROM 
				unit_kerja_ref 
			WHERE 
				unit_kerja_ref.unitkerjaId='%s')
	)
ORDER BY p.unitkerjaKode ASC	
";

$sql['get_data_pagu_by_id']="
	SELECT 
		paguAnggUnitId AS id,
		paguAnggUnitUnitKerjaId AS unitpagu_id,
		paguAnggUnitThAnggaranId AS tahun_anggaran,
		paguAnggUnitPaguBasId AS bas_id,
		paguBasKeterangan AS bas_nama,

		IFNULL(p.unitkerjaId,c.unitkerjaId) AS satker,
		IFNULL(p.unitkerjaNama,c.unitkerjaNama) AS satker_label,
		IFNULL(p.unitkerjaKode,c.unitkerjaKode) AS kodesatker,
		c.unitkerjaId AS unitkerja,
		c.unitkerjaNama AS unitkerja_label,
		
		c.unitkerjaNama AS subUnitNama,
		c.unitkerjaParentId AS parentId,
		paguAnggUnitNominal AS nominal,
		thanggarNama AS tahun_anggaran_label,
		paguSumberDana AS sumber_dana,
		sumberdanaNama AS sumber_dana_label, 
		paguAnggUnitNominalTersedia AS nominal_tersedia 
		FROM unit_kerja_ref c
		LEFT JOIN 
			unit_kerja_ref p ON p.unitkerjaId = c.unitkerjaParentId
		JOIN finansi_pagu_anggaran_unit ON (c.unitkerjaId = paguAnggUnitUnitKerjaId)
		JOIN finansi_ref_pagu_bas ON (paguAnggUnitPaguBasId = paguBasId)
		JOIN tahun_anggaran ON (thanggarId = paguAnggUnitThAnggaranId)
		LEFT JOIN finansi_ref_sumber_dana ON paguSumberDana = sumberdanaId
	WHERE 
		paguAnggUnitId=%s
";

$sql['do_add_pagu']="
	INSERT INTO
		finansi_pagu_anggaran_unit(paguAnggUnitUnitKerjaId, paguAnggUnitThAnggaranId, paguAnggUnitPaguBasId, paguAnggUnitNominal,paguSumberDana)
	VALUES(%s, %s, %s, %s, %s)
";
$sql['do_update_pagu']="
	UPDATE 
		finansi_pagu_anggaran_unit
	SET
		paguAnggUnitUnitKerjaId=%s, 
		paguAnggUnitThAnggaranId=%s, 
		paguAnggUnitPaguBasId=%s, 
		paguAnggUnitNominal=%s,
		paguSumberDana=%s, 
		paguAnggUnitNominalTersedia=%s 
	WHERE
		paguAnggUnitId=%s
";
//COMBO
$sql['get_combo_tahun_anggaran']="
	SELECT
		thanggarId as id,
		thanggarNama as name
	FROM 
		tahun_anggaran
	ORDER BY thanggarNama DESC
";

$sql['get_combo_bas']="
	SELECT
		paguBasId as id,
		concat(paguBasKode,'-',paguBasKeterangan) as name
	FROM 
		finansi_ref_pagu_bas 
    WHERE paguBasParentId = 0 AND paguBasStatusAktif = 'Y'
	ORDER BY paguBasKeterangan DESC
";

//aktif
$sql['get_tahun_anggaran_aktif']="
	SELECT
		thanggarId as id,
		thanggarNama as name
	FROM 
		tahun_anggaran
	WHERE
		thanggarIsAktif='Y'
";

$sql['do_delete_pagu_by_id']="
	DELETE from finansi_pagu_anggaran_unit
   WHERE 
      paguAnggUnitId='%s'
";

$sql['do_delete_pagu_by_array_id']="
	DELETE from finansi_pagu_anggaran_unit
   WHERE 
      paguAnggUnitId IN ('%s')
";

$sql['do_copy_pagu_naik']="
   INSERT INTO 
      finansi_pagu_anggaran_unit (
         paguAnggUnitThAnggaranId,
         paguAnggUnitUnitKerjaId,
         paguAnggUnitPaguBasId,
         paguAnggUnitNominal
      )
   SELECT
      (SELECT thanggarId FROM tahun_anggaran WHERE thanggarId='%s'),
      paguAnggUnitUnitKerjaId,
      paguAnggUnitPaguBasId,
      (SELECT paguAnggUnitNominal + ((paguAnggUnitNominal*%d)/100))  
   FROM 
      finansi_pagu_anggaran_unit 
   WHERE  
      paguAnggUnitThAnggaranId = '%s' AND 
      paguAnggUnitUnitKerjaId='%s'
";

$sql['do_copy_pagu_turun']="
   INSERT INTO 
      finansi_pagu_anggaran_unit (
         paguAnggUnitThAnggaranId,
         paguAnggUnitUnitKerjaId,
         paguAnggUnitPaguBasId,
         paguAnggUnitNominal
      )
   SELECT
      (SELECT thanggarId FROM tahun_anggaran WHERE thanggarId='%s'),
      paguAnggUnitUnitKerjaId,
      paguAnggUnitPaguBasId,
      (SELECT paguAnggUnitNominal - ((paguAnggUnitNominal*%d)/100))  
   FROM 
      finansi_pagu_anggaran_unit 
   WHERE  
      paguAnggUnitThAnggaranId = '%s' AND 
      paguAnggUnitUnitKerjaId='%s'
";

?>
