<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_program/response/ProcessRkaklProgram.proc.class.php';

class DoDeleteRkaklProgram extends HtmlResponse {

	function TemplateModule() {}
	
	function ProcessRequest() {
		$obj = new ProcessRkaklProgram();
		$urlRedirect = $obj->Delete();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {}
}
?>
