<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/finansi_coa_jenis_biaya/response/ProcessCoaJenisBiaya.proc.class.php';

class DoDeleteCoaJenisBiaya extends HtmlResponse {

	function TemplateModule() {}
	
	function ProcessRequest() {
		$Obj = new ProcessCoaJenisBiaya();
		$urlRedirect = $Obj->Delete();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {}
}
?>
