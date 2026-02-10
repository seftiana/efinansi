<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rencana_pengeluaran/response/ProcessRencanaPengeluaran.proc.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/approval_pencairan/business/AppApprovalPencairan.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot').'main/function/terbilang.php';

class PrintPencairan extends HtmlResponse {
   
	protected $RencanaPengeluaran;
	protected $data;
   
	function PrintPencairan() {
		$obj = new ProcessRencanaPengeluaran;
		$this->RencanaPengeluaran = $obj->RencanaPengeluaran;   
	 unset($obj);
	}
   
   
	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/approval_pencairan/template');
		$this->SetTemplateFile('print_pencairan.html');
	}
   
	function TemplateBase() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
		$this->SetTemplateFile('document-print.html');
		$this->SetTemplateFile('layout-common-print.html');
	}
   
	function ProcessRequest() {
		if(isset($_GET['grp'])) { 
			if(is_object($_GET['grp']))
				$this->data['realisasi_id'] = $_GET['grp']->mrVariable;
			else
				$this->data['realisasi_id'] = $_GET['grp'];        
		}
	
		$objPencairan = new AppApprovalPencairan;

		$kegdetId = $objPencairan->GetKegDetIdCetak($this->data['realisasi_id']);	
		$return['data'] = $objPencairan->GetDataCetak($kegdetId['kegdetId'],$this->data['realisasi_id']) ;
		//print_r($return['data']);
		//debug($return['data'] );
		$return['data_tambahan']=$objPencairan->GetDataCetakTambahan($this->data['realisasi_id'], $return['data'][0]['kegiatan_nama']);
		//$return['data_tambahan_rekening']=$objPencairan->GetDataCetakTambahanRekening($this->data['kegiatandetail_id']);
	  
		return $return;
	}
   
	function ParseTemplate($data = NULL) {    
        $this->mrTemplate->AddVar('content','TIMESTAMP',date('Y/m/d H:i:s', time()));
        $number     = new Number();
        $userName = Security::Instance()->mAuthentication->GetCurrentUser()->GetRealName();
        $this->mrTemplate->AddVar('content', 'USERNAME', $userName);
		$nomor_program =''; //inisialisasi nomor program
		$nomor_kegiatan =''; //inisialisasi nomor kegiatan
		$detail = $data['data'][0];
		$tanggal_sekarang = date("Y-m-d");
		$this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN',  $detail['ta_nama']);
		$this->mrTemplate->AddVar('content', 'KODE_MATA_ANGGARAN', $detail['kode_mata_anggaran']); 
		$this->mrTemplate->AddVar('content', 'TGL_NOW',  $tanggal_sekarang);
		
		$dataTambahan=$data['data_tambahan'][0];
		$this->mrTemplate->AddVar('content', 'JENIS_ANGGARAN', $dataTambahan['jenis_anggaran']);
		$this->mrTemplate->AddVar('content', 'NAMA_PIMPINAN', $dataTambahan['nama_pimpinan']);		
	 
	 
		$i=0;
		if (empty($data['data'])) {
			$this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
			$dataGrid = $data['data'];
			$dataTambahan = $data['data_tambahan_rekening'];
			//print_r($dataGrid);
			//data sudah siap ditampilkan... kirim ke pat-template
			$total_pengeluaran =0;
			for($j=0;$j < sizeof($dataGrid);$j++) {
			   $total_pengeluaran += (int) $dataGrid[$j]['jumlah'];
            $realisasi = (int) $dataGrid[$j]['realisasi_nominal_approve'];			   		   
			}
			
			if (($realisasi !== 0) && ($total_pengeluaran !== 0)){
            $rasio = $realisasi/$total_pengeluaran;
			}else{
            $rasio = 0;
         }
         for($j=0;$j < sizeof($dataGrid);$j++) {
			   $total_anggaran = $dataGrid[$j]['realisasi_nominal_approve'];
			   $dataGrid[$j]['nomor'] = $j + 1;
			   
            $dataGrid[$j]['jumlah'] = $dataGrid[$j]['jumlah'] * $rasio;   
			   $dataGrid[$j]['jumlah'] =number_format($dataGrid[$j]['jumlah'], 2, ',', '.');
			   $this->mrTemplate->AddVars('data_item', $dataGrid[$j], 'DATA_');
	           $this->mrTemplate->parseTemplate('data_item', 'a');			   
			}
			
	
		$totalTerbilang = $number->Terbilang($total_anggaran,2);
		$this->mrTemplate->AddVar('content', 'TERBILANG',$totalTerbilang);
			
		$total_anggaran =number_format($total_anggaran, 2, ',', '.');
		$this->mrTemplate->AddVar('content', 'TOTAL_ANGGARAN', $total_anggaran);
   }
   
  
   }
  
}
?>