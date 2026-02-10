<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/usulan_kegiatan/response/ProcessDetilUsulanKegiatan.proc.class.php';

class DoUpdateDetilUsulanKegiatan extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$usulan_kegiatanObj = new ProcessDetilUsulanKegiatan();
		$urlRedirect = $usulan_kegiatanObj->Update();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
