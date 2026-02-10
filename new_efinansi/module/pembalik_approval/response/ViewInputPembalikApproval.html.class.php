<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/pembalik_approval/business/AppPembalikApproval.class.php';

class ViewInputPembalikApproval extends HtmlResponse {
	var $Data;
	var $Pesan;
	
	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 'module/pembalik_approval/template');
		$this->SetTemplateFile('input_pembalik_approval.html');
	}
	
	function ProcessRequest() {
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$approvalObj = new AppPembalikApproval();
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->Data = $msg[0][0];

		$dataPembalikApproval = $approvalObj->GetDataPembalikApprovalById($idDec);

		$return['decDataId'] = $idDec;
		$return['dataPembalikApproval'] = $dataPembalikApproval;
		return $return;
	}

	function ParseTemplate($data = NULL) {
		if ($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
		}
		$dataPembalikApproval = $data['dataPembalikApproval'];
		
		if ($_REQUEST['dataId']=='') {
			$url="addPembalikApproval";
			$tambah="Tambah";
		} else {
			$url="updatePembalikApproval";
			$tambah="Ubah";
		}
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		$this->mrTemplate->AddVar('content', 'APPROVAL_NAMA', empty($dataPembalikApproval[0]['approval_nama'])?$this->Data['approval_nama']:$dataPembalikApproval[0]['approval_nama']);

		$this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('pembalik_approval', $url, 'do', 'html') . "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']));

		$this->mrTemplate->AddVar('content', 'DATAID', Dispatcher::Instance()->Decrypt($_GET['dataId']));
		$this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));
	}
}
?>
