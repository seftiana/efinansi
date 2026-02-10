<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/realisasi_pencairan/business/AppPopupSubKegiatan.class.php';

class PopupSubKegiatan extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/realisasi_pencairan/template');
		$this->SetTemplateFile('popup_subkegiatan.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$popupKegiatanRefObj = new AppPopupKegiatanRef();
		$POST = $_POST->AsArray();
		if(!empty($POST)) {
			$kegiatanref = $POST['kegiatanref'];
			$kode= $POST['kode'];
		} elseif(isset($_GET['cari'])) {
			$kegiatanref = Dispatcher::Instance()->Decrypt($_GET['kegiatanref']);
			$kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
		} else {
			$kegiatanref="";
			$kode="";
		}
		//$this->decSubProgramId = Dispatcher::Instance()->Decrypt($_GET['subprogramId']);
		$this->decSubProgramId = $_GET['subprogramId'];
		$this->encSubProgramId = $_GET['subprogramId'];
		$kegiatanunit_id= Dispatcher::Instance()->Decrypt($_GET['grp']);
		

		$totalData = $popupKegiatanRefObj->GetCountDataKegiatanRef($this->decSubProgramId,$kegiatanunit_id, $kegiatanref, $kode);

		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataKegiatanRef = $popupKegiatanRefObj->getDataKegiatanRef($startRec, $itemViewed, $this->decSubProgramId,$kegiatanunit_id, $kegiatanref, $kode);
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&subprogramId=' . $this->encSubProgramId . '&kegiatanref=' . Dispatcher::Instance()->Encrypt($kegiatanref) . '&kode=' . Dispatcher::Instance()->Encrypt($kode) . '&cari=' . Dispatcher::Instance()->Encrypt(1));
		$dest = "popup-subcontent";
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage, $dest), Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataKegiatanRef'] = $dataKegiatanRef;
		$return['start'] = $startRec+1;

		$return['search']['kegiatanref'] = $kegiatanref;
		$return['search']['kode'] = $kode;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'KEGIATANREF', $search['kegiatanref']);
		$this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('realisasi_pencairan', 'subKegiatan', 'popup', 'html') . '&subprogramId' + $this->encSubProgramId + '&grp=' . $_GET['grp']);
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataKegiatanRef'])) {
			$this->mrTemplate->AddVar('data_kegiatanref', 'KEGIATANREF_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_kegiatanref', 'KEGIATANREF_EMPTY', 'NO');
			$dataKegiatanRef = $data['dataKegiatanRef'];
			/*for($i=0;$i<sizeof($dataKegiatanRef);$i++) {
				$dataKegiatanRef[$i]['enc_kegiatanref_id'] = Dispatcher::Instance()->Encrypt($dataKegiatanRef[$i]['id']);
				$dataKegiatanRef[$i]['enc_kegiatanref_nama'] = Dispatcher::Instance()->Encrypt($dataKegiatanRef[$i]['nama']);
				$dataKegiatanRef[$i]['kegiatandetail_id'] = Dispatcher::Instance()->Encrypt($dataKegiatanRef[$i]['kegiatandetail_id']);
			}*/

			for ($i=0; $i<sizeof($dataKegiatanRef); $i++) {
				$no = $i+$data['start'];
				$dataKegiatanRef[$i]['number'] = $no;
				$dataKegiatanRef[$i]['total_realisasi'] = $dataKegiatanRef[$i]['realisasi_nominal'] + $dataKegiatanRef[$i]['realisasi_pencairan'];
				$dataKegiatanRef[$i]['realisasi_nominal'] =  number_format($dataKegiatanRef[$i]['realisasi_nominal'], 2, ',', '.');
				$dataKegiatanRef[$i]['realisasi_pencairan'] = number_format($dataKegiatanRef[$i]['realisasi_pencairan'], 2, ',', '.');
				$dataKegiatanRef[$i]['total_realisasi'] = number_format($dataKegiatanRef[$i]['total_realisasi'], 2, ',', '.');
				$dataKegiatanRef[$i]['total_anggaran'] = number_format($dataKegiatanRef[$i]['total_anggaran'], 2, ',', '.');			
				
				
				if ($no % 2 != 0) $dataKegiatanRef[$i]['class_name'] = 'table-common-even';
				else $dataKegiatanRef[$i]['class_name'] = '';

				$this->mrTemplate->AddVars('data_kegiatanref_item', $dataKegiatanRef[$i], 'KEGIATANREF_');
				$this->mrTemplate->parseTemplate('data_kegiatanref_item', 'a');	 
			}
			//debug($dataKegiatanRef);
		}
	}
}
?>
