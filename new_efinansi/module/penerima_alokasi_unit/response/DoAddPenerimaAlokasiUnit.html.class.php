<?php  if ( ! defined('GTFW_BASE_DIR')) exit('No direct script access allowed');

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
			'module/penerima_alokasi_unit/response/ProcessPenerimaAlokasiUnit.proc.class.php';

class DoAddPenerimaAlokasiUnit extends HtmlResponse 
{
	public function TemplateModule() {}
	
	public function ProcessRequest() 
	{
		$obj = new ProcessPenerimaAlokasiUnit();
		$urlRedirect = $obj->Add();
		$this->RedirectTo($urlRedirect);
		return NULL;
	 }
	public function ParseTemplate($data = NULL) {}
}
?>