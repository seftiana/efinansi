<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/realisasi_pencairan_2/response/ProcessSppBerdasarkanNoPengajuan.proc.class.php';

class DoDeleteSppBerdasarkanNoPengajuan extends HtmlResponse 
{

	public function TemplateModule() {}
   
	public function ProcessRequest() 
	{
		$Obj = new ProcessSppBerdasarkanNoPengajuan();         
		$urlRedirect = $Obj->Delete();     
		$this->RedirectTo($urlRedirect) ;      
		return NULL;
    }

	public function ParseTemplate($data = NULL) {}
	
}

?>