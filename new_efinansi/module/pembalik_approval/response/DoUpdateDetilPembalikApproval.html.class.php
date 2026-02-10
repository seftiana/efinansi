<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/pembalik_approval/response/ProcessDetilPembalikApproval.proc.class.php';

class DoUpdateDetilPembalikApproval extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$approvalObj = new ProcessDetilPembalikApproval();
		$urlRedirect = $approvalObj->Update();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
