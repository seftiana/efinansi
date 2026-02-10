<?php
// transaksi penyusutan

$sql['get_detail_penyusutan'] = "
   SELECT
      SQL_CALC_FOUND_ROWS
	   CONCAT(invKodeAset,'.', LPAD(invKodeBarang,4,0)) AS kode_aset,
	   det.invMstLabel,
	   mstPenystnNilaiPerolehan AS nilai_perolehan,
	   mstPenystnNilaiPenyusutan AS nilai_penyusutan,
      mstPenystnDisusutkan,
	   CONCAT(invUmurEkonomis,' Bulan') AS umur_ekonomis,
      CONCAT(mstPenystnUmrEko,' Bulan') AS sisa_umur_ek
   FROM
      penyusutan_brg_mst
      LEFT JOIN
      inventarisasi_detail det
      ON mstPenystnBarangId = invDetId
      LEFT JOIN
      inventarisasi_mst mst
      ON invDetMstId = invMstId
      LEFT JOIN
      barang_ref
      ON invBarangId = barangId
      LEFT JOIN
      sub_kelompok_barang_ref
      ON barangSubkelbrgId = subkelbrgId
      LEFT JOIN
      kelompok_barang_ref
      ON subkelbrgKelbrgId = kelbrgId
      LEFT JOIN
      bidang_barang_ref
      ON kelbrgBidangbrgId = bidangbrgId
   WHERE
      mstPenystnDisusutkan > mstPenystnNilaiResidu
      AND mstPenystnDisusutkan > 0
      AND mstPenystnUmrEko > 0
      AND bidangbrgGolbrgId = %s
	   AND mstPenystnBarangId IS NOT NULL
   ORDER BY invKodeAset ASC, invKodeBarang ASC
   LIMIT %s,%s
";

$sql['get_data_penyusutan_kib'] = "
   SELECT
      golbrgId As kib_id,
      golbrgNama,
      golbrgKibKode AS kib_kode,
      golbrgNama AS kib_nama,
      ifnull(SUM(nilai_perolehan),0) AS nilai_perolehan,
      ifnull(SUM(nilai_residu),0) AS nilai_residu,
      ifnull(SUM(umur_ekonomis),0) AS umur_ekonomis,
      ifnull(SUM(disusutkan),0) AS disusutkan,
      ifnull(SUM(nilai_penyusutan),0) AS nilai_penyusutan
   FROM(
   SELECT
      golbrgId,
      golbrgKibKode,
      golbrgNama,
      nilai_perolehan,
      nilai_residu,
      umur_ekonomis,
      disusutkan,
      nilai_penyusutan
   FROM
      golongan_barang_ref
   LEFT JOIN bidang_barang_ref ON bidangbrgGolbrgId = golbrgId
   LEFT JOIN kelompok_barang_ref ON kelbrgBidangbrgId=bidangbrgId
   LEFT JOIN sub_kelompok_barang_ref ON subkelbrgKelbrgId = kelbrgId
   LEFT JOIN barang_ref ON barangSubkelbrgId = subkelbrgId
   LEFT JOIN (
   SELECT
      invBarangId AS id_barang,
      ifnull(SUM(mstPenystnNilaiPerolehan),0) AS nilai_perolehan,
      ifnull(SUM(mstPenystnNilaiResidu),0) AS nilai_residu,
      ifnull(SUM(mstPenystnUmrEko),0) AS umur_ekonomis,
      ifnull(SUM(mstPenystnDisusutkan),0) AS disusutkan,
      ifnull(SUM(mstPenystnNilaiPenyusutan),0) AS nilai_penyusutan
   FROM
         penyusutan_brg_mst a
   LEFT JOIN inventarisasi_detail b ON mstPenystnBarangId = invDetId
   LEFT JOIN inventarisasi_mst ON invDetMstId = invMstId
   WHERE mstPenystnBarangId IS NOT NULL
   GROUP BY invBarangId
   UNION
   SELECT
      mstPenystnGedungId AS id_barang,
      ifnull(SUM(mstPenystnNilaiPerolehan),0) AS nilai_perolehan,
      ifnull(SUM(mstPenystnNilaiResidu),0) AS nilai_residu,
      ifnull(SUM(mstPenystnUmrEko),0) AS umur_ekonomis,
      ifnull(SUM(mstPenystnDisusutkan),0) AS disusutkan,
      ifnull(SUM(mstPenystnNilaiPenyusutan),0) AS nilai_penyusutan
   FROM
      penyusutan_brg_mst a
   WHERE
      mstPenystnGedungId IS NOT NULL AND mstPenystnBarangId IS NULL
   GROUP BY mstPenystnGedungId
   ) penyusutan ON id_barang = barangId
   WHERE
   golbrgKibKode IN ('B', 'C', 'E')
   AND (golbrgNama like '%s' OR golbrgKibKode like '%s')
   ) a
   GROUP BY
         golbrgId
";

$sql['get_count_penyusutan_per_kib'] = "
   SELECT
      COUNT(penyusutanDetBrg) AS total
   FROM
      penyusutan_brg_mst a
		LEFT JOIN inventarisasi_detail b ON mstPenystnBarangId = invDetId
		LEFT JOIN inventarisasi_mst ON invDetMstId = invMstId
		LEFT JOIN barang_ref ON invBarangId = barangId
		LEFT JOIN sub_kelompok_barang_ref ON barangSubkelbrgId = subkelbrgId
		LEFT JOIN kelompok_barang_ref ON subkelbrgKelbrgId = kelbrgId
		LEFT JOIN bidang_barang_ref ON kelbrgBidangbrgId=bidangbrgId
		LEFT JOIN golongan_barang_ref ON bidangbrgGolbrgId = golbrgId
   WHERE
      golbrgId = %s
   GROUP BY penyusutanDetBrg
   LIMIT 1
";

$sql['get_count_detail_penyusutan'] = "
   SELECT COUNT(*) AS total FROM (SELECT
      mstPenystnBrgId AS penysusutan_brg_id,
      mstPenystnNilaiPerolehan AS nilai_perolehan,
      mstPenystnNilaiResidu AS nilai_residu,
      mstPenystnUmrEko AS umur_ekonomis,
      sisa_umur_ek,
      mstPenystnDisusutkan AS disusutkan,
      mstPenystnNilaiPenyusutan AS nilai_penyusutan,
      mstPenystnTglPerubahan AS tgl_perubahan,
      barangNama AS brg_nama,
      subKelbrgNama AS brg_sub_klmp,
      kelbrgNama AS brg_klmp,
      bidangbrgNama AS brg_bidang,
      golbrgNama
   FROM(
   SELECT
      mstPenystnBrgId,
      invBarangId AS id_barang,
      mstPenystnNilaiPerolehan,
      mstPenystnNilaiResidu,
      mstPenystnUmrEko,
      mstPenystnDisusutkan,
      mstPenystnNilaiPenyusutan,
      ifnull(( SELECT
                     penyusutanDetSisaUmrEk
                  FROM
                     penyusutan_det
                     JOIN penyusutan_mst ON penyusutanMstId =  penyusutanDetMst
                  WHERE
                     penyusutanDetBrg = invDetId
                     AND penyusutanMstPeriode < now()
                  ORDER BY penyusutanDetId DESC
                  LIMIT 1), mstPenystnUmrEko) AS sisa_umur_ek,
      mstPenystnTglPerubahan

   FROM
         penyusutan_brg_mst a
   LEFT JOIN inventarisasi_detail b ON mstPenystnBarangId = invDetId
   LEFT JOIN inventarisasi_mst ON invDetMstId = invMstId
   WHERE mstPenystnBarangId IS NOT NULL
   UNION
   SELECT
      mstPenystnBrgId,
      gedungBarangId AS id_barang,
      mstPenystnNilaiPerolehan,
      mstPenystnNilaiResidu,
      mstPenystnUmrEko,
      mstPenystnDisusutkan,
      mstPenystnNilaiPenyusutan,
      ifnull(( SELECT
                     penyusutanDetSisaUmrEk
                  FROM
                     penyusutan_det
                     JOIN penyusutan_mst ON penyusutanMstId =  penyusutanDetMst
                  WHERE
                     penyusutanDetGedungId = gedungId
                     AND penyusutanMstPeriode < now()
                  ORDER BY penyusutanDetId DESC
                  LIMIT 1), mstPenystnUmrEko) AS sisa_umur_ek,
      mstPenystnTglPerubahan
   FROM
      penyusutan_brg_mst a
   LEFT JOIN gedung ON gedungId = mstPenystnGedungId
   WHERE
      mstPenystnGedungId IS NOT NULL AND mstPenystnBarangId IS NULL
   )a
   LEFT JOIN barang_ref ON id_barang = barangId
   LEFT JOIN sub_kelompok_barang_ref ON barangSubkelbrgId = subkelbrgId
   LEFT JOIN kelompok_barang_ref ON subkelbrgKelbrgId = kelbrgId
   LEFT JOIN bidang_barang_ref ON kelbrgBidangbrgId = bidangbrgId
   LEFT JOIN golongan_barang_ref ON bidangbrgGolbrgId = golbrgId
   WHERE golbrgId = '%s'
   AND sisa_umur_ek >0
   ORDER BY id_barang )a

";

$sql['get_detail_penyusuran'] = "
   SELECT
      kode_aset,
      mstPenystnBrgId AS penysusutan_brg_id,
      mstPenystnNilaiPerolehan AS nilai_perolehan,
      mstPenystnNilaiResidu AS nilai_residu,
      mstPenystnUmrEko AS umur_ekonomis,
      sisa_umur_ek,
      mstPenystnDisusutkan AS disusutkan,
      mstPenystnNilaiPenyusutan AS nilai_penyusutan,
      mstPenystnTglPerubahan AS tgl_perubahan,
      nama_barang AS brg_nama,
      subKelbrgNama AS brg_sub_klmp,
      kelbrgNama AS brg_klmp,
      bidangbrgNama AS brg_bidang,
      golbrgNama
   FROM(
   SELECT
      b.invMstLabel AS nama_barang,
      CONCAT(invKodeAset,'.',LPAD(invKodeBarang,5,0)) AS kode_aset,
      mstPenystnBrgId,
      invBarangId AS id_barang,
      mstPenystnNilaiPerolehan,
      mstPenystnNilaiResidu,
      mstPenystnUmrEko,
      mstPenystnDisusutkan,
      mstPenystnNilaiPenyusutan,
      ifnull(( SELECT
                     penyusutanDetSisaUmrEk
                  FROM
                     penyusutan_det
                     JOIN penyusutan_mst ON penyusutanMstId =  penyusutanDetMst
                  WHERE
                     penyusutanDetBrg = invDetId
                     AND penyusutanMstPeriode < now()
                  ORDER BY penyusutanDetId DESC
                  LIMIT 1), mstPenystnUmrEko) AS sisa_umur_ek,
      mstPenystnTglPerubahan

   FROM
         penyusutan_brg_mst a
   LEFT JOIN inventarisasi_detail b ON mstPenystnBarangId = invDetId
   LEFT JOIN inventarisasi_mst ON invDetMstId = invMstId
   WHERE mstPenystnBarangId IS NOT NULL
   UNION
   SELECT
      CONCAT(gedungKode,'-',gedungNama) AS nama_barang,
      CONCAT(LPAD(golbrgKode,2,'0'),'.',LPAD(bidangbrgKode,2,'0'),'.',LPAD(kelbrgKode,2,'0'),'.',LPAD(subkelbrgKode,2,'0'),'.',LPAD(barangKode,4,'0')) AS kode_aset,
      mstPenystnBrgId,
      gedungBarangId AS id_barang,
      mstPenystnNilaiPerolehan,
      mstPenystnNilaiResidu,
      mstPenystnUmrEko,
      mstPenystnDisusutkan,
      mstPenystnNilaiPenyusutan,
      ifnull(( SELECT
                     penyusutanDetSisaUmrEk
                  FROM
                     penyusutan_det
                     JOIN penyusutan_mst ON penyusutanMstId =  penyusutanDetMst
                  WHERE
                     penyusutanDetGedungId = gedungId
                     AND penyusutanMstPeriode < now()
                  ORDER BY penyusutanDetId DESC
                  LIMIT 1), mstPenystnUmrEko) AS sisa_umur_ek,
      mstPenystnTglPerubahan
   FROM
      penyusutan_brg_mst a
   LEFT JOIN gedung ON gedungId = mstPenystnGedungId
   LEFT JOIN barang_ref ON gedungBarangId = barangId
   LEFT JOIN sub_kelompok_barang_ref ON barangSubkelbrgId = subkelbrgId
   LEFT JOIN kelompok_barang_ref ON subkelbrgKelbrgId = kelbrgId
   LEFT JOIN bidang_barang_ref ON kelbrgBidangbrgId = bidangbrgId
   LEFT JOIN golongan_barang_ref ON bidangbrgGolbrgId = golbrgId
   WHERE
      mstPenystnGedungId IS NOT NULL AND mstPenystnBarangId IS NULL
   )a
   LEFT JOIN barang_ref ON id_barang = barangId
   LEFT JOIN sub_kelompok_barang_ref ON barangSubkelbrgId = subkelbrgId
   LEFT JOIN kelompok_barang_ref ON subkelbrgKelbrgId = kelbrgId
   LEFT JOIN bidang_barang_ref ON kelbrgBidangbrgId = bidangbrgId
   LEFT JOIN golongan_barang_ref ON bidangbrgGolbrgId = golbrgId
   WHERE golbrgId = '%s'
   AND sisa_umur_ek >0
   ORDER BY id_barang
   LIMIT %s,%s
";

$sql['get_detail_data_penyusutan'] = "
   SELECT
      mstPenystnBrgId AS penysusutan_brg_id,
      mstPenystnBarangId AS brg_id,
      mstPenystnGedungId AS gedung_id,
      mstPenystnNilaiPerolehan AS nilai_perolehan,
      mstPenystnNilaiResidu AS nilai_residu,
      mstPenystnUmrEko AS umur_ekonomis,
      mstPenystnDisusutkan AS disusutkan,
      mstPenystnNilaiPenyusutan AS nilai_penyusutan,
      mstPenystnTglPerubahan AS tgl_perubahan,
      invKodeAset AS kode_aset,
      -- invMstLabel AS brg_label,
      invDetId AS id_inv_barang,
      barangId AS id_barang_ref,
      barangNama AS brg_nama,
      subKelbrgNama AS brg_sub_klmp,
      kelbrgNama AS brg_klmp,
      bidangbrgNama AS brg_bidang,
      golbrgNama
   FROM
      penyusutan_brg_mst
      LEFT JOIN inventarisasi_detail ON mstPenystnBarangId = invDetId
      LEFT JOIN gedung ON mstPenystnGedungId = gedungId
      LEFT JOIN inventarisasi_mst ON invDetMstId = invMstId
      JOIN barang_ref ON (invBarangId = barangId) OR (gedungBarangId = barangId)
      JOIN sub_kelompok_barang_ref ON barangSubkelbrgId = subkelbrgId
      JOIN kelompok_barang_ref ON subkelbrgKelbrgId = kelbrgId
      JOIN bidang_barang_ref ON kelbrgBidangbrgId = bidangbrgId
      JOIN golongan_barang_ref ON bidangbrgGolbrgId = golbrgId
   WHERE
      golbrgId = %s
";

$sql['get_max_id_penyusutan_mst'] = "
   SELECT
      MAX(penyusutanMstId) AS max_id_penyusutan_mst
   FROM
      penyusutan_mst
";

#untuk list detil penyusutan
$sql['get_combo_kib'] = "
   SELECT
      golbrgId AS id,
      golbrgNama AS name
   FROM
      golongan_barang_ref
   WHERE
      golbrgKibKode IN ('A','B','E')
";

$sql['get_count_list_penyusutan'] = "
     SELECT SUM(total) AS total FROM
  (SELECT
      COUNT(penyusutanDetId) AS total
   FROM
      penyusutan_brg_mst
      LEFT JOIN inventarisasi_detail ON mstPenystnBarangId = invDetId
      LEFT JOIN gedung ON mstPenystnGedungId = gedungId
      LEFT JOIN penyusutan_det ON (invDetId = penyusutanDetBrg)
      LEFT JOIN penyusutan_mst ON penyusutanMstId = penyusutanDetMst
      LEFT JOIN inventarisasi_mst ON invDetMstId = invMstId
      LEFT JOIN barang_ref ON (invBarangId = barangId)
      LEFT JOIN sub_kelompok_barang_ref ON barangSubkelbrgId = subkelbrgId
      LEFT JOIN kelompok_barang_ref ON subkelbrgKelbrgId = kelbrgId
      LEFT JOIN bidang_barang_ref ON kelbrgBidangbrgId = bidangbrgId
      LEFT JOIN golongan_barang_ref ON bidangbrgGolbrgId = golbrgId
   WHERE
      (invKodeAset like '%s' OR barangNama like '%s')
      %s
   GROUP BY mstPenystnBarangId
  UNION
  SELECT
      COUNT(penyusutanDetId) AS total
   FROM
      penyusutan_brg_mst
      LEFT JOIN inventarisasi_detail ON mstPenystnBarangId = invDetId
      LEFT JOIN gedung ON mstPenystnGedungId = gedungId
      LEFT JOIN penyusutan_det ON (penyusutanDetGedungId = gedungId)
      LEFT JOIN penyusutan_mst ON penyusutanMstId = penyusutanDetMst
      LEFT JOIN inventarisasi_mst ON invDetMstId = invMstId
      LEFT JOIN barang_ref ON (gedungBarangId = barangId)
      LEFT JOIN sub_kelompok_barang_ref ON barangSubkelbrgId = subkelbrgId
      LEFT JOIN kelompok_barang_ref ON subkelbrgKelbrgId = kelbrgId
      LEFT JOIN bidang_barang_ref ON kelbrgBidangbrgId = bidangbrgId
      LEFT JOIN golongan_barang_ref ON bidangbrgGolbrgId = golbrgId
  WHERE
      (invKodeAset like '%s' OR barangNama like '%s')
      %s
   GROUP BY mstPenystnBarangId
) a
";

$sql['get_list_penyusutan'] = "
   SELECT
    SQL_CALC_FOUND_ROWS
    mstPenystnBarangId,
    CONCAT(invKodeAset,'.',LPAD(invKodeBarang,4,'0')) AS kode_aset,
    mst.invMstLabel AS nama_aset,
    unitkerjaNama,
    mstPenystnNilaiPerolehan,
    mstPenystnDisusutkan AS nilai_buku,
    mstPenystnNilaiPenyusutan AS nilai_penyusutan,
    mstPenystnNilaiTotalPenyusutan AS total_penyusutan
   FROM
      penyusutan_brg_mst
      JOIN
      inventarisasi_detail
      ON mstPenystnBarangId = invDetId
      JOIN
      inventarisasi_mst mst
      ON invDetMstId = invMstId
      JOIN
      barang_ref
      ON invBarangId = barangId
      JOIN
      sub_kelompok_barang_ref
      ON barangSubkelbrgId = subkelbrgId
      JOIN
      kelompok_barang_ref
      ON subkelbrgKelbrgId = kelbrgId
      JOIN
      bidang_barang_ref
      ON kelbrgBidangbrgId = bidangbrgId
	   LEFT JOIN unit_kerja_ref ON invUnitKerja = unitkerjaId
   WHERE mstPenystnBarangId IS NOT NULL
      AND
         (bidangbrgGolbrgId = '%s' OR 'all' = '%s')
      AND
         (mst.invMstLabel LIKE '%s' OR CONCAT(invKodeAset,'.',LPAD(invKodeBarang,4,'0')) LIKE '%s')
   ORDER BY invKodeAset ASC, invKodeBarang ASC
   LIMIT %s, %s
";

$sql['get_list_penyusutan_all'] = "
   SELECT
    SQL_CALC_FOUND_ROWS
    mstPenystnBarangId,
    CONCAT(invKodeAset,'.',LPAD(invKodeBarang,4,'0')) AS kode_aset,
    mst.invMstLabel AS nama_aset,
    unitkerjaNama,
    mstPenystnNilaiPerolehan,
    mstPenystnDisusutkan AS nilai_buku,
    mstPenystnNilaiPenyusutan AS nilai_penyusutan,
    mstPenystnNilaiTotalPenyusutan AS total_penyusutan
   FROM
      penyusutan_brg_mst
      JOIN
      inventarisasi_detail
      ON mstPenystnBarangId = invDetId
      JOIN
      inventarisasi_mst mst
      ON invDetMstId = invMstId
      JOIN
      barang_ref
      ON invBarangId = barangId
      JOIN
      sub_kelompok_barang_ref
      ON barangSubkelbrgId = subkelbrgId
      JOIN
      kelompok_barang_ref
      ON subkelbrgKelbrgId = kelbrgId
      JOIN
      bidang_barang_ref
      ON kelbrgBidangbrgId = bidangbrgId
	   LEFT JOIN unit_kerja_ref ON invUnitKerja = unitkerjaId
   WHERE mstPenystnBarangId IS NOT NULL
      AND
         (bidangbrgGolbrgId = '%s' OR 'all' = '%s')
      AND
         (mst.invMstLabel LIKE '%s' OR CONCAT(invKodeAset,'.',LPAD(invKodeBarang,4,'0')) LIKE '%s')
   ORDER BY invKodeAset ASC, invKodeBarang ASC
";

$sql['get_search_count'] = "
   SELECT FOUND_ROWS() AS total
";

$sql['get_cetak_penyusutan'] = "
   SELECT
   penyusutanMstPeriode AS periode_penyusutan,
   invKodeAset AS kode_aset,
   concat(barangNama,' - ', inventarisasi_mst.invMstLabel) AS brg_nama,
   (SELECT unitkerjaNama FROM unit_kerja_ref WHERE unitkerjaId = invUnitKerja) AS unit_pj,
   mstPenystnNilaiPenyusutan AS nilai_penyusutan,
   (mstPenystnNilaiPerolehan - penyusutanDetNilaiAkhir) AS akumulasi_penyusutan,
   (mstPenystnNilaiPerolehan - ((mstPenystnNilaiPerolehan - penyusutanDetNilaiAkhir) )) AS nilai_buku
   FROM
      penyusutan_brg_mst
      JOIN inventarisasi_detail ON mstPenystnBarangId = invDetId
      JOIN penyusutan_det ON invDetId = penyusutanDetBrg
      JOIN penyusutan_mst ON penyusutanMstId = penyusutanDetMst
      JOIN inventarisasi_mst ON invDetMstId = invMstId
      JOIN barang_ref ON invBarangId = barangId
      JOIN sub_kelompok_barang_ref ON barangSubkelbrgId = subkelbrgId
      JOIN kelompok_barang_ref ON subkelbrgKelbrgId = kelbrgId
      JOIN bidang_barang_ref ON kelbrgBidangbrgId = bidangbrgId
      JOIN golongan_barang_ref ON bidangbrgGolbrgId = golbrgId
   WHERE
      (LEFT(penyusutanMstPeriode,8)  BETWEEN '%s' AND '%s')
";

$sql['count_log_penyusutan'] = "
   SELECT
      COUNT(penyusutanDetId) AS total
   FROM
      penyusutan_brg_mst
      LEFT JOIN inventarisasi_detail ON mstPenystnBarangId = invDetId
      LEFT JOIN gedung ON mstPenystnGedungId = gedungId
      LEFT JOIN penyusutan_det ON invDetId = penyusutanDetBrg OR (penyusutanDetGedungId = gedungId)
      LEFT JOIN penyusutan_mst ON penyusutanMstId = penyusutanDetMst
      LEFT JOIN inventarisasi_mst ON invDetMstId = invMstId
      JOIN barang_ref ON (invBarangId = barangId) OR (gedungBarangId = barangId)
      JOIN sub_kelompok_barang_ref ON barangSubkelbrgId = subkelbrgId
      JOIN kelompok_barang_ref ON subkelbrgKelbrgId = kelbrgId
      JOIN bidang_barang_ref ON kelbrgBidangbrgId = bidangbrgId
      JOIN golongan_barang_ref ON bidangbrgGolbrgId = golbrgId
   WHERE
      (invDetId = %s  OR penyusutanDetGedungId = %s)
";


$sql['log_penyusutan'] = "
   SELECT
	CONCAT(
      invKodeAset,
      '.',
      LPAD(invKodeBarang, 4, '0')
   ) AS kode_aset,
	barangNama,
   mst.invMstLabel AS label_aset,
	mst.invMerek,
	mst.invSpesifikasi,
   mstPenystnNilaiTotalPenyusutan,
	mstPenystnDisusutkan,
	penyusutanMstPeriode,
	penyusutanMstNoBA,
	penyusutanDetNilaiPenyusutan,
	penyusutanDetNilaiAkhir
FROM
   penyusutan_det
   RIGHT JOIN
   inventarisasi_detail
   ON penyusutanDetBrg = invDetId
   JOIN
   inventarisasi_mst mst
   ON invDetMstId = invMstId
   JOIN
   barang_ref
   ON invBarangId = barangId
   JOIN
   sub_kelompok_barang_ref
   ON barangSubkelbrgId = subkelbrgId
   JOIN
   kelompok_barang_ref
   ON subkelbrgKelbrgId = kelbrgId
   JOIN
   bidang_barang_ref
   ON kelbrgBidangbrgId = bidangbrgId
   LEFT JOIN
   unit_kerja_ref
   ON invUnitKerja = unitkerjaId
	LEFT JOIN penyusutan_brg_mst ON mstPenystnBarangId = invDetId
	LEFT JOIN penyusutan_mst ON penyusutanDetMst = penyusutanMstId
WHERE
	invDetId = '%s'
ORDER BY invKodeAset ASC,
   invKodeBarang ASC
LIMIT 0, 20
";

$sql['log_penyusutan_old'] = "
   SELECT
      penyusutanMstPeriode AS periode_penyusutan,
      ifnull(invKodeAset, gedungKode) AS kode_aset,
      ifnull(barangNama, gedungNama) AS brg_nama,
      inventarisasi_mst.invMstLabel AS brg_label,
      inventarisasi_detail.invMerek AS brg_merk,
      inventarisasi_detail.invSpesifikasi AS brg_spek,
      penyusutanMstNoBA AS no_ba,
      (SELECT unitkerjaNama FROM unit_kerja_ref WHERE unitkerjaId = invUnitKerja) AS unit_pj,
      mstPenystnNilaiPenyusutan AS nilai_penyusutan,
      (mstPenystnNilaiPerolehan - penyusutanDetNilaiAkhir) AS akumulasi_penyusutan,
      (mstPenystnNilaiPerolehan - ((mstPenystnNilaiPerolehan - penyusutanDetNilaiAkhir) )) AS nilai_buku
      FROM
         penyusutan_brg_mst
         LEFT JOIN inventarisasi_detail ON mstPenystnBarangId = invDetId
         LEFT JOIN gedung ON mstPenystnGedungId = gedungId
         LEFT JOIN penyusutan_det ON invDetId = penyusutanDetBrg OR (penyusutanDetGedungId = gedungId)
         LEFT JOIN penyusutan_mst ON penyusutanMstId = penyusutanDetMst
         LEFT JOIN inventarisasi_mst ON invDetMstId = invMstId
         JOIN barang_ref ON (invBarangId = barangId) OR (gedungBarangId = barangId)
         JOIN sub_kelompok_barang_ref ON barangSubkelbrgId = subkelbrgId
         JOIN kelompok_barang_ref ON subkelbrgKelbrgId = kelbrgId
         JOIN bidang_barang_ref ON kelbrgBidangbrgId = bidangbrgId
         JOIN golongan_barang_ref ON bidangbrgGolbrgId = golbrgId
      WHERE
         (invDetId = %s  OR penyusutanDetGedungId = '%s')
      ORDER BY penyusutanMstPeriode DESC
      LIMIT %s, %s
";

#cek penyusutan barang sebelom nya
$sql['cek_penyusutan_sebelomnya'] = "
   SELECT
      penyusutanDetBrg AS id_barang_ref,
      penyusutanDetNilaiAkhir AS nilai_akhir,
      penyusutanDetSisaUmrEk AS umur_ek
   FROM
      penyusutan_det
      JOIN penyusutan_mst ON penyusutanMstId = penyusutanDetMst
   WHERE
      penyusutanDetBrg = %s
       AND penyusutanMstPeriode = (SELECT penyusutanMstPeriode FROM penyusutan_mst WHERE penyusutanMstPeriode < now() ORDER BY penyusutanMstPeriode DESC LIMIT 1)
      -- AND penyusutanMstPeriode = (SELECT penyusutanMstPeriode FROM penyusutan_mst WHERE penyusutanMstPeriode < (SELECT MAX(penyusutanMstPeriode) FROM penyusutan_mst ORDER BY penyusutanMstPeriode DESC LIMIT 1))
";

$sql['cek_penyusutan_gedung_sebelomnya'] = "
   SELECT
      penyusutanDetGedungId AS id_gedung,
      penyusutanDetNilaiAkhir AS nilai_akhir,
      penyusutanDetSisaUmrEk AS umur_ek
   FROM
      penyusutan_det
      JOIN penyusutan_mst ON penyusutanMstId = penyusutanDetMst
   WHERE
      penyusutanDetGedungId = %s
       AND penyusutanMstPeriode = (SELECT penyusutanMstPeriode FROM penyusutan_mst WHERE penyusutanMstPeriode < now() ORDER BY penyusutanMstPeriode DESC LIMIT 1)
      -- AND penyusutanMstPeriode = (SELECT penyusutanMstPeriode FROM penyusutan_mst WHERE penyusutanMstPeriode < (SELECT MAX(penyusutanMstPeriode) FROM penyusutan_mst ORDER BY penyusutanMstPeriode DESC LIMIT 1))
";

#do insert penyusutan
$sql['insert_penyusutan_mst'] = "
   INSERT INTO penyusutan_mst
      (penyusutanMstPeriode, penyusutanMstNoBA, penyusutanMstKet)
   VALUES
      ('%s', '%s', '%s')
";

$sql['insert_penyusutan_det_old'] = "
   INSERT INTO penyusutan_det
      (penyusutanDetMst, penyusutanDetBrg, penyusutanDetNilaiAkhir, penyusutanDetSisaUmrEk, penyusutanDetIsTransaksi)
   VALUES
      ('%s', '%s', '%s', '%s', '1')
";

$sql['insert_penyusutan_det'] = "
INSERT INTO penyusutan_det
      (penyusutanDetMst, penyusutanDetBrg, penyusutanDetNilaiAkhir, penyusutanDetSisaUmrEk, penyusutanDetIsTransaksi)
   SELECT
     (SELECT
         MAX(penyusutanMstId) AS max_id_penyusutan_mst
      FROM
         penyusutan_mst
      ),
      mstPenystnBarangId,
      IFNULL(mstPenystnDisusutkan-mstPenystnNilaiPenyusutan,0) AS nilaiAkhir,
      mstPenystnUmrEko-1 AS sisaUmurEko,
      '1'
   FROM
      penyusutan_brg_mst
   WHERE
      mstPenystnBarangId IS NOT NULL
   AND
      mstPenystnDisusutkan>=mstPenystnNilaiResidu
   AND 	mstPenystnDisusutkan>0
   ORDER BY mstPenystnBarangId
";

$sql['insert_penyusutan_det_gedung_old'] = "
   INSERT INTO penyusutan_det
      (penyusutanDetMst, penyusutanDetGedungId, penyusutanDetNilaiAkhir, penyusutanDetSisaUmrEk, penyusutanDetIsTransaksi)
   VALUES
      ('%s', '%s', '%s', '%s', '1')
";

//query insert det penyusutan gedung
$sql['insert_penyusutan_det_gedung'] = "
   INSERT INTO penyusutan_det
         (penyusutanDetMst, penyusutanDetGedungId, penyusutanDetNilaiAkhir, penyusutanDetSisaUmrEk, penyusutanDetIsTransaksi)
   SELECT
      (SELECT
         MAX(penyusutanMstId) AS max_id_penyusutan_mst
      FROM
         penyusutan_mst
      ),
      mstPenystnGedungId,
      mstPenystnDisusutkan-mstPenystnNilaiPenyusutan AS nilaiAkhir,
      mstPenystnUmrEko-1 AS sisaUmurEko,
      '1'
   FROM
      penyusutan_brg_mst
   WHERE
      mstPenystnGedungId IS NOT NULL
   AND
      mstPenystnBarangId IS NULL
   AND
      mstPenystnDisusutkan>=mstPenystnNilaiResidu
   AND 	mstPenystnDisusutkan>0
   ORDER BY mstPenystnGedungId
";

$sql['cek_kib'] = "
   SELECT
      golbrgKibKode AS kib
   FROM
      golongan_barang_ref
   WHERE
      golbrgId = %s
";

$sql['counting_untuk_nomor_ba'] = "
   SELECT
      COUNT(golbrgId)+1 AS count_ba
   FROM
      penyusutan_brg_mst
      LEFT JOIN inventarisasi_detail ON mstPenystnBarangId = invDetId
      LEFT JOIN gedung ON mstPenystnGedungId = gedungId
      LEFT JOIN penyusutan_det ON invDetId = penyusutanDetBrg OR (penyusutanDetGedungId = gedungId)
      LEFT JOIN penyusutan_mst ON penyusutanMstId = penyusutanDetMst
      LEFT JOIN inventarisasi_mst ON invDetMstId = invMstId
      JOIN barang_ref ON (invBarangId = barangId) OR (gedungBarangId = barangId)
      JOIN sub_kelompok_barang_ref ON barangSubkelbrgId = subkelbrgId
      JOIN kelompok_barang_ref ON subkelbrgKelbrgId = kelbrgId
      JOIN bidang_barang_ref ON kelbrgBidangbrgId = bidangbrgId
      JOIN golongan_barang_ref ON bidangbrgGolbrgId = golbrgId
   WHERE
   golbrgId = %s
";

$sql['get_total_penyusutan_barang'] = "
   SELECT
      COUNT(*) AS total
   FROM
      penyusutan_brg_mst
   WHERE
      mstPenystnBarangId IS NOT NULL
   AND
      mstPenystnDisusutkan>=mstPenystnNilaiResidu
   AND 	mstPenystnDisusutkan>0
   ORDER BY mstPenystnBarangId
";

$sql['get_total_penyusutan_gedung'] = "
   SELECT
      COUNT(*) AS total
   FROM
      penyusutan_brg_mst
   WHERE
      mstPenystnGedungId IS NOT NULL
   AND
      mstPenystnBarangId IS NULL
   AND
      mstPenystnDisusutkan>=mstPenystnNilaiResidu
   AND
      mstPenystnDisusutkan>0
   ORDER BY mstPenystnGedungId
";

$sql['get_penyusutan_data_barang'] = "
   SELECT
      mstPenystnBarangId,
      IFNULL(mstPenystnDisusutkan-mstPenystnNilaiPenyusutan,0) AS nilaiAkhir,
      mstPenystnUmrEko-1 AS sisaUmurEko
   FROM
      penyusutan_brg_mst
   WHERE
      mstPenystnBarangId IS NOT NULL
   AND
      mstPenystnDisusutkan>=mstPenystnNilaiResidu
   AND 	mstPenystnDisusutkan>0
   ORDER BY mstPenystnBarangId
";

$sql['get_penyusutan_data_gedung'] = "
   SELECT
      penyusutanDetGedungId,
      gedungNama,
      penyusutanMstPeriode,
      mstPenystnNilaiPenyusutan
   FROM penyusutan_det
   LEFT JOIN penyusutan_brg_mst ON mstPenystnGedungId = penyusutanDetGedungId
   LEFT JOIN penyusutan_mst ON penyusutanDetMst = penyusutanMstId
   LEFT JOIN gedung ON penyusutanDetGedungId = gedungId
   WHERE penyusutanDetMst = (SELECT
            MAX(penyusutanMstId) AS max_id_penyusutan_mst
         FROM
            penyusutan_mst
         )
";

$sql['update_data_penyusutan_mst']="
      UPDATE
      penyusutan_brg_mst
	   JOIN
      inventarisasi_detail
      ON mstPenystnBarangId = invDetId
      JOIN
      inventarisasi_mst
      ON invDetMstId = invMstId
      JOIN
      barang_ref
      ON invBarangId = barangId
      JOIN
      sub_kelompok_barang_ref
      ON barangSubkelbrgId = subkelbrgId
      JOIN
      kelompok_barang_ref
      ON subkelbrgKelbrgId = kelbrgId
      JOIN
      bidang_barang_ref
      ON kelbrgBidangbrgId = bidangbrgId
   SET
      mstPenystnDisusutkan = IFNULL(
         mstPenystnDisusutkan - mstPenystnNilaiPenyusutan,
         0
      ),
	   mstPenystnNilaiTotalPenyusutan = mstPenystnNilaiTotalPenyusutan+mstPenystnNilaiPenyusutan,
      mstPenystnUmrEko = mstPenystnUmrEko - 1
   WHERE mstPenystnBarangId IS NOT NULL
      AND mstPenystnDisusutkan >= mstPenystnNilaiResidu
      AND mstPenystnDisusutkan > 0
      AND mstPenystnUmrEko >= 0
	   AND bidangbrgGolbrgId = '%s'
";

$sql['update_data_penyusutan_gedung_mst']="
   UPDATE penyusutan_brg_mst
      SET mstPenystnDisusutkan = IFNULL(mstPenystnDisusutkan-mstPenystnNilaiPenyusutan,0),
      mstPenystnUmrEko = mstPenystnUmrEko-1,
   WHERE
      mstPenystnBarangId IS NULL
   AND
      mstPenystnGedungId IS NOT NULL
   AND
      mstPenystnDisusutkan>=mstPenystnNilaiResidu
   AND
	mstPenystnDisusutkan>0
   AND
	mstPenystnUmrEko>=0
   ORDER BY mstPenystnBarangId
";

$sql['insert_detil_penyusutan']="
   INSERT INTO penyusutan_det (
   penyusutanDetMst,
   penyusutanDetBrg,
	penyusutanDetNilaiPenyusutan,
   penyusutanDetNilaiAkhir,
   penyusutanDetSisaUmrEk,
   penyusutanDetIsTransaksi
)
SELECT

   (SELECT
      MAX(penyusutanMstId)
   FROM
      penyusutan_mst) AS penyusutanId,
   mstPenystnBarangId,
	mstPenystnNilaiPenyusutan,
   mstPenystnDisusutkan,
   mstPenystnUmrEko,
   '1'
FROM
   penyusutan_brg_mst
   JOIN
   inventarisasi_detail
   ON mstPenystnBarangId = invDetId
   JOIN
   inventarisasi_mst
   ON invDetMstId = invMstId
   JOIN
   barang_ref
   ON invBarangId = barangId
   JOIN
   sub_kelompok_barang_ref
   ON barangSubkelbrgId = subkelbrgId
   JOIN
   kelompok_barang_ref
   ON subkelbrgKelbrgId = kelbrgId
   JOIN
   bidang_barang_ref
   ON kelbrgBidangbrgId = bidangbrgId
WHERE mstPenystnBarangId IS NOT NULL
   AND mstPenystnDisusutkan > mstPenystnNilaiResidu
   AND mstPenystnDisusutkan > 0
   AND mstPenystnUmrEko > 0
   AND bidangbrgGolbrgId = %s
ORDER BY mstPenystnBarangId
";

$sql['insert_detil_penyusutan_gedung']="
   INSERT INTO penyusutan_det(penyusutanDetMst,penyusutanDetGedungId, penyusutanDetNilaiAkhir,penyusutanDetSisaUmrEk,penyusutanDetIsTransaksi)
SELECT
     (SELECT MAX(penyusutanMstId) FROM penyusutan_mst) AS penyusutanId,
      mstPenystnBarangId,
      mstPenystnDisusutkan,
      mstPenystnUmrEko,
      '1'
   FROM
      penyusutan_brg_mst
   WHERE
      mstPenystnBarangId IS NULL
   AND
      mstPenystnDisusutkan>mstPenystnNilaiResidu
   AND
      mstPenystnDisusutkan>mstPenystnNilaiResidu
   AND
	mstPenystnDisusutkan>0
   AND
	mstPenystnUmrEko>=0
   ORDER BY mstPenystnBarangId
";

$sql['get_data_penyusutan']="
   SELECT
	   penyusutanMstPeriode AS periodePenyusutan,
	   golbrgId,
	   b.invMstLabel AS brg_nama,
	   penyusutanDetBrg AS id_inv_barang,
	   penyusutanDetNilaiPenyusutan,
	   penyusutanDetSisaUmrEk
   FROM
	   penyusutan_det
	   LEFT JOIN penyusutan_mst ON penyusutanDetMst = penyusutanMstId
	   LEFT JOIN inventarisasi_detail ON invDetId = penyusutanDetBrg
	   LEFT JOIN inventarisasi_mst b ON invDetMstId = invMstId
	   LEFT JOIN barang_ref ON invBarangId = barangId
	   LEFT JOIN sub_kelompok_barang_ref ON barangSubkelbrgId = subkelbrgId
	   LEFT JOIN kelompok_barang_ref ON subkelbrgKelbrgId = kelbrgId
	   LEFT JOIN bidang_barang_ref ON kelbrgBidangbrgId = bidangbrgId
	   LEFT JOIN golongan_barang_ref ON bidangbrgGolbrgId = golbrgId
   WHERE
	   penyusutanDetMst = '%s'
   ORDER BY penyusutanDetId
";

$sql['get_gedung_penyusutan']="
   SELECT
      (SELECT penyusutanMstPeriode FROM penyusutan_mst ORDER BY penyusutanMstId DESC LIMIT 0,1) AS periodePenyusutan,
      golbrgId,
      gedungNama AS brg_nama,
      mstPenystnBarangId AS id_inv_barang,
      mstPenystnDisusutkan,
      mstPenystnUmrEko
   FROM
      penyusutan_brg_mst
      LEFT JOIN gedung ON mstPenystnGedungId = gedungId
      LEFT JOIN barang_ref ON gedungBarangId = barangId
      LEFT JOIN sub_kelompok_barang_ref ON barangSubkelbrgId = subkelbrgId
      LEFT JOIN kelompok_barang_ref ON subkelbrgKelbrgId = kelbrgId
      LEFT JOIN bidang_barang_ref ON kelbrgBidangbrgId = bidangbrgId
      LEFT JOIN golongan_barang_ref ON bidangbrgGolbrgId = golbrgId
   WHERE
      mstPenystnBarangId IS NULL
   AND
      mstPenystnGedungId IS NOT NULL
   AND
	   mstPenystnDisusutkan>0
   AND
	   mstPenystnUmrEko>=0
   AND
      penyusutanMstId = '%s'
   ORDER BY mstPenystnBarangId
";

?>