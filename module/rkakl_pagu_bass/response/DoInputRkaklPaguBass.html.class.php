<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_pagu_bass/response/ProcessRkaklPaguBass.proc.class.php';

class DoInputRkaklPaguBass extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessRkaklPaguBass();
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
