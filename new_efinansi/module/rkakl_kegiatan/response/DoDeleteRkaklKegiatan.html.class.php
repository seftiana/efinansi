<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_kegiatan/response/ProcessRkaklKegiatan.proc.class.php';

class DoDeleteRkaklKegiatan extends HtmlResponse {

	function TemplateModule() {}
	
	function ProcessRequest() {
		$obj = new ProcessRkaklKegiatan();
		$urlRedirect = $obj->Delete();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {}
}
?>
