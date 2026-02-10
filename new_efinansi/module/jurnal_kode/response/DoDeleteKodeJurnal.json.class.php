<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/jurnal_kode/response/ProcessKodeJurnal.proc.class.php';

class DoDeleteKodeJurnal extends JsonResponse {

	function TemplateModule() {}

	function ProcessRequest() {
		$Obj = new ProcessKodeJurnal();
		$urlRedirect = $Obj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {}
}
?>
