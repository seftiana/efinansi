<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/usulan_kegiatan/response/ProcessUsulanKegiatan.proc.class.php';

class DoDeleteUsulanKegiatan extends JsonResponse {

	function TemplateModule() {}

	function ProcessRequest() {
		$usulan_kegiatanObj = new ProcessUsulanKegiatan();
		$urlRedirect = $usulan_kegiatanObj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {}
}
?>
