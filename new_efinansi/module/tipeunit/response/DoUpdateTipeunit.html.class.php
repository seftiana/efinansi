<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/tipeunit/response/ProcessTipeunit.proc.class.php';

class DoUpdateTipeunit extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$tipeunitObj = new ProcessTipeunit();
		$urlRedirect = $tipeunitObj->Update();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
