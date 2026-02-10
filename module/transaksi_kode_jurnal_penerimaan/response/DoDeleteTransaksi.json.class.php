<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_kode_jurnal_penerimaan/response/ProcessTransaksi.proc.class.php';

class DoDeleteTransaksi extends JsonResponse {

	function TemplateModule() {}

	function ProcessRequest() {
		$Obj = new ProcessTransaksi();
		$urlRedirect = $Obj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {}
}
?>
