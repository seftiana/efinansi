<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
    'module/kelompok_laporan/response/ProcessDetilKlpLapSummary.proc.class.php';

class DoAddDetilKlpLapSummary extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessDetilKlpLapSummary();
		$urlRedirect = $Obj->Add();
		$this->RedirectTo($urlRedirect);
		return NULL;
	 }
	function ParseTemplate($data = NULL) {	
	}
}
?>
