<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/usulan_kegiatan/business/AppPopupIku.class.php';

class ViewPopupIku extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/usulan_kegiatan/template');
		$this->SetTemplateFile('view_popup_iku.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$popupIkuObj = new AppPopupIku();
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

		$totalData = $popupIkuObj->GetCountDataIku($nama, $kode);

		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataIku = $popupIkuObj->getDataIku($startRec, $itemViewed, $nama, $kode);
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&nama=' . Dispatcher::Instance()->Encrypt($nama) . '&kode=' . Dispatcher::Instance()->Encrypt($kode) . '&cari=' . Dispatcher::Instance()->Encrypt(1));
      $dest = "popup-subcontent";
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage, $dest), Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataIku'] = $dataIku;
		$return['start'] = $startRec+1;

		$return['search']['nama'] = $nama;
		$return['search']['kode'] = $kode;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
		$this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'popupIku', 'view', 'html'));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataIku'])) {
			$this->mrTemplate->AddVar('data_iku', 'IKU_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_iku', 'IKU_EMPTY', 'NO');
			$dataIku = $data['dataIku'];
			for($i=0;$i<sizeof($dataIku);$i++) {
				$dataIku[$i]['enc_iku_id'] = Dispatcher::Instance()->Encrypt($dataIku[$i]['id']);
				$dataIku[$i]['enc_iku_nama'] = Dispatcher::Instance()->Encrypt($dataIku[$i]['nama']);
			}

			for ($i=0; $i<sizeof($dataIku); $i++) {
				$no = $i+$data['start'];
				$dataIku[$i]['number'] = $no;
				if ($no % 2 != 0) $dataIku[$i]['class_name'] = 'table-common-even';
				else $dataIku[$i]['class_name'] = '';

				$this->mrTemplate->AddVars('data_iku_item', $dataIku[$i], 'IKU_');
				$this->mrTemplate->parseTemplate('data_iku_item', 'a');	 
			}
		}
	}
}
?>
