<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/pembalik_approval/response/ProcessDetilPembalikApproval.proc.class.php';

class DoUpdateDetilPembalikApproval extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$approvalObj = new ProcessDetilPembalikApproval();
		$urlRedirect = $approvalObj->Update();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
