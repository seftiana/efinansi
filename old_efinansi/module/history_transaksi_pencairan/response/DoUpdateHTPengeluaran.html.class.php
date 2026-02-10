<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
	'module/history_transaksi_pencairan/response/transaksi_pengeluaran/ProcessTransaksi.proc.class.php';

class DoUpdateHTPengeluaran extends HtmlResponse {

	function TemplateModule() {
	}

	function ProcessRequest() {
		$Obj = new ProcessTransaksi();
		$urlRedirect = $Obj->Update();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {
	}
}
