<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/adjustment_pengeluaran/response/ProcessInputAdjustmentPengeluaran.proc.class.php';

class DoInputAdjustmentPengeluaran extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$approvalObj = new ProcessInputAdjustmentPengeluaran();
		$urlRedirect = $approvalObj->Input();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
