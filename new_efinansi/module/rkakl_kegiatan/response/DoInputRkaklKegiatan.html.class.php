<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/rkakl_kegiatan/response/ProcessRkaklKegiatan.proc.class.php';

class DoInputRkaklKegiatan extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
	
		$Obj = new ProcessRkaklKegiatan();
		if ($_GET['dataId']!="")
         $urlRedirect = $Obj->Update();
		else $urlRedirect = $Obj->Add();
		 
		$this->RedirectTo($urlRedirect);
		return NULL;
	 }
	function ParseTemplate($data = NULL) {	
	}
}
?>
