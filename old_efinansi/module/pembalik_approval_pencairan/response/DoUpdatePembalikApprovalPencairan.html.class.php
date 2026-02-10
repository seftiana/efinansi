<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/pembalik_approval_pencairan/response/ProcessPembalikApprovalPencairan.proc.class.php';

class DoUpdatePembalikApprovalPencairan extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$approval_pencairanObj = new ProcessPembalikApprovalPencairan();
		$urlRedirect = $approval_pencairanObj->Update();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
