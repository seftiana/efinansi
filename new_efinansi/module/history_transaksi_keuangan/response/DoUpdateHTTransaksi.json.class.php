<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/history_transaksi_keuangan/response/ProcessTransaksi.proc.class.php';

class DoUpdateHTTransaksi extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessTransaksi();
		$urlRedirect = $Obj->Update();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.
		htmlentities($urlRedirect).'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
