<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/tipeunit/response/ProcessTipeunit.proc.class.php';

class DoUpdateTipeunit extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$tipeunitObj = new ProcessTipeunit();
		$urlRedirect = $tipeunitObj->Update();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
