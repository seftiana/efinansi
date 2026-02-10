<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/usulan_kegiatan/response/ProcessUsulanKegiatan.proc.class.php';

class DoUpdateUsulanKegiatan extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$usulan_kegiatanObj = new ProcessUsulanKegiatan();
		$urlRedirect = $usulan_kegiatanObj->Update();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
