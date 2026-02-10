<?php
$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data_buku_besar_sementara']="
select
	sql_calc_found_rows 
	ifnull(tr.id,'') as id,
	ifnull(tr.tp_id,'') as tp_id,
  coa.coaId as coa_id,	
  coa.coaKodeAkun  as akun_kode,
  coa.coaNamaAkun as akun_nama,
  coa.coaCoaKelompokId as kelompok_id,
	ifnull(tr.tanggalJurnalEntri,'') as tanggalJurnalEntri,
	ifnull(tr.nomorReferensi,'') as nomorReferensi,
	ifnull(tr.keterangan,'') as keterangan,
	sa.saldo_awal as saldo_awal,
	ifnull(tr.debet,'') as debet,
	ifnull(tr.kredit,'') as kredit,
	ifnull(tr.is_jurnal_balik,'') as is_jurnal_balik
from
(
  select 
    coaId,
    coaKodeAkun ,
    coaNamaAkun,
    coaCoaKelompokId
  FROM(	
    (select
      c.coaId,
      c.coaKodeAkun ,
      c.coaNamaAkun,
      c.coaCoaKelompokId
    from 
      transaksi tr
      join pembukuan_referensi pr on
        pr.prTransId = tr.transId
      join pembukuan_detail pd on
        pd.pdPrId = pr.prId 
      join coa c on
        c.coaId = pd.pdCoaId 	 
      where
            tr.transTanggalEntri BETWEEN  '%s' AND '%s' 
            AND
        pr.prIsPosting = 'T' 	
    group by c.coaId  
    ) union (
      select
        c.coaId,
        c.coaKodeAkun ,
        c.coaNamaAkun,
        c.coaCoaKelompokId
      from  
        coa c
        join buku_besar_his bhis on bhis.bbCoaId = c.coaId  	
      group by c.coaId  
      )) coa
) coa
left join	(
		select
			saldo.sCoaId as coa_id,
      saSubAccount as sa_sub_akun,
			saldo.sCoaKode as coa_kode,
			saldo.sCoaNama as coa_nama,
			sum(if(substr(saldo.sCoaKode, 1, 1) = 1, if(saldo.coaIsDebetPositif = 0, (-1 * saldo.saldoAwal), saldo.saldoAwal), saldo.saldoAwal )) as saldo_awal
		from
			((
                select
                    tpp.tppId as sTppId,
                    tpp.tppTanggalAwal as sTppTanggalAwal,
                    tpp.tppTanggalAkhir as sTppTanggalAkhir,
                    c.coaId as sCoaId,
                    c.coaKodeAkun as sCoaKode,
                    c.coaNamaAkun as sCoaNama,
                    c.coaIsDebetPositif as coaIsDebetPositif,   
                    sum(ifnull(tph.tpSaldoAwal,0)) + (
                      IF( c.coaIsDebetPositif = 0 ,
                        SUM(IFNULL(tph.tpKredit,0)) - SUM(IFNULL(tph.tpDebet,0)),
                        SUM(IFNULL(tph.tpDebet,0)) - SUM(IFNULL(tph.tpKredit,0))
                      )
                    ) AS saldoAwal,
                    CONCAT_WS(
                      '-',
                      TRIM(BOTH FROM tpSubaccPertamaKode),
                      TRIM(BOTH FROM tpSubaccKeduaKode),
                      TRIM(BOTH FROM tpSubaccKetigaKode),
                      TRIM(BOTH FROM tpSubaccKeempatKode),
                      TRIM(BOTH FROM tpSubaccKelimaKode),
                      TRIM(BOTH FROM tpSubaccKeenamKode),
                      TRIM(BOTH FROM tpSubaccKetujuhKode)
                    ) AS saSubAccount
                from
                    tahun_pembukuan tph
                join tahun_pembukuan_periode tpp on
                    tpp.tppId = tph.tpTppId
                join coa c on
                    c.coaId = tph.tpCoaId
                group by
                    tpp.tppId ,
                    saSubAccount,
                    tph.tpCoaId
            )
            union 
            (
            select
                tpp.tppId as sTppId,
                tpp.tppTanggalAwal as sTppTanggalAwal,
                tpp.tppTanggalAkhir as sTppTanggalAkhir,
                c.coaId as sCoaId,
                c.coaKodeAkun as sCoaKode,
                c.coaNamaAkun as sCoaNama,
                c.coaIsDebetPositif as coaIsDebetPositif,
                sum(tph.tphSaldoAwal) as saldoAwal,
                CONCAT_WS(
                  '-',
                  TRIM(BOTH FROM tphSubaccPertamaKode),
                  TRIM(BOTH FROM tphSubaccKeduaKode),
                  TRIM(BOTH FROM tphSubaccKetigaKode),
                  TRIM(BOTH FROM tphSubaccKeempatKode),
                  TRIM(BOTH FROM tphSubaccKelimaKode),
                  TRIM(BOTH FROM tphSubaccKeenamKode),
                  TRIM(BOTH FROM tphSubaccKetujuhKode)
                ) AS saSubAccount
            from
                tahun_pembukuan_hist tph
            join tahun_pembukuan_periode tpp on
                tpp.tppId = tph.tphTppId
            join coa c on
                c.coaId = tph.tphCoaId
            group by
                tpp.tppId ,
                saSubAccount,
                tph.tphCoaId 
            )) saldo

        where (
            year(saldo.sTppTanggalAwal)  = year('%s')
            or
            year(saldo.sTppTanggalAkhir) = year('%s')
          ) 
          and (saSubAccount LIKE '%s')
         group by saldo.sCoaId 
	) sa ON sa.coa_id = coa.coaId
	left join (
		select 
			transid as id,
			transTppId as tp_id,
			pdCoaId as coa_id,
			coaKodeAkun as akun_kode,
			coaNamaAkun as akun_nama,
			transTanggalEntri as tanggalJurnalEntri,
			transReferensi as nomorReferensi,
			transCatatan as keterangan,
			 
			if(UPPER(pdStatus) = 'D',
			pdNilai,
			0) as debet,
			if(UPPER(pdStatus) = 'K',
			pdNilai,
			0) as kredit,
			prIsJurnalBalik as is_jurnal_balik,
      CONCAT_WS(
	      '-',
	      TRIM(BOTH FROM pdSubaccPertamaKode),
	      TRIM(BOTH FROM pdSubaccKeduaKode),
	      TRIM(BOTH FROM pdSubaccKetigaKode),
	      TRIM(BOTH FROM pdSubaccKeempatKode),
	      TRIM(BOTH FROM pdSubaccKelimaKode),
	      TRIM(BOTH FROM pdSubaccKeenamKode),
	      TRIM(BOTH FROM pdSubaccKetujuhKode)
	   ) AS subAccount
		from
		transaksi
		join pembukuan_referensi on
			pembukuan_referensi.prTransId = transaksi.transId
		join pembukuan_detail on
			pembukuan_detail.pdPrId = pembukuan_referensi.prId 
		left join coa on
			coa.coaId = pembukuan_detail.pdCoaId 	 
		where
            transTanggalEntri BETWEEN '%s' AND '%s'
    HAVING (subAccount LIKE '%s')
	) as tr on tr.coa_id = coa.coaId
where
	1 = 1
	and (coa.coaId = %s	or 1 = %s)
order by
	akun_kode,
	tanggalJurnalEntri,
	nomorReferensi,
	is_jurnal_balik asc 
";

$sql['get_data_buku_besar_sementara_old']="
SELECT SQL_CALC_FOUND_ROWS 
        transid AS id,
        transTppId AS tp_id,
        pdCoaId AS coa_id,
        coaKodeAkun AS akun_kode,
        coaNamaAkun AS akun_nama,
        transTanggalEntri AS tanggalJurnalEntri,
        transReferensi AS nomorReferensi,
        transCatatan AS keterangan,
        sa.saldo_awal AS saldoAwal,
        IF(UPPER(pdStatus) = 'D', pdNilai, 0) AS debet,
        IF(UPPER(pdStatus) = 'K', pdNilai, 0) AS kredit,
        prIsJurnalBalik as is_jurnal_balik
    FROM     
        ( 
        select 
          saldo.sCoaId as coa_id,
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
              sum(ifnull(tph.tpSaldoAwal,0)) + (
                IF( c.coaIsDebetPositif = 0 ,
                  SUM(IFNULL(tph.tpKredit,0)) - SUM(IFNULL(tph.tpDebet,0)),
                  SUM(IFNULL(tph.tpDebet,0)) - SUM(IFNULL(tph.tpKredit,0))
                )
              ) AS saldoAwal
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
      ) sa 
      JOIN coa ON coaId = sa.coa_id 
      LEFT JOIN pembukuan_detail 
        ON pembukuan_detail.pdCoaId = coa.coaId
      LEFT JOIN pembukuan_referensi
        ON pembukuan_referensi.`prId` = pembukuan_detail.`pdPrId`
      LEFT JOIN transaksi 
        ON  transaksi.`transId` = pembukuan_referensi.`prTransId` 
        AND (transTanggalEntri BETWEEN '%s' AND '%s')
      LEFT JOIN tahun_pembukuan_periode 
        ON (tppId = transTppId )   
    WHERE 1 = 1
    AND (coaId = %s OR 1 = %s) 
    ORDER BY  akun_kode,tanggalJurnalEntri,nomorReferensi,prIsJurnalBalik ASC  
";

$sql['get_limit'] = "
LIMIT %s,%s
";

$sql['get_minmax_tahun_transaksi'] = "
SELECT
   YEAR(MIN(transTanggalEntri)) - 5 AS minTahun,
   YEAR(MAX(transTanggalEntri)) + 5 AS maxTahun
FROM
   transaksi
";

$sql['get_rekening_coa'] = "
SELECT
   coaId AS id,
   concat(coaKodeAkun,' [', coaNamaAkun,']') AS name
FROM
   coa
";


$sql['get_info_coa'] = "
SELECT
   coaId AS coa_id,
   coaKodeAkun AS no_rekening,
   coaNamaAkun AS rekening
FROM
   coa
WHERE
   coaId = '%s'
";

$sql['get_sub_account_combobox'] = "
SELECT
   CONCAT_WS('-', TRIM(BOTH FROM subaccPertamaKode),
   TRIM(BOTH FROM subaccKeduaKode),
   TRIM(BOTH FROM subaccKetigaKode),
   TRIM(BOTH FROM subaccKeempatKode),
   TRIM(BOTH FROM subaccKelimaKode),
   TRIM(BOTH FROM subaccKeenamKode),
   TRIM(BOTH FROM subaccKetujuhKode)) AS id,
   CONCAT(TRIM(BOTH FROM subaccPertamaKode), ' - ',subaccPertamaNama) AS name
FROM finansi_keu_ref_subacc_1
LEFT JOIN finansi_keu_ref_subacc_2
   ON subaccKeduaKode = 00
LEFT JOIN finansi_keu_ref_subacc_3
   ON subaccKetigaKode = 00
LEFT JOIN finansi_keu_ref_subacc_4
  ON subaccKeempatKode = 00
LEFT JOIN finansi_keu_ref_subacc_5
   ON subaccKelimaKode =  00
LEFT JOIN finansi_keu_ref_subacc_6
   ON subaccKeenamKode = 00
LEFT JOIN finansi_keu_ref_subacc_7
   ON subaccKetujuhKode = 00
";

?>
