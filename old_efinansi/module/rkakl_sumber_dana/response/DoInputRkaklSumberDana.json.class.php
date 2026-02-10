<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/rkakl_sumber_dana/response/ProcessRkaklSumberDana.proc.class.php';

class DoInputRkaklSumberDana extends JsonResponse {

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
		 
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.
		$urlRedirect.'&ascomponent=1")');
	 }

	public function ParseTemplate($data = NULL) {}
}