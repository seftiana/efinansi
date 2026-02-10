<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/pagu_anggaran_unit/business/SumberDana.class.php';

class ViewPopupSumberDana extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/pagu_anggaran_unit/template');
		$this->SetTemplateFile('view_popup_sumber_dana.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$popupSumberDanaObj = new SumberDana();
		$POST = $_POST->AsArray();
		if(!empty($POST)) {
			$nama = $POST['nama_sumber_dana'];
		} elseif(isset($_GET['cari'])) {
			$nama = Dispatcher::Instance()->Decrypt($_GET['nama_sumber_dana']);
		} else {
			$nama="";
		}
		
		$totalData = $popupSumberDanaObj->GetCountDataSumberDana($nama);

		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		$dest = "popup-subcontent";
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataSumberDana = $popupSumberDanaObj->GetDataSumberDana($startRec, $itemViewed, $nama);
		
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&kode=' . Dispatcher::Instance()->Encrypt($kode) . '&nama=' . Dispatcher::Instance()->Encrypt($nama) . '&cari=' . Dispatcher::Instance()->Encrypt(1));

		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage, $dest), Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataSumberDana'] = $dataSumberDana;
		$return['start'] = $startRec+1;

		$return['search']['nama'] = $nama;
		$return['search']['kode'] = $kode;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'NAMA', $search['nama']);
		$this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('pagu_anggaran_unit', 'popupSumberDana', 'view', 'html'));
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataSumberDana'])) {
			$this->mrTemplate->AddVar('data_sumber_dana_subkegiatan', 'SUMBER_DANA_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_sumber_dana_subkegiatan', 'SUMBER_DANA_EMPTY', 'NO');
			$dataSumberDana = $data['dataSumberDana'];
			//for($i=0;$i<sizeof($dataRkaklKegiatan);$i++) {
			//}

			for ($i=0; $i<sizeof($dataSumberDana); $i++) {
				$dataSumberDana[$i]['enc_sumber_dana_id'] = Dispatcher::Instance()->Encrypt($dataSumberDana[$i]['id']);
				$dataSumberDana[$i]['enc_sumber_dana_nama'] = Dispatcher::Instance()->Encrypt($dataSumberDana[$i]['nama']);
				
				$dataSumberDana[$i]['linknama']		= str_replace("'","\'",$dataSumberDana[$i]['nama']);
				$no = $i+$data['start'];
				$dataSumberDana[$i]['number'] = $no;
				if ($no % 2 != 0) $dataSumberDana[$i]['class_name'] = 'table-common-even';
				else $dataSumberDana[$i]['class_name'] = '';

				$this->mrTemplate->AddVars('data_sumber_dana_item', $dataSumberDana[$i], 'SD_');
				$this->mrTemplate->parseTemplate('data_sumber_dana_item', 'a');	 
			}
		}
	}
}
?>
