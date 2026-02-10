<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_output/response/ProcessRkakl.proc.class.php';

class DoDeleteRkakl extends HtmlResponse {

	function TemplateModule() {}
	
	function ProcessRequest() {
		$obj = new ProcessRkakl();
		$urlRedirect = $obj->Delete();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {}
}
?>
