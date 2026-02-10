<?php

/**
 * get data lppa yang sudah di approve
 */
$sql['get_lppa'] ="
SELECT
   SQL_CALC_FOUND_ROWS   
   ppr.pengrealId AS id,
   lppa.lppaId AS lppaId,
   lppa.lppaTanggal as tanggal,
   thanggarId AS taId,
   thanggarNama AS taNama,
   unitkerjaId AS unitId,
   unitkerjaKode AS unitKode,
   unitkerjaNama AS unitNama,
   kegrefNomor AS kode,
   kegrefNama AS nama,  
   ppr.pengrealNomorPengajuan AS no_pengajuan,
   SUM(ppr.pengrealNominalAprove) AS nominalApprove,
   IFNULL(lppa.nominal, 0) AS nominalSpjLppa,
   if(IFNULL(pencairan_bank.jmlNominalBank, 0) > 0,
      IFNULL(pencairan_bank.jmlNominalBank, 0),
         IFNULL(pencairan.realisasiNominal, 0)) AS nominalRealisasiPencairan,
   if(trpb.transaksiBankId = '%s',0,ifnull(trpb.transaksiBankLppaId,0))  as isTercatat
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
   join (
      select
         lppa.lapLppaId as lppaId,
         lppa.lapLppaRealisasiId as pengrealId,
         lppa.lapLppaTanggal as lppaTanggal,
         sum(lppa_det.lapLppaDetailNominal) as nominal 
      from 
         finansi_pa_lap_lppa lppa
         join finansi_pa_lap_lppa_detail lppa_det on lppa_det.lapLppaDetailLppaId = lppa.lapLppaId
      where 
         lppa.lapLppaIsApprove = 'Y'
         group by lppa.lapLppaId,lppa.lapLppaRealisasiId 
   ) lppa on lppa.pengrealId = ppr.pengrealId
   left join finansi_pa_transaksi_bank trpb on trpb.transaksiBankLppaId = lppa.lppaid
WHERE 1 = 1
   AND thanggarId = '%s'
   AND UPPER(pengrealIsApprove) = 'YA'
   AND UPPER(kegdetIsAprove) = 'YA'
   AND kegUnitkerjaId = %s
   AND (kegrefNomor LIKE '%s' OR ppr.pengrealNomorPengajuan  LIKE '%s')
   AND kegrefNama LIKE '%s'  
GROUP BY lppaId,ppr.pengrealId
HAVING nominalSpjLppa > 0
ORDER BY tanggal ,kegdetId,ppr.pengrealId,kegrefNomor
LIMIT %s, %s
";

$sql['get_count_data'] =" 
   SELECT FOUND_ROWS() AS total
";