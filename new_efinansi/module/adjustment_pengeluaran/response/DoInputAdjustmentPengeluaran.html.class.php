<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/adjustment_pengeluaran/response/ProcessInputAdjustmentPengeluaran.proc.class.php';

class DoInputAdjustmentPengeluaran extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$approvalObj = new ProcessInputAdjustmentPengeluaran();
		$urlRedirect = $approvalObj->Input();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
