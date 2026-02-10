<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_pagu_bass/response/ProcessRkaklPaguBass.proc.class.php';

class DoInputRkaklPaguBass extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessRkaklPaguBass();
		if ($_GET['dataId']!=""){
            $urlRedirect = $Obj->Update();
		}else{
		    $urlRedirect = $Obj->Add();
		}
		 
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	 }

	function ParseTemplate($data = NULL) {
	}
}
?>
