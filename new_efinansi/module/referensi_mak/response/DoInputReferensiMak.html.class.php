<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/referensi_mak/response/ProcessReferensiMak.proc.class.php';

class DoInputReferensiMak extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessReferensiMak();
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