<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/daftar_pinjaman/response/ProcessPinjaman.proc.class.php';

class DoUpdatePinjaman extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$propObj = new ProcessPinjaman();
		$urlRedirect = $propObj->Update();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {
	}
}
?>