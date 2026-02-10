<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/usulan_kegiatan/business/AppPopupIkk.class.php';

class ViewPopupIkk extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/usulan_kegiatan/template');
		$this->SetTemplateFile('view_popup_ikk.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$popupIkkObj = new AppPopupIkk();
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

		$totalData = $popupIkkObj->GetCountDataIkk($nama, $kode);

		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataIkk = $popupIkkObj->getDataIkk($startRec, $itemViewed, $nama, $kode);
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&nama=' . Dispatcher::Instance()->Encrypt($nama) . '&kode=' . Dispatcher::Instance()->Encrypt($kode) . '&cari=' . Dispatcher::Instance()->Encrypt(1));
      $dest = "popup-subcontent";
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage, $dest), Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataIkk'] = $dataIkk;
		$return['start'] = $startRec+1;

		$return['search']['nama'] = $nama;
		$return['search']['kode'] = $kode;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
		$this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'popupIkk', 'view', 'html'));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataIkk'])) {
			$this->mrTemplate->AddVar('data_ikk', 'IKK_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_ikk', 'IKK_EMPTY', 'NO');
			$dataIkk = $data['dataIkk'];
			for($i=0;$i<sizeof($dataIkk);$i++) {
				$dataIkk[$i]['enc_ikk_id'] = Dispatcher::Instance()->Encrypt($dataIkk[$i]['id']);
				$dataIkk[$i]['enc_ikk_nama'] = Dispatcher::Instance()->Encrypt($dataIkk[$i]['nama']);
			}

			for ($i=0; $i<sizeof($dataIkk); $i++) {
				$no = $i+$data['start'];
				$dataIkk[$i]['number'] = $no;
				if ($no % 2 != 0) $dataIkk[$i]['class_name'] = 'table-common-even';
				else $dataIkk[$i]['class_name'] = '';

				$this->mrTemplate->AddVars('data_ikk_item', $dataIkk[$i], 'IKK_');
				$this->mrTemplate->parseTemplate('data_ikk_item', 'a');	 
			}
		}
	}
}
?>
