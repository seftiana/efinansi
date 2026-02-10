<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_program/response/ProcessRkaklProgram.proc.class.php';

class DoDeleteRkaklProgram extends JsonResponse {

	function TemplateModule() {}

	function ProcessRequest() {
		$obj = new ProcessRkaklProgram();
		$urlRedirect = $obj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {}
}
?>
