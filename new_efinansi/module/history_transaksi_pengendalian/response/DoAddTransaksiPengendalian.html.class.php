<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_pengendalian/response/ProcessTransaksiPengendalian.proc.class.php';

class DoAddTransaksiPengendalian extends HtmlResponse {

	function TemplateModule() {
   
	}
	
	function ProcessRequest() {
		$Obj = new ProcessTransaksiPengendalian();
		$urlRedirect = $Obj->Add();
		$this->RedirectTo($urlRedirect);
		return NULL;
   }
   
	function ParseTemplate($data = NULL) {	
      
	}
}
?>
