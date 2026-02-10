<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_output/response/ProcessRkakl.proc.class.php';

class DoDeleteRkakl extends JsonResponse {

	function TemplateModule() {}

	function ProcessRequest() {
		$obj = new ProcessRkakl();
		$urlRedirect = $obj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {}
}
?>
