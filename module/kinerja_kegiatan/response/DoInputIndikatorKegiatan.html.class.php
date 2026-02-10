<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kinerja_kegiatan/response/ProcessIndikator.proc.class.php';

class DoInputIndikatorKegiatan extends HtmlResponse {

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
