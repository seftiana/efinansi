<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/finansi_coa_jenis_biaya/response/ProcessCoaJenisBiaya.proc.class.php';

class DoUpdateCoaJenisBiaya extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$Obj = new ProcessCoaJenisBiaya();
		$urlRedirect = $Obj->Update();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
