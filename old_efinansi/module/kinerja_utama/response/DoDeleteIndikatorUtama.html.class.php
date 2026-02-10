<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kinerja_utama/response/ProcessIndikator.proc.class.php';

class DoDeleteIndikatorUtama extends HtmlResponse {

	function TemplateModule() {}
	
	function ProcessRequest() {
		$obj = new ProcessIndikator();
		$urlRedirect = $obj->Delete();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {}
}
?>
