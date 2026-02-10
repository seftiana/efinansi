<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/tipeunit/business/AppTipeunit.class.php';

class ViewInputTipeunit extends HtmlResponse {
	var $Data;
	var $Pesan;
	
	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 'module/tipeunit/template');
		$this->SetTemplateFile('input_tipeunit.html');
	}
	
	function ProcessRequest() {
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$tipeunitObj = new AppTipeunit();
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->Data = $msg[0][0];

		$dataTipeunit = $tipeunitObj->GetDataTipeunitById($idDec);

		$return['decDataId'] = $idDec;
		$return['dataTipeunit'] = $dataTipeunit;
		return $return;
	}

	function ParseTemplate($data = NULL) {
		if ($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
		}
		$dataTipeunit = $data['dataTipeunit'];
		
		if ($_REQUEST['dataId']=='') {
			$url="addTipeunit";
			$tambah="Tambah";
		} else {
			$url="updateTipeunit";
			$tambah="Ubah";
		}
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		$this->mrTemplate->AddVar('content', 'TIPEUNIT_NAMA', empty($dataTipeunit[0]['tipeunit_nama'])?$this->Data['tipeunit_nama']:$dataTipeunit[0]['tipeunit_nama']);

		$this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('tipeunit', $url, 'do', 'html') . "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']));

		$this->mrTemplate->AddVar('content', 'DATAID', Dispatcher::Instance()->Decrypt($_GET['dataId']));
		$this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));
	}
}
?>
