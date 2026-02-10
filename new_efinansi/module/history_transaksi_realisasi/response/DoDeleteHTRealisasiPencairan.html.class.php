<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/history_transaksi_realisasi/response/ProcessTransaksi.proc.class.php';

class DoDeleteHTRealisasiPencairan extends HtmlResponse 
{

	public function TemplateModule() {}
	
	public function ProcessRequest() 
	{
		$Obj = new ProcessTransaksi();
		$urlRedirect = $Obj->Delete();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	public function ParseTemplate($data = NULL) {}
	
}

?>