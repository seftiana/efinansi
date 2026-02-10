<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/usulan_kegiatan/business/AppPopupTupoksi.class.php';

class ViewPopupTupoksi extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
				'module/usulan_kegiatan/template');
		$this->SetTemplateFile('view_popup_tupoksi.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
	  		'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$popupTupoksiObj = new AppPopupTupoksi();
		$POST = $_POST->AsArray();
		//$tahun_anggaran = $_GET['tahun_anggaran'];
		if(!empty($POST)) {
			$nama = $POST['nama'];
			$kode= $POST['kode'];
		} elseif(isset($_GET['cari'])) {
			$nama = Dispatcher::Instance()->Decrypt($_GET['nama']);
			$kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
		} else {
			$nama="";
			$kode="";
		}

		$totalData = $popupTupoksiObj->GetCountDataTupoksi($nama, $kode);

		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataTupoksi = $popupTupoksiObj->GetDataTupoksi($startRec, $itemViewed, $nama, $kode);
		$url = Dispatcher::Instance()->GetUrl(
										Dispatcher::Instance()->mModule, 
										Dispatcher::Instance()->mSubModule, 
										Dispatcher::Instance()->mAction, 
										Dispatcher::Instance()->mType . 
										'&nama=' . Dispatcher::Instance()->Encrypt($nama) . 
										'&kode=' . Dispatcher::Instance()->Encrypt($kode) . 
										'&cari=' . Dispatcher::Instance()->Encrypt(1));
										
      	$dest = "popup-subcontent";
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', 
							array($itemViewed,$totalData, $url, $currPage, $dest), 
							Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataTupoksi'] = $dataTupoksi;
		$return['start'] = $startRec+1;

		$return['search']['nama'] = $nama;
		$return['search']['kode'] = $kode;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
		$this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl(
							'usulan_kegiatan', 
							'popupTupoksi', 
							'view', 
							'html'));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataTupoksi'])) {
			$this->mrTemplate->AddVar('data_tupoksi', 'TUPOKSI_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_tupoksi', 'TUPOKSI_EMPTY', 'NO');
			$dataTupoksi = $data['dataTupoksi'];
			for($i=0;$i<sizeof($dataTupoksi);$i++) {
				$dataTupoksi[$i]['enc_tupoksi_id'] = Dispatcher::Instance()->Encrypt($dataTupoksi[$i]['id']);
				$dataTupoksi[$i]['enc_tupoksi_nama'] = Dispatcher::Instance()->Encrypt($dataTupoksi[$i]['nama']);
			}

			for ($i=0; $i<sizeof($dataTupoksi); $i++) {
				$no = $i+$data['start'];
				$dataTupoksi[$i]['number'] = $no;
				if ($no % 2 != 0) $dataTupoksi[$i]['class_name'] = 'table-common-even';
				else $dataTupoksi[$i]['class_name'] = '';

				$this->mrTemplate->AddVars('data_tupoksi_item', $dataTupoksi[$i], 'V_');
				$this->mrTemplate->parseTemplate('data_tupoksi_item', 'a');	 
			}
		}
	}
}
?>
