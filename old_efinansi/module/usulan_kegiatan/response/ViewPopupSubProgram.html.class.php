<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/usulan_kegiatan/business/AppPopupSubProgram.class.php';

class ViewPopupSubProgram extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/usulan_kegiatan/template');
		$this->SetTemplateFile('view_popup_sub_program.html');
	}
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }
	
	function ProcessRequest() {
		$popupSubProgramObj = new AppPopupSubProgram();
		$POST = $_POST->AsArray();
		if(!empty($POST)) {
			$subprogram = $POST['subprogram'];
			$jenis = $POST['jenis'];
			//$kode= $POST['kode'];
		} elseif(isset($_GET['cari'])) {
			$subprogram = Dispatcher::Instance()->Decrypt($_GET['subprogram']);
			$jenis = Dispatcher::Instance()->Decrypt($_GET['jenis']);
			//$kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
		} else {
			$subprogram="";
			$jenis="";
			//$kode="";
		}
		$this->decProgramId = Dispatcher::Instance()->Decrypt($_GET['programId']);
		$this->encProgramId = Dispatcher::Instance()->Encrypt($this->decProgramId);
		//combo jenis
      //echo "666";
		$arr_jenis = $popupSubProgramObj->GetComboJenis();
		Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'jenis', array('jenis', $arr_jenis, $jenis, true, ' style="width:200px;" id="jenis"'), Messenger::CurrentRequest);

		$totalData = $popupSubProgramObj->GetCountDataSubProgram($this->decProgramId, $subprogram, $kode, $jenis);

		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$dataSubProgram = $popupSubProgramObj->getDataSubProgram($startRec, $itemViewed, $this->decProgramId, $subprogram, $kode, $jenis);
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType . '&programId=' . $this->encProgramId . '&subprogram=' . Dispatcher::Instance()->Encrypt($subprogram) . '&jenis=' . Dispatcher::Instance()->Encrypt($jenis) . '&kode=' . Dispatcher::Instance()->Encrypt($kode) . '&cari=' . Dispatcher::Instance()->Encrypt(1));

		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage, $dest), Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['dataSubProgram'] = $dataSubProgram;
		$return['start'] = $startRec+1;

		$return['search']['subprogram'] = $subprogram;
		$return['search']['kode'] = $kode;
		return $return;
	}
	
	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'SUBPROGRAM', $search['subprogram']);
		$this->mrTemplate->AddVar('content', 'KODE', $search['kode']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('usulan_kegiatan', 'popupSubProgram', 'view', 'html') . '&programId=' . $this->encProgramId);
		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['dataSubProgram'])) {
			$this->mrTemplate->AddVar('data_subprogram', 'SUBPROGRAM_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_subprogram', 'SUBPROGRAM_EMPTY', 'NO');
			$dataSubProgram = $data['dataSubProgram'];
			//for($i=0;$i<sizeof($dataSubProgram);$i++) {
			//}

			for ($i=0; $i<sizeof($dataSubProgram); $i++) {
				$dataSubProgram[$i]['enc_subprogram_id'] = Dispatcher::Instance()->Encrypt($dataSubProgram[$i]['id']);
				$dataSubProgram[$i]['enc_subprogram_nama'] = Dispatcher::Instance()->Encrypt($dataSubProgram[$i]['nama']);
				$no = $i+$data['start'];
				$dataSubProgram[$i]['number'] = $no;
				if ($no % 2 != 0) $dataSubProgram[$i]['class_name'] = 'table-common-even';
				else $dataSubProgram[$i]['class_name'] = '';

				$this->mrTemplate->AddVars('data_subprogram_item', $dataSubProgram[$i], 'SUBPROGRAM_');
				$this->mrTemplate->parseTemplate('data_subprogram_item', 'a');	 
			}
		}
	}
}
?>
