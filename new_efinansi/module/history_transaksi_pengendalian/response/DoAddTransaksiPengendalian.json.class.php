<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_pengendalian/response/ProcessTransaksiPengendalian.proc.class.php';

class DoAddTransaksiPengendalian extends JsonResponse {

	function TemplateModule() {
   
	}
	
	function ProcessRequest() {
		$Obj = new ProcessTransaksiPengendalian();
		$urlRedirect = $Obj->Add();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
   }

	function ParseTemplate($data = NULL) {
	}
}
?>
