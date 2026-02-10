<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/penerima_alokasi_unit/business/PopupKodePenerimaanCari.class.php';

class ViewPopupKodePenerimaanCari extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
			'module/penerima_alokasi_unit/template');
		$this->SetTemplateFile('view_popup_kode_penerimaan_cari.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$Obj = new PopupKodePenerimaanCari();
        
       	if($_POST || isset($_GET['cari'])) {
		  if(isset($_POST['key'])) {
		      	$keyword = $_POST['key'];
		  } elseif(isset($_GET['key'])) {
		      	$keyword = Dispatcher::Instance()->Decrypt($_GET['key']);
		  } else {
		      	$keyword = '';
		  }
       }
		
	//view
		$totalData = $Obj->GetCountData($keyword);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$data = $Obj->GetData($startRec, $itemViewed, $keyword);
		$url = Dispatcher::Instance()->GetUrl(
									Dispatcher::Instance()->mModule, 
									Dispatcher::Instance()->mSubModule, 
									Dispatcher::Instance()->mAction, 
									Dispatcher::Instance()->mType . 
                                    '&key=' . Dispatcher::Instance()->Encrypt($keyword) .
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
		$return['search']['keyword'] = $keyword;
		$return['decUnitkerja'] = $decUnitkerja;
        $return['decThAnggar'] = $decThAnggar;
        
		return $return;
	}
	
	function ParseTemplate($data = NULL) 
	{
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'KEYWORD', $search['keyword']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH',
								Dispatcher::Instance()->GetUrl(
													'penerima_alokasi_unit', 
													'PopupKodePenerimaanCari', 
													'view', 
													'html') 
                                                    );


		if (empty($data['data'])) {
			$this->mrTemplate->AddVar('data_kodepenerimaan', 'KODEPENERIMAAN_EMPTY', 'YES');
		} else {;
			$this->mrTemplate->AddVar('data_kodepenerimaan', 'KODEPENERIMAAN_EMPTY', 'NO');
			$dataKodePenerimaan = $data['data'];
			for ($i=0; $i<sizeof($dataKodePenerimaan); $i++) {
				$no = $i+$data['start'];
				$dataKodePenerimaan[$i]['number'] = $no;

				if ($dataKodePenerimaan[$i]['tipe'] == "header") {
					$dataKodePenerimaan[$i]['class_name'] = 'table-common-even';
				}
				else {
					$dataKodePenerimaan[$i]['class_name'] = '';
					$this->mrTemplate->AddVar('show_button_pilih', 'IS_SHOW', 'YES');
					$this->mrTemplate->AddVar('show_button_pilih', 'DATA_ID', $dataKodePenerimaan[$i]['id']);
					$this->mrTemplate->AddVar('show_button_pilih', 'DATA_KODE', $dataKodePenerimaan[$i]['kode']);
					$this->mrTemplate->AddVar('show_button_pilih', 'DATA_NAMA', $dataKodePenerimaan[$i]['nama']);
					$this->mrTemplate->AddVar('show_button_pilih', 'DATA_SATUAN', 
								$dataKodePenerimaan[$i]['satuan']);
					$this->mrTemplate->AddVar('show_button_pilih', 'DATA_ALOKASI_UNIT', 
								$dataKodePenerimaan[$i]['alokasi_unit']);
					$this->mrTemplate->AddVar('show_button_pilih', 'DATA_ALOKASI_PUSAT', 
								$dataKodePenerimaan[$i]['alokasi_pusat']);
				}

				$this->mrTemplate->AddVars('data_kodepenerimaan_item', $dataKodePenerimaan[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_kodepenerimaan_item', 'a');	 
			}
		}
	}
}
?>