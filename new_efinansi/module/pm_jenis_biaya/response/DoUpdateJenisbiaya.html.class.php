<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/pm_jenis_biaya/response/ProcessJenisbiaya.proc.class.php';

class DoUpdateJenisbiaya extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$jenisbiayaObj = new ProcessJenisbiaya();
		$urlRedirect = $jenisbiayaObj->Update();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
