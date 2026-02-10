<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kode_penerimaan/business/AppPopupKodeRkakl.class.php';

class ViewPopupKodeRkakl extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/kode_penerimaan/template');
		$this->SetTemplateFile('view_popup_kode_rkakl.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$popupKodeRkaklObj = new AppPopupKodeRkakl();
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

		$totalData = $popupKodeRkaklObj->GetCountDataKodeRkakl($nama, $kode);

		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataKodeRkakl = $popupKodeRkaklObj->getDataKodeRkakl($startRec, $itemViewed, $nama, $kode);
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&nama=' . Dispatcher::Instance()->Encrypt($nama) . '&kode=' . Dispatcher::Instance()->Encrypt($kode) . '&cari=' . Dispatcher::Instance()->Encrypt(1));
      $dest = "popup-subcontent";
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage, $dest), Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataKodeRkakl'] = $dataKodeRkakl;
		$return['start'] = $startRec+1;

		$return['search']['nama'] = $nama;
		$return['search']['kode'] = $kode;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
		$this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('kode_penerimaan', 'popupKodeRkakl', 'view', 'html'));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataKodeRkakl'])) {
			$this->mrTemplate->AddVar('data_kode_rkakl', 'KODE_RKAKL_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_kode_rkakl', 'KODE_RKAKL_EMPTY', 'NO');
			$dataKodeRkakl = $data['dataKodeRkakl'];
			for($i=0;$i<sizeof($dataKodeRkakl);$i++) {
				$dataKodeRkakl[$i]['enc_kode_rkakl_id'] = Dispatcher::Instance()->Encrypt($dataKodeRkakl[$i]['id']);
				$dataKodeRkakl[$i]['enc_kode_rkakl_nama'] = Dispatcher::Instance()->Encrypt($dataKodeRkakl[$i]['nama']);
			}

			for ($i=0; $i<sizeof($dataKodeRkakl); $i++) {
				$dataKodeRkakl[$i]['link_nama']		= str_replace("'","\'",$dataKodeRkakl[$i]['nama']);
				$no = $i+$data['start'];
				$dataKodeRkakl[$i]['number'] = $no;
				if ($no % 2 != 0) $dataKodeRkakl[$i]['class_name'] = 'table-common-even';
				else $dataKodeRkakl[$i]['class_name'] = '';

				$this->mrTemplate->AddVars('data_kode_rkakl_item', $dataKodeRkakl[$i], 'KODE_RKAKL_');
				$this->mrTemplate->parseTemplate('data_kode_rkakl_item', 'a');	 
			}
		}
	}
}
?>