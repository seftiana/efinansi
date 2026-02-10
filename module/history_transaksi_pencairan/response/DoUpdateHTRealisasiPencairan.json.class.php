<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
	'module/history_transaksi_pencairan/response/transaksi_realisasi/ProcessTransaksi.proc.class.php';


class DoUpdateHTRealisasiPencairan extends JsonResponse {

	function TemplateModule() {
	}

	function ProcessRequest() {
		$Obj = new ProcessTransaksi();
		$urlRedirect = $Obj->Update();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.
		$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {
	}
}
