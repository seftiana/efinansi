<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/history_transaksi/business/transaksi/AppPopupUnitkerja.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
		'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewPopupUnitKerja extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
					'module/history_transaksi/template');
		$this->SetTemplateFile('transaksi/view_popup_unitkerja.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$unitkerjaObj = new AppPopupUnitkerja();
		//$satker = Dispatcher::Instance()->Decrypt($_GET['satker']);
		//$satker_label = Dispatcher::Instance()->Decrypt($_GET['satker_label']);
		//$satker=$_GET['satker'];
		//$satker_label=$_GET['satker_label'];
		$userUnitKerjaObj = new UserUnitKerja();
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$role = $userUnitKerjaObj->GetRoleUser($userId);
		$unitkerjaUserId = $userUnitKerjaObj->GetUnitKerjaUser($userId);
		if($_POST || isset($_GET['cari'])) {
			if(isset($_POST['unitkerja_kode'])) {
				$kode = $_POST['unitkerja_kode'];
			} elseif(isset($_GET['kode'])) {
				$kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
			} else {
				$kode = '';
			}
		  
			if(isset($_POST['unitkerja'])) {
				$unitkerja = $_POST['unitkerja'];
			} elseif(isset($_GET['unitkerja'])) {
				$unitkerja = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
			} else {
				$unitkerja = '';
			}

			if($_POST['tipeunit'] != "all") {
				$tipeunit = $_POST['tipeunit'];
			} elseif(isset($_GET['tipeunit'])) {
				$tipeunit = Dispatcher::Instance()->Decrypt($_GET['tipeunit']);
			} else {
				$tipeunit = '';
			}
		}
		
	//view
		$totalData = $unitkerjaObj->GetCountDataUnitkerja($kode, $unitkerja, $tipeunit, $role, $unitkerjaUserId);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataUnitkerja = $unitkerjaObj->getDataUnitkerja($startRec, $itemViewed, $kode, $unitkerja, $tipeunit, $role, $unitkerjaUserId);
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&kode=' . Dispatcher::Instance()->Encrypt($kode) . '&unitkerja=' . Dispatcher::Instance()->Encrypt($unitkerja) . '&tipeunit=' . Dispatcher::Instance()->Encrypt($tipeunit) . '&cari=' . Dispatcher::Instance()->Encrypt(1));
		$dest = "popup-subcontent";
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage, $dest), Messenger::CurrentRequest);

		$arr_tipeunit = $unitkerjaObj->GetDataTipeunit();

		Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'tipeunit', array('tipeunit', $arr_tipeunit, $tipeunit, 'true', ' style="width:200px;" '), Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);

		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];
				
		$return['dataUnitkerja'] = $dataUnitkerja;
		$return['start'] = $startRec+1;

		$return['search']['kode'] = $kode;
		$return['search']['unitkerja'] = $unitkerja;
		$return['search']['tipeunit'] = $tipeunit;

		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'UNITKERJA_KODE', $search['kode']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA', $search['unitkerja']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', 
									Dispatcher::Instance()->GetUrl(
															'history_transaksi', 
															'popupUnitKerja', 
															'view', 
															'html') . 
															"&satker=" . 
															Dispatcher::Instance()->Encrypt($search['satker']));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataUnitkerja'])) {
			$this->mrTemplate->AddVar('data_unitkerja', 'UNITKERJA_EMPTY', 'YES');
		} else {
			//$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			//$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data_unitkerja', 'UNITKERJA_EMPTY', 'NO');
			$dataUnitkerja = $data['dataUnitkerja'];
         /*
			for ($i=0; $i<sizeof($dataUnitkerja); $i++) {
				$dataUnitkerja[$i]['enc_unitkerja_id'] = Dispatcher::Instance()->Encrypt($dataUnitkerja[$i]['unitkerja_id']);
				$dataUnitkerja[$i]['enc_unitkerja_nama'] = Dispatcher::Instance()->Encrypt($dataUnitkerja[$i]['unitkerja_nama']);
			}
         */
         //print_r($dataUnitkerja);
			for ($i=0; $i<sizeof($dataUnitkerja); $i++) {
				$no = $i+$data['start'];
				$dataUnitkerja[$i]['number'] = $no;
				//if ($no % 2 != 0) $dataUnitkerja[$i]['class_name'] = 'table-common-even';
				//else $dataUnitkerja[$i]['class_name'] = '';
				if($dataUnitkerja[$i]['parentId'] == "0") {
					$dataUnitkerja[$i]['class_name'] = 'table-common-even1';
               $dataUnitkerja[$i]['label_unit'] = str_replace("'","\'",$dataUnitkerja[$i]['unit']);
				} else {
               $dataUnitkerja[$i]['label_unit'] = str_replace("'","\'",$dataUnitkerja[$i]['satker']) . " / " . str_replace("'","\'",$dataUnitkerja[$i]['unit']);
            }

				$this->mrTemplate->AddVars('data_unitkerja_item', $dataUnitkerja[$i], 'UNITKERJA_');
				$this->mrTemplate->parseTemplate('data_unitkerja_item', 'a');	 
			}
		}
	}
}
?>
