<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_kegiatan/response/ProcessRkaklKegiatan.proc.class.php';

class DoDeleteRkaklKegiatan extends JsonResponse {

	function TemplateModule() {}

	function ProcessRequest() {
		$obj = new ProcessRkaklKegiatan();
		$urlRedirect = $obj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {}
}
?>
