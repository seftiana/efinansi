<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/history_transaksi_keuangan/response/ProcessTransaksi.proc.class.php';

class DoDeleteTransaksi extends HtmlResponse {

	function TemplateModule() {}
	
	function ProcessRequest() {
		$Obj = new ProcessTransaksi();
		$urlRedirect = $Obj->Delete();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {}
}
?>
