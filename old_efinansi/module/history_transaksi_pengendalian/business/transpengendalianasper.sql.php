<?php

$sql['combo_status_brg'] = "
   SELECT
      statusPengBrgId AS id,
      statusPengBrgNama AS name
   FROM
      status_peng_brg_ref
";

$sql['count_pengendalian'] = "
   SELECT
      pengBrgId AS total
   FROM
      pengendalian_barang_mst
      JOIN pengendalian_barang_det ON (pengBrgId = pengBrgDetMstId)
      RIGHT JOIN status_peng_brg_ref ON (statusPengBrgId = pengBrgStatusId)
      JOIN barang_ref ON (barangId = pengBrgBrgId)
      JOIN unit_kerja_ref ON (unitkerjaId = pengBrgUnitId)
      JOIN sub_kelompok_barang_ref ON (barangSubkelbrgId = subkelbrgId)
      JOIN kelompok_barang_ref ON (subkelbrgKelbrgId = kelbrgId)
      JOIN bidang_barang_ref ON (kelbrgBidangbrgId = bidangbrgId)
      JOIN golongan_barang_ref ON (bidangbrgGolbrgId = golbrgId)
   WHERE
      (CONCAT(LPAD(golbrgKode,2,'0'),'.',LPAD(bidangbrgKode,2,'0'),'.',LPAD(kelbrgKode,2,'0'),'.',LPAD(subkelbrgKode,2,'0'),'.',LPAD(barangKode,4,'0')) like '%s' OR barangNama like '%s')
      AND (DATE(pengBrgTgl) BETWEEN '%s' AND '%s')
      AND pengBrgIsJurnal = '0'
      %s
";

$sql['get_list_data_pengendalian'] = "
   SELECT
      pengBrgId AS id,
      pengBrgStatusId AS status,
      DATE(pengBrgTgl) AS peng_tgl,
      statusPengBrgNama AS status_peng_brg,
      CONCAT(LPAD(golbrgKode,2,'0'),'.',LPAD(bidangbrgKode,2,'0'),'.',LPAD(kelbrgKode,2,'0'),'.',LPAD(subkelbrgKode,2,'0'),'.',LPAD(barangKode,4,'0')) AS aset_kode,
      barangNama as aset_nama,
      unitkerjaNama AS unit_kerja,
      pengPIC AS pic
   FROM
      pengendalian_barang_mst
      JOIN pengendalian_barang_det ON (pengBrgId = pengBrgDetMstId)
      RIGHT JOIN status_peng_brg_ref ON (statusPengBrgId = pengBrgStatusId)
      JOIN barang_ref ON (barangId = pengBrgBrgId)
      JOIN unit_kerja_ref ON (unitkerjaId = pengBrgUnitId)
      JOIN sub_kelompok_barang_ref ON (barangSubkelbrgId = subkelbrgId)
      JOIN kelompok_barang_ref ON (subkelbrgKelbrgId = kelbrgId)
      JOIN bidang_barang_ref ON (kelbrgBidangbrgId = bidangbrgId)
      JOIN golongan_barang_ref ON (bidangbrgGolbrgId = golbrgId)
   WHERE
      (CONCAT(LPAD(golbrgKode,2,'0'),'.',LPAD(bidangbrgKode,2,'0'),'.',LPAD(kelbrgKode,2,'0'),'.',LPAD(subkelbrgKode,2,'0'),'.',LPAD(barangKode,4,'0')) like '%s' OR barangNama like '%s')
      AND (DATE(pengBrgTgl) BETWEEN '%s' AND '%s')
      AND pengBrgIsJurnal = '0'
      %s
   LIMIT %s,%s
";

$sql['detil_pengendalian'] = "
   SELECT
      pengBrgDetId AS id,
      barangKode AS brg_kode,
      barangNama AS brg_nama,
      statusPengBrgNama AS status_peng_brg,
      unitkerjaNama AS unitkerja,
      ruangNama AS ruang,
      DATE(pengBrgTgl) AS tgl,
      pengBAPeng AS BA,
      pengPIC AS pic,
      pengKet AS keterangan,
      pengBrgHrgJual AS harga_perkiraan
   FROM
      pengendalian_barang_det
      JOIN pengendalian_barang_mst ON (pengBrgDetMstId = pengBrgId)
      JOIN status_peng_brg_ref ON (statusPengBrgId = pengBrgStatusId)
      JOIN barang_ref ON (barangId = pengBrgBrgId)
      JOIN unit_kerja_ref ON (unitkerjaId = pengBrgUnitId)
      JOIN sub_kelompok_barang_ref ON (barangSubkelbrgId = subkelbrgId)
      JOIN kelompok_barang_ref ON (subkelbrgKelbrgId = kelbrgId)
      JOIN bidang_barang_ref ON (kelbrgBidangbrgId = bidangbrgId)
      JOIN golongan_barang_ref ON (bidangbrgGolbrgId = golbrgId)
      JOIN ruang ON (ruangId = pengBrgLokasi)
   WHERE
      pengBrgDetMstId = %s
      AND pengBrgStatusId = %s
";

?>