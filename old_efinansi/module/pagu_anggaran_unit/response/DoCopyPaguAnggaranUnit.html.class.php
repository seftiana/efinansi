<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/pagu_anggaran_unit/response/ProcessPaguAnggaranUnit.proc.class.php';

class DoCopyPaguAnggaranUnit extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$usulan_kegiatanObj = new ProcessPaguAnggaranUnit();
		$urlRedirect = $usulan_kegiatanObj->Copy();
		$this->RedirectTo($urlRedirect);
		return NULL;
	 }
	function ParseTemplate($data = NULL) {	
	}
}
?>
