<?php

//===GET===
$sql['get_count_data'] = "
   SELECT FOUND_ROWS() as total
";

$sql['get_data'] = "
     SELECT
      SQL_CALC_FOUND_ROWS
      transId AS id,
      transTanggalEntri AS tanggal,
      transReferensi AS kkb,
      transjenNama AS jenis,
      ttNamaTransaksi AS tipe,
      transTtId AS tipe_id,
      transCatatan AS uraian,
      transNilai AS nominal,
      ref_transaksi.ref AS ref_transaksi,	
      transPenanggungJawabNama AS penanggung_jawab,
      transIsJurnal AS is_jurnal
   FROM 
      transaksi
      LEFT JOIN transaksi_tipe_ref ON (ttId = transTtId)
      LEFT JOIN transaksi_jenis_ref ON (transjenId = transTransjenId)
      LEFT JOIN finansi_transaksi_ref_transaksi ON (transaksiTranskasiId = transId)
      LEFT JOIN (
			SELECT 
				tr.transId AS id,
				tr.transReferensi AS ref
			FROM 
				transaksi tr
      		) ref_transaksi ON ref_transaksi.id = transaksiTransaksiRefId
      
   WHERE
      transTtId = 6
      AND transTanggalEntri BETWEEN '%s' AND '%s'
      AND transReferensi LIKE '%s' 
      AND ref_transaksi.ref LIKE '%s'
   ORDER BY tanggal DESC
   LIMIT %s, %s
";

$sql['get_daftar_ref_transaksi']="
SELECT
  SQL_CALC_FOUND_ROWS
  transDueDate AS tanggal,
  transReferensi AS referensi,
  transCatatan AS catatan
FROM
  transaksi
WHERE
  transIsJurnal = 'T' AND
  transTtId IN (2,4) AND
  transReferensi LIKE %s
LIMIT %s,%s
";