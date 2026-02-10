<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_saldomaster/business/AppLapSaldomaster.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewRtfLapSaldomaster extends HtmlResponse {	
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
		
		$Obj = new AppLapSaldomaster();
		$_GET = $_GET->AsArray();
		$tgl_akhir = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
		$tgl_awal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
		
		$gridList = $Obj->GetSaldo($tgl_awal,$tgl_akhir);
		//print_r($gridList);
		$contents = file_get_contents(GTFWConfiguration::GetValue( 'application', 'docroot')."doc/template_LaporanSaldomaster.rtf");
		
		$contents = str_replace('[NAMA_COMPANY]',GTFWConfiguration::GetValue('organization', 'company_name'), $contents);
		$contents = str_replace('[HEADER]','\f1\fs25'.'LAPORAN SALDO MASTER'.'\par', $contents);
		
		$contents = str_replace('[INTERVAL_WAKTU]',' '.IndonesianDate($tgl_awal, 'yyyy-mm-dd').' s/d '.IndonesianDate($tgl_akhir, 'yyyy-mm-dd').'\par', $contents);
		
		$saldoAwal = $mutasiDebet = $mutasiKredit = $saldoAkhir =0;
      	$no=1;
      	$saldoAwal = $debet = $kredit = $saldoAkhir = 0;
      	for ($i=0;$i<sizeof($gridList);$i++) {
        		if ($i==0) $saldoAwal = $gridList[$i]['saldo_awal'];
        		$debet += $gridList[$i]['debet'];
        		$kredit += $gridList[$i]['kredit'];
        	
        		if ($gridList[$i]['coa_kode_akun']!=$gridList[$i+1]['coa_kode_akun']) {
          		$saldoAkhir = $gridList[$i]['saldo_akhir'];
          		$kode = explode(".", $gridList[$i]['coa_kode_akun']);
          		$kodeNext = explode(".", $gridList[$i+1]['coa_kode_akun']);
		
					$tes=sizeof($gridList[$i]['coa_kode_akun']);
					$jmlhTes +=$tes;
		
          		$coaKodeAkun .= ' ' .$gridList[$i]['coa_kode_akun'].'\par';
          		$coaNamaAkun .= '\f1\fs16' .$gridList[$i]['coa_nama_akun'].'\par';
					if($saldoAwal < 0)
						$sldoAwal .= ' '.'('.number_format(str_replace('-','',$saldoAwal),2,',','.').')'.'\par';
					else
						$sldoAwal .= ' '.number_format($saldoAwal,2,',','.').'\par';
					
					if($debet < 0)
						$dbet .= ' '.'('.number_format(str_replace('-','',$debet),2,',','.').')'.'\par';
          		else
						$dbet .= ' '.number_format($debet,2,',','.').'\par';
					
					if($kredit < 0)	
						$krdit .= ' '.'('.number_format(str_replace('-','',$kredit),2,',','.').')'.'\par';
          		else
						$krdit .= ' '. number_format($kredit,2,',','.').'\par';
					
					if($saldoAkhir < 0)
						$sldoAkhir .= ' '.'('.number_format(str_replace('-','',$saldoAkhir),2,',','.').')'.'\par';
          		else
						$sldoAkhir .= ' '. number_format($saldoAkhir,2,',','.').'\par';
					$no++;
       
				$subSAwal += $saldoAwal;
          		$subSAkhir += $saldoAkhir;
          		$mDebet += $debet;
          		$mKredit += $kredit;
          		
				if ($kode[0]!=$kodeNext[0]) {
            		$subSdAwal .= ' '. number_format($subSAwal,2,',','.').'\par';
            		$subSdAkhir .= ' '. number_format($subSAkhir,2,',','.').'\par';
            		$mDbet .= ' '. number_format($mDebet,2,',','.').'\par';
            		$mKrdit .= ' '. number_format($mKredit,2,',','.').'\par';            
            		$subSAwal = $subSAkhir = $mDebet = $mKredit = 0;
            		$no=1;
          		}
          		$saldoAwal = $debet = $kredit = $saldoAkhir = 0;
          		$saldoAwal = $gridList[$i+1]['saldo_awal'];        
        		}
      	}
		
		for($k=1;$k<=$jmlhTes;$k++){
		$nomer .= ' '.$k.'\par';}
		//-------------------------------------------------------------------------------
		$contents = str_replace('[NO]',$nomer, $contents);
		$contents = str_replace('[NO_REK]',$coaKodeAkun, $contents);
		$contents = str_replace('[SALDO_AWAL]',$sldoAwal, $contents);
		$contents = str_replace('[MUTASI_DEBET]',$dbet, $contents);
		$contents = str_replace('[MUTASI_KREDIT]',$krdit, $contents);
		$contents = str_replace('[SALDO_AKHIR]',$sldoAkhir, $contents);
		
		header("Content-type: application/msword");
		header("Content-disposition: inline; filename=laporanSaldomaster_".date('d-m-Y').".rtf");
		header("Content-length: " . strlen($contents));
		print $contents;
      
	} 
}
?>
