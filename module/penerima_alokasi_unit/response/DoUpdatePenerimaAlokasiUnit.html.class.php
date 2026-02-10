<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
			'module/penerima_alokasi_unit/response/ProcessPenerimaAlokasiUnit.proc.class.php';

class DoUpdatePenerimaAlokasiUnit extends HtmlResponse 
{
	public function TemplateModule() {}
	
	public function ProcessRequest() 
	{
		$obj = new ProcessPenerimaAlokasiUnit();
		$urlRedirect = $obj->Update();
		$this->RedirectTo($urlRedirect);
		return NULL;
	 }
	function ParseTemplate($data = NULL) {	
	}
}
?>