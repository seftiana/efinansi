<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_realisasi_kode_jurnal/response/ProcessTransaksi.proc.class.php';

class DoAddTransaksi extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessTransaksi();
		$urlRedirect = $Obj->Add();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.htmlentities($urlRedirect).'&ascomponent=1")');
	 }

	function ParseTemplate($data = NULL) {
	}
}
?>
