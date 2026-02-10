<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kelompok_laporan/response/ProcessKlpLaporan.proc.class.php';

class DoDeleteKlpLaporan extends HtmlResponse {

	function TemplateModule() {}
	
	function ProcessRequest() {
		$Obj = new ProcessKlpLaporan();
		$urlRedirect = $Obj->Delete();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {}
}
?>
