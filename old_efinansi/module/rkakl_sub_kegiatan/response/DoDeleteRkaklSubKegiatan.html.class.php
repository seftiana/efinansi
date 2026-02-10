<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_sub_kegiatan/response/ProcessRkaklSubKegiatan.proc.class.php';

class DoDeleteRkaklSubKegiatan extends HtmlResponse {

	function TemplateModule() {}
	
	function ProcessRequest() {
		$obj = new ProcessRkaklSubKegiatan();
		$urlRedirect = $obj->Delete();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {}
}
?>
