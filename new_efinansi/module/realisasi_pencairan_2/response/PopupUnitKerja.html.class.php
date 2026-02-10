<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
			'module/realisasi_pencairan_2/business/AppPopupUnitKerja.class.php';

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
			'module/user_unit_kerja/business/UserUnitKerja.class.php';

class PopupUnitKerja extends HtmlResponse {

	protected $Pesan;
	protected $popupUnitKerjaObj;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
				'module/realisasi_pencairan_2/template');
		$this->SetTemplateFile('popup_unit_kerja.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
	  		'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$this->popupUnitKerjaObj = new AppPopupUnitKerja();
		
		$userUnitKerjaObj 	= new UserUnitKerja();
		$userId 			= trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$unitkerjaUserId 	= $userUnitKerjaObj->GetUnitKerjaUser($userId);
		
		$POST = $_POST->AsArray();
		if(!empty($POST)) {
			$unitkerja = $POST['unitkerja'];
			$pimpinan= $POST['pimpinan'];
		} elseif(isset($_GET['cari'])) {
			$unitkerja = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
			$pimpinan = Dispatcher::Instance()->Decrypt($_GET['pimpinan']);
		} else {
			$unitkerja="";
			$pimpinan="";
		}
		
		if(isset($_GET['pop']) && $_GET['pop'] == 'home')
		   $return['showclear'] = 'NO';
		else
		   $return['showclear'] = 'YES';

		$totalData = $this->popupUnitKerjaObj->GetCountDataUnitKerjaPimpinan(
									$satker, 
									$pimpinan,
									$unitkerjaUserId['unit_kerja_id']);

		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataUnitKerja = $this->popupUnitKerjaObj->GetDataUnitKerjaPimpinan(
									$startRec, 
									$itemViewed, 
									$satker,
									$pimpinan,
									$unitkerjaUserId['unit_kerja_id']);
									
		$url = Dispatcher::Instance()->GetUrl(
									Dispatcher::Instance()->mModule, 
									Dispatcher::Instance()->mSubModule, 
									Dispatcher::Instance()->mAction, 
									Dispatcher::Instance()->mType . 
									'&unitkerja=' . 
									Dispatcher::Instance()->Encrypt($satker) . 
									'&pimpinan=' . Dispatcher::Instance()->Encrypt($pimpinan) . 
									'&cari=' . Dispatcher::Instance()->Encrypt(1));
									
		$dest = "popup-subcontent";
		Messenger::Instance()->SendToComponent(
											'paging', 
											'Paging', 
											'view', 
											'html', 
											'paging_top', 
											array(
													$itemViewed,
													$totalData, 
													$url, 
													$currPage, 
													$dest), 
											Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataUnitKerja'] = $dataUnitKerja;
		$return['start'] = $startRec+1;

		$return['search']['unitkerja'] = $unitkerja;
		$return['search']['pimpinan'] = $pimpinan;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'UNITKERJA', $search['unitkerja']);
		$this->mrTemplate->AddVar('content', 'PIMPINAN', $search['pimpinan']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
						Dispatcher::Instance()->GetUrl(
										'realisasi_pencairan_2', 
										'unitKerja', 
										'popup', 
										'html'));
										
		$this->mrTemplate->AddVar('show_clear', 'IS_SHOW', $data['showclear']);
		
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataUnitKerja'])) {
			$this->mrTemplate->AddVar('data_unitkerja', 'UNITKERJA_EMPTY', 'YES');
		} else {
			$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data_unitkerja', 'UNITKERJA_EMPTY', 'NO');
			$dataUnitKerja = $data['dataUnitKerja'];
			/*
			for($i=0;$i<sizeof($dataSatker);$i++) {
				$dataSatker[$i]['enc_satker_id'] = Dispatcher::Instance()->Encrypt($dataSatker[$i]['satker_id']);
				$dataSatker[$i]['enc_satker_nama'] = $dataSatker[$i]['satker_nama'];
			}*/
			
			for ($i=0; $i<sizeof($dataUnitKerja); $i++) {
				$no = $i+$data['start'];
				$dataUnitKerja[$i]['number'] = $no;
				//if ($no % 2 != 0) $dataUnitKerja[$i]['class_name'] = 'table-common-even';
				if($this->popupUnitKerjaObj->GetTotalSubUnitKerja($dataUnitKerja[$i]['unit_kerja_id']) > 0)
				{
					$dataUnitKerja[$i]['class_name'] = 'table-common-even1';
				} else {
					$dataUnitKerja[$i]['class_name'] = '';
				}
				$this->mrTemplate->AddVars('data_unitkerja_item', $dataUnitKerja[$i], 'UNITKERJA_');
				$this->mrTemplate->parseTemplate('data_unitkerja_item', 'a');	 
			}
		}
	}
}
?>
