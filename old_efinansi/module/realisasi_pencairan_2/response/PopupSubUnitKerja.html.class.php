<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/realisasi_pencairan_2/business/AppPopupSubUnitKerja.class.php';

class PopupSubUnitkerja extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/realisasi_pencairan_2/template');
		$this->SetTemplateFile('popup_subunit_kerja.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$unitkerjaObj = new AppPopupSubUnitKerja();
		//$satker = Dispatcher::Instance()->Decrypt($_GET['satker']);
		//$satker_label = Dispatcher::Instance()->Decrypt($_GET['satker_label']);
		$satker=$_GET['satker'];
		$satker_label=$_GET['satker_label'];
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
		
		if(isset($_GET['pop']) && $_GET['pop'] == 'home')
		   $return['showclear'] = 'NO';
		else
		   $return['showclear'] = 'YES';
		
	//view
		$totalData = $unitkerjaObj->GetCountDataUnitkerja($satker, $kode, $unitkerja, $tipeunit);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataUnitkerja = $unitkerjaObj->getDataUnitkerja($startRec, $itemViewed, $satker, $kode, $unitkerja, $tipeunit);
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&satker=' . Dispatcher::Instance()->Encrypt($satker) . '&satker_label=' . Dispatcher::Instance()->Encrypt($satker_label) . '&kode=' . Dispatcher::Instance()->Encrypt($kode) . '&unitkerja=' . Dispatcher::Instance()->Encrypt($unitkerja) . '&tipeunit=' . Dispatcher::Instance()->Encrypt($tipeunit) . '&cari=' . Dispatcher::Instance()->Encrypt(1));
		$dest = "popup-subcontent";
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage, $dest), Messenger::CurrentRequest);

		$arr_tipeunit = $unitkerjaObj->GetDataTipeunit();

		Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'tipeunit', array('tipeunit', $arr_tipeunit, $tipeunit, 'true', ' style="width:200px;" '), Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);

		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];
				
		$return['dataUnitkerja'] = $dataUnitkerja;
		$return['start'] = $startRec+1;

		$return['search']['satker'] = $satker;
		$return['search']['satker_label'] = $satker_label;
		$return['search']['kode'] = $kode;
		$return['search']['unitkerja'] = $unitkerja;
		$return['search']['tipeunit'] = $tipeunit;

		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'SATKER_LABEL', Dispatcher::Instance()->Decrypt($_GET['satker_label']));
		$this->mrTemplate->AddVar('content', 'UNITKERJA_KODE', $search['kode']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA', $search['unitkerja']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('realisasi_pencairan_2', 'subUnitKerja', 'popup', 'html') . "&satker=" . Dispatcher::Instance()->Encrypt($search['satker']) . "&satker_label=" . Dispatcher::Instance()->Encrypt($search['satker_label']));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}
		
		$this->mrTemplate->AddVar('show_clear', 'IS_SHOW', $data['showclear']);

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
			}*/
			for ($i=0; $i<sizeof($dataUnitkerja); $i++) {
				$no = $i+$data['start'];
				$dataUnitkerja[$i]['number'] = $no;
				if ($no % 2 != 0) $dataUnitkerja[$i]['class_name'] = 'table-common-even';
				else $dataUnitkerja[$i]['class_name'] = '';
            $dataUnitkerja[$i]['unitkerja_nama_for_js'] = addslashes($dataUnitkerja[$i]['unitkerja_nama']); 
				$this->mrTemplate->AddVars('data_unitkerja_item', $dataUnitkerja[$i], 'UNITKERJA_');
				$this->mrTemplate->parseTemplate('data_unitkerja_item', 'a');	 
			}
		}
		/*

		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'UNITKERJA', $search['satker']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA_KODE', $search['kode']);
		$this->mrTemplate->AddVar('content', 'UNITKERJA', $search['unitkerja']);
		$this->mrTemplate->AddVar('content', 'TIPEUNIT', $search['tipeunit']);

		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('unitkerja', 'unitkerja', 'view', 'html'));
		$this->mrTemplate->AddVar('content', 'URL_ADD', Dispatcher::Instance()->GetUrl('unitkerja', 'inputUnitkerja', 'view', 'html'));

		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataUnitkerja'])) {
			$this->mrTemplate->AddVar('data_unitkerja', 'UNITKERJA_EMPTY', 'YES');
		} else {
			$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data_unitkerja', 'UNITKERJA_EMPTY', 'NO');
			$dataUnitkerja = $data['dataUnitkerja'];
//mulai bikin tombol delete
			$label = "Manajemen Sub Unit Kerja";
			$urlDelete = Dispatcher::Instance()->GetUrl('unitkerja', 'deleteUnitKerja', 'do', 'html');
			$urlReturn = Dispatcher::Instance()->GetUrl('unitkerja', 'unitkerja', 'view', 'html');
			Messenger::Instance()->Send('confirm', 'confirmDelete', 'do', 'html', array($label, $urlDelete, $urlReturn),Messenger::NextRequest);
			$this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html'));
//selesai bikin tombol delete

			for ($i=0; $i<sizeof($dataUnitkerja); $i++) {
				$no = $i+$data['start'];
				$dataUnitkerja[$i]['number'] = $no;
				if ($no % 2 != 0) 
					$dataUnitkerja[$i]['class_name'] = 'table-common-even';
				else 
					$dataUnitkerja[$i]['class_name'] = '';
				
				if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);
				if($i == sizeof($dataUnitkerja)-1) $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
				$idEnc = Dispatcher::Instance()->Encrypt($dataUnitkerja[$i]['unitkerja_id']);
				$dataUnitkerja[$i]['url_edit'] = Dispatcher::Instance()->GetUrl('unitkerja', 'inputUnitkerja', 'view', 'html') . '&dataId=' . $idEnc . '&page=' . $encPage . '&cari='.$cari;
				//$dataUnitkerja[$i]['url_delete'] = Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html').'&urlDelete='. $urlAccept.'&urlReturn='.$urlReturn.'&id='.$idEnc.'&label='.$label.'&dataName='.$dataUnitkerja[$i]['unitkerja_nama'];

				$this->mrTemplate->AddVars('data_unitkerja_item', $dataUnitkerja[$i], 'UNITKERJA_');
				$this->mrTemplate->parseTemplate('data_unitkerja_item', 'a');	 
			}
		}		
		*/
	}
}
?>
