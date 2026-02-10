<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/approval/business/AppApproval.class.php';

class ViewInputApproval extends HtmlResponse {
	var $Data;
	var $Pesan;
	
	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 'module/approval/template');
		$this->SetTemplateFile('input_approval.html');
	}
	
	function ProcessRequest() {
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$approvalObj = new AppApproval();
		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->Data = $msg[0][0];

		$dataApproval = $approvalObj->GetDataApprovalById($idDec);

		$return['decDataId'] = $idDec;
		$return['dataApproval'] = $dataApproval;
		return $return;
	}

	function ParseTemplate($data = NULL) {
		if ($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
		}
		$dataApproval = $data['dataApproval'];
		
		if ($_REQUEST['dataId']=='') {
			$url="addApproval";
			$tambah="Tambah";
		} else {
			$url="updateApproval";
			$tambah="Ubah";
		}
		$this->mrTemplate->AddVar('content', 'JUDUL', $tambah);
		$this->mrTemplate->AddVar('content', 'APPROVAL_NAMA', empty($dataApproval[0]['approval_nama'])?$this->Data['approval_nama']:$dataApproval[0]['approval_nama']);

		$this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('approval', $url, 'do', 'html') . "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']));

		$this->mrTemplate->AddVar('content', 'DATAID', Dispatcher::Instance()->Decrypt($_GET['dataId']));
		$this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));
	}
}
?>
