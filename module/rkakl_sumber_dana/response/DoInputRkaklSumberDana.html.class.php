<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/rkakl_sumber_dana/response/ProcessRkaklSumberDana.proc.class.php';

class DoInputRkaklSumberDana extends HtmlResponse {

	public function TemplateModule() {}
	
	public function ProcessRequest() 
	{
		$Obj = new ProcessRkaklSumberDana();
		if ($_GET['dataId']!=""){
         	$urlRedirect = $Obj->Update();
  		}
		else {	
			$urlRedirect = $Obj->Add();
		}
		 
		$this->RedirectTo($urlRedirect);
		return NULL;
	 }
	function ParseTemplate($data = NULL) {}
}