<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_penyusutan/response/ProcessTransaksiPenyusutan.proc.class.php';

class DoUpdateTransaksiPenyusutan extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessTransaksiPenyusutan();
		$urlRedirect = $Obj->Update();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.htmlentities($urlRedirect).'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
