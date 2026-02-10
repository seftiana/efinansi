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
      transReferensi as no_bukti,
      transjenNama as jenis,
      ttNamaTransaksi as tipe,
      transTtId as tipe_id,
      transCatatan as uraian,
      transNilai as nominal,
      transPenanggungJawabNama as penanggung_jawab,
      transIsJurnal as is_jurnal,
      IFNULL((SELECT pr.`prIsApproved` FROM `pembukuan_referensi` pr WHERE pr.`prTransId` = transId),'T') AS  approval_jurnal
   FROM 
      transaksi
      INNER JOIN transaksi_tipe_ref ON (ttId = transTtId)
      INNER JOIN transaksi_jenis_ref ON (transjenId = transTransjenId)
		INNER JOIN tahun_pembukuan_periode ON (tppId = transTppId)
   WHERE
      transTransjenId IN (1)
      AND
      transKelompok IN('sp2d')
      AND
      transIsDariAplikasiKeuangan ='Y'
		AND tppIsBukaBuku='Y'
      AND (transTanggalEntri BETWEEN '%s' AND '%s') 
		AND transReferensi LIKE '%s' 
		AND transIsJurnal LIKE '%s'
   ORDER BY tanggal DESC
   LIMIT %s, %s
";


?>