<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/referensi_mak/response/ProcessReferensiMak.proc.class.php';

class DoDeleteReferensiMak extends HtmlResponse {

	function TemplateModule() {}
	
	function ProcessRequest() {
		$obj = new ProcessReferensiMak();
		$urlRedirect = $obj->Delete();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {}
}
?>