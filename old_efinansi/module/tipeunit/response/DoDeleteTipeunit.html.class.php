<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/tipeunit/response/ProcessTipeunit.proc.class.php';

class DoDeleteTipeunit extends HtmlResponse {

	function TemplateModule() {}
	
	function ProcessRequest() {
		$tipeunitObj = new ProcessTipeunit();
		$urlRedirect = $tipeunitObj->Delete();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {}
}
?>
