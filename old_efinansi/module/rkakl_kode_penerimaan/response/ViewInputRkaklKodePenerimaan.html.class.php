<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_kode_penerimaan/business/RkaklKodePenerimaan.class.php';

class ViewInputRkaklKodePenerimaan extends HtmlResponse {

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 'module/rkakl_kode_penerimaan/template');
		$this->SetTemplateFile('input_rkakl_kode_penerimaan.html');
	}
	
	function ProcessRequest() {
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$Obj = new RkaklKodePenerimaan();
		$msg = Messenger::Instance()->Receive(__FILE__);
		$return['Pesan'] = $msg[0][1];
		$return['Data'] = $msg[0][0];

		$data = $Obj->GetRkaklKodePenerimaanById($idDec);

		$return['decDataId'] = $idDec;
		$return['dataK'] = $data;
		return $return;
	}

	function ParseTemplate($data = NULL) {
		if ($data['Pesan']) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['Pesan']);
		}
		$dataK = $data['dataK'];
		
		if ($_REQUEST['dataId']=='') {
			$tambah="Tambah";
		} else {
			$tambah="Ubah";
		}
		
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		$this->mrTemplate->AddVar('content', 'NAMA', empty($dataK[0]['nama'])?$data['Data']['nama']:$dataK[0]['nama']);
      $this->mrTemplate->AddVar('content', 'KODE', empty($dataK[0]['kode'])?$data['Data']['kode']:$dataK[0]['kode']);
		
		$this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('rkakl_kode_penerimaan', 'inputRkaklKodePenerimaan', 'do', 'html') . "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']));
      
		$this->mrTemplate->AddVar('content', 'ID', Dispatcher::Instance()->Decrypt($_GET['dataId']));
		//$this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));
	}
}
?>
