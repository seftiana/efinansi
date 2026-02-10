<?php

$sql['get_tahun_pembukuan_periode']  = "
SELECT
   tppId AS `id`,
   tppTanggalAwal AS `awal`,
   tppTanggalAkhir AS `akhir`,
   tppIsBukaBuku AS `open`
FROM tahun_pembukuan_periode
WHERE 1 = 1
AND (tppIsBukaBuku = 'Y' OR 1 = %s)
";

//===GET===
$sql['get_count_data'] = "
   SELECT FOUND_ROWS() as total
";

$sql['get_data'] = "
   SELECT
      SQL_CALC_FOUND_ROWS
      transId as id,
      transTppId AS tp_id,
      transTanggalEntri as tanggal,
      transReferensi as kkb,
      transjenNama as jenis,
      ttNamaTransaksi as tipe,
      transTtId as tipe_id,
      transCatatan as uraian,
      transNilai as nominal,
      mk.nama as mak,
      mk.nomorPengajuan as no_pengajuan,
      transPenanggungJawabNama as penanggung_jawab,
      transIsJurnal as is_jurnal
   FROM 
      transaksi
      LEFT JOIN transaksi_tipe_ref ON (ttId = transTtId)
      LEFT JOIN transaksi_jenis_ref ON (transjenId = transTransjenId)
      LEFT JOIN (
		 SELECT
			tdp.transdtpencairanTransId AS maktransId,
			tdp.transdtpencairanKegdetId AS kode,
			tdp.transdtpencairanId AS id,
			kr.kegrefNama AS nama,
         pr.`pengrealNomorPengajuan` AS nomorPengajuan
		FROM
			transaksi_detail_pencairan tdp
			JOIN kegiatan_detail kd ON (kd.kegdetId = tdp.transdtpencairanKegdetId)
			JOIN kegiatan_ref kr ON (kr.kegrefId = kd.kegdetKegrefId)
         JOIN pengajuan_realisasi pr ON pr.`pengrealId` = tdp.`transdtpencairanPengrealId`
		) mk ON  mk.maktransId = transId
   WHERE
      transTtId = 4 
      AND transTanggalEntri BETWEEN '%s' AND '%s'
      AND transReferensi LIKE '%s'
      AND transIsJurnal LIKE '%s'
      AND (transNilai %s 500000 OR 1 = %s )
      %s
   ORDER BY kkb ASC
   LIMIT %s, %s
";


?>