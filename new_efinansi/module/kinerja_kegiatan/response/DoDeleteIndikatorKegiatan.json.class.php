<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/kinerja_kegiatan/response/ProcessIndikator.proc.class.php';

class DoDeleteIndikatorKegiatan extends JsonResponse {

	function TemplateModule() {}

	function ProcessRequest() {
		$obj = new ProcessIndikator();
		$urlRedirect = $obj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {}
}
?>
