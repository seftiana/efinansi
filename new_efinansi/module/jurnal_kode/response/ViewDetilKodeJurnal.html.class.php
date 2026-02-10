<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/jurnal_kode/business/DetilKodeJurnal.class.php';

class ViewDetilKodeJurnal extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir($this->mrConfig->mApplication['docroot'].'module/jurnal_kode/template');
		$this->SetTemplateFile('view_detil_kode_jurnal.html');
	}
	
	function ProcessRequest() {
		$detilKodeJurnalObj = new DetilKodeJurnal();
		
		$jurnal_id = Dispatcher::Instance()->Decrypt($_GET['dataId']);
		
	//view
		$totalData = $detilKodeJurnalObj->GetCountData($jurnal_id);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataDetilKodeJurnal = $detilKodeJurnalObj->getData($startRec, $itemViewed, $jurnal_id);
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&dataId=' . Dispatcher::Instance()->Encrypt($jurnal_id));
      
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage), Messenger::CurrentRequest);


		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataDetilKodeJurnal'] = $dataDetilKodeJurnal;
		$return['start'] = $startRec+1;

		$jurkodeData = $detilKodeJurnalObj->GetJurkodeById($jurnal_id);
		$return['jurkode']['jurnal_id'] = $jurnal_id;
		$return['jurkode']['data'] = $jurkodeData[0];
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$jurkode = $data['jurkode'];
		$this->mrTemplate->AddVar('content', 'IDKODEJURNAL', $jurkode['jurnal_id']);
		$this->mrTemplate->AddVar('content', 'KODEJURNAL', $jurkode['data']['kode']);
		$this->mrTemplate->AddVar('content', 'NAMA', $jurkode['data']['nama']);
      
		$this->mrTemplate->AddVar('content', 'URL_BACK', Dispatcher::Instance()->GetUrl('jurnal_kode', 'kodeJurnal', 'view', 'html'));
      
	   // $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('jurnal_kode', 'kodeJurnal', 'view', 'html'));
		$this->mrTemplate->AddVar('content', 'URL_ADD', Dispatcher::Instance()->GetUrl('jurnal_kode', 'inputKodeJurnal', 'view', 'html'));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}
		
		//mulai bikin tombol delete
		$label = "Manajemen Kode Jurnal";
		$urlDelete = Dispatcher::Instance()->GetUrl('jurnal_kode', 'deleteDetilKodeJurnal', 'do', 'html') . '&dataId=' . Dispatcher::Instance()->Encrypt($jurkode['jurnal_id']);
		$urlReturn = Dispatcher::Instance()->GetUrl('jurnal_kode', 'detilKodeJurnal', 'view', 'html') . '&dataId=' . Dispatcher::Instance()->Encrypt($jurkode['jurnal_id']);
		Messenger::Instance()->Send('confirm', 'confirmDelete', 'do', 'html', array($label, $urlDelete, $urlReturn),Messenger::NextRequest);
		$this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html'));
//selesai bikin tombol delete

		if (empty($data['dataDetilKodeJurnal'])) {
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
		} else {
			$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
			$dataDetilKodeJurnal = $data['dataDetilKodeJurnal'];

			for ($i=0; $i<sizeof($dataDetilKodeJurnal); $i++) {
				$no = $i+$data['start'];
				$dataDetilKodeJurnal[$i]['number'] = $no;
				if ($no % 2 != 0) $dataDetilKodeJurnal[$i]['class_name'] = 'table-common-even';
				else $dataDetilKodeJurnal[$i]['class_name'] = '';
				
				if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);				
				if($i == sizeof($dataDetilKodeJurnal)-1) $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
				
				if ($dataDetilKodeJurnal[$i]['isdebet'] == 1) {
					$dataDetilKodeJurnal[$i]['isdebet'] = "Debet";
				} else {
					$dataDetilKodeJurnal[$i]['isdebet'] = "Kredit";
				}
				
				$this->mrTemplate->AddVars('data_item', $dataDetilKodeJurnal[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_item', 'a');	 
			}
		}
	}
}
?>
