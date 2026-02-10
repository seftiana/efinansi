<?php

$sql['get_list_coa_as_tahun_pembukuan_tahun_lalu'] ="
SELECT
   c.coaId,
   c.coaUnitkerjaId,
   c.coaKodeAkun,
   c.coaNamaAkun,
   c.coaLevelAkun,
   c.coaParentAkun,
   c.coaIsDebetPositif,
   c.coaIsKas,
   c.coaIsLabaRugiThJln,
   c.coaIsLocked,
   SUM(tph.tphSaldoAkhir) AS tpSaldoAwal 
FROM coa c
LEFT JOIN tahun_pembukuan_hist tph ON tphCoaId = c.coaId 
WHERE c.coaId NOT IN(SELECT DISTINCT(coaParentAkun) FROM coa) AND (coaCoaKelompokId NOT IN (4,5)) 
AND
(coaUnitkerjaId = '%s' OR coaUnitkerjaId IS NULL)
GROUP BY c.coaId
ORDER BY c.coaKodeAkun ASC
";

$sql['get_laba_rugi_tahun_berjalan_pembukuan_aktif'] ="	
select  
   c.coaId as coa_id, 
   ifnull(sum(bbh.bbDebet),0) as debit,
   ifnull(sum(bbh.bbKredit),0) as kredit,
   ifnull(sum(bbh.bbSaldo),0) as saldo 
from 
   buku_besar_his bbh 
   join coa c on c.coaId = bbh.bbCoaId 
   join tahun_pembukuan_periode tpp on tpp.tppId = bbh.bbTppId 
   left join (
      select
         pr.prId as prId,
         tr.transTanggalEntri as tanggal,
         pr.prTanggal as tanggalPr
      from
         transaksi tr
      join pembukuan_referensi pr on
         pr.prTransId = tr.transId
      join tahun_pembukuan_periode tpp on
         tpp.tppIsBukaBuku = 'Y'
         and (tpp.tppTanggalAwal) <= (tr.transTanggalEntri)
         and (tpp.tppTanggalAkhir) >= (tr.transTanggalEntri) 
   ) jurnal on jurnal.prId = bbh.bbPembukuanRefId
where 
   c.coaIsLabaRugiThJln ='1'
   and
   tppIsBukaBuku ='Y'
	AND (
      (jurnal.prId is null and bbh.bbPembukuanRefId is null)
      OR
      (jurnal.prId is not null and bbh.bbPembukuanRefId is not null)
   )
";

$sql['get_laba_rugi_awal_tahun_pembukuan_aktif'] ="
select  
   c.coaId as coa_id,  
   ifnull(sum(bbh.bbDebet),0) as debit,
   ifnull(sum(bbh.bbKredit),0) as kredit,
   ifnull(sum(bbh.bbSaldo),0) as saldo 
from 
   buku_besar_his bbh 
   join coa c on c.coaId = bbh.bbCoaId 
   join tahun_pembukuan_periode tpp on tpp.tppId = bbh.bbTppId 
where 
   c.coaIsLabaRugiThAwal ='1'
   and
   tppIsBukaBuku ='Y'
";

$sql['get_laba_rugi_awal_tahun_pembukuan_aktif_saldo_awal'] = "
select  
   c.coaId as coa_id,  
   ifnull(sum(bbh.bbSaldo),0)  as saldo_akhir 
from 
   buku_besar_his bbh 
   join coa c on c.coaId = bbh.bbCoaId 
   join tahun_pembukuan_periode tpp on tpp.tppId = bbh.bbTppId 
where 
   bbh.bbPembukuanRefId is null 
   and 
   bbh.bbPdId is null 
   AND
   c.coaIsLabaRugiThAwal ='1'
   and
   tppIsBukaBuku ='Y'
";

$sql['get_list_coa_as_tahun_pembukuan']="
SELECT
   coaId,
   coaUnitkerjaId,
   coaKodeAkun,
   coaNamaAkun,
   coaLevelAkun,
   coaParentAkun,
   coaIsDebetPositif,
   coaIsKas,
   coaIsLabaRugiThJln,
   coaIsLocked,
   SUM(tpSaldoAwal) AS tpSaldoAwal,
   SUM(tpDebet) AS debet,
  SUM(tpKredit) AS kredit,
   SUM(tpSaldoAkhir) AS tpSaldoAkhir
FROM coa
LEFT JOIN tahun_pembukuan ON tpCoaId = coaId
WHERE coaId NOT IN(SELECT DISTINCT(coaParentAkun) FROM coa) AND (substr(coaKodeAkun,1,1) NOT IN (4,5))
AND
(coaUnitkerjaId = '%s' OR coaUnitkerjaId IS NULL)
GROUP BY coaId
ORDER BY coaKodeAkun ASC
";

$sql['get_list_coa_as_tahun_pembukuan_old']="
   SELECT
      coaId,
      coaUnitkerjaId,
      coaKodeAkun,
      coaNamaAkun,
      coaLevelAkun,
      coaParentAkun,
      coaIsDebetPositif,
      coaIsKas,
      coaIsLabaRugiThJln,
      coaIsLocked,
      ctrNamaTipe,
      IFNULL((CASE coaIsDebetPositif
         WHEN 1 THEN
         (SELECT tpDebet - tpKredit FROM tahun_pembukuan WHERE tpCoaId=coaId)
         WHEN 0 THEN
         (SELECT tpKredit - tpDebet FROM tahun_pembukuan WHERE tpCoaId=coaId)
      END),0) AS saldo_akhir
   FROM coa
   LEFT JOIN coa_tipe_coa ON(coaId=coatipecoaCoaId)
   LEFT JOIN coa_tipe_ref ON(coatipecoaCtrId=ctrId)
   WHERE coaId NOT IN(SELECT DISTINCT(coaParentAkun) FROM coa) and (coaCoaKelompokId NOT IN (4,5))
   ORDER BY coaKodeAkun ASC
";

$sql['get_balance_pembukuan_coa']="
SELECT
   coaId,
   coaUnitkerjaId,
   coaKodeAkun,
   coaNamaAkun,
   coaLevelAkun,
   coaParentAkun,
   coaIsDebetPositif,
   coaIsKas,
   coaIsLabaRugiThJln,
   coaIsLocked,
   /*ctrNamaTipe,*/
   tpDebet AS debet,
   tpKredit AS kredit,
   tpSaldoAkhir,
   tpId
   [SUBACC_VIEW]
FROM coa
/*LEFT JOIN coa_tipe_coa ON(coaId=coatipecoaCoaId)
LEFT JOIN coa_tipe_ref ON(coatipecoaCtrId=ctrId)*/
LEFT JOIN tahun_pembukuan ON tpCoaId = coaId
WHERE coaId = '%s'
";

$sql['get_balance_pembukuan_sub_acc_coa']="
SELECT
   tpId,
   coaId,
   coaUnitkerjaId,
   coaKodeAkun,
   coaNamaAkun,
   coaLevelAkun,
   coaParentAkun,
   coaIsDebetPositif,
   coaIsKas,
   coaIsLabaRugiThJln,
   coaIsLocked,
   ctrNamaTipe,
   tpSaldoAwal,
   tpDebet AS debet,
   tpKredit AS kredit,
   tpSaldoAkhir
   [SUBACC_VIEW]
FROM tahun_pembukuan
LEFT JOIN coa ON coaId = tpCoaId
LEFT JOIN coa_tipe_coa ON coatipecoaCoaId = coaId
LEFT JOIN coa_tipe_ref ON ctrId = coatipecoaCtrId
WHERE coaId = %s AND tpUnitKerjaId = (SELECT coaUnitkerjaId FROM coa WHERE coaId = '%s') [FILTER_SUBACC]
GROUP BY coaId,subAcc
";

$sql['get_balance_pembukuan_coa_old'] = "
   SELECT
      coaId,
      coaUnitkerjaId,
      coaKodeAkun,
      coaNamaAkun,
      coaLevelAkun,
      coaParentAkun,
      coaIsDebetPositif,
      coaIsKas,
      coaIsLabaRugiThJln,
      coaIsLocked,
      ctrNamaTipe,
      IFNULL((CASE coaIsDebetPositif
         WHEN 1 THEN
         (SELECT tpDebet - tpKredit FROM tahun_pembukuan WHERE tpCoaId=coaId)
         WHEN 0 THEN
         (SELECT tpKredit - tpDebet FROM tahun_pembukuan tpKredit WHERE tpCoaId=coaId)
      END),0) AS saldo_akhir
   FROM coa
   LEFT JOIN coa_tipe_coa ON(coaId=coatipecoaCoaId)
   LEFT JOIN coa_tipe_ref ON(coatipecoaCtrId=ctrId)
   WHERE coaId = '%s'
";

$sql['check_balance']="
SELECT
   IF((SUM(tphDebet)=SUM(tphKredit)), 1, 0) AS status_balance
FROM
   tahun_pembukuan_hist
   LEFT JOIN coa ON (coaId=tphCoaId)
   LEFT JOIN coa_kelompok ON (coaKelompokId=coaCoaKelompokId)
WHERE
   coaKelompokNama IN ('Aktiva', 'Modal', 'Pasiva')
";

$sql['get_aktiva']="
SELECT
  #ada akun akumulasi penyusutan (mengurangi saldo awal)
   SUM( if(coaIsDebetPositif =1, ifnull(tpSaldoAwal,0),ifnull((tpSaldoAwal * -1),0))) 
      + (SUM(IFNULL(tpDebet,0)) - SUM(IFNULL(tpKredit,0))) AS jumlah
FROM
   tahun_pembukuan
 LEFT JOIN coa ON (coaId=tpCoaId)
 LEFT JOIN coa_kelompok ON (coaKelompokId=coaCoaKelompokId)
WHERE
   #coaKelompokNama = '%s' 
   substr(coa.coaKodeAkun,1,1) = 1
";

$sql['get_kewajiban']="
SELECT
sum(ifnull(tpSaldoAwal,0)) + (SUM(IFNULL(tpKredit,0)) - SUM(IFNULL(tpDebet,0))) AS jumlah
FROM
  tahun_pembukuan 
LEFT JOIN coa ON (coaId=tpCoaId)
LEFT JOIN coa_kelompok ON (coaKelompokId=coaCoaKelompokId)
WHERE
   #coaKelompokNama = '%s'
   substr(coa.coaKodeAkun,1,1) = 2
";

$sql['get_modal']= "
SELECT
sum(ifnull(tpSaldoAwal,0)) + (SUM(IFNULL(tpKredit,0)) - SUM(IFNULL(tpDebet,0))) AS jumlah
FROM
  tahun_pembukuan 
LEFT JOIN coa ON (coaId=tpCoaId)
LEFT JOIN coa_kelompok ON (coaKelompokId=coaCoaKelompokId)
WHERE
   #coaKelompokNama = '%s'
   substr(coa.coaKodeAkun,1,1) = 3
";

$sql['get_count_transaksi_not_jurnal'] = "
   SELECT count(transId) AS jumlah  FROM transaksi WHERE transIsJurnal = 'T'
";


$sql['get_count_jurnal_not_posting'] = "
   SELECT count(prId) AS jumlah FROM pembukuan_referensi WHERE prIsPosting = 'T'
";

$sql['insert_tahun_pembukuan'] = "
   INSERT INTO tahun_pembukuan
   SET
      tpCoaId='%s',
      tpUnitkerjaId = (SELECT coaUnitkerjaId FROM coa WHERE coaId = '%s'),
      tpDebet='%s',
      tpKredit='%s',
      tpSaldo='%s',
      tpSaldoAkhir='%s'
      [INSERT_SUBACC]
";

$sql['update_tahun_pembukuan'] = "
   UPDATE tahun_pembukuan SET
      tpDebet='%s' ,
      tpKredit='%s' ,
      tpSaldo='%s',
      tpSaldoAkhir='%s'
   WHERE tpCoaId = '%s' [FILTER_SUBACC]
";

$sql['get_tahun_pembukuan_from_coa'] = "
   SELECT
      tpId,
      tpTppId,
      tpCoaId,
      tpUnitkerjaId,
      tpSaldoAwal,
      tpDebet,
      tpKredit,
      tpSaldo,
      tpSaldoAkhir,
      tpAnggaran,
      tpBukaBukuUserId,
      tpTutupBukuUserId
      [SUBACC_VIEW]
   FROM tahun_pembukuan
   WHERE
        tpCoaId = '%s'
        [FILTER_SUBACC]
";

$sql['get_tahun_pembukuan_by_id']="
SELECT
   tpId,
   coaId,
   coaUnitkerjaId,
   coaKodeAkun,
   coaNamaAkun,
   coaLevelAkun,
   coaParentAkun,
   coaIsDebetPositif,
   coaIsKas,
   coaIsLabaRugiThJln,
   coaIsLocked,
   /*ctrNamaTipe,*/
   tpDebet AS debet,
   tpKredit AS kredit,
   tpSaldoAkhir
   [SUBACC_VIEW]
FROM tahun_pembukuan
LEFT JOIN coa ON coaId = tpCoaId
/*LEFT JOIN coa_tipe_coa ON coatipecoaCoaId = coaId
LEFT JOIN coa_tipe_ref ON ctrId = coatipecoaCtrId*/
WHERE tpId = %s
";

$sql['get_jumlah_transaksi_not_jurnal'] = "
SELECT count(tr.transId) AS jumlah
FROM  transaksi tr 
   join tahun_pembukuan_periode tpp on
      tpp.tppIsBukaBuku = 'Y'
      and (tpp.tppTanggalAwal) <= (tr.transTanggalEntri)
      and (tpp.tppTanggalAkhir) >= (tr.transTanggalEntri)
WHERE transIsJurnal = 'T'
";

$sql['get_jumlah_jurnal_not_posting'] = "
SELECT count(pr.prId) As jumlah
FROM  transaksi tr
   join pembukuan_referensi pr on
      pr.prTransId = tr.transId
   join tahun_pembukuan_periode tpp on
      tpp.tppIsBukaBuku = 'Y'
      and (tpp.tppTanggalAwal) <= (tr.transTanggalEntri)
      and (tpp.tppTanggalAkhir) >= (tr.transTanggalEntri)
WHERE prIsPosting = 'T' and prIsApproved = 'Y'
";

// jika sudah ditable buku besar = jurnal sudah di approve /sudah melalui proses posting
$sql['get_buku_besar_as_tahun_pembukuan'] = "
   SELECT
      coaId,
      coaUnitkerjaId,
      coaKodeAkun,
      coaLevelAkun,
      coaIsKas,
      coaIsLabaRugiThJln,
      coaIsLocked,
      coaIsDebetPositif,
      sum(if( bbh.bbPdId is null and bbh.bbPembukuanRefId is null ,bbh.bbSaldo,0)) as saldo_awal,
      sum(if( bbh.bbPdId is null and bbh.bbPembukuanRefId is null ,0,bbh.bbDebet)) as debet,
      sum(if( bbh.bbPdId is null and bbh.bbPembukuanRefId is null ,0,bbh.bbKredit)) as kredit,
      sum(if( bbh.bbPdId is null and bbh.bbPembukuanRefId is null ,0,bbh.bbSaldo)) as saldo,
      sum(bbSaldo) as saldo_akhir 
      [SUBACC_VIEW]
   FROM 
      buku_besar_his bbh 
      JOIN coa c ON(c.coaId= bbh.bbCoaId)
      LEFT JOIN (
	      select
	      	pr.prId as prId,
	      	tr.transTanggalEntri as tanggal,
	      	pr.prTanggal as tanggalPr
	      from
	      	transaksi tr
	      join pembukuan_referensi pr on
	      	pr.prTransId = tr.transId
	      join tahun_pembukuan_periode tpp on
	      	tpp.tppIsBukaBuku = 'Y'
	      	and (tpp.tppTanggalAwal) <= (tr.transTanggalEntri)
	      	and (tpp.tppTanggalAkhir) >= (tr.transTanggalEntri) 
      ) jurnal on jurnal.prId = bbh.bbPembukuanRefId 
   WHERE 
      c.coaId = '%s' 
      AND 
      bbh.bbTppId = '%s'
      AND
      ((jurnal.prId is null and bbh.bbPembukuanRefId is null)
      OR 
      (jurnal.prId is not null and bbh.bbPembukuanRefId is not null))
   GROUP BY bbh.bbCoaId [SUBACC_GROUP] 
   ORDER BY bbh.bbhisId ASC
";

$sql['get_all_coa_as_tahun_pembukuan'] = "
   SELECT
      coaId,
      coaUnitkerjaId,
      coaKodeAkun,
      coaNamaAkun,
      coaLevelAkun,
      coaParentAkun,
      coaIsDebetPositif,
      coaIsKas,
      coaIsLabaRugiThJln,
      coaIsLocked
   FROM
      coa
   WHERE coaId NOT IN(SELECT coaParentAkun FROM coa GROUP BY coaParentAkun)
   ORDER BY
      coaKodeAkun ASC
";

$sql['get_data_kode_akun'] = "
SELECT
`subaccPertamaKode` AS id,`subaccPertamaNama` AS nama, '1' AS akun
FROM finansi_keu_ref_subacc_1
UNION
SELECT
subaccKeduaKode,`subaccKeduaNama` AS nama, '2'
FROM finansi_keu_ref_subacc_2
UNION
SELECT
subaccKetigaKode,`subaccKetigaNama` AS nama, '3'
FROM finansi_keu_ref_subacc_3
UNION
SELECT
subaccKeempatKode, `subaccKeempatNama` AS nama,'4'
FROM finansi_keu_ref_subacc_4
UNION
SELECT
subaccKelimaKode,`subaccKelimaNama` AS nama, '5'
FROM finansi_keu_ref_subacc_5
UNION
SELECT
subaccKeenamKode, `subaccKeenamNama` AS nama,'6'
FROM finansi_keu_ref_subacc_6
UNION
SELECT
subaccKetujuhKode, `subaccKetujuhNama` AS nama,'7'
FROM finansi_keu_ref_subacc_7";

$sql['update_tahun_pembukuan_by_coa_id_as_tutup_buku'] = "
UPDATE tahun_pembukuan
SET
   tpSaldoAwal = '%s',
   tpDebet = '%s',
   tpKredit = '%s',
      tpSaldo = '%s',
    tpSaldoAkhir = '%s',
    tpTutupBukuUserId = '%s'
WHERE
   tpCoaId = '%s'
   [FILTER_SUBACC]
";

$sql['insert_tahun_pembukuan_history'] = "
INSERT INTO tahun_pembukuan_hist (
   tphCoaId,
   tphUnitkerjaId,
   tphDebet,
   tphKredit,
   tphTppId
   )
VALUES (
   '%s',
   '%s',
   '%s',
   '%s',
   '%s'
)
";

$sql['get_all_coa_buku_besar'] = "
   SELECT
      bbCoaId
   FROM
      buku_besar
   LEFT JOIN
      coa ON coaId=bbCoaId
   WHERE
      bbTppId='%s'
";

$sql['insert_tahun_pembukuan_history_as_tutup_buku'] = "
INSERT INTO tahun_pembukuan_hist
SET
   tphTppId='%s',
      tphCoaId='%s',
      tphUnitkerjaId='%s',
      tphSaldoAwal='%s',
      tphDebet='%s',
      tphKredit='%s',
      tphSaldo='%s',
      tphSaldoAkhir='%s',
      tphAnggaran='%s',
      tphBukaBukuUserId='%s',
      tphTutupBukuUserId='%s'
   [INSERT_SUBACC]
";

$sql['insert_tahun_pembukuan_as_buka_buku'] = "
INSERT INTO tahun_pembukuan
SET
   tpTppId='%s',
   tpCoaId='%s',
   tpUnitkerjaId='%s',
   tpBukaBukuUserId='%s'
   [INSERT_SUBACC]
";

$sql['update_tahun_pembukuan_as_buka_buku'] = "
   UPDATE tahun_pembukuan SET
      tpTppId = '%s',
      tpBukaBukuUserId = '%s'
   WHERE tpCoaId='%s'
";

$sql['is_coa_tahun_pembukuan_exist'] = "
   SELECT tpId,tpTppId,tpCoaId
   FROM tahun_pembukuan
   WHERE tpCoaId = '%s'
";

#tambahan untuk insert posisi saldo buku besar ke tahun_pembukuan
$sql['cek_coa_thn_pembukuan'] = "
SELECT tpId AS id
FROM tahun_pembukuan
WHERE
   tpCoaId = %s
   [FILTER_SUBACC]
";

$sql['insert_tahun_pembukuan_by_coa_id_as_tutup_buku'] = "
INSERT INTO tahun_pembukuan
SET
   tpTppId = (SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y'),
   tpCoaId = '%s',
   tpUnitkerjaId = '%s',
   tpSaldoAwal = '%s',
   tpDebet = '%s',
   tpKredit = '%s',
   tpSaldo = '%s',
   tpSaldoAkhir = '%s',
   tpTutupBukuUserId = '%s'
   [INSERT_SUBACC]
";

$sql['delete_tahun_pembukuan_by_id']="
DELETE FROM tahun_pembukuan
WHERE
tpId = %s AND
tpTppId IS NULL
";
?>
