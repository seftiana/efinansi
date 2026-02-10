<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/pagu_anggaran_unit/response/ProcessPaguAnggaranUnit.proc.class.php';

class DoCopyPaguAnggaranUnit extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$usulan_kegiatanObj = new ProcessPaguAnggaranUnit();
		$urlRedirect = $usulan_kegiatanObj->Copy();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	 }

	function ParseTemplate($data = NULL) {
	}
}
?>
