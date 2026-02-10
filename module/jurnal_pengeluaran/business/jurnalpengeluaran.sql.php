<?php

//===GET===
$sql['get_combo_coa']="
SELECT
 coaId AS id,
 coaNamaAkun AS name
FROM
 coa 
WHERE
 coaIsKas = 1 AND
 coaId NOT IN (SELECT DISTINCT(coaParentAkun) FROM coa)
";

$sql['get_referensi_transaksi']="
SELECT
   t.transId AS transaksi_id,
   t.transDueDate AS transaksi_tanggal,
   t.transReferensi AS transaksi_referensi,
   t.transCatatan AS transaksi_catatan,
   t.transNilai AS transaksi_nilai

FROM
 transaksi t
 JOIN tahun_anggaran ta ON t.transThanggarId = ta.thanggarId

WHERE
 ta.thanggarIsAktif ='Y' AND
 t.transIsJurnal ='T' AND
 t.transTtId ='2'
";

$sql['get_data']="
SELECT
 pr.prId AS id,
 t.transReferensi AS referensi,
 t.transTanggalEntri AS tanggal,
 pd.pdKeterangan AS keterangan,
 c.coaKodeAkun AS rekening_kode,
 c.coaNamaAkun AS rekening_nama, 
 pd.pdNilai AS nilai,
 pd.pdStatus AS tipeakun,
 t.transIsJurnal AS is_jurnal,
 pr.prIsPosting AS is_posting,
 pr.prDelIsLocked AS is_locked,
 pr.prIsApproved AS is_approved,
 gu.RealName AS petugas_entri

FROM
  transaksi t
  JOIN pembukuan_referensi pr ON t.transId=pr.prTransId
  JOIN pembukuan_detail pd ON pr.prId = pd.pdPrId
  JOIN coa c ON pd.pdCoaId = c.coaId
  JOIN gtfw_user gu ON gu.UserId =  pr.prUserId

WHERE 
   pr.prId IN ('%s') 
   AND pr.prIsJurnalBalik != 1
ORDER BY t.transTanggalEntri DESC, pd.pdId , t.transId DESC, pd.pdStatus ASC
";

$sql['get_data_all'] = "

";

$sql['get_pembukuan_referensi'] ="
   SELECT
      prId as id
   FROM
      transaksi 
      JOIN pembukuan_referensi ON transId=prTransId
      JOIN pembukuan_detail ON prId = pdPrId
   WHERE transTtId IN (2,4) AND transTanggalEntri BETWEEN '%s' AND '%s' AND prIsJurnalBalik != 1 AND transReferensi LIKE  '%s' AND prIsPosting LIKE '%s'
   GROUP BY prId
   ORDER BY transTanggalEntri DESC
   LIMIT %s, %s
";

$sql['get_pembukuan_referensi_all'] ="
   SELECT
      prId as id
   FROM
      transaksi 
      JOIN pembukuan_referensi ON transId=prTransId
      JOIN pembukuan_detail ON prId = pdPrId
   WHERE transTtId IN (2,4)
   AND prIsJurnalBalik != 1
   GROUP BY prId
   ORDER BY transTanggalEntri DESC
   LIMIT %s, %s
";

$sql['get_count']="
SELECT
    COUNT(DISTINCT(prId)) as total
FROM
  transaksi t
  JOIN pembukuan_referensi pr ON t.transId=pr.prTransId
  JOIN pembukuan_detail pd ON pr.prId = pd.pdPrId
WHERE 
   pr.prId IN (
         SELECT
            prId
         FROM
            transaksi 
            JOIN pembukuan_referensi ON transId=prTransId
            JOIN pembukuan_detail ON prId = pdPrId
         WHERE t.transTtId IN (2,4) AND transTanggalEntri BETWEEN %s AND %s AND transReferensi LIKE  '%s' AND prIsPosting LIKE '%s'
      )
   AND pr.prIsJurnalBalik != 1
GROUP BY prId
";

$sql['get_count_all']="
SELECT
    COUNT(DISTINCT(prId)) as total
FROM
  transaksi t
  JOIN pembukuan_referensi pr ON t.transId=pr.prTransId
  JOIN pembukuan_detail pd ON pr.prId = pd.pdPrId
WHERE 
   pr.prId IN (
         SELECT
            prId
         FROM
            transaksi 
            JOIN pembukuan_referensi ON transId=prTransId
            JOIN pembukuan_detail ON prId = pdPrId
         WHERE t.transTtId IN (2,4)
      )
   AND pr.prIsJurnalBalik != 1
GROUP BY prId
";

$sql['get_min_max_tahun_pencatatan'] = "
SELECT
 YEAR(MIN(transTanggalEntri)) - 5 AS minTahun,
 YEAR(MAX(transTanggalEntri)) + 5 AS maxTahun
FROM
 transaksi
";


$sql['get_data_by_id']="
SELECT
 t.transId AS referensi_id,
 t.transReferensi AS referensi_nama,
 t.transNilai AS referensi_nilai,
 t.transDueDate AS referensi_tanggal,
 pr.prId AS pembukuan_referensi_id,
 pd.pdId AS detail_id,
 pd.pdKeterangan AS detail_keterangan,
 pd.pdNilai AS detail_nilai,
 pd.pdStatus AS detail_status,
 c.coaId AS coa_id,
 c.coaKodeAkun AS coa_kode,
 c.coaNamaAkun AS coa_nama
 

FROM
 transaksi t
 JOIN pembukuan_referensi pr ON t.transId =pr.prTransId
 JOIN pembukuan_detail pd ON pr.prId = pdPrId
 JOIN coa c ON pd.pdCoaId = c.coaId

WHERE
 pr.prId = %s

ORDER BY pr.prId,pd.pdId,pd.pdStatus
";
	
//===DO===
$sql['do_add_pembukuan_referensi']="
INSERT INTO `pembukuan_referensi` 
   ( `prTransId`, `prUserId`, `prTanggal`, `prKeterangan`, `prIsPosting`, `prDelIsLocked`, `prIsApproved` )       
VALUES 
   ( %s,  %s,  %s,  %s,  'T',  'T',  'T' )
";

$sql['do_add_pembukuan_detail']="
INSERT INTO `pembukuan_detail` 
   ( `pdPrId`, `pdCoaId`, `pdNilai`, `pdKeterangan`, `pdStatus` ) 
VALUES
   ( %s,  %s,  %s,  %s,  %s )
";

$sql['do_approve']="
UPDATE `pembukuan_referensi`
SET  
 `prIsApproved`='Y',
 `prIsKas` = '%s',
 `prBentukTransaksi` = '%s'
WHERE `prId` = '%s' 
";

$sql['do_update_pembukuan_referensi']="
UPDATE `pembukuan_referensi`
SET  
 `prTransId`=%s,
 `prUserId`=%s,
 `prKeterangan`=%s,
 `prIsApproved`='T'
WHERE `prId`=%s 
";

$sql['do_update_pembukuan_detail']="
UPDATE 
  `pembukuan_detail` 
SET
  `pdCoaId`='%s',
  `pdNilai`='%s',
  `pdKeterangan`='%s'
where 
  `pdId`='%s' 
";


$sql['do_delete_pembukuan_detail_by_array_id']="
DELETE FROM pembukuan_detail WHERE pdId IN('%s');
";

$sql['do_delete_pembukuan_detail']="
DELETE FROM pembukuan_detail WHERE pdPrId = %s;
";

$sql['do_delete_pembukuan_referensi']="
DELETE FROM pembukuan_referensi WHERE prId = %s;
";

//LOGGER LOGGER LOGGER

$sql['do_add_log'] = "
   INSERT INTO logger(logUserId, logAlamatIp, logUpdateTerakhir, logKeterangan)
   VALUES ('%s', '%s', NOW(), '%s')
";

$sql['do_add_log_detil'] = "
   INSERT INTO logger_detail(logId, logAksiQuery)
   VALUES ('%s', '%s')
";

$sql['update_status_jurnal'] = "
UPDATE
   transaksi
SET
   transIsJurnal = 'Y'
WHERE
   transId = %s
";

$sql['get_max_pembukuan_referensi_id'] = "
SELECT 
   MAX(prId) AS max_id
FROM
   pembukuan_referensi
";

$sql['update_status_posting_saat_jurnal_balik'] = "
UPDATE
   pembukuan_referensi
SET
   prIsPosting = 'T',
   prIsApproved = 'T'
WHERE
   prId = '%s'
";

$sql['update_status_is_jurnal'] = "
UPDATE
   transaksi
SET
   transIsJurnal = 'Y'
WHERE
   transId = %s
";

//posting setelah jurnal balik
$sql['get_data_jurnal_balik'] = "
   SELECT
      prId AS pembukuan_ref_id,
      transID AS transaksi_id,
      transTanggalEntri AS transaksi_tanggal,
      transReferensi AS transaksi_referensi,
      transCatatan AS transaksi_catatan,
      pdCoaId AS coa_id,
      coaKodeAkun AS akun_kode,
      coaNamaAkun AS akun_nama,
      pdNilai AS nilai,
      pdStatus AS status_pembukuan,
      coaIsDebetPositif AS coa_status_debet,
      coaCoaKelompokId AS coa_kelompok
   FROM
      transaksi
      JOIN pembukuan_referensi ON transId = prTransId
      JOIN pembukuan_detail ON prId = pdPrId
      JOIN coa ON pdCoaId = coaId
      JOIN coa_kelompok ON coaCoaKelompokId = coaKelompokId
   WHERE
      prId = '%s'
";

$sql['cek_akun_buku_besar'] = "
   SELECT
      bbId AS bb_id,
      bbSaldoAwal AS saldo_awal,
      bbSaldoAkhir AS saldo_akhir
   FROM
      buku_besar
   WHERE
      bbCoaId = '%s'
";

//hanya mengakomodir 1 coa laba rigi saja. kalo lebih dari 1 ??
$sql['cek_akun_laba_rugi_buku_besar'] = "
   SELECT
      bbId AS bb_id,
      bbSaldoAwal AS saldo_awal,
      bbSaldoAkhir AS saldo_akhir
   FROM
      buku_besar
   WHERE
      bbCoaId = (SELECT coaId FROM coa WHERE `coaIsLabaRugiThJln` = '1')
";

$sql['get_coa_laba_rugi'] = "
   SELECT 
      coaKelompokId 
   FROM 
      coa_kelompok 
   WHERE 
      coaKelompokNama = 'Pendapatan' OR coaKelompokNama = 'Biaya';
";

$sql['do_insert_buku_besar'] = "
   INSERT INTO buku_besar
      (bbTppId, bbTanggal, bbCoaId, bbSaldoAwal, bbDebet, bbKredit, bbSaldo, bbSaldoAkhir, bbUserId)
   VALUES ((SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'), now(), '%s', '%s', '%s', '%s', '%s', '%s', '%s');
";

$sql['do_insert_laba_rugi_buku_besar'] = "
   INSERT INTO buku_besar
      (bbTppId, bbTanggal, bbCoaId, bbSaldoAwal, bbDebet, bbKredit, bbSaldo, bbSaldoAkhir, bbUserId)
   VALUES ((SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'), now(), (SELECT coaId FROM coa WHERE `coaIsLabaRugiThJln` = '1' AND coaUnitkerjaId = (SELECT userunitkerjaUnitkerjaId FROM user_unit_kerja WHERE userunitkerjaUserId = %s)), '%s', '%s', '%s', '%s', '%s', '%s');
";

$sql['do_update_buku_besar'] = "
   UPDATE 
      buku_besar
   SET
      bbTppId = (SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'),
      bbTanggal = now(),
      bbCoaId = '%s',
      bbSaldoAwal = '%s',
      bbDebet = '%s',
      bbKredit = '%s',
      bbSaldo = '%s',
      bbSaldoAkhir = '%s',
      bbUserId = '%s'
   WHERE
      bbId = '%s'
";

$sql['do_update_laba_rugi_buku_besar'] = "
   UPDATE 
      buku_besar
   SET
      bbTppId = (SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'),
      bbTanggal = now(),
      bbCoaId = (SELECT coaId FROM coa WHERE `coaIsLabaRugiThJln` = '1' AND coaUnitkerjaId = (SELECT userunitkerjaUnitkerjaId FROM user_unit_kerja WHERE userunitkerjaUserId = %s)),
      bbSaldoAwal = '%s',
      bbDebet = '%s',
      bbKredit = '%s',
      bbSaldo = '%s',
      bbSaldoAkhir = '%s',
      bbUserId = '%s'
   WHERE
      bbId = '%s'
";

$sql['do_insert_buku_besar_his'] = "
   INSERT INTO buku_besar_his
      (bbTppId, bbPembukuanRefId, bbTanggal, bbCoaId, bbSaldoAwal, bbDebet, bbKredit, bbSaldo, bbSaldoAkhir, bbUserId)
   VALUES ((SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'), '%s', now(), '%s', '%s', '%s', '%s', '%s', '%s', '%s');
";

$sql['do_insert_laba_rugi_buku_besar_his'] = "
   INSERT INTO buku_besar_his
      (bbTppId, bbPembukuanRefId, bbTanggal, bbCoaId, bbSaldoAwal, bbDebet, bbKredit, bbSaldo, bbSaldoAkhir, bbUserId)
   VALUES ((SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'), '%s', now(), (SELECT coaId FROM coa WHERE `coaIsLabaRugiThJln` = '1' AND coaUnitkerjaId = (SELECT userunitkerjaUnitkerjaId FROM user_unit_kerja WHERE userunitkerjaUserId = %s)), '%s', '%s', '%s', '%s', '%s', '%s');
";

$sql['update_status_posting_balik_pembukuan_ref'] = "
   UPDATE
      pembukuan_referensi
   SET
      prIsPosting = 'Y',
      prIsJurnalBalik = 1
   WHERE
      prId = '%s'
";

$sql['update_status_is_jurnal_ketika_delete'] = "
   UPDATE
      transaksi
   SET
      transIsJurnal = '%s'
   WHERE
      transId = (SELECT prTransId FROM pembukuan_referensi WHERE prId = %s)
";

$sql['get_bentuk_transaksi'] = "
   SELECT 
      kelJnsId AS id, 
      kelJnsNama AS name 
   FROM 
      kelompok_jenis_laporan_ref 
   WHERE kelJnsPrntId = '2'
";

?>
