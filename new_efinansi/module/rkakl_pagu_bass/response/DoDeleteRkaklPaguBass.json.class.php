<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_pagu_bass/response/ProcessRkaklPaguBass.proc.class.php';

class DoDeleteRkaklPaguBass extends JsonResponse {

	function TemplateModule() {}

	function ProcessRequest() {
		$obj = new ProcessRkaklPaguBass();
		$urlRedirect = $obj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {}
}
?>
