<?php
/**
 * @package SQL-FILE
 */
$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_tipe_unit']   = "
SELECT
   tipeunitId AS `id`,
   tipeunitNama AS `name`
FROM tipe_unit_kerja_ref
WHERE 1 = 1
ORDER BY tipeunitNama ASC
";

$sql['get_data_unit']   = "
SELECT
   SQL_CALC_FOUND_ROWS
   unitkerjaId AS id,
   unitkerjaKode AS kode,
   unitkerjaNama AS nama,
   unitkerjaNamaPimpinan AS namaPimpinan,
   unitkerjaKodeSistem AS kodeSistem,
   unitkerjaParentId AS parentId,
   tipeunitId AS tipeId,
   tipeunitNama AS tipeNama
FROM
   `unit_kerja_ref`
   JOIN (
      SELECT unitkerjaId AS id,
      CASE
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN CONCAT(unitkerjaKodeSistem, '.0')
         WHEN unitkerjaKodeSistem REGEXP '^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$' THEN unitkerjaKodeSistem
      END AS kode
      FROM unit_kerja_ref
   ) AS tmp ON tmp.id = unitkerjaId
   LEFT JOIN tipe_unit_kerja_ref
      ON tipeunitId = unitkerjaTipeunitId
WHERE 1 = 1
AND unitkerjaKode LIKE '%s'
AND unitkerjaNama LIKE '%s'
AND (tipeunitId = '%s' OR 1 = %s)
AND SUBSTR(`unitkerjaKodeSistem`,1,
   (SELECT LENGTH(CONCAT(`unitkerjaKodeSistem`,'.'))  FROM unit_kerja_ref WHERE `unitkerjaId` = '%s')) = (SELECT CONCAT(`unitkerjaKodeSistem`,'.')
FROM unit_kerja_ref WHERE `unitkerjaId` = '%s') OR unitkerjaId = '%s'
ORDER BY SUBSTRING_INDEX(tmp.kode, '.', 1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 2), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 3), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 4), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 5), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 6), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 7), '.', -1)+0,
SUBSTRING_INDEX(SUBSTRING_INDEX(tmp.kode, '.', 8), '.', -1)+0
LIMIT %s, %s
";

$sql['get_data_realisasi']    = "
SELECT
   SQL_CALC_FOUND_ROWS   
   ppr.pengrealId AS id,
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   kegrefNomor AS kode,
   kegrefNama AS nama,  
   ppr.pengrealNomorPengajuan AS no_pengajuan,
   SUM(ppr.pengrealNominalAprove) AS nominalApprove,
   IFNULL(lppa.nominal, 0) AS nominalSpj,
   if(IFNULL(pencairan_bank.jmlNominalBank, 0) > 0,
      IFNULL(pencairan_bank.jmlNominalBank, 0),
         IFNULL(pencairan.realisasiNominal, 0)) AS nominalRealisasi,
   IFNULL(lppa.pengrealId,0) as isTercatat
FROM
   kegiatan_detail
   JOIN kegiatan_ref
      ON kegrefId = kegdetKegrefId
   JOIN kegiatan
      ON kegId = kegdetKegId
   JOIN pengajuan_realisasi ppr
      ON ppr.pengrealKegdetId = kegdetId
   JOIN unit_kerja_ref
      ON unitkerjaid = kegUnitkerjaId
   JOIN tahun_anggaran
      ON thanggarId = kegThanggarId
   #LEFT JOIN(
   #   SELECT
   #      transdtspjKegdetId AS id,
   #      SUM(transNilai) AS nominal
   #   FROM transaksi_detail_spj
   #      JOIN transaksi
   #         ON transid = transdtspjTransId
   #   GROUP BY transdtspjKegdetId
   #) AS spj ON spj.id = kegdetId
  left join( 
         select
            kegdetId as kegiatanId,
            pengrealId as realisasiId,
            SUM(IFNULL(transNilai, 0)) as realisasiNominal
         from
            transaksi
         join transaksi_detail_pencairan on
            transdtpencairanTransId = transid
         join kegiatan_detail on
            kegdetId = transdtpencairanKegdetId
         join pengajuan_realisasi on
            pengrealKegdetId = kegdetId
            and pengrealid = transdtpencairanPengrealId
         group by
            kegdetId,
            pengrealId 
  ) pencairan on pencairan.realisasiId = ppr.pengrealId AND pencairan.kegiatanId = kegdetId
  left join(
  		select 
    		pr.pengrealId as pengrealId,
		    sum(pr_det.pengrealdetNominalApprove) as jmlNominalBank,
		    count(sppu_det.sppuDetId) as jmlFpaDiSppu,
		    tpb.transaksiBankId,
			tpb.transaksiBankNominal as totalNominalBank
		from
			finansi_pa_transaksi_bank tpb
			join finansi_pa_sppu sppu on sppu.sppuId = tpb.transaksiBankSppuId 
			join finansi_pa_sppu_det sppu_det on sppu_det.sppuDetSppuId = sppu.sppuId 
			join pengajuan_realisasi_detil pr_det on pr_det.pengrealdetId  = sppu_det.sppuDetPengrealDetId 
			join pengajuan_realisasi pr on pr.pengrealId = pr_det.pengrealdetPengRealId
		where 
		tpb.transaksiBankTipe ='pengeluaran'  
		group by pr.pengrealId
	) pencairan_bank on pencairan_bank.pengrealId = ppr.pengrealId
   left join (
      select
         lppa.lapLppaRealisasiId as pengrealId,
         IF(lppa.lapLppaIsApprove = 'Y',sum(lppa_det.lapLppaDetailNominal),0) as nominal 
      from 
         finansi_pa_lap_lppa lppa
         join finansi_pa_lap_lppa_detail lppa_det on lppa_det.lapLppaDetailLppaId = lppa.lapLppaId 
      where  
         lppa.lapLppaIsApprove not in('T')
         or 
         lppa.lapLppaIsApprove is null
      group by lppa.lapLppaRealisasiId 
   ) lppa on lppa.pengrealId = ppr.pengrealId 
WHERE 1 = 1
   AND thanggarId = '%s'
   AND UPPER(pengrealIsApprove) = 'YA'
   AND UPPER(kegdetIsAprove) = 'YA'
   AND (kegUnitkerjaId = %s OR 1 = 0)
   AND (kegrefNomor LIKE '%s' OR  ppr.pengrealNomorPengajuan LIKE '%s')
   AND kegrefNama LIKE '%s' 
GROUP BY ppr.pengrealId
HAVING nominalRealisasi >= 0
ORDER BY kegdetId,
ppr.pengrealId,
kegrefNomor
LIMIT %s, %s
";

$sql['get_detail_belanja_fpa'] = "
SELECT
  peng_real_det.`pengrealdetPengRealId` AS pid,
  peng_real_det.`pengrealdetId` AS pdet_id,
  rpeng.`rncnpengeluaranKomponenKode` AS komponen_kode,
  rpeng.`rncnpengeluaranKomponenNama` AS komponen_nama,
   peng_real_det.`pengrealdetDeskripsi` AS komponen_deskripsi,
  lppa_d.`lapLppaDetailDeskripsi` AS komponen_deskripsi_lppa,
  komp.`kompCoaId` AS komponen_coa_id,
  c.`coaKodeAkun` AS komponen_coa_kode,
  c.`coaNamaAkun` AS komponen_coa_nama,
  IFNULL(lppa_d.`lapLppaDetailNominal`,0) AS nominal_lppa,
 (peng_real_det.`pengrealdetNominalApprove` - (IF( lppa.lapLppaIsApprove = 'Y',IFNULL(lppa_d.`lapLppaDetailNominal`,0),0))) AS nominal_approval, 
 if(IFNULL(pencairan_bank.jmlNominalBank, 0) > 0,
      (IFNULL(pencairan_bank.jmlNominalBank, 0) - (IF( lppa.lapLppaIsApprove = 'Y',IFNULL(lppa_d.`lapLppaDetailNominal`,0),0 ))),
         (IFNULL(pencairan.nominal, 0) - ( IF( lppa.lapLppaIsApprove = 'Y',IFNULL(lppa_d.`lapLppaDetailNominal`,0),0 ) ))) AS nominal 
FROM 
  `pengajuan_realisasi_detil` peng_real_det
  join pengajuan_realisasi pr on pr.pengrealId = peng_real_det.pengrealdetPengRealId 
  join kegiatan_detail kd on kd.kegdetId = pr.pengrealKegdetId 
  join kegiatan k on k.kegId = kd.kegdetKegId 
  JOIN rencana_pengeluaran rpeng 
    ON rpeng.`rncnpengeluaranId` = peng_real_det.`pengrealdetRncnpengeluaranId`
    left join(
      select
         kegdetId as kegiatanId,
         pengrealId as realisasiId,
         tdpkb.transdtpencairanKompBelanjaPengrealDetId as realisasiDetId,
         sum(tdpkb.transdtpencairanKompBelanjaNominal) as nominal
      from
         transaksi tr
      join transaksi_detail_pencairan tdp on
         tdp.transdtpencairanTransId = tr.transid
      join transaksi_detail_pencairan_komponen_belanja tdpkb on
         tdpkb.transdtpencairanKompBelanjaTransDtPencairanId = tdp.transdtpencairanId
      join kegiatan_detail on
         kegdetId = tdp.transdtpencairanKegdetId
      join pengajuan_realisasi on
         pengrealKegdetId = kegdetId
         and pengrealid = tdp.transdtpencairanPengrealId
      group by kegiatanId,realisasiId,realisasiDetId) pencairan on
      pencairan.realisasiId = pr.pengrealId
      and pencairan.kegiatanId = kd.kegdetId
      and pencairan.realisasiDetId = peng_real_det.pengrealdetId
   left join(
      select
         pr_det.pengrealdetId as pengrealDetId,
         pr_det.pengrealdetPengRealId as pengrealId,
         sum(pr_det.pengrealdetNominalApprove) as jmlNominalBank
      from
         finansi_pa_transaksi_bank tpb
      join finansi_pa_sppu sppu on
         sppu.sppuId = tpb.transaksiBankSppuId
      join finansi_pa_sppu_det sppu_det on
         sppu_det.sppuDetSppuId = sppu.sppuId
      join pengajuan_realisasi_detil pr_det on
         pr_det.pengrealdetId = sppu_det.sppuDetPengrealDetId
      join pengajuan_realisasi pr on
         pr.pengrealId = pr_det.pengrealdetPengRealId
      where
         tpb.transaksiBankTipe = 'pengeluaran' 
      group by pengrealDetId, pr_det.pengrealdetPengRealId
         ) pencairan_bank on
      pencairan_bank.pengrealDetId = peng_real_det.pengrealdetId
      and pencairan_bank.pengrealId = peng_real_det.pengrealdetPengRealId
  LEFT JOIN komponen komp
    ON komp.`kompKode` = `rncnpengeluaranKomponenKode`
  LEFT JOIN coa c
        ON c.`coaId` =  komp.`kompCoaId`
  LEFT JOIN `finansi_pa_lap_lppa` lppa
    ON lppa.`lapLppaRealisasiId` = peng_real_det.`pengrealdetPengRealId` 
  LEFT JOIN `finansi_pa_lap_lppa_detail` lppa_d
    ON 
    lppa_d.`lapLppaDetailLppaId` = lppa.`lapLppaId` 
    AND 
    lppa_d.`lapLppaDetailRealDetailId` = peng_real_det.`pengrealdetId` 
";

$sql['get_detail_belanja_fpa_where_2'] ="
WHERE
   k.kegThanggarId = '%s'
   and 
   k.kegUnitkerjaId = '%s'
   AND UPPER(pr.pengrealIsApprove) = 'YA'
   AND UPPER(kd.kegdetIsAprove) = 'YA'
";

$sql['get_detail_belanja_fpa_where']="
WHERE 
   peng_real_det.`pengrealdetPengRealId` ='%s'
   AND (lppa.`lapLppaId` =  '%s' OR 1 = %s)
";
?>