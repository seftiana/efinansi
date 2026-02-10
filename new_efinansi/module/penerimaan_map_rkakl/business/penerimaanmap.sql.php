<?php

$sql['get_data'] = "
SELECT
	kodeterimaRKAKLKodePenerimaanId AS id,
	rkaklKodePenerimaanKode AS kode_map,
	rkaklKodePenerimaanNama AS nama_map,
	SUM(transNilai) AS nominal
FROM
	rencana_penerimaan
JOIN
   kode_penerimaan_ref ON kodeterimaId = renterimaKodeterimaId
JOIN
   finansi_ref_rkakl_kode_penerimaan ON kodeterimaRKAKLKodePenerimaanId = rkaklKodePenerimaanId
JOIN
   realisasi_penerimaan ON realrenterimaId = renterimaId
JOIN
   transaksi ON transId = realterimaTransId
GROUP BY
   kodeterimaRKAKLKodePenerimaanId
";

$sql['get_count_data'] = "
SELECT
  COUNT(DISTINCT(kodeterimaRKAKLKodePenerimaanId)) as total

FROM
	rencana_penerimaan
JOIN
   kode_penerimaan_ref ON kodeterimaId = renterimaKodeterimaId
JOIN
   finansi_ref_rkakl_kode_penerimaan ON kodeterimaRKAKLKodePenerimaanId = rkaklKodePenerimaanId
JOIN
   realisasi_penerimaan ON realrenterimaId = renterimaId
JOIN
   transaksi ON transId = realterimaTransId
";

$sql['get_data_by_id'] = "
SELECT
	kodeterimaRKAKLKodePenerimaanId AS id,
	rkaklKodePenerimaanKode AS kode_map,
	rkaklKodePenerimaanNama AS nama_map,
	SUM(transNilai) AS nominal
FROM
	rencana_penerimaan
JOIN
   kode_penerimaan_ref ON kodeterimaId = renterimaKodeterimaId
JOIN
   finansi_ref_rkakl_kode_penerimaan ON kodeterimaRKAKLKodePenerimaanId = rkaklKodePenerimaanId
JOIN
   realisasi_penerimaan ON realrenterimaId = renterimaId
JOIN
   transaksi ON transId = realterimaTransId
WHERE
   kodeterimaRKAKLKodePenerimaanId = '%s'
GROUP BY kodeterimaRKAKLKodePenerimaanId
";

$sql['get_data_detil'] = "
SELECT
	kodeTerimaKode AS kode,
	kodeTerimaNama AS nama,
	transTransjenId AS jenis,
   transTtId AS tipe,
   transReferensi AS no_kkb,
   transTanggalEntri AS tanggal,
   transDueDate AS due_date,
   transCatatan AS catatan_transaksi,
   transNilai AS nominal
FROM
	rencana_penerimaan
JOIN
   kode_penerimaan_ref ON kodeterimaId = renterimaKodeterimaId
JOIN
   finansi_ref_rkakl_kode_penerimaan ON kodeterimaRKAKLKodePenerimaanId = rkaklKodePenerimaanId
JOIN
   realisasi_penerimaan ON realrenterimaId = renterimaId
JOIN
   transaksi ON transId = realterimaTransId
WHERE
   kodeterimaRKAKLKodePenerimaanId = '%s'
";

?>