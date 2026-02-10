<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/approval_rencana_penerimaan/response/ProcessRencanaPenerimaan.proc.class.php';

class DoUpdateRencanaPenerimaan extends JsonResponse {

	function TemplateModule() {}
	
	function ProcessRequest() {
		$Obj = new ProcessRencanaPenerimaan();
		$urlRedirect = $Obj->Update();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
