<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_posisi_keuangan/business/AppLapPosisiKeuangan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class PrintRtfLapPosisiKeuangan extends HtmlResponse {	
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
		$Obj = new AppLapPosisiKeuangan;
   	
		 $tgl_akhir = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
		 $gridList = $Obj->GetLaporanAll($tgl_akhir);
		
		$contents = file_get_contents(GTFWConfiguration::GetValue( 'organization', 'docroot')."doc/template_LaporanPosisiKeuangan.rtf");
		
		$contents = str_replace('[COMPANY_NAME]',GTFWConfiguration::GetValue('organization', 'company_name'), $contents);
		$contents = str_replace('[HEADER]','\f1\fs25'.'LAPORAN POSISI KEUANGAN'.'\par', $contents);
		//---new-------------------------------------------------------------------------------------------------------'
		
				for($i=0;$i<count($gridList);$i++){
					if($gridList[$i]['kelJnsNama']=='Aktiva Lancar')
						{
						$aktivaLancar.= '\f1\fs16'.$gridList[$i]['nama_kel_lap'].'\par';
						if($gridList[$i]['nilai'] < 0)
							$nominalAktivaLancar.= ' '.'('.number_format(str_replace('-','',$gridList[$i]['nilai']), 2, ',', '.').')'.'\par';
						else
							$nominalAktivaLancar.= ' '.number_format($gridList[$i]['nilai'], 2, ',', '.').'\par';
							
						$totalAktivaLancar += $gridList[$i]['nilai'];
						}

					if($gridList[$i]['kelJnsNama']=='Aktiva Tidak Lancar')
						{
						$aktivaTidakLancar.= '\f1\fs16'.$gridList[$i]['nama_kel_lap'].'\par';
						if($gridList[$i]['nilai'] < 0)
							$nominalAktivaTidakLancar.= ' '.'('.number_format(str_replace('-','',$gridList[$i]['nilai']), 2, ',', '.').')'.'\par';
						else
							$nominalAktivaTidakLancar.= ' '.number_format($gridList[$i]['nilai'], 2, ',', '.').'\par';
						
						$totalAktivaTidakLancar += $gridList[$i]['nilai'];
						}
					
					if($gridList[$i]['kelJnsNama']=='Kewajiban Jangka Pendek')
						{
						$jangkaPendek.= '\f1\fs16'.$gridList[$i]['nama_kel_lap'].'\par';
						if($gridList[$i]['nilai'] < 0)
							$nominalJangkaPendek.= ' '.'('.number_format(str_replace('-','',$gridList[$i]['nilai']), 2, ',', '.').')'.'\par';
						else
							$nominalJangkaPendek.= ' '.number_format($gridList[$i]['nilai'], 2, ',', '.').'\par';
							
						$totalJangkaPendek += $gridList[$i]['nilai'];
						}
					
					if($gridList[$i]['kelJnsNama']=='Kewajiban Jangka Panjang')
						{
						$jangkaPanjang= '\f1\fs16'.$gridList[$i]['nama_kel_lap'].'\par';
						if($gridList[$i]['nilai'] < 0)
							$nominalJangkaPanjang.= ' '.'('.number_format(str_replace('-','',$gridList[$i]['nilai']), 2, ',', '.').')'.'\par';
						else
							$nominalJangkaPanjang.= ' '.number_format($gridList[$i]['nilai'], 2, ',', '.').'\par';
						
						$totalJangkaPanjang += $gridList[$i]['nilai'];
						}
					
					if($gridList[$i]['kelJnsNama']=='Aktiva Bersih')
						{
						$aktivaBersih.= '\f1\fs16'.$gridList[$i]['nama_kel_lap'].'\par';
						if($gridList[$i]['nilai'] < 0)
							$nominalAktivaBersih.= ' '.'('.number_format(str_replace('-','',$gridList[$i]['nilai']), 2, ',', '.').')'.'\par';
						else
							$nominalAktivaBersih.= ' '.number_format($gridList[$i]['nilai'], 2, ',', '.').'\par';
						
						$totalAktivaBersih += $gridList[$i]['nilai'];
						}
				}
		
		$contents = str_replace('[AKTIVA_LANCAR]',$aktivaLancar, $contents);
		$contents = str_replace('[NOMINAL_AKTIVA_LANCAR]',$nominalAktivaLancar, $contents);
		
		if($totalAktivaLancar < 0)
			$contents = str_replace('[TOTAL_AKTIVA_LANCAR]','('.number_format(str_replace('-','',$totalAktivaLancar), 2, ',', '.').')', $contents);
		else
			$contents = str_replace('[TOTAL_AKTIVA_LANCAR]',number_format($totalAktivaLancar, 2, ',', '.'), $contents);
		
		$contents = str_replace('[AKTIVA_TIDAK_LANCAR]',$aktivaTidakLancar, $contents);
		$contents = str_replace('[NOMINAL_TIDAK_LANCAR]',$nominalAktivaTidakLancar, $contents);
		
		if($totalAktivaTidakLancar < 0)
			$contents = str_replace('[TOTAL_TIDAK_LANCAR]','('.number_format(str_replace('-','',$totalAktivaTidakLancar), 2, ',', '.').')', $contents);
		else
			$contents = str_replace('[TOTAL_TIDAK_LANCAR]',number_format($totalAktivaTidakLancar, 2, ',', '.'), $contents);
		
		$totalAktiva=$totalAktivaLancar+$totalAktivaTidakLancar;
		if($totalAktiva < 0)
			$contents = str_replace('[TOTAL_AKTIVA]','('.number_format(str_replace('-','',$totalAktiva), 2, ',', '.').')', $contents);
		else
			$contents = str_replace('[TOTAL_AKTIVA]',number_format($totalAktiva, 2, ',', '.'), $contents);
		
		$contents = str_replace('[KEWAJIBAN_JANGKA_PENDEK]',$jangkaPendek, $contents);
		$contents = str_replace('[NOMINAL_JANGKA_PENDEK]',$nominalJangkaPendek, $contents);
		
		if($totalJangkaPendek < 0)
			$contents = str_replace('[TOTAL_JANGKA_PENDEK]','('.number_format(str_replace('-','',$totalJangkaPendek), 2, ',', '.').')', $contents);
		else
			$contents = str_replace('[TOTAL_JANGKA_PENDEK]',number_format($totalJangkaPendek, 2, ',', '.'), $contents);
		
		$contents = str_replace('[AKEWAJIBAN_JANGKA_PANJANG]',$jangkaPanjang, $contents);
		$contents = str_replace('[NOMINAL_JANGKA_PANJANG]',$nominalJangkaPanjang, $contents);
		
		if($totalJangkaPanjang < 0)
			$contents = str_replace('[TOTAL_JANGKA_PANJANG]','('.number_format(str_replace('-','',$totalJangkaPanjang), 2, ',', '.').')', $contents);
		else
			$contents = str_replace('[TOTAL_JANGKA_PANJANG]',number_format($totalJangkaPanjang, 2, ',', '.'), $contents);
		
		$contents = str_replace('[AKTIVA_BERSIH]',$aktivaBersih, $contents);
		$contents = str_replace('[NOMINAL_AKTIVA_BERSIH]',$nominalAktivaBersih, $contents);
		
		if($totalAktivaBersih < 0)
			$contents = str_replace('[TOTAL_AKTIVA_BERSIH]','('.number_format(str_replace('-','',$totalAktivaBersih), 2, ',', '.').')', $contents);
		else
			$contents = str_replace('[TOTAL_AKTIVA_BERSIH]',number_format($totalAktivaBersih, 2, ',', '.'), $contents);
		
		$totalWajibBersih=$totalJangkaPendek+$totalJangkaPanjang+$totalAktivaBersih;
		if($totalWajibBersih < 0)
			$contents = str_replace('[TOTAL_WAJIB_BERSIH]','('.number_format(str_replace('-','',$totalWajibBersih), 2, ',', '.').')', $contents);
		else
			$contents = str_replace('[TOTAL_WAJIB_BERSIH]',number_format($totalWajibBersih, 2, ',', '.'), $contents);
		//----end new------------------------------------------------------------------------------------------------------
		
		header("Content-type: application/msword");
		header("Content-disposition: inline; filename=laporanPosisiKeuangan_".date('d-m-Y').".rtf");
		header("Content-length: " . strlen($contents));
		print $contents;
      
	}
   
}
?>
