<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/tahun_pembukuan/response/ProcessSaldo.proc.class.php';

class DoDeleteSaldoAwal extends JsonResponse {

	function TemplateModule() {}

	function ProcessRequest() {
		$Obj = new ProcessSaldo();
		$Obj->SetPost($_POST);
		$urlRedirect = $Obj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	function ParseTemplate($data = NULL) {}
}
?>
