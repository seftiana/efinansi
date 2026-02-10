<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/pm_jenis_biaya/response/ProcessJenisbiaya.proc.class.php';

class DoOrderJenisbiaya extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$jenisbiayaObj = new ProcessJenisbiaya();
		$urlRedirect = $jenisbiayaObj->Order();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
