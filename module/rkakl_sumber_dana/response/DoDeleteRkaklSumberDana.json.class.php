<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
 'module/rkakl_sumber_dana/response/ProcessRkaklSumberDana.proc.class.php';

class DoDeleteRkaklSumberDana extends JsonResponse {

	public function TemplateModule() {}

	public function ProcessRequest() 
	{
		$obj = new ProcessRkaklSumberDana();
		$urlRedirect = $obj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.
		 	$urlRedirect.'&ascomponent=1")');
	}

	public function ParseTemplate($data = NULL) {}
}