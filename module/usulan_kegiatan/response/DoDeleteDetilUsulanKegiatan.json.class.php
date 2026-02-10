<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/usulan_kegiatan/response/ProcessDetilUsulanKegiatan.proc.class.php';

class DoDeleteDetilUsulanKegiatan extends JsonResponse {

	function TemplateModule() {}

	function ProcessRequest() {
		$obj = new ProcessDetilUsulanKegiatan();
		$urlRedirect = $obj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {}
}
?>
