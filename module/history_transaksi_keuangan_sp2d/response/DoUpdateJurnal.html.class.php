<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/history_transaksi_keuangan_sp2d/response/ProcessJurnal.proc.class.php';

class DoUpdateJurnal extends HtmlResponse 
{
	public function TemplateModule() {}
	   
	public function ProcessRequest() 
	{	   
		$Obj = new ProcessJurnal();
		$urlRedirect = $Obj->Update();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	public function ParseTemplate($data = NULL) {}
	
}

?>