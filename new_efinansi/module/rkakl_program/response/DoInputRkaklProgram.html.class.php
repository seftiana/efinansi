<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_program/response/ProcessRkaklProgram.proc.class.php';

class DoInputRkaklProgram extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessRkaklProgram();
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
