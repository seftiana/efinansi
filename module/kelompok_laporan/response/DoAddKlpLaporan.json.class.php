<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kelompok_laporan/response/ProcessKlpLaporan.proc.class.php';

class DoAddKlpLaporan extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessKlpLaporan();
		$urlRedirect = $Obj->Add();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	 }

	function ParseTemplate($data = NULL) {
	}
}
?>
