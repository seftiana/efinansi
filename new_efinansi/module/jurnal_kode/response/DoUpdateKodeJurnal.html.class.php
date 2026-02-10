<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/jurnal_kode/response/ProcessKodeJurnal.proc.class.php';

class DoUpdateKodeJurnal extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessKodeJurnal();
		$urlRedirect = $Obj->Update();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
