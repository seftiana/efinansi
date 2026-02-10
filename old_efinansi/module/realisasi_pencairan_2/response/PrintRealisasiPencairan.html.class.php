<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/realisasi_pencairan_2/business/RealisasiPencairan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot').'main/function/terbilang.php';
require_once GTFWConfiguration::GetValue('application', 'docroot').'main/function/date.php';

class PrintRealisasiPencairan extends HtmlResponse
{
   function TemplateBase()
	{
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
	}
   
	function TemplateModule ()
	{
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/'.Dispatcher::Instance()->mModule.'/template');
      $this->SetTemplateFile('print_realisasi_pencairan.html');
	}
   
	function ProcessRequest ()
	{
		$objReal = new RealisasiPencairan();
		
		$id = Dispatcher::Instance()->Decrypt($_REQUEST['id']);
		
		$return['dataCetak']=$objReal->GetDataCetak($id);
		$return['dataSPMU']=$objReal->GetTransaksiPencairan($id);
      return $return;
	}
   
	function ParseTemplate ($data = NULL)
	{
	
   $a =  new Number();
	$cetak=$data['dataCetak'];
	//$cetak[0]['jumlah_anggaran']='543000';
	$cetak[0]['terbilang'] = $a->terbilang($cetak[0]['jumlah_anggaran'],2);
	$cetak[0]['jumlah_anggaran'] = number_format($cetak[0]['jumlah_anggaran'], 2, ',', '.');
   
	$cetak[0]['terbilang2'] = $a->terbilang($cetak[0]['jumlah_anggaran_diminta'],2);
	$cetak[0]['jumlah_anggaran_diminta'] = number_format($cetak[0]['jumlah_anggaran_diminta'], 2, ',', '.');
	$cetak[0]['jumlah_anggaran_disetujui'] = number_format($cetak[0]['jumlah_anggaran_disetujui'], 2, ',', '.');
	
	if ($cetak[0]['nominal']=='0,00'){
	$cetak[0]['terbilang']='nol';}
   
	   $tanggal_sekarang = date("Y-m-d");
	   $this->mrTemplate->AddVar('content', 'ANGGARAN_BULAN',  $cetak[0]['anggaran_bulan']);
	   $this->mrTemplate->AddVar('content', 'NAMA_KEGIATAN',  $cetak[0]['nama_kegiatan']);
	   $this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN',  $cetak[0]['tahun_anggaran']);
	   $this->mrTemplate->AddVar('content', 'KODE_ANGGARAN',  $cetak[0]['kode_anggaran']);
	   $this->mrTemplate->AddVar('content', 'SK_KEGIATAN',  $cetak[0]['sk_kegiatan']);
	   $this->mrTemplate->AddVar('content', 'JUMLAH_ANGGARAN',  $cetak[0]['jumlah_anggaran']);
		$this->mrTemplate->AddVar('content', 'JUMLAH_ANGGARAN_DIMINTA',  $cetak[0]['jumlah_anggaran_diminta']);
		$this->mrTemplate->AddVar('content', 'JUMLAH_ANGGARAN_DISETUJUI',  $cetak[0]['jumlah_anggaran_disetujui']);
	   $this->mrTemplate->AddVar('content', 'JURUSAN',  $cetak[0]['jurusan']);
	   $this->mrTemplate->AddVar('content', 'TGL_NOW', $tanggal_sekarang);
	   $this->mrTemplate->AddVar('content', 'TERBILANG',  $cetak[0]['terbilang']);
		$this->mrTemplate->AddVar('content', 'TERBILANG2',  $cetak[0]['terbilang2']);
	   $this->mrTemplate->AddVar('content', 'TGL_TTD',$tanggal_sekarang);
	   $this->mrTemplate->AddVar('content', ' NAMA_PEJABAT', $cetak[0]['nama_pejabat']);
        $this->mrTemplate->AddVar('content','TIMESTAMP',date('Y/m/d H:i:s', time()));
        $userName = Security::Instance()->mAuthentication->GetCurrentUser()->GetRealName();
        $this->mrTemplate->AddVar('content', 'USERNAME', $userName);

	    if (empty($data['dataSPMU'])) {
	         $this->mrTemplate->AddVar('data_cetak', 'CETAK_EMPTY', 'YES');
			} else {
			 $this->mrTemplate->AddVar('data_cetak', 'CETAK_EMPTY', 'NO');
				$dataList=$data['dataSPMU'];
				for ($i=0; $i<sizeof($dataList); $i++) {
					if ($no % 2 != 0) $dataList[$i]['class_name'] = 'table-common-even';
					else $dataList[$i]['class_name'] = '';
					$totalSpmu +=$dataList[$i]['nominal_trans'];
               $dataList[$i]['nominal_trans'] = number_format($dataList[$i]['nominal_trans'], 0, ',', '.');
					$dataList[$i]['tanggal_trans'] = ConvertDate($dataList[$i]['tanggal_trans'],"YYYY-MM-DD","DD/MM/YYYY");
					$this->mrTemplate->AddVars('data_item', $dataList[$i], 'DATA_');
					$this->mrTemplate->parseTemplate('data_item', 'a');	 
				}

	    $this->mrTemplate->AddVar('data_cetak', 'TOTAL_SPMU',number_format($totalSpmu, 0, ',', '.'));
	    $this->mrTemplate->AddVar('content', 'UANG_DITERIMA', number_format($totalSpmu, 0, ',', '.'));
		}
	}		
}
?>
