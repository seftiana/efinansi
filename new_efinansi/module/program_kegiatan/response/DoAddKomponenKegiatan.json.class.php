<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/program_kegiatan/response/ProcessKomponenKegiatan.proc.class.php';

class DoAddKomponenKegiatan extends JsonResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {

		$Obj = new ProcessKomponenKegiatan();	
		
		$urlRedirect = $Obj->Add();
		
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');		 
	 }

	function ParseTemplate($data = NULL) {
	}
}
?>
