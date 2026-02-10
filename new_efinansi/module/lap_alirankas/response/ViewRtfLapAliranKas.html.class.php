<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_alirankas/business/AppLapAliranKas.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewRtfLapAliranKas extends HtmlResponse {
	function __construct(){
      $this->arrBulan = array("Januari",
                        "Februari",
                        "Maret",
                        "April",
                        "Mei",
                        "Juni",
                        "Juli",
                        "Agustus",
                        "September",
                        "Oktober",
                        "November",
                        "Desember");
	}

	function ProcessRequest() {

		$Obj = new AppLapAliranKas();
		$_GET = $_GET->AsArray();
		$tgl_awal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
		$tgl = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
      $tglKas = Dispatcher::Instance()->Decrypt($_GET['tgl_kas']);

		$gridList = $Obj->GetLaporanAll($tgl_awal,$tgl);
	   $saldoCoa = $Obj->GetSaldoCoaAliranKas();
	   $gridListKasSetaraKas = $Obj->GetLaporanKasSetaraKas($tglKas);

		$contents = file_get_contents(GTFWConfiguration::GetValue( 'application', 'docroot')."doc/template_LaporanAliranKas.rtf");

		$contents = str_replace('[COMPANY_NAME]',GTFWConfiguration::GetValue('organization', 'company_name'), $contents);
		$contents = str_replace('[CITY]',GTFWConfiguration::GetValue('organization', 'city'), $contents);

		$contents = str_replace('[NAMA_KEP_BAU]',GTFWConfiguration::GetValue('organization', 'kep_bau'), $contents);
		$contents = str_replace('[NAMA_PUKET1]',GTFWConfiguration::GetValue('organization', 'puket1'), $contents);
		$contents = str_replace('[NAMA_KETUA]',GTFWConfiguration::GetValue('organization', 'ketua'), $contents);

		$contents = str_replace('[KOTA]',GTFWConfiguration::GetValue('application', 'city'), $contents);
		$contents = str_replace('[HEADER]',' '.'LAPORAN ALIRAN KAS'.'\par', $contents);


		$contents = str_replace('[INTERVAL_WAKTU]',' '.IndonesianDate($_GET['tgl_akhir'], 'yyyy-mm-dd').'\par', $contents);

				for($i=0;$i<count($gridList);$i++){
					if($gridList[$i]['kelJnsNama']=='Operasional')
						{
						$operasional.= ' '.$gridList[$i]['nama_kel_lap'].'\par';
						if($gridList[$i]['nilai'] < 0)
							$nominalOperasional.= ' '.'('.number_format(str_replace('-','',$gridList[$i]['nilai']), 2, ',', '.').')'.'\par';
						else
							$nominalOperasional.= ' '.number_format($gridList[$i]['nilai'], 2, ',', '.').'\par';

							if($gridList[$i]['status']=='Ya'){$totalOperasional +=$gridList[$i]['nilai'];}
							else{$totalOperasional -=$gridList[$i]['nilai'];}
						}

					if($gridList[$i]['kelJnsNama']=='Investasi')
						{
						$investasi.= ' '.$gridList[$i]['nama_kel_lap'].'\par';
						if($gridList[$i]['nilai'] < 0)
							$nominalInvestasi.= ' '.'('.number_format(str_replace('-','',$gridList[$i]['nilai']), 2, ',', '.').')'.'\par';
						else
							$nominalInvestasi.= ' '.number_format($gridList[$i]['nilai'], 2, ',', '.').'\par';

							if($gridList[$i]['status']=='Ya'){$totalInvestasi +=$gridList[$i]['nilai'];}
							else{$totalInvestasi -=$gridList[$i]['nilai'];}
						}

					if($gridList[$i]['kelJnsNama']=='Pendanaan')
						{
						$pendanaan.= ' '.$gridList[$i]['nama_kel_lap'].'\par';
						if($gridList[$i]['nilai'] < 0)
							$nominalPendanaan.= ' '.'('.number_format(str_replace('-','',$gridList[$i]['nilai']), 2, ',', '.').')'.'\par';
						else
							$nominalPendanaan.= ' '.number_format($gridList[$i]['nilai'], 2, ',', '.').'\par';

							if($gridList[$i]['status']=='Ya'){$totalPendanaan +=$gridList[$i]['nilai'];}
							else{$totalPendanaan -=$gridList[$i]['nilai'];}
						}
				}

			for($i=0;$i<count($gridListKasSetaraKas);$i++){
            $ksk.= ' '.$gridListKasSetaraKas[$i]['nama_kel_lap'].'\par';
               if($gridListKasSetaraKas[$i]['nilai'] < 0)
                  $nominalKsk.= ' '.'('.number_format(str_replace('-','',$gridListKasSetaraKas[$i]['nilai']), 2, ',', '.').')'.'\par';
               else
                  $nominalKsk.= ' '.number_format($gridListKasSetaraKas[$i]['nilai'], 2, ',', '.').'\par';

               if($gridListKasSetaraKas[$i]['status']=='Ya'){$totalKsk +=$gridListKasSetaraKas[$i]['nilai'];}
               else{$totalKsk -=$gridListKasSetaraKas[$i]['nilai'];}
			}
		//---untuk aktiva-----------------------------------------------------------------------------
		$contents = str_replace('[OPERASI]',$operasional, $contents);
		$contents = str_replace('[NOMINAL_OPERASI]',$nominalOperasional, $contents);
		if($totalOperasional < 0)
			$contents = str_replace('[TOTAL_OPERASI]','('.number_format(str_replace('-','',$totalOperasional), 2, ',', '.').')', $contents);
		else
			$contents = str_replace('[TOTAL_OPERASI]',number_format($totalOperasional, 2, ',', '.'), $contents);

		$contents = str_replace('[INVESTASI]',$investasi, $contents);
		$contents = str_replace('[NOMINAL_INVESTASI',$nominalInvestasi, $contents);
		if($totalInvestasi < 0)
			$contents = str_replace('[TOTAL_INVESTASI]','('.number_format(str_replace('-','',$totalInvestasi), 2, ',', '.').')', $contents);
		else
			$contents = str_replace('[TOTAL_INVESTASI]',number_format($totalInvestasi, 2, ',', '.'), $contents);

		$contents = str_replace('[PENDANAAN]',$pendanaan, $contents);
		$contents = str_replace('[NOMINAL_PENDANAAN]',$nominalPendanaan, $contents);
		if($totalPendanaan < 0)
			$contents = str_replace('[TOTAL_PENDANAAN]','('.number_format(str_replace('-','',$totalPendanaan), 2, ',', '.').')', $contents);
		else
			$contents = str_replace('[TOTAL_PENDANAAN]',number_format($totalPendanaan, 2, ',', '.'), $contents);

		$contents = str_replace('[KSK]',$ksk, $contents);
		$contents = str_replace('[NOMINAL_KSK]',$nominalKsk, $contents);

		$totalKas=$totalOperasional+$totalInvestasi+$totalPendanaan;
		if($totalKas < 0)
			$contents = str_replace('[TOTAL_KAS]','('.number_format(str_replace('-','',$totalKas), 2, ',', '.').')', $contents);
		else
			$contents = str_replace('[TOTAL_KAS]',number_format($totalKas, 2, ',', '.'), $contents);

		if($saldoCoa < 0)
			$contents = str_replace('[TOTAL_KAS_FROM_COA]','('.number_format(str_replace('-','',$saldoCoa), 2, ',', '.').')', $contents);
		else
			$contents = str_replace('[TOTAL_KAS_FROM_COA]',number_format($saldoCoa, 2, ',', '.'), $contents);

		$totalSetara = $totalKas+$totalKsk;
		if($totalSetara < 0)
			$contents = str_replace('[KAS_AKHIR_TAHUN]','('.number_format(str_replace('-','',$totalSetara), 2, ',', '.').')', $contents);
		else
			$contents = str_replace('[KAS_AKHIR_TAHUN]',number_format($totalSetara, 2, ',', '.'), $contents);

		header("Content-type: application/msword");
		header("Content-disposition: inline; filename=laporanAliranKas_".date('d-m-Y').".rtf");
		header("Content-length: " . strlen($contents));
		print $contents;

	}
}
?>