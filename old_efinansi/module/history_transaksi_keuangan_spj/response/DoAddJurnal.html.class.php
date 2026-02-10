<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/history_transaksi_keuangan_spj/response/ProcessJurnal.proc.class.php';

class DoAddJurnal extends HtmlResponse 
{
	public function TemplateModule() {}
	   
	public function ProcessRequest() 
	{	   
		$Obj = new ProcJurnalUmum();         
		$urlRedirect = $Obj->Add();            
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	public function ParseTemplate($data = NULL) {}
	
}

?>