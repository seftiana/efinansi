<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/tipeunit/business/AppTipeunit.class.php';

class ViewTipeunit extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/tipeunit/template');
		$this->SetTemplateFile('view_tipeunit.html');
	}
	
	function ProcessRequest() {
		$tipeunitObj = new AppTipeunit();
		if($_POST || isset($_GET['cari'])) {
			if(isset($_POST['tipeunit'])) {
				$tipeunit = $_POST['tipeunit'];
			} elseif(isset($_GET['tipeunit'])) {
				$tipeunit = Dispatcher::Instance()->Decrypt($_GET['tipeunit']);
			} else {
				$tipeunit = '';
			}
		}
		
	//view
		$totalData = $tipeunitObj->GetCountDataTipeunit($tipeunit);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataTipeunit = $tipeunitObj->getDataTipeunit($startRec, $itemViewed, $tipeunit);
//		print_r($dataTipeunit);
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&tipeunit=' . Dispatcher::Instance()->Encrypt($tipeunit) . '&cari=' . Dispatcher::Instance()->Encrypt(1));

		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage), Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataTipeunit'] = $dataTipeunit;
		$return['start'] = $startRec+1;

		$return['search']['tipeunit'] = $tipeunit;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'TIPEUNIT', $search['tipeunit']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('tipeunit', 'tipeunit', 'view', 'html'));
		$this->mrTemplate->AddVar('content', 'URL_ADD', Dispatcher::Instance()->GetUrl('tipeunit', 'inputTipeunit', 'view', 'html'));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataTipeunit'])) {
			$this->mrTemplate->AddVar('data_tipeunit', 'TIPEUNIT_EMPTY', 'YES');
		} else {
			$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data_tipeunit', 'TIPEUNIT_EMPTY', 'NO');
			$dataTipeunit = $data['dataTipeunit'];
		
//mulai bikin tombol delete
			$label = "Manajemen Tipe Unit";
			$urlDelete = Dispatcher::Instance()->GetUrl('tipeunit', 'deleteTipeunit', 'do', 'html');
			$urlReturn = Dispatcher::Instance()->GetUrl('tipeunit', 'tipeunit', 'view', 'html');
			Messenger::Instance()->Send('confirm', 'confirmDelete', 'do', 'html', array($label, $urlDelete, $urlReturn),Messenger::NextRequest);
			$this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html'));

			for ($i=0; $i<sizeof($dataTipeunit); $i++) {
				$no = $i+$data['start'];
				$dataTipeunit[$i]['number'] = $no;
				if ($no % 2 != 0) $dataTipeunit[$i]['class_name'] = 'table-common-even';
				else $dataTipeunit[$i]['class_name'] = '';
				
				if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);				
				if($i == sizeof($dataTipeunit)-1) $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
				$idEnc = Dispatcher::Instance()->Encrypt($dataTipeunit[$i]['tipeunit_id']);

				$dataTipeunit[$i]['url_edit'] = Dispatcher::Instance()->GetUrl('tipeunit', 'inputTipeunit', 'view', 'html') . '&dataId=' . $idEnc . '&page=' . $encPage . '&cari='.$cari;

				$this->mrTemplate->AddVars('data_tipeunit_item', $dataTipeunit[$i], 'TIPEUNIT_');
				$this->mrTemplate->parseTemplate('data_tipeunit_item', 'a');	 
			}
		}
	}
}
?>
