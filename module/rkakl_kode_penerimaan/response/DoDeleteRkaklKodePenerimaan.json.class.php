<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/rkakl_kode_penerimaan/response/ProcessRkaklKodePenerimaan.proc.class.php';

class DoDeleteRkaklKodePenerimaan extends JsonResponse {

	function TemplateModule() {}

	function ProcessRequest() {
		$obj = new ProcessRkaklKodePenerimaan();
		$urlRedirect = $obj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {}
}
?>
