<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/referensi_mak/response/ProcessReferensiMak.proc.class.php';

class DoDeleteReferensiMak extends JsonResponse {

	function TemplateModule() {}

	function ProcessRequest() {
		$obj = new ProcessReferensiMak();
		$urlRedirect = $obj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {}
}
?>