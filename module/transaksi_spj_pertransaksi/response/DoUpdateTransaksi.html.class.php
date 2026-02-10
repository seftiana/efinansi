<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_realisasi_kode_jurnal/response/ProcessTransaksi.proc.class.php';

class DoUpdateTransaksi extends HtmlResponse {

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
?>
