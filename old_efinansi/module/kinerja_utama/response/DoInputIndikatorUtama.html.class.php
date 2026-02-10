<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kinerja_utama/response/ProcessIndikator.proc.class.php';

class DoInputIndikatorUtama extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessIndikator();
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
