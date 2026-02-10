<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_output/response/ProcessRkakl.proc.class.php';

class DoInputRkakl extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessRkakl();
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
