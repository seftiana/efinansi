<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
			'module/penerima_alokasi_unit/response/ProcessPenerimaAlokasiUnit.proc.class.php';

class DoDeletePenerimaAlokasiUnit extends HtmlResponse 
{

	public function TemplateModule() {}
	
	public function ProcessRequest() 
	{
		$obj = new ProcessPenerimaAlokasiUnit();
		$urlRedirect = $obj->Delete();
		$this->RedirectTo($urlRedirect);
		return NULL;
	 }
	
	public function ParseTemplate($data = NULL) {}
}
?>