<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_kode_penerimaan/response/ProcessRkaklKodePenerimaan.proc.class.php';

class DoInputRkaklKodePenerimaan extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessRkaklKodePenerimaan();
		if ($_GET['dataId']!="")
         $urlRedirect = $Obj->Update();
		else $urlRedirect = $Obj->Add();
		 
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	 }

	function ParseTemplate($data = NULL) {
	}
}
?>
