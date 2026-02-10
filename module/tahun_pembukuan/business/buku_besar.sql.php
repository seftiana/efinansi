<?php

$sql['insert_buku_besar'] = "
   INSERT buku_besar
   SET 
   	bbTanggal='%s',
   	bbCoaId='%s',
   	bbSaldoAwal='0',
   	bbDebet='%s',
   	bbKredit='%s',
   	bbSaldo='%s',
   	bbSaldoAkhir='%s',
   	bbUserId='%s'
   	[INSERT_SUBACC]
";

$sql['update_buku_besar_where_coa'] = "
   UPDATE buku_besar SET
      bbTanggal = '%s',
      bbSaldoAwal = '0',
      bbDebet = '%s',
      bbKredit = '%s',
      bbSaldo = '%s',
      bbSaldoAkhir = '%s',
      bbUserId = '%s'
   WHERE 
   bbCoaId = '%s'
   [FILTER_SUBACC]
";

$sql['update_buku_besar_where_coa_tutup_buku'] = "
   UPDATE buku_besar SET
      bbTanggal = '%s',
      bbSaldoAwal = '%s',
      bbDebet = '%s',
      bbKredit = '%s',
      bbSaldo = '%s',
      bbSaldoAkhir = '%s',
      bbUserId = '%s'
   WHERE 
   bbCoaId = '%s'
   [FILTER_SUBACC]
";

$sql['insert_buku_besar_history'] = "
INSERT buku_besar_his
SET
   	bbTanggal='%s',
   	bbCoaId='%s',
   	bbSaldoAwal='0',
   	bbDebet='%s',
   	bbKredit='%s',
   	bbSaldo='%s',
   	bbSaldoAkhir='%s',
   	bbUserId='%s'
  	[INSERT_SUBACC]
";

$sql['insert_buku_besar_history_tutup_buku'] = "
INSERT buku_besar_his
SET
   	bbTanggal='%s',
      bbTppId = '%s',
   	bbCoaId='%s',
   	bbSaldoAwal= '%s',
   	bbDebet='%s',
   	bbKredit='%s',
   	bbSaldo='%s',
   	bbSaldoAkhir='%s',
   	bbUserId='%s',
      bbPembukuanRefId = '%s',
      bbPdId = '%s'
  	[INSERT_SUBACC]
";

$sql['update_buku_besar_hist_where_coa'] = "
   UPDATE buku_besar_his SET
      bbTanggal = '%s',
      bbSaldoAwal = '0',
      bbDebet = '%s',
      bbKredit = '%s',
      bbSaldo = '%s',
      bbSaldoAkhir = '%s',
      bbUserId = '%s'
   WHERE 
   bbCoaId = '%s'
   [FILTER_SUBACC]
";

$sql['get_buku_besar_histori_akhir_from_coa'] = "
SELECT
   bbhisId,
   bbTppId,
   bbTanggal,
   bbCoaId,
   bbSaldoAwal,
   bbDebet,
   bbKredit,
   bbSaldo,
   bbSaldoAkhir,
   bbIsJurnalBalik,
   bbUserId
FROM buku_besar_his
WHERE bbhisId = (SELECT MAX(bbhisId) FROM buku_besar_his WHERE  bbCoaId = '%s' [FILTER_SUBACC])
";

$sql['get_buku_besar_from_coa'] = "
 SELECT
   bbId,
   bbTppId,
   bbTanggal,
   bbCoaId,
   bbSaldoAwal,
   bbDebet,
   bbKredit,
   bbSaldo,
   bbSaldoAkhir,
   bbUserId
FROM buku_besar
WHERE bbCoaId = '%s' [FILTER_SUBACC]
";

$sql['update_tahun_periode_buku_besar'] = "
   UPDATE buku_besar
   SET bbTppId = '%s'
";

$sql['update_tahun_periode_buku_besar_histori_is_null'] = "
   UPDATE buku_besar_his
      SET bbTppId = '%s'
   WHERE bbTppId IS NULL 
";

$sql['delete_buku_besar_by_coa_sub_account']="
DELETE FROM buku_besar
WHERE 
	`bbCoaId` = '%s' AND bbTppId IS NULL
	[FILTER_SUBACC]
";

$sql['delete_buku_besar_history_by_coa_sub_account']="
DELETE FROM buku_besar_his
WHERE 
	`bbCoaId` = '%s' AND bbTppId IS NULL
	[FILTER_SUBACC]
";

$sql['coa_pengali'] = "
   SELECT IF(coaIsDebetPositif,1,-1) AS pengaliDebet, IF(!coaIsDebetPositif,1,-1) AS pengaliKredit FROM coa WHERE coaId = '%s'
";

$sql['get_tanggal']="
SELECT bbTanggal AS tanggal
FROM buku_besar
WHERE
	bbCoaId ='%s'
    [FILTER_SUBACC]
";

$sql['get_user'] = "
select
	RealName,
	UserName
from
	gtfw_user
where
	UserId = '%s'
";

$sql['get_coa'] = "
select 
	coaKodeAkun,
	coaNamaAkun  
from
	coa
where
coaId = '%s'
";

$sql['get_ta_aktif'] ="
   SELECT 
      thanggarId 
   FROM 
      tahun_anggaran 
   WHERE 
      thanggarIsAktif = 'Y' 
   LIMIT 1
";

$sql['do_insert_transaksi'] = "
insert transaksi 
set 
	transTtId = '%s',
	transTransjenId = '%s',
	transUnitkerjaId = '%s',
	transTppId = '%s',
	transThanggarId = '%s',
	transReferensi = '%s',
	transUserId = '%s',
	transTanggal = '%s',
	transTanggalEntri = '%s',
	transDueDate = '%s',
	transCatatan = '%s',
	transNilai = '%s',
	transPenanggungJawabNama = '%s',
	transPenerimaNama = '%s',
	transIsJurnal = 'Y'
";
 
$sql['do_insert_pembukuan_referensi']  = "
INSERT INTO pembukuan_referensi
SET prTransId = '%s',
   prUserId = '%s',
   prTanggal = '%s',
   prKeterangan = '%s',
   prIsPosting = 'Y',
   prDelIsLocked = 'T',
   prIsApproved = 'Y',
   prIsKas = 'T'
";
 

$sql['do_insert_pembukuan_detail']  = "
INSERT INTO pembukuan_detail
SET pdPrId = '%s',
   pdCoaId = '%s',
   pdNilai = '%s',
   pdKeterangan = '%s',
   pdKeteranganTambahan = '%s', /*nomor referensi*/
   pdStatus = '%s',
   pdSubaccPertamaKode = %s,
   pdSubaccKeduaKode = %s,
   pdSubaccKetigaKode = %s,
   pdSubaccKeempatKode = %s,
   pdSubaccKelimaKode = %s,
   pdSubaccKeenamKode = %s,
   pdSubaccKetujuhKode = %s
";

?>