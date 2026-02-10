<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_kode_penerimaan/response/ProcessRkaklKodePenerimaan.proc.class.php';

class DoDeleteRkaklKodePenerimaan extends HtmlResponse {

	function TemplateModule() {}
	
	function ProcessRequest() {
		$obj = new ProcessRkaklKodePenerimaan();
		$urlRedirect = $obj->Delete();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {}
}
?>
