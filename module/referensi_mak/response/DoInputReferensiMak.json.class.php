<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/referensi_mak/response/ProcessReferensiMak.proc.class.php';

class DoInputReferensiMak extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessReferensiMak();
		if ($_GET['dataId']!="")
         $urlRedirect = $Obj->Update();
		else $urlRedirect = $Obj->Add();
		 
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	 }

	function ParseTemplate($data = NULL) {
	}
}
?>