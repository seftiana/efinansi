<?php

//===GET===
$sql['get_count_data'] = "
   SELECT FOUND_ROWS() as total
";

$sql['get_data'] = "
   SELECT
      SQL_CALC_FOUND_ROWS
      transId as id,
      transTanggalEntri as tanggal,
      transReferensi as kkb,
      transjenNama as jenis,
      ttNamaTransaksi as tipe,
      transTtId as tipe_id,
      transCatatan as uraian,
      transNilai as nominal,
      transIsJurnal as is_jurnal
   FROM 
      transaksi
      INNER JOIN transaksi_tipe_ref ON (ttId = transTtId)
      INNER JOIN transaksi_jenis_ref ON (transjenId = transTransjenId)
   WHERE
      transTtId = 4  AND transIsJurnal = 'Y'
      AND transTanggalEntri BETWEEN '%s' AND '%s'
      AND transReferensi LIKE '%s' 
      AND transIsJurnal LIKE '%s'
   ORDER BY tanggal DESC
   LIMIT %s, %s
";