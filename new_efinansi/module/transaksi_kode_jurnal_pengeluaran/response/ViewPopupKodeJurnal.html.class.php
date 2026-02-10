<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_kode_jurnal_pengeluaran/business/KodeJurnal.class.php';

class ViewPopupKodeJurnal extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/transaksi_kode_jurnal_pengeluaran/template');
		$this->SetTemplateFile('view_popup_kode_jurnal.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$kodeJurnalObj = new KodeJurnal();
		if($_POST || isset($_GET['cari'])) {
			if(isset($_POST['nama'])) {
				$nama = $_POST['nama'];
			} elseif(isset($_GET['nama'])) {
				$nama = Dispatcher::Instance()->Decrypt($_GET['nama']);
			} else {
				$nama = '';
			}
		}
		
	//view
		$totalData = $kodeJurnalObj->GetCountData($nama);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataKodeJurnal = $kodeJurnalObj->getData($startRec, $itemViewed, $nama);
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&nama=' . Dispatcher::Instance()->Encrypt($nama) . '&cari=' . Dispatcher::Instance()->Encrypt(1));
      
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage, 'popup-subcontent'), Messenger::CurrentRequest);
      
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataKodeJurnal'] = $dataKodeJurnal;
      $return['json']['detail_kode_jurnal'] = json_encode($kodeJurnalObj->getDetailKodeJurnal($dataKodeJurnal));
		$return['start'] = $startRec+1;

		$return['search']['nama'] = $nama;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
	   $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal_pengeluaran', 'popupKodeJurnal', 'view', 'html'));
		$this->mrTemplate->AddVars('content', $data['json'], 'JSON_');
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataKodeJurnal'])) {
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
		} else {
			$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
			$dataKodeJurnal = $data['dataKodeJurnal'];
		
//mulai bikin tombol delete
			$label = "Manajemen Kode Jurnal";
			$urlDelete = Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal_pengeluaran', 'deleteKodeJurnal', 'do', 'html');
			$urlReturn = Dispatcher::Instance()->GetUrl('transaksi_kode_jurnal_pengeluaran', 'kodeJurnal', 'view', 'html');
			Messenger::Instance()->Send('confirm', 'confirmDelete', 'do', 'html', array($label, $urlDelete, $urlReturn),Messenger::NextRequest);
			$this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html'));
//selesai bikin tombol delete

			for ($i=0; $i<sizeof($dataKodeJurnal); $i++) {
				$no = $i+$data['start'];
				$dataKodeJurnal[$i]['number'] = $no;
				if ($no % 2 == 0) $dataKodeJurnal[$i]['class_name'] = 'table-common-even';
				else $dataKodeJurnal[$i]['class_name'] = '';
				
				if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);				
				if($i == sizeof($dataKodeJurnal)-1) $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
				$idEnc = Dispatcher::Instance()->Encrypt($dataKodeJurnal[$i]['id']);
            
				$this->mrTemplate->AddVars('data_item', $dataKodeJurnal[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_item', 'a');	 
			}
		}
	}
}
?>
