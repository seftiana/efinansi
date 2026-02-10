<?php

/**
 * @package  SQL-FILE
 */
$sql['get_data_transaksi_items'] = "
   SELECT
      SQL_CALC_FOUND_ROWS
      tr.transId AS id,      
      tr.transReferensi AS no_bpkb,      
      tr.transTanggalEntri AS tanggal,
      tr.transCatatan AS keterangan,      
      uk.`unitkerjaNama` AS unit_nama,
      IFNULL(tr.`transPenerimaNama`,'-') AS nama_penerima,
      tr.transNilai AS nominal
   FROM 
      transaksi tr
      JOIN transaksi_tipe_ref ttr 
	ON ttr.ttId = tr.transTtId
      JOIN transaksi_jenis_ref tjr 
	ON tjr.transjenId = tr.transTransjenId
      JOIN (
		 SELECT
         tdp.`transdtpencairanPengrealId` AS pengrealId,
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
	JOIN unit_kerja_ref uk ON uk.unitkerjaId =  tr.`transUnitkerjaId` 	
   WHERE
      tr.transTtId = 4 
      AND tr.transTanggalEntri BETWEEN '%s' AND '%s'
      AND tr.transReferensi LIKE '%s'
      AND (tr.transIsJurnal = '%s' OR %s)
      AND (tr.transNilai <= 500000 )
      AND mk.nomorPengajuan LIKE '%s'
   ORDER BY no_bpkb ASC
";


$sql['get_setting_name'] = "
SELECT
   settingValue AS `name`
FROM setting
WHERE 1 = 1
AND settingName = '%s'
LIMIT 1
";
?>