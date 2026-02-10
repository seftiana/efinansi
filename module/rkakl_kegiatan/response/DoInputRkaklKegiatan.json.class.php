<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/rkakl_kegiatan/response/ProcessRkaklKegiatan.proc.class.php';

class DoInputRkaklKegiatan extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessRkaklKegiatan();
		if ($_GET['dataId']!="")
         $urlRedirect = $Obj->Update();
		else $urlRedirect = $Obj->Add();
		 
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	 }

	function ParseTemplate($data = NULL) {
	}
}
?>
