<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/tipeunit/response/ProcessTipeunit.proc.class.php';

class DoAddTipeunit extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$tipeunitObj = new ProcessTipeunit();
		$urlRedirect = $tipeunitObj->Add();
		$this->RedirectTo($urlRedirect);
		return NULL;
	 }
	function ParseTemplate($data = NULL) {	
	}
}
?>
