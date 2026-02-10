<?php

//===GET===
$sql['get_jenis_laporan'] = "
	SELECT
		kelJnsId AS id,
		kelJnsNama AS name
	FROM
		kelompok_jenis_laporan_ref
	WHERE
		kelJnsPrntId = 0
";

$sql['get_bentuk_transaksi'] = "
	SELECT
		kelJnsId AS id,
		kelJnsNama AS name
	FROM
		kelompok_jenis_laporan_ref
	WHERE
		kelJnsPrntId != 0
	AND
		kelJnsPrntId = '%s'
";

$sql['get_count'] =
   "SELECT
      count(*) AS total
		FROM kelompok_laporan_ref
		LEFT JOIN(
		SELECT
			a.kelJnsId,
			CASE WHEN jenis_laporan=bentuk_transaksi
			THEN ''
			WHEN jenis_laporan!=bentuk_transaksi THEN bentuk_transaksi END AS bentuk_transaksi,
			jenis_laporan,
            jenis_laporan_id
		FROM(
			SELECT
				a.kelJnsId,
                IFNULL(b.kelJnsId,a.kelJnsId) AS  jenis_laporan_id,
				IFNULL(b.kelJnsNama,a.kelJnsNama) AS jenis_laporan,
				a.kelJnsNama AS bentuk_transaksi
			FROM kelompok_jenis_laporan_ref a
			LEFT JOIN kelompok_jenis_laporan_ref b ON b.kelJnsId = a.kelJnsPrntId
		)a ORDER BY kelJnsId
		) a ON a.kelJnsId = kellapJnsId
	WHERE
		kellapNama LIKE '%s'
        %s
        ";

$sql['get_data'] =
   "SELECT
			kellapId AS id,
			kellapNama AS nama,
			bentuk_transaksi,
			jenis_laporan AS jns_lap,
            jenis_laporan_id,
			kellapIsTambah AS is_tambah
		FROM kelompok_laporan_ref
		LEFT JOIN(
		SELECT
			a.kelJnsId,
			CASE WHEN jenis_laporan=bentuk_transaksi
			THEN ''
			WHEN jenis_laporan!=bentuk_transaksi THEN bentuk_transaksi END AS bentuk_transaksi,
			jenis_laporan,
            jenis_laporan_id
		FROM(
			SELECT
				a.kelJnsId,
                IFNULL(b.kelJnsId,a.kelJnsId) AS  jenis_laporan_id,
				IFNULL(b.kelJnsNama,a.kelJnsNama) AS jenis_laporan,
				a.kelJnsNama AS bentuk_transaksi
			FROM kelompok_jenis_laporan_ref a
			LEFT JOIN kelompok_jenis_laporan_ref b ON b.kelJnsId = a.kelJnsPrntId
		)a ORDER BY kelJnsId
		) a ON a.kelJnsId = kellapJnsId
	WHERE
		kellapNama LIKE '%s'
        %s
   ORDER BY
	  /*jenis_laporan, bentuk_transaksi, kellapNama,*/
      kellapJnsId,kellapOrderBy asc
   LIMIT %s, %s";

$sql['get_data_by_id'] =
   "SELECT
			kellapId AS id,
			kellapNama AS nama,
			bentuk_transaksi,
			jenis_laporan AS jns_lap,
			kellapIsTambah AS is_tambah,
			jenis_laporan_id,
            kellapOrderBy AS no_urutan,
			CASE WHEN bentuk_transaksi_id=jenis_laporan_id
			THEN NULL
			WHEN bentuk_transaksi_id!=jenis_laporan_id
			THEN bentuk_transaksi_id END AS bentuk_transaksi_id
		FROM kelompok_laporan_ref
		LEFT JOIN(
		SELECT
			jenis_laporan_id,
			bentuk_transaksi_id,
			jenis_laporan,
			CASE WHEN jenis_laporan=bentuk_transaksi
			THEN NULL
			WHEN jenis_laporan!=bentuk_transaksi THEN bentuk_transaksi END AS bentuk_transaksi

		FROM(
			SELECT
				a.kelJnsId AS bentuk_transaksi_id,
				IFNULL(b.kelJnsId, a.kelJnsId) AS jenis_laporan_id,
				IFNULL(b.kelJnsNama,a.kelJnsNama) AS jenis_laporan,
				a.kelJnsNama AS bentuk_transaksi
			FROM kelompok_jenis_laporan_ref a
			LEFT JOIN kelompok_jenis_laporan_ref b ON b.kelJnsId = a.kelJnsPrntId
		)a ORDER BY jenis_laporan_id, bentuk_transaksi_id
		) a ON a.bentuk_transaksi_id = kellapJnsId
	WHERE
	kellapId ='%s'";

$sql['get_data_by_array_id'] =
   "SELECT
      kellapId AS id,
      kellapNama AS nama,
      kellapBentukTransaksi AS bentuk_transaksi,
      kellapIsTambah AS is_tambah,
      kellapJenisLaporan AS jns_lap
   FROM
      kelompok_laporan_ref
   WHERE
      kellapId IN ('%s')";

//===DO===

$sql['do_add'] =
   "INSERT INTO kelompok_laporan_ref
      (kellapNama, kellapIsTambah, kellapJnsId,kellapOrderBy)
   VALUES
      ('%s', '%s', '%s','%s')";

$sql['do_update'] =
   "UPDATE kelompok_laporan_ref
   SET
      kellapNama = '%s',
      kellapIsTambah = '%s',
      kellapJnsId = '%s',
      kellapOrderBy = '%s'
   WHERE
      kellapId = '%s'";

$sql['do_delete_by_id'] =
   "DELETE from kelompok_laporan_ref
   WHERE
      kellapId='%s'";

$sql['do_delete_by_array_id'] =
   "DELETE from kelompok_laporan_ref
   WHERE
      kellapId IN ('%s')";


$sql['get_count_detil_klp_laporan'] = "
   SELECT
      COUNT(a.coakellapId) AS total
   FROM
      coa_kelompok_laporan_ref a
      JOIN coa b ON a.coakellapCoaId = b.coaId
   WHERE
      coakellapIdKellap = '%s'
      AND b.coaNamaAkun like '%s'
";
$sql['get_data_detil_klp_laporan'] = "
   SELECT
      f.coakellapId AS id,
      f.coakellapCoaId AS coa_id,
      f.coakellapDK AS coa_tipe,
      g.coaKodeAkun AS coa_kode,
      g.coaNamaAkun AS coa_nama,
      h.kellapNama AS kelompok_laporan,
      jenis_laporan ,
      bentuk_transaksi
   FROM
      coa_kelompok_laporan_ref f
      JOIN coa g ON f.coakellapCoaId = g.coaId
      JOIN kelompok_laporan_ref h ON h.kellapId = f.coakellapIdKellap

      	LEFT JOIN(
		SELECT
			a.kelJnsId,
			CASE WHEN jenis_laporan=bentuk_transaksi
			THEN ''
			WHEN jenis_laporan!=bentuk_transaksi THEN bentuk_transaksi END AS bentuk_transaksi,
			jenis_laporan
		FROM(
			SELECT
				a.kelJnsId,
				IFNULL(b.kelJnsNama,a.kelJnsNama) AS jenis_laporan,
				a.kelJnsNama AS bentuk_transaksi
			FROM kelompok_jenis_laporan_ref a
			LEFT JOIN kelompok_jenis_laporan_ref b ON b.kelJnsId = a.kelJnsPrntId
		)a ORDER BY kelJnsId
		) a ON a.kelJnsId = h.kellapJnsId

   WHERE
      coakellapIdKellap = '%s'
      AND g.coaNamaAkun like '%s'
   ORDER BY
      g.coaKodeAkun
   -- LIMIT %s, %s
";

// do add detil coa kel laporan
$sql['do_add_detil_coa_kel_lap'] = "
   INSERT INTO coa_kelompok_laporan_ref
      (coakellapIdKellap, coakellapCoaId, coakellapDK)
   VALUES
      (%s, %s, %s)
";
/**
 * old
$sql['get_kelompok_info'] = "
   SELECT
      `kellapNama`,
     parent.`kelJnsNama`,
      IFNULL(parent.`kelJnsNama`,detil.`kelJnsNama`) AS jenisLaporan
   FROM `kelompok_laporan_ref`
   LEFT JOIN kelompok_jenis_laporan_ref AS detil ON kellapJnsId = detil.kelJnsId
   LEFT JOIN kelompok_jenis_laporan_ref AS parent ON parent.kelJnsId = detil.kelJnsPrntId
   WHERE kellapId='%s'
";
*/
$sql['get_kelompok_info']="
        SELECT
			kellapNama,
			bentuk_transaksi as kelJnsNama,
			jenis_laporan AS jenisLaporan
		FROM kelompok_laporan_ref
		LEFT JOIN(
		SELECT
			jenis_laporan_id,
			bentuk_transaksi_id,
			jenis_laporan,
			CASE WHEN jenis_laporan=bentuk_transaksi
			THEN NULL
			WHEN jenis_laporan!=bentuk_transaksi THEN bentuk_transaksi END AS bentuk_transaksi

		FROM(
			SELECT
				a.kelJnsId AS bentuk_transaksi_id,
				IFNULL(b.kelJnsId, a.kelJnsId) AS jenis_laporan_id,
				IFNULL(b.kelJnsNama,a.kelJnsNama) AS jenis_laporan,
				a.kelJnsNama AS bentuk_transaksi
			FROM kelompok_jenis_laporan_ref a
			LEFT JOIN kelompok_jenis_laporan_ref b ON b.kelJnsId = a.kelJnsPrntId
		)a ORDER BY jenis_laporan_id, bentuk_transaksi_id
		) a ON a.bentuk_transaksi_id = kellapJnsId
	WHERE
	kellapId ='%s'
";

$sql['do_delete_detil_by_id'] =
   "DELETE FROM coa_kelompok_laporan_ref
   WHERE
      coakellapId='%s'";

$sql['do_delete_detil_by_array_id'] =
   "DELETE FROM coa_kelompok_laporan_ref
   WHERE
      coakellapId IN ('%s')";


$sql['generate_no_urutan'] = "
SELECT 
        IFNULL(MAX(kl.`kellapOrderBy`) + 1,1)  AS no_urutan
FROM
    `kelompok_laporan_ref` kl
     LEFT JOIN 
	`kelompok_jenis_laporan_ref` kj ON kj.`kelJnsId` = kl.`kellapJnsId`
	 LEFT JOIN
	`kelompok_jenis_laporan_ref` kj_p ON kj_p.`kelJnsId` = kj.`kelJnsPrntId`
WHERE 
	kj_p.`kelJnsId` = %s OR  kj.`kelJnsId` =  %s
";
