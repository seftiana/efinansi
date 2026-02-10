<?php
#get min-max tahun
$sql['get_minmax_tahun_transaksi'] = "
	SELECT
 		YEAR(MIN(transTanggalEntri)) - 5 AS minTahun,
 		YEAR(MAX(transTanggalEntri)) + 5 AS maxTahun
	FROM
 		transaksi
";
/**
 * new Query
 * untuk menampilkan saldo awal meskipun belum ada transaksi jurnal yang terposting
 * @since 3 Februari 2016
 */
$sql['get_saldo'] =" 
select 
	c.coaId as coa_id,
	c.coaKodeAkun as coa_kode_akun,
	c.coaNamaAkun as coa_nama_akun,
	c.coaIsLabaRugiThAwal as rl_awal,
	c.coaIsLabaRugiThJln as rl_berjalan,
	(IFNULL(sa.saldo_awal,0)) as saldo_awal,
	ifnull(bhis.debet,0) as debet,
	ifnull(bhis.kredit,0) as kredit,
	((IFNULL(sa.saldo_awal,0)) + ifnull( bhis.saldo_akhir,0)) as saldo_akhir
from
	coa c
	join buku_besar_his bhis on bhis.bbCoaId = c.coaId 
	left join
  ( 
  select 
    saldo.sCoaId as coa_id, 
    saldo.sCoaKode as coa_kode,
    saldo.sCoaNama as coa_nama,
    sum(if(substr(saldo.sCoaKode,1,1) = 1,
    	if(saldo.coaIsDebetPositif =0,
    		(-1*saldo.saldoAwal),saldo.saldoAwal),saldo.saldoAwal 
    )) as saldo_awal
  from 
  ((
    SELECT
        tpp.tppId as sTppId,
        tpp.tppTanggalAwal as sTppTanggalAwal,
        tpp.tppTanggalAkhir as sTppTanggalAkhir,
        c.coaId as sCoaId,
        c.coaKodeAkun as sCoaKode,
        c.coaNamaAkun as sCoaNama,
        c.coaIsDebetPositif as coaIsDebetPositif, 
        sum(tph.tpSaldoAkhir) as saldoAwal 
    FROM
        tahun_pembukuan tph
        join tahun_pembukuan_periode tpp on tpp.tppId = tph.tpTppId 
        join coa c on c.coaId = tph.tpCoaId     
      GROUP BY tpp.tppId ,tph.tpCoaId)   union (
  select
        tpp.tppId as sTppId,
        tpp.tppTanggalAwal as sTppTanggalAwal,
        tpp.tppTanggalAkhir as sTppTanggalAkhir,
        c.coaId as sCoaId,
        c.coaKodeAkun as sCoaKode,
        c.coaNamaAkun as sCoaNama,
        c.coaIsDebetPositif as coaIsDebetPositif, 
        sum(tph.tphSaldoAwal) as saldoAwal 
    from
      tahun_pembukuan_hist tph
      join tahun_pembukuan_periode tpp on tpp.tppId = tph.tphTppId 
      join coa c on c.coaId = tph.tphCoaId 	 
    group by tpp.tppId ,tph.tphCoaId
    )) saldo
  where 
    year(saldo.sTppTanggalAwal)  = year('%s')
    or
    year(saldo.sTppTanggalAkhir) = year('%s')
  group by saldo.sCoaId
) sa  on sa.coa_id= c.coaId 
left join (
	select 
		bhis.bbTppId as tp_id,
		pr.prTanggal as trans_tanggal,
		bhis.`bbTanggal` as tanggal,
		bhis.`bbCoaId` as coa_id,
		c.`coaKodeAkun` as coa_kode_akun,
		c.`coaNamaAkun` as coa_nama_akun,  
		SUM(if(`bbPembukuanRefId` is not null and `bbPdId` is not null, `bbDebet`, 0)) as debet,
		SUM(if(`bbPembukuanRefId` is not null and `bbPdId` is not null, `bbKredit`, 0)) as kredit,
		SUM(`bbSaldo`) as saldo_akhir
	from `buku_besar_his` bhis 
	join coa c on
			c.`coaId` = bhis.`bbCoaId`
		join pembukuan_referensi pr on
			pr.`prId` = bhis.`bbPembukuanRefId`
		join transaksi tr ON tr.`transId` = pr.`prTransId`
	where 
		`prIsPosting` = 'Y' 
		and tr.transTanggalEntri between '%s' and '%s'
group by
	`bbCoaId`
) bhis on bhis.coa_id = c.coaId  
order by
	c.coaKodeAkun asc       
";

/**
 * non active sejak 3 Februari 2016
 */
/*
$sql['get_saldo'] = "
	SELECT
  		bbTanggal AS bb_tanggal, 
		b.coaId AS coa_id, 
		b.coaKodeAkun AS coa_kode_akun, 
		b.coaNamaAkun AS coa_nama_akun, 
		b.coaIsDebetPositif AS coa_status_debet, 
		a.bbSaldoAwal AS saldo_awal, 
		a.bbSaldoAkhir AS saldo_akhir, 
		a.bbDebet AS debet, 
		a.bbKredit AS kredit
  	FROM
  		buku_besar_his a
  		JOIN coa b ON a.bbCoaId=b.coaId
                JOIN pembukuan_referensi c ON c.prId = a.bbPembukuanRefId   
  	WHERE
		a.bbTanggal BETWEEN '%s' AND '%s' 
		AND a.bbTppId = (SELECT tppId FROM tahun_pembukuan_periode WHERE tppIsBukaBuku = 'Y')
                AND c.prIsPosting = 'Y'
   ORDER BY
    	b.coaKodeAkun, a.bbHisId asc
";
*/


$sql['get_saldo_tahun_berjalan'] ="
select
	tr.transReferensi,
    pr.prTanggal as tanggal,
	bhis.`bbCoaId` as coa_id,
	c.`coaKodeAkun` as coa_kode_akun,
	c.`coaNamaAkun` as coa_nama_akun,
	sum(`bbSaldo` * -1) as saldo_akhir
from
	`buku_besar_his` bhis
join coa c on
	c.`coaId` = bhis.`bbCoaId`
join pembukuan_referensi pr on
	pr.`prId` = bhis.`bbPembukuanRefId`
join transaksi tr on
	tr.`transId` = pr.`prTransId`
where
	`prIsPosting` = 'Y'
	 and tr.transTanggalEntri = '%s'
	and tr.transReferensi = concat(
		replace('%s','-',''),
		'.',
		(select c.coaKodeAkun from coa c where c.coaIsLabaRugiThJln = 1 limit 1),
		'.',
		(select c.coaKodeAkun from coa c where c.coaIsLabaRugiThAwal = 1 limit 1)
	)
	and 
	c.coaKodeAkun  = (select c.coaKodeAkun from coa c where c.coaIsLabaRugiThJln = 1 limit 1) 
";