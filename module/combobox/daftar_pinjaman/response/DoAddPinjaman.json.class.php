<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/daftar_pinjaman/response/ProcessPinjaman.proc.class.php';

class DoAddPinjaman extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$propObj = new ProcessPinjaman();
		$urlRedirect = $propObj->Add();
		return array('exec'=>'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	 }
	function ParseTemplate($data = NULL) {	
	}
}
?>