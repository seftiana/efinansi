<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_sub_kegiatan/response/ProcessRkaklSubKegiatan.proc.class.php';

class DoInputRkaklSubKegiatan extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessRkaklSubKegiatan();
		if (isset($_GET['dataId']) && $_GET['dataId'] !='')
         $urlRedirect = $Obj->Update();
		else $urlRedirect = $Obj->Add();
		 
		$this->RedirectTo($urlRedirect);
		return NULL;
	 }
	function ParseTemplate($data = NULL) {	
	}
}
?>
