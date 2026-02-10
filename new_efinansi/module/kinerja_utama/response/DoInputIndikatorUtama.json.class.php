<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kinerja_utama/response/ProcessIndikator.proc.class.php';

class DoInputIndikatorUtama extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessIndikator();
		if ($_GET['dataId']!="")
         $urlRedirect = $Obj->Update();
		else $urlRedirect = $Obj->Add();
		 
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	 }

	function ParseTemplate($data = NULL) {
	}
}
?>
