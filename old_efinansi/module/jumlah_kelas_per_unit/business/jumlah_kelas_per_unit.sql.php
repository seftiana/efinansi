<?php

$sql['get_count_row_data'] = "
SELECT 
  COUNT(jku.`jmlKelasId`) AS total
FROM
  `finansi_pa_jumlah_kelas_per_unit` jku 
WHERE
jku.`jmlKelasTahunAnggaranId` = %s
AND
jku.`jmlKelasUnitKerjaId` = %s
";

$sql['get_data_jml_kelas_per_unit'] = "
SELECT
    SQL_CALC_FOUND_ROWS
    jku.`jmlKelasId` AS kelas_id,
    uk.`unitkerjaKode` AS unit_kerja_kode,
    uk.`unitkerjaNama` AS unit_kerja_nama,
    IFNULL(jku.`jmlKelasTotalSmGasal`,jku.`jmlKelasTotal`) AS jumlah_kelas_gasal,
    IFNULL(jku.`jmlKelasTotalSmGenap`,jku.`jmlKelasTotal`) AS jumlah_kelas_genap
FROM 
   `finansi_pa_jumlah_kelas_per_unit` jku
   LEFT JOIN unit_kerja_ref uk ON uk.`unitkerjaId` = jku.`jmlKelasUnitKerjaId`
   LEFT JOIN tahun_anggaran ta ON ta.`thanggarId` = jku.`jmlKelasTahunAnggaranId`
WHERE
jku.`jmlKelasTahunAnggaranId` = %s   
LIMIT %s,%s
";

$sql['get_count_data_jml_kelas_per_unit'] = "
SELECT
    FOUND_ROWS() AS total
FROM 
    `finansi_pa_jumlah_kelas_per_unit`

";

$sql['get_data_jml_kelas_per_unit_by_id'] = "
SELECT
    jku.`jmlKelasId` AS jumlah_kelas_id,
    uk.`unitkerjaId` AS unit_kerja_id,
    uk.`unitkerjaId` AS unit_kerja_id_old,
    uk.`unitkerjaKode` AS unit_kerja_kode,
    uk.`unitkerjaNama` AS unit_kerja_nama,
    jku.`jmlKelasTotal` AS jumlah_kelas,
    jku.`jmlKelasTahunAnggaranId` AS tahun_anggaran_id,
    jku.`jmlKelasTahunAnggaranId` AS tahun_anggaran_id_old,
    jku.`jmlKelasProdiNama` AS prodi_nama,
    jku.`jmlKelasProdiSmGasalId` AS prodi_sm_gasal_id,
    jku.`jmlKelasProdiSmGenapId` AS prodi_sm_genap_id,
    CONCAT(jku.`jmlKelasTotalSmGasal`,'|', jku.`jmlKelasProdiNama`) AS prodi_sm_gasal,
    CONCAT(jku.`jmlKelasTotalSmGenap`,'|', jku.`jmlKelasProdiNama`) AS prodi_sm_genap
FROM
    `finansi_pa_jumlah_kelas_per_unit` jku
    INNER JOIN `unit_kerja_ref` uk
        ON uk.`unitkerjaId` = jku.`jmlKelasUnitKerjaId`
WHERE
  jku.`jmlKelasId` = '%s'
";

$sql['do_add_jml_kelas_per_unit']="
INSERT INTO `finansi_pa_jumlah_kelas_per_unit`
            (`jmlKelasTahunAnggaranId`,
             `jmlKelasUnitKerjaId`,
             `jmlKelasTotal`,
             `jmlKelasProdiNama`,
             `jmlKelasProdiSmGasalId`,
             `jmlKelasProdiSmGenapId`,
             `jmlKelasTotalSmGasal`,
             `jmlKelasTotalSmGenap`
            )
VALUES ('%s','%s','%s','%s','%s','%s','%s','%s')
";

$sql['do_update_jml_kelas_per_unit']="
UPDATE 
    `finansi_pa_jumlah_kelas_per_unit`
SET 
             `jmlKelasTahunAnggaranId` = '%s',
             `jmlKelasUnitKerjaId` = '%s',
             `jmlKelasTotal` = '%s',
             `jmlKelasProdiNama` = '%s',
             `jmlKelasProdiSmGasalId` = '%s',
             `jmlKelasProdiSmGenapId` = '%s',
             `jmlKelasTotalSmGasal` = '%s',
             `jmlKelasTotalSmGenap` = '%s'
WHERE `jmlKelasId` = '%s'
";


$sql['do_delete_jml_kelas_per_unit_by_id']="
DELETE
FROM `finansi_pa_jumlah_kelas_per_unit`
WHERE `jmlKelasId` IN (%s)
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


?>