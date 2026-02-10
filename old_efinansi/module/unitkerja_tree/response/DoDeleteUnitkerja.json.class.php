<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/unitkerja_tree/response/ProcessUnitkerja.proc.class.php';

class DoDeleteUnitkerja extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$unitkerjaObj = new ProcessUnitkerja();
		$urlRedirect = $unitkerjaObj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {
		
	}
}
?>
