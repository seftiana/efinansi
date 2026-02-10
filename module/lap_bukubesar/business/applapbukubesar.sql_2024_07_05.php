<?php
$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']  = "
SELECT SQL_CALC_FOUND_ROWS
   IFNULL(transid,0) AS id,
   coaKodeSistem,
   tppId,
   tppTanggalAwal,
   tppTanggalAkhir,
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   IFNULL(transTanggalEntri,bbTanggal) AS tanggalJurnalEntri,
   prTanggal AS tanggalJurnal,
   transDueDate AS tanggalDueDate,
   bbTanggal AS tanggalPosting,
   transReferensi AS nomorReferensi,
   transCatatan AS keterangan,
   coaId AS akunId,
   coaKodeAkun AS akunKode,
   coaNamaAkun AS akunNama,
   coaCoaKelompokId as kelompokId,
   CONCAT_WS('-', TRIM(BOTH FROM subaccPertamaKode),
   TRIM(BOTH FROM subaccKeduaKode),
   TRIM(BOTH FROM subaccKetigaKode),
   TRIM(BOTH FROM subaccKeempatKode),
   TRIM(BOTH FROM subaccKelimaKode),
   TRIM(BOTH FROM subaccKeenamKode),
   TRIM(BOTH FROM subaccKetujuhKode)) AS subAccount,   
   IFNULL(sa.saldo_awal,0) AS saldoAwal, 
   bbDebet AS debet,
   bbKredit AS kredit,
   bbSaldo AS saldo,
   bbSaldoAkhir AS saldoAkhir
FROM buku_besar_his
LEFT JOIN pembukuan_referensi
   ON prId = bbPembukuanRefId 
      #AND prIsJurnalBalik = 0 
      AND `prIsPosting` = 'Y'
LEFT JOIN pembukuan_detail
   ON pdId = bbPdId
   AND pdPrId = bbPembukuanRefId
LEFT JOIN transaksi 
   ON transid = prTransId and transReferensi NOT IN ('%s')
JOIN coa
     ON coaId = bbCoaId
LEFT JOIN tahun_pembukuan_periode
   ON (tppId =  bbTppId OR tppId = transTppId )
   AND pdCoaId = bbCoaId
   AND tppIsBukaBuku = 'Y'
LEFT JOIN tahun_anggaran
   ON thanggarId = transThanggarId
LEFT JOIN unit_kerja_ref
   ON unitkerjaId = transUnitkerjaId
   AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
LEFT JOIN (
   SELECT unitkerjaId AS id,
   CASE
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0')
      WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN unitkerjaKodeSistem
   END AS kode
   FROM unit_kerja_ref
) AS tmp_unit ON tmp_unit.id = unitkerjaId
LEFT JOIN finansi_keu_ref_subacc_1
   ON subaccPertamaKode = pdSubaccPertamaKode
LEFT JOIN finansi_keu_ref_subacc_2
   ON subaccKeduaKode = pdSubaccKeduaKode
LEFT JOIN finansi_keu_ref_subacc_3
   ON subaccKetigaKode = pdSubaccKetigaKode
LEFT JOIN finansi_keu_ref_subacc_4
   ON subaccKeempatKode = pdSubaccKeempatKode
LEFT JOIN finansi_keu_ref_subacc_5
   ON subaccKelimaKode = pdSubaccKelimaKode
LEFT JOIN finansi_keu_ref_subacc_6
   ON subaccKeenamKode = pdSubaccKeenamKode
LEFT JOIN finansi_keu_ref_subacc_7
   ON subaccKetujuhKode = pdSubaccKetujuhKode
LEFT JOIN ( 
   select 
     saldo.sCoaId as coa_id,
     saldo.saSubAccount as saSubAccount,
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
     FROM
         tahun_pembukuan tph
         join tahun_pembukuan_periode tpp on tpp.tppId = tph.tpTppId 
         join coa c on c.coaId = tph.tpCoaId     
       GROUP BY tpp.tppId,saSubAccount, tph.tpCoaId)   union (
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
       join tahun_pembukuan_periode tpp on tpp.tppId = tph.tphTppId 
       join coa c on c.coaId = tph.tphCoaId 	 
     group by tpp.tppId,saSubAccount,tph.tphCoaId
     )) saldo
   where 
     year(saldo.sTppTanggalAwal)  = year('%s')
     or
     year(saldo.sTppTanggalAkhir) = year('%s')
   group by saldo.saSubAccount, saldo.sCoaId
 ) sa ON sa.coa_id = coaId AND (
  CONCAT_WS('-',
    TRIM(BOTH FROM subaccPertamaKode),
    TRIM(BOTH FROM subaccKeduaKode),
    TRIM(BOTH FROM subaccKetigaKode),
    TRIM(BOTH FROM subaccKeempatKode),
    TRIM(BOTH FROM subaccKelimaKode),
    TRIM(BOTH FROM subaccKeenamKode),
    TRIM(BOTH FROM subaccKetujuhKode)
  ) = sa.saSubAccount
 )
WHERE 1 = 1
AND ((transTanggalEntri BETWEEN '%s' AND '%s')  OR( bbPembukuanRefId IS NULL AND bbPdId IS NULL))
AND (coaId = %s OR 1 = %s) 
HAVING ( subAccount LIKE '%s' OR 1=%s) 
ORDER BY  akunKode,tanggalJurnalEntri,nomorReferensi,prIsJurnalBalik ASC
";

$sql['get_limit'] = "
LIMIT %s,%s
";
/* 
ORDER BY SUBSTRING_INDEX(tmp_unit.kode, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp_unit.kode, '.', 5), '.', -1)+0,
prTanggal DESC, SUBSTR(SUBSTRING_INDEX(transReferensi, '/' , 1), 3)+0 DESC, pdStatus ASC, pdId
";
 */

// AND (SUBSTR(coaKodeSistem, 1, (SELECT LENGTH(CONCAT(coaKodeSistem, '.')) FROM coa WHERE coaId = %s)) = (SELECT CONCAT(coaKodeSistem, '.') FROM coa WHERE coaId = %s) OR 1 = %s)
#get min-max tahun

$sql['get_total_saldo'] = "
SELECT 
    IFNULL(transid,0) AS id,
    coaCoaKelompokId as kelompok_id,
    coaKodeAkun AS akun_kode,
    coaNamaAkun AS akun_nama,
    transTanggalEntri AS tanggal_transaksi,
    transReferensi AS nomor_referensi,
    IFNULL(sa.saldo_awal,0) AS saldo_awal, # saldo awal yang digunakan
    IF(bbPembukuanRefId IS NULL,0,bbDebet ) AS debet, # saldo paling awal set 0
    IF(bbPembukuanRefId IS NULL,0,bbKredit)  AS kredit,
    CONCAT_WS('-',
      TRIM(BOTH FROM pdSubaccPertamaKode),
      TRIM(BOTH FROM pdSubaccKeduaKode),
      TRIM(BOTH FROM pdSubaccKetigaKode),
      TRIM(BOTH FROM pdSubaccKeempatKode),
      TRIM(BOTH FROM pdSubaccKelimaKode),
      TRIM(BOTH FROM pdSubaccKeenamKode),
      TRIM(BOTH FROM pdSubaccKetujuhKode)
    ) as subAccount
FROM
  buku_besar_his 
  LEFT JOIN pembukuan_referensi 
    ON prId = bbPembukuanRefId 
      #AND prIsJurnalBalik = 0 
      AND `prIsPosting` = 'Y'
  LEFT JOIN pembukuan_detail 
    ON pdId = bbPdId 
    AND pdPrId = bbPembukuanRefId 
  LEFT JOIN transaksi 
    ON transid = prTransId and transReferensi NOT IN ('%s')
  LEFT JOIN coa 
    ON coaId = bbCoaId 
  LEFT JOIN tahun_pembukuan_periode 
    ON tppId = transTppId 
    AND pdCoaId = bbCoaId 
    AND tppIsBukaBuku = 'Y'
  left JOIN tahun_anggaran 
    ON thanggarId = transThanggarId
  left JOIN unit_kerja_ref 
    ON unitkerjaId = transUnitkerjaId 
    AND (SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s')
 
LEFT JOIN ( 
   select 
     saldo.sCoaId as coa_id,
     sum(if(substr(saldo.sCoaKode,1,1) = 1,
        if(saldo.coaIsDebetPositif =0,
           (-1*saldo.saldoAwal),saldo.saldoAwal),saldo.saldoAwal 
     )) as saldo_awal,
     saldo.saSubAccount as saSubAccount
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
     FROM
         tahun_pembukuan tph
         join tahun_pembukuan_periode tpp on tpp.tppId = tph.tpTppId 
         join coa c on c.coaId = tph.tpCoaId     
       GROUP BY tpp.tppId, saSubAccount, tph.tpCoaId)   union (
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
       join tahun_pembukuan_periode tpp on tpp.tppId = tph.tphTppId 
       join coa c on c.coaId = tph.tphCoaId 	 
     group by tpp.tppId,saSubAccount,tph.tphCoaId
     )) saldo
   where 
     year(saldo.sTppTanggalAwal)  = year('%s')
     or
     year(saldo.sTppTanggalAkhir) = year('%s')
   group by saldo.saSubAccount, saldo.sCoaId
 ) sa ON sa.coa_id = coaId AND (
  CONCAT_WS('-',
    TRIM(BOTH FROM pdSubaccPertamaKode),
    TRIM(BOTH FROM pdSubaccKeduaKode),
    TRIM(BOTH FROM pdSubaccKetigaKode),
    TRIM(BOTH FROM pdSubaccKeempatKode),
    TRIM(BOTH FROM pdSubaccKelimaKode),
    TRIM(BOTH FROM pdSubaccKeenamKode),
    TRIM(BOTH FROM pdSubaccKetujuhKode)
  ) = sa.saSubAccount
 )
WHERE 1 = 1
AND ((transTanggalEntri BETWEEN '%s' AND '%s')  OR( bbPembukuanRefId IS NULL AND bbPdId IS NULL))
AND (coaId = %s OR 1 = %s)
HAVING ( subAccount LIKE '%s' OR 1=%s) 
ORDER BY akun_kode,tanggal_transaksi,nomor_referensi,prIsJurnalBalik ASC

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

$sql['get_data_buku_besar'] = "
SELECT 
  a.bbhisId AS bbhis_id,
  a.bbCoaId AS coa_id,
  e.coaKodeAkun AS rekening,
  e.coaNamaAkun AS coa,
  a.bbTanggal AS bb_tanggal,
  SUM(a.bbSaldoAwal) AS saldo_awal,
  SUM(a.bbDebet) AS debet,
  SUM(a.bbKredit) AS kredit,
  SUM(a.bbSaldo) AS saldo,
  SUM(a.bbSaldoAkhir) AS saldo_akhir,
  b.pdKeterangan AS keterangan,
  d.transReferensi AS referensi,
  f.unitkerjaId 
FROM
  buku_besar_his a 
  JOIN pembukuan_referensi c 
    ON a.bbPembukuanRefId = c.prId 
  JOIN pembukuan_detail b 
    ON b.pdPrId = c.prId 
  JOIN transaksi d 
    ON d.transId = c.prTransId 
  JOIN coa e 
    ON a.bbCoaId = e.coaId 
  JOIN unit_kerja_ref f 
    ON e.coaUnitKerjaId = f.unitkerjaId 
  JOIN tahun_anggaran ta 
    ON ta.thanggarId = d.transThanggarId 
    AND ta.thanggarIsAktif = 'Y' 
  JOIN tahun_pembukuan_periode tpp 
    ON tpp.tppId = d.transTppId 
    AND b.pdCoaId = a.bbCoaId 
    AND tpp.tppIsBukaBuku = 'Y' 
WHERE 
   (
    SUBSTR(
      `unitkerjaKodeSistem`,
      1,
      (SELECT 
        LENGTH(
          CONCAT(`unitkerjaKodeSistem`, '.')
        ) 
      FROM
        unit_kerja_ref 
      WHERE `unitkerjaId` = '%s')
    ) = 
    (SELECT 
      CONCAT(`unitkerjaKodeSistem`, '.') 
    FROM
      unit_kerja_ref 
    WHERE `unitkerjaId` = '%s') 
    OR unitkerjaId = '%s'
  ) 
  AND
 (d.transTanggalEntri BETWEEN '%s' AND '%s' )
 AND
 (e.coaId = '%s'  OR 1 = %s) 
GROUP BY a.bbCoaId 
ORDER BY rekening ASC 
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



$sql['get_saldo_tahun_berjalan'] ="
select
	ifnull(tr.transReferensi,'') as trans_ref,
  pr.prTanggal as tanggal,
	bhis.`bbCoaId` as coa_id,
	c.`coaKodeAkun` as coa_kode_akun,
	c.`coaNamaAkun` as coa_nama_akun, 
	ifnull(sum(`bbSaldo` * -1),0) as saldo_akhir
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