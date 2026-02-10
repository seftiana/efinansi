<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/approval_pencairan/response/ProcessApprovalPencairan.proc.class.php';

class DoUpdateApprovalPencairan extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$approval_pencairanObj = new ProcessApprovalPencairan();
		$urlRedirect = $approval_pencairanObj->Update();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
