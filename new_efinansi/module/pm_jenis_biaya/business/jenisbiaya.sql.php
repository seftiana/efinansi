<?php


$sql['get_search_count'] = "
   SELECT FOUND_ROWS() AS total
";

$sql['get_data_jenisbiaya'] = "
    SELECT
      SQL_CALC_FOUND_ROWS 
		jenisBiayaId			as jenisbiaya_id,
		jenisBiayaKode			as jenisbiaya_kode,
		KelJnsBiayaNama			as jeniskeljns_nama,
		jenisBiayaNama			as jenisbiaya_nama,
		jenisBiayaStatus 		as jenisbiaya_status,
		kelJnsBiayaId			as id,
        jenisBiayaAccrual       as jenisbiaya_pencatatan
   FROM
      pm_jenis_biaya a
		JOIN pm_kel_jenis_biaya ON kelJnsBiayaId = jenisKelJnsBiayaId
	WHERE 
		jenisBiayaNama LIKE '%s'
		AND (jenisKelJnsBiayaId = '%s' OR 'all' = '%s')
		AND jenisBiayaStatusEdit = 'boleh'
";

$sql['get_data_jenisbiaya_jb_accrual'] = "
        AND (jenisBiayaAccrual = '%s')
";

$sql['get_data_jenisbiaya_jb_accrual_isnull'] = "
        AND (jenisBiayaAccrual IS NULL)
";

$sql['get_data_jenisbiaya_order_by'] = "
   ORDER BY 
		jenisBiayaKode ASC 
   LIMIT %s, %s
";

$sql['get_data_jenisbiaya_belum_diset_tipe_pencatatan'] = "
SELECT
  COUNT(`jenisBiayaId`) AS total_row
FROM
  pm_jenis_biaya
WHERE
  jenisBiayaStatusEdit = 'boleh'
  AND
  jenisBiayaAccrual IS NULL
";


$sql['get_combo_kelompok'] = "
SELECT
   kelJnsBiayaId AS id,
   kelJnsBiayaNama AS name
FROM 
   pm_kel_jenis_biaya";

$sql['get_combo_kelompok_pencarian'] = "
SELECT
   kelJnsBiayaId AS id,
   kelJnsBiayaNama AS name
FROM 
   pm_kel_jenis_biaya
";


$sql['do_update_jenisbiaya'] = "
UPDATE pm_jenis_biaya 
SET
	jenisBiayaAccrual ='%s'
WHERE 
    jenisBiayaId = '%s'
";
?>