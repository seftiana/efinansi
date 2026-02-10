<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/usulan_kegiatan/business/AppPopupOutput.class.php';

class ViewPopupOutput extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/usulan_kegiatan/template');
		$this->SetTemplateFile('view_popup_output.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$popupOutputObj = new AppPopupOutput();
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

		$totalData = $popupOutputObj->GetCountDataOutput($nama, $kode);

		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataOutput = $popupOutputObj->GetDataOutput($startRec, $itemViewed, $nama, $kode);
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&nama=' . Dispatcher::Instance()->Encrypt($nama) . '&kode=' . Dispatcher::Instance()->Encrypt($kode) . '&cari=' . Dispatcher::Instance()->Encrypt(1));
      $dest = "popup-subcontent";
		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage, $dest), Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataOutput'] = $dataOutput;
		$return['start'] = $startRec+1;

		$return['search']['nama'] = $nama;
		$return['search']['kode'] = $kode;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
		$this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'popupOutput', 'view', 'html'));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataOutput'])) {
			$this->mrTemplate->AddVar('data_output', 'OUTPUT_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_output', 'OUTPUT_EMPTY', 'NO');
			$dataOutput = $data['dataOutput'];
			for($i=0;$i<sizeof($dataOutput);$i++) {
				$dataOutput[$i]['enc_output_id'] = Dispatcher::Instance()->Encrypt($dataOutput[$i]['id']);
				$dataOutput[$i]['enc_output_nama'] = Dispatcher::Instance()->Encrypt($dataOutput[$i]['nama']);
			}

			for ($i=0; $i<sizeof($dataOutput); $i++) {
				$no = $i+$data['start'];
				$dataOutput[$i]['number'] = $no;
				if ($no % 2 != 0) $dataOutput[$i]['class_name'] = 'table-common-even';
				else $dataOutput[$i]['class_name'] = '';

				$this->mrTemplate->AddVars('data_output_item', $dataOutput[$i], 'OUTPUT_');
				$this->mrTemplate->parseTemplate('data_output_item', 'a');	 
			}
		}
	}
}
?>
