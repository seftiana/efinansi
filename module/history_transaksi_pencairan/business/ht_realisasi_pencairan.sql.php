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
   mk.nama AS mak,
   transPenanggungJawabNama AS penanggung_jawab,
   transIsJurnal AS is_jurnal
FROM
   transaksi
   LEFT JOIN transaksi_tipe_ref ON (ttId = transTtId)
   LEFT JOIN transaksi_jenis_ref ON (transjenId = transTransjenId)
   LEFT JOIN (
    SELECT
      tdp.transdtpencairanTransId AS maktransId,
      tdp.transdtpencairanKegdetId AS kode,
      tdp.transdtpencairanId AS id,
      kr.kegrefNama AS nama
   FROM
      transaksi_detail_pencairan tdp
      JOIN kegiatan_detail kd ON (kd.kegdetId = tdp.transdtpencairanKegdetId)
      JOIN kegiatan_ref kr ON (kr.kegrefId = kd.kegdetKegrefId)
   ) mk ON  mk.maktransId = transId
WHERE 1 = 1
   AND transTtId = '4'
   AND transTanggalEntri BETWEEN '%s' AND '%s'
   AND transReferensi LIKE '%s'
   AND (UPPER(transIsJurnal) = '%s' OR 1 = %s)
   AND mk.nama LIKE '%s'
ORDER BY tanggal DESC
LIMIT %s, %s
";