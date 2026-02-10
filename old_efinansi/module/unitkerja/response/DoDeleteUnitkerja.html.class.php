<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/unitkerja/response/ProcessUnitkerja.proc.class.php';

class DoDeleteUnitkerja extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$unitkerjaObj = new ProcessUnitkerja();
		$urlRedirect = $unitkerjaObj->Delete();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {
		
	}
}
?>
