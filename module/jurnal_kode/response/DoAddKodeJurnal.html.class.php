<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/jurnal_kode/response/ProcessKodeJurnal.proc.class.php';

class DoAddKodeJurnal extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessKodeJurnal();
		$urlRedirect = $Obj->Add();
		$this->RedirectTo($urlRedirect);
		return NULL;
	 }
	function ParseTemplate($data = NULL) {	
	}
}
?>
