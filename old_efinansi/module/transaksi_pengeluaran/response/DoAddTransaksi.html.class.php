<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_pengeluaran/response/ProcessTransaksi.proc.class.php';

class DoAddTransaksi extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessTransaksi();
		$urlRedirect = $Obj->Add();
		$this->RedirectTo($urlRedirect);
		return NULL;
	 }
	function ParseTemplate($data = NULL) {	
	}
}
?>
