<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/pagu_anggaran_unit_per_mak/response/ProcessPaguAnggaranUnitPerMak.proc.class.php';

class DoDeletePaguAnggaranUnitPerMak extends HtmlResponse {

	function TemplateModule() {
	}
	
	function ProcessRequest() {
		$usulan_kegiatanObj = new ProcessPaguAnggaranUnitPerMak();
		$urlRedirect = $usulan_kegiatanObj->Delete();
		$this->RedirectTo($urlRedirect);
		return NULL;
	 }
	function ParseTemplate($data = NULL) {	
	}
}
?>