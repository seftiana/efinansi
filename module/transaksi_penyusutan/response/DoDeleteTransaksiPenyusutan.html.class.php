<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_penyusutan/response/ProcessTransaksiPenyusutan.proc.class.php';

class DoDeleteTransaksiPenyusutan extends HtmlResponse {

	function TemplateModule() {}
	
	function ProcessRequest() {
		$Obj = new ProcessTransaksiPenyusutan();
		$urlRedirect = $Obj->Delete();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {}
}
?>
