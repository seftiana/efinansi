<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/unitkerja_tree/response/ProcessUnitkerja.proc.class.php';

class DoUpdateUnitkerja extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$unitkerjaObj = new ProcessUnitkerja();
		$urlRedirect = $unitkerjaObj->Update();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
