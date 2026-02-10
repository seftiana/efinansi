<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/usulan_kegiatan/response/ProcessDetilUsulanKegiatan.proc.class.php';

class DoAddDetilUsulanKegiatan extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$obj = new ProcessDetilUsulanKegiatan();
		$urlRedirect = $obj->Add();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	 }

	function ParseTemplate($data = NULL) {
	}
}
?>
