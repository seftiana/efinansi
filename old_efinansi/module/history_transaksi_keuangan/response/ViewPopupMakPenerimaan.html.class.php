<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/history_transaksi_keuangan/business/AppPopupMakPenerimaan.class.php';

class ViewPopupMakPenerimaan extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
		'module/history_transaksi_keuangan/template');
		$this->SetTemplateFile('view_popup_makpenerimaan.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$Obj = new AppPopupMakPenerimaan();
      $_GET = $_GET->AsArray();   
		$decUnitkerja = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
      #print_r($decUnitkerja); echo '<br />'; print_r($_GET);  
		if(isset($_POST['keyword'])) {
			$keyword = $_POST['keyword'];
		} elseif(isset($_GET['keyword'])) {
			$keyword = Dispatcher::Instance()->Decrypt($_GET['keyword']);
		} else {
			$keyword = '';
		}
		
	//view
		$totalData = $Obj->GetCountData($keyword, $decUnitkerja);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$data = $Obj->GetData($startRec, $itemViewed, $keyword, $decUnitkerja);
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&unitkerja=' . Dispatcher::Instance()->Encrypt($decUnitkerja) . '&keyword=' . Dispatcher::Instance()->Encrypt($keyword) . '&cari=' . Dispatcher::Instance()->Encrypt(1));
		$dest = "popup-subcontent";
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage), Messenger::CurrentRequest);

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
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
											Dispatcher::Instance()->GetUrl(
																		'history_transaksi_keuangan', 
																		'PopupMakPenerimaan', 
																		'view', 
																		'html'));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['data'])) {
			$this->mrTemplate->AddVar('data_kodepenerimaan', 'KODEPENERIMAAN_EMPTY', 'YES');
		} else {;
			$this->mrTemplate->AddVar('data_kodepenerimaan', 'KODEPENERIMAAN_EMPTY', 'NO');
			$dataKodePenerimaan = $data['data'];
			for ($i=0; $i<sizeof($dataKodePenerimaan); $i++) {
				$no = $i+$data['start'];
				$dataKodePenerimaan[$i]['number'] = $no;
            $dataKodePenerimaan[$i]['total'] = $dataKodePenerimaan[$i]['total_terima'];
            $dataKodePenerimaan[$i]['mata_anggaran'] = $dataKodePenerimaan[$i]['mak'];
            $dataKodePenerimaan[$i]['total_terima'] = number_format($dataKodePenerimaan[$i]['total_terima'], 2, ',', '.');
				if ($no % 2 == 1) {
					$dataKodePenerimaan[$i]['class_name'] = 'table-common-even';
				}
				$this->mrTemplate->AddVars('data_kodepenerimaan_item', $dataKodePenerimaan[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_kodepenerimaan_item', 'a');	 
			}
		}
	}
}

?>