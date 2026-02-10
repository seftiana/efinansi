<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/kelompok_laporan/response/ProcessDetilKlpLapSummary.proc.class.php';

class DoUpdateDetilKlpLapSummary extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessDetilKlpLapSummary();
		$urlRedirect = $Obj->Update();
		return array( 'exec' => 
            'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
