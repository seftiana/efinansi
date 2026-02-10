<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/approval/response/ProcessDetilApproval.proc.class.php';

class DoUpdateDetilApproval extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$approvalObj = new ProcessDetilApproval();
		$urlRedirect = $approvalObj->Update();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
