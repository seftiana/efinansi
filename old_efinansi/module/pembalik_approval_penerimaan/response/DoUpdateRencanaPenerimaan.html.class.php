<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/pembalik_approval_penerimaan/response/ProcessRencanaPenerimaan.proc.class.php';

class DoUpdateRencanaPenerimaan extends HtmlResponse {

	function TemplateModule() {}
	
	function ProcessRequest() {
		$Obj = new ProcessRencanaPenerimaan();
		$urlRedirect = $Obj->Update();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}

	function ParseTemplate($data = NULL) {
	}
}
?>
