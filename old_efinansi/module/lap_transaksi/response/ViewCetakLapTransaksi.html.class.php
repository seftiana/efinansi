<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_transaksi/business/AppLapTransaksi.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewCetakLapTransaksi extends HtmlResponse {
	#var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/lap_transaksi/template');
		$this->SetTemplateFile('view_cetak_lap_transaksi.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }
	
	function ProcessRequest() {
		$Obj = new AppLapTransaksi();
		$_GET = $_GET->AsArray();
		#print_r($_GET); exit;
		$key = Dispatcher::Instance()->Decrypt($_GET['key']);
		$tgl_awal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
		$tgl_akhir = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
		$tipeTransaksi = Dispatcher::Instance()->Decrypt($_GET['tipe_transaksi']);
		
		$data_cetak = $Obj->GetDataCetak($tgl_awal,$tgl_akhir,$key,$tipeTransaksi);
		#print_r($data_cetak); exit;
		$data['tgl_awal'] = $tgl_awal;
		$data['tgl_akhir'] = $tgl_akhir;
		$data['tipe_transaksi'] = $tipeTransaksi;
		$data['transaksi'] = $data_cetak;
		return $data;
	}
	
	function ParseTemplate($data = NULL) {
		if (empty($data)) {
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
			$this->mrTemplate->AddVar('content', 'TGL_AWAL', $this->date2string($data['tgl_awal']));
			$this->mrTemplate->AddVar('content', 'TGL_AKHIR', $this->date2string($data['tgl_akhir']));
			#print_r($data['transaksi']); exit;
			$totalTransaksi = 0;
			for($i=0; $i<count($data['transaksi']); $i++) {
				$data['transaksi'][$i]['nomor'] = $i+1;
				if (strtoupper($data['transaksi'][$i]['transaksi_is_jurnal']) == 'Y')
					#echo "asd"; exit;
					$data['transaksi'][$i]['class_td_name'] = 'style="background-color:#DCDCDC"';
				elseif (strtoupper($data['transaksi'][$i]['transaksi_is_jurnal']) == 'T')
					$data['transaksi'][$i]['class_td_name'] = '';
					
				/*if ($no % 2 != 0) 
					$data['data_transaksi'][$i]['class_name'] = 'table-common-even';
				else
					$data['data_transaksi'][$i]['class_name'] = '';*/
				$totalTransaksi += $data['transaksi'][$i]['transaksi_nilai'];
                $data['transaksi'][$i]['transaksi_nilai'] = number_format($data['transaksi'][$i]['transaksi_nilai'],2,',','.');
				$data['transaksi'][$i]['transaksi_tanggal'] = $this->date2string($data['transaksi'][$i]['transaksi_tanggal']);
				$this->mrTemplate->AddVars('data_lap_transaksi_item', $data['transaksi'][$i], '');
				$this->mrTemplate->parseTemplate('data_lap_transaksi_item', 'a');	
			}
			$this->mrTemplate->AddVar('data', 'TOTAL_TRANSAKSI_NILAI',  number_format($totalTransaksi,2,',','.'));
		}
	}
	
	function date2string($date) {
	   $bln = array(
	                1  => 'Januari',
					2  => 'Februari',
					3  => 'Maret',
					4  => 'April',
					5  => 'Mei',
					6  => 'Juni',
					7  => 'Juli',
					8  => 'Agustus',
					9  => 'September',
					10 => 'Oktober',
					11 => 'November',
					12 => 'Desember'					
	               );
	   $arrtgl = explode('-',$date);
	   return $arrtgl[2].' '.$bln[(int) $arrtgl[1]].' '.$arrtgl[0];
	}
	
}
?>