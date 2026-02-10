<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/finansi_coa_jenis_biaya/response/ProcessDetilCoaJenisBiaya.proc.class.php';

class DoDeleteDetilCoaJenisBiaya extends JsonResponse {

	function TemplateModule() {}

	function ProcessRequest() {
		$Obj = new ProcessDetilCoaJenisBiaya();
		$urlRedirect = $Obj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {}
}
?>
