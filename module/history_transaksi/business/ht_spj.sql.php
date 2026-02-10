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
      mk.nama as mak,
      transPenanggungJawabNama as penanggung_jawab,
      transIsJurnal as is_jurnal
   FROM 
      transaksi
      LEFT JOIN transaksi_tipe_ref ON (ttId = transTtId)
      LEFT JOIN transaksi_jenis_ref ON (transjenId = transTransjenId)
      LEFT JOIN (
	  	 SELECT
			spj.`transdtspjTransId` AS maktransId,
			spj.`transdtspjKegdetId` AS kode,
			kr.kegrefNama AS nama
		FROM
			transaksi_detail_spj spj
			JOIN kegiatan_detail kd ON (kd.kegdetId = spj.`transdtspjKegdetId`)
			JOIN kegiatan_ref kr ON (kr.kegrefId = kd.kegdetKegrefId)
	  ) mk ON mk.maktransId = transId
   WHERE
      transTtId = 6
      AND transTanggalEntri BETWEEN '%s' AND '%s'
      AND transReferensi LIKE '%s' 
      AND mk.nama LIKE '%s'
   ORDER BY tanggal DESC
   LIMIT %s, %s
";