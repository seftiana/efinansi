<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_bukubesar/business/AppPopupCoa.class.php';

class ViewPopupCoa extends HtmlResponse {

	private $Pesan;

	function TemplateModule()
   {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/'.Dispatcher::Instance()->mModule.'/template');
		$this->SetTemplateFile('view_popup_coa.html');
	}
	 function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
   
   function ProcessRequest() {
		$kodeObj = new AppPopupCoa();
		if($_POST || isset($_GET['cari'])) {
			if(isset($_POST['kode'])) {
				$kodeCoa = $_POST['kode'];
				$namaCoa = $_POST['nama'];
			} elseif(isset($_GET['kode'])) {
				$kodeCoa = Dispatcher::Instance()->Decrypt($_GET['kode']);
				$namaCoa = Dispatcher::Instance()->Decrypt($_GET['nama']);
			} else {
				$kodeCoa = '';
				$namaCoa = '';
			}
		}
	//view
		$totalData = $kodeObj->GetCountCoa($kodeCoa, $namaCoa);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataList = $kodeObj->GetDataCoa($startRec, $itemViewed, $kodeCoa, $namaCoa);
//		print_r($dataList);
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType 
				. '&kode=' . Dispatcher::Instance()->Encrypt($kodeCoa)
				. '&nama=' . Dispatcher::Instance()->Encrypt($namaCoa)
				. '&cari=' . Dispatcher::Instance()->Encrypt(1));

		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage, 'popup-subcontent'), Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataList'] = $dataList;
		$return['start'] = $startRec+1;

		$return['search']['kode'] = $kodeCoa;
		$return['search']['nama'] = $namaCoa;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
		$this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'popupCoa', 'view', 'html'));
		
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataList'])) {
			$this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
		} else {
			$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
			$dataList = $data['dataList'];

			for ($i=0; $i<sizeof($dataList); $i++) {
				$no = $i+$data['start'];
				$dataList[$i]['number'] = $no;
				//$dataList[$i]['nama']	= "asdasdsad'adasd";
				$dataList[$i]['link']	= str_replace("'","\'",$dataList[$i]['nama']);
				if ($no % 2 != 0) $dataList[$i]['class_name'] = 'table-common-even';
				else $dataList[$i]['class_name'] = '';
				
				if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);				
				if($i == sizeof($dataList)-1) $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
				$idEnc = Dispatcher::Instance()->Encrypt($dataList[$i]['id']);
				//print_r($dataList[$i]);
				$this->mrTemplate->AddVars('data_item', $dataList[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_item', 'a');	 
			}
		}
	}
}

?>
