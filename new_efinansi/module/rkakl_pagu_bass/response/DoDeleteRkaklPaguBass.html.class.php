<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_pagu_bass/response/ProcessRkaklPaguBass.proc.class.php';

class DoDeleteRkaklPaguBass extends HtmlResponse {

	function TemplateModule() {}
	
	function ProcessRequest() {
		$obj = new ProcessRkaklPaguBass();
		$urlRedirect = $obj->Delete();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {}
}
?>
