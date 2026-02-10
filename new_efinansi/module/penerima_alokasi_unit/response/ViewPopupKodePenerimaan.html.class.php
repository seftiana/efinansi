<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/penerima_alokasi_unit/business/AppPopupKodePenerimaan.class.php';

class ViewPopupKodePenerimaan extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
			'module/penerima_alokasi_unit/template');
		$this->SetTemplateFile('view_popup_kodepenerimaan.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$Obj = new AppPopupKodePenerimaan();
		//$_POST = $_POST->AsArray();
		
       	if($_POST || isset($_GET['cari'])) {
			
		  if(isset($_POST['kode_penerimaan'])) {
		      	$kodePenerimaan = $_POST['kode_penerimaan'];
		  } elseif(isset($_GET['kode_penerimaan'])) {
		      	$kodePenerimaan = Dispatcher::Instance()->Decrypt($_GET['kode_penerimaan']);
		  } else {
		      	$kodePenerimaan='';
		  }

		  if(isset($_POST['unit_kerja'])) {
		      	$unitKerja = $_POST['unit_kerja'];
		  } elseif(isset($_GET['unit_kerja'])) {
		      	$unitKerja = Dispatcher::Instance()->Decrypt($_GET['unit_kerja']);
		  } else {
		      	$unitKerja ='';
		  }		  
       }
		
	//view
		$totalData = $Obj->GetCountData($kodePenerimaan,$unitKerja);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$data = $Obj->GetData($startRec, $itemViewed,$kodePenerimaan,$unitKerja);
		$url = Dispatcher::Instance()->GetUrl(
									Dispatcher::Instance()->mModule, 
									Dispatcher::Instance()->mSubModule, 
									Dispatcher::Instance()->mAction, 
									Dispatcher::Instance()->mType . 
									'&unit_kerja=' . Dispatcher::Instance()->Encrypt($unitKerja) . 
                                    '&kode_penerimaan=' . Dispatcher::Instance()->Encrypt($kodePenerimaan) .
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
				
		$return['data'] = $data;
		$return['start'] = $startRec+1;
		$return['kodePenerimaan'] = $kodePenerimaan;
		$return['unitKerja'] = $unitKerja;
        
		return $return;
	}
	
	function ParseTemplate($data = NULL) 
	{
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'KODE_PENERIMAAN', $data['kodePenerimaan']);
		$this->mrTemplate->AddVar('content', 'UNIT_KERJA', $data['unitKerja']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH',
								Dispatcher::Instance()->GetUrl(
													'penerima_alokasi_unit', 
													'popupKodePenerimaan', 
													'view', 
													'html') /*. 
													'&unitKerja=' . Dispatcher::Instance()->Encrypt($data['unitKerja']) . 
													'&kodePenerimaan=' . Dispatcher::Instance()->Encrypt($data['kodePenerimaan'])*/
                                                    );
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
				
				$this->mrTemplate->AddVars('data_kodepenerimaan_item', $dataKodePenerimaan[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_kodepenerimaan_item', 'a');	 
			}
		}
	}
}
?>