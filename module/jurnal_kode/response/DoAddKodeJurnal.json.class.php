<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/jurnal_kode/response/ProcessKodeJurnal.proc.class.php';

class DoAddKodeJurnal extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessKodeJurnal();
		$urlRedirect = $Obj->Add();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	 }

	function ParseTemplate($data = NULL) {
	}
}
?>
