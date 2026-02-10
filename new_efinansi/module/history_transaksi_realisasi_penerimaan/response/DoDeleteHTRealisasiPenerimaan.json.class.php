<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/history_transaksi_realisasi_penerimaan/response/ProcessTransaksi.proc.class.php';

class DoDeleteHTRealisasiPenerimaan extends JsonResponse {

	function TemplateModule() {}

	function ProcessRequest() {
		$Obj = new ProcessTransaksi();
		$urlRedirect = $Obj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {}
}

?>