<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_sub_kegiatan/response/ProcessRkaklSubKegiatan.proc.class.php';

class DoDeleteRkaklSubKegiatan extends JsonResponse {

	function TemplateModule() {}

	function ProcessRequest() {
		$obj = new ProcessRkaklSubKegiatan();
		$urlRedirect = $obj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {}
}
?>
