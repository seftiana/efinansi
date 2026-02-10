<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/program_kegiatan/business/RkaklKegiatan.class.php';

class ViewPopupRkaklKegiatan extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/program_kegiatan/template');
		$this->SetTemplateFile('view_popup_rkakl_kegiatan.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$popupRkaklKegiatanObj = new RkaklKegiatan();
		$POST = $_POST->AsArray();
		if(!empty($POST)) {
			$kode = $POST['kode_kegiatan_rkakl'];
			$nama = $POST['nama_kegiatan_rkakl'];
		} elseif(isset($_GET['cari'])) {
			$kode = Dispatcher::Instance()->Decrypt($_GET['kode_kegiatan_rkakl']);
			$nama = Dispatcher::Instance()->Decrypt($_GET['nama_kegiatan_rkakl']);
		} else {
			$kode="";
			$nama="";
		}
		
		$totalData = $popupRkaklKegiatanObj->GetCountDataRkaklKegiatan($kode,$nama);

		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		$dest = "popup-subcontent";
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataRkaklKegiatan = $popupRkaklKegiatanObj->GetDataRkaklKegiatan($startRec, $itemViewed, $kode,$nama);
		
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&kode=' . Dispatcher::Instance()->Encrypt($kode) . '&nama=' . Dispatcher::Instance()->Encrypt($nama) . '&cari=' . Dispatcher::Instance()->Encrypt(1));

		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage, $dest), Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataRkaklKegiatan'] = $dataRkaklKegiatan;
		$return['start'] = $startRec+1;

		$return['search']['nama'] = $nama;
		$return['search']['kode'] = $kode;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
		$this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('program_kegiatan', 'popupRkaklKegiatan', 'view', 'html'));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataRkaklKegiatan'])) {
			$this->mrTemplate->AddVar('data_rkakl_kegiatan', 'RKAKL_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_rkakl_kegiatan', 'RKAKL_EMPTY', 'NO');
			$dataRkaklKegiatan = $data['dataRkaklKegiatan'];
			//for($i=0;$i<sizeof($dataRkaklKegiatan);$i++) {
			//}

			for ($i=0; $i<sizeof($dataRkaklKegiatan); $i++) {
				
				$dataRkaklKegiatan[$i]['enc_rkakl_id'] = Dispatcher::Instance()->Encrypt($dataRkaklKegiatan[$i]['id']);
				$dataRkaklKegiatan[$i]['enc_rkakl_nama'] = Dispatcher::Instance()->Encrypt($dataRkaklKegiatan[$i]['nama']);
				//$dataRkaklKegiatan[$i]['nama']			= "nam'a";
				$dataRkaklKegiatan[$i]['link']			= str_replace("'","\'",$dataRkaklKegiatan[$i]['nama']);
				$no = $i+$data['start'];
				$dataRkaklKegiatan[$i]['number'] = $no;
				if ($no % 2 != 0) $dataRkaklKegiatan[$i]['class_name'] = 'table-common-even';
				else $dataRkaklKegiatan[$i]['class_name'] = '';

				$this->mrTemplate->AddVars('data_rkakl_item', $dataRkaklKegiatan[$i], 'RKAKL_');
				$this->mrTemplate->parseTemplate('data_rkakl_item', 'a');	 
			}
		}
	}
}
?>
