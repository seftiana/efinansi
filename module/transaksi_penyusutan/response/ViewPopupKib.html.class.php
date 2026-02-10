<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_penyusutan/business/AppTransaksiPenyusutanAsper.class.php';

class ViewPopupKib extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/transaksi_penyusutan/template');
		$this->SetTemplateFile('view_popup_kib.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$Obj = new AppTransaksiPenyusutanAsper();
      $_GET = $_GET->AsArray();    
		if(isset($_POST['keyword'])) {
			$keyword = $_POST['keyword'];
		} elseif(isset($_GET['keyword'])) {
			$keyword = Dispatcher::Instance()->Decrypt($_GET['keyword']);
		} else {
			$keyword = '';
		}
		
		$data = $Obj->GetDataPenyusutanKib($keyword);
      #print_r($data);
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];
				
		$return['data'] = $data;
		$return['start'] = $startRec+1;
		$return['search']['keyword'] = $keyword;
		$return['decUnitkerja'] = $decUnitkerja;

		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'KEYWORD', $search['keyword']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('transaksi_penyusutan', 'PopupKib', 'view', 'html'));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['data'])) {
			$this->mrTemplate->AddVar('data_kib', 'KIB_EMPTY', 'YES');
		} else {;
			$this->mrTemplate->AddVar('data_kib', 'KIB_EMPTY', 'NO');
			$kib = $data['data'];
         $no = 1; 
			for ($i=0; $i<sizeof($kib); $i++) {
				$kib[$i]['number'] = $no++;
            $kib[$i]['nilai_perolehan'] = number_format($kib[$i]['nilai_perolehan'], 2, ',', '.');
            $kib[$i]['nilai_penyusutan_rp'] = number_format($kib[$i]['nilai_penyusutan'], 2, ',', '.');
            if ($kib[$i]['nilai_penyusutan'] == 0){
               $this->mrTemplate->AddVar('penyusutan', 'KIB_ZERO', 'YES');
            }else{
               $this->mrTemplate->AddVar('penyusutan', 'KIB_ZERO', 'NO');
               $this->mrTemplate->AddVar('penyusutan', 'KIB_ID', $kib[$i]['kib_id']);
               $this->mrTemplate->AddVar('penyusutan', 'KIB_NAMA', $kib[$i]['kib_nama']);
               $this->mrTemplate->AddVar('penyusutan', 'NILAI_PENYUSUTAN_RP', $kib[$i]['nilai_penyusutan_rp']);
               $this->mrTemplate->AddVar('penyusutan', 'NILAI_PENYUSUTAN', $kib[$i]['nilai_penyusutan']);
               $this->mrTemplate->AddVar('penyusutan', 'KIB_KODE', $kib[$i]['kib_kode']);
            }
				if ($no % 2 == 1) {
					$kib[$i]['class_name'] = 'table-common-even';
				}
				$this->mrTemplate->AddVars('data_kib_item', $kib[$i], '');
				$this->mrTemplate->parseTemplate('data_kib_item', 'a');	 
			}
		}
	}
}
?>
