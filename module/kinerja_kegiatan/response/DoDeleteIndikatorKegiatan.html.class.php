<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kinerja_kegiatan/response/ProcessIndikator.proc.class.php';

class DoDeleteIndikatorKegiatan extends HtmlResponse {

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
