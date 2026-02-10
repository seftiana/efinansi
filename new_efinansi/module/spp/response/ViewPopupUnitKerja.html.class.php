<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/spp/business/AppPopupUnitKerja.class.php';

class ViewPopupUnitKerja extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/spp/template');
		$this->SetTemplateFile('popup_unit_kerja.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$popupSatkerObj = new AppPopupUnitKerja();
		$POST = $_POST->AsArray();
		if(!empty($POST)) {
			$satker = $POST['satker'];
			$pimpinan= $POST['pimpinan'];
		} elseif(isset($_GET['cari'])) {
			$satker = Dispatcher::Instance()->Decrypt($_GET['satker']);
			$pimpinan = Dispatcher::Instance()->Decrypt($_GET['pimpinan']);
		} else {
			$satker="";
			$pimpinan="";
		}

		$totalData = $popupSatkerObj->GetCountDataSatker($satker, $pimpinan);

		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataSatker = $popupSatkerObj->getDataSatker($startRec, $itemViewed, $satker, $pimpinan);
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&satker=' . Dispatcher::Instance()->Encrypt($satker) . '&pimpinan=' . Dispatcher::Instance()->Encrypt($pimpinan) . '&cari=' . Dispatcher::Instance()->Encrypt(1));
		$dest = "popup-subcontent";
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage, $dest), Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataSatker'] = $dataSatker;
		$return['start'] = $startRec+1;

		$return['search']['satker'] = $satker;
		$return['search']['pimpinan'] = $pimpinan;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'SATKER', $search['satker']);
		$this->mrTemplate->AddVar('content', 'PIMPINAN', $search['pimpinan']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('rencana_pengeluaran', 'unitKerja', 'popup', 'html'));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataSatker'])) {
			$this->mrTemplate->AddVar('data_satker', 'SATKER_EMPTY', 'YES');
		} else {
			$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data_satker', 'SATKER_EMPTY', 'NO');
			$dataSatker = $data['dataSatker'];
			for($i=0;$i<sizeof($dataSatker);$i++) {
				$dataSatker[$i]['enc_satker_id'] = Dispatcher::Instance()->Encrypt($dataSatker[$i]['satker_id']);
				$dataSatker[$i]['enc_satker_nama'] = Dispatcher::Instance()->Encrypt($dataSatker[$i]['satker_nama']);
			}

			for ($i=0; $i<sizeof($dataSatker); $i++) {
				$no = $i+$data['start'];
				$dataSatker[$i]['number'] = $no;
				if ($no % 2 != 0) $dataSatker[$i]['class_name'] = 'table-common-even';
				else $dataSatker[$i]['class_name'] = '';

				$this->mrTemplate->AddVars('data_satker_item', $dataSatker[$i], 'SATKER_');
				$this->mrTemplate->parseTemplate('data_satker_item', 'a');	 
			}
		}
	}
}
?>
