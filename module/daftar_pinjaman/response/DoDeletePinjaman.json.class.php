<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/daftar_pinjaman/response/ProcessPinjaman.proc.class.php';

class DoDeletePinjaman extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		
		$propObj = new ProcessPinjaman();
		$urlRedirect = $propObj->Delete();
		return array('exec'=>'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	 }
	function ParseTemplate($data = NULL) {	
	
	}
}
?>