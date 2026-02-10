<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_program/response/ProcessRkaklProgram.proc.class.php';

class DoInputRkaklProgram extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessRkaklProgram();
		if ($_GET['dataId']!="")
         $urlRedirect = $Obj->Update();
		else $urlRedirect = $Obj->Add();
		 
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	 }

	function ParseTemplate($data = NULL) {
	}
}
?>
