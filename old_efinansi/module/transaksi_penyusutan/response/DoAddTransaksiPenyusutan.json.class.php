<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/transaksi_penyusutan/response/ProcessTransaksiPenyusutan.proc.class.php';

class DoAddTransaksiPenyusutan extends JsonResponse {

	function TemplateModule() {
   
	}
	
	function ProcessRequest() {
		$Obj = new ProcessTransaksiPenyusutan();
		$urlRedirect = $Obj->Add(true);
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
   }

	function ParseTemplate($data = NULL) {
	}
}
?>
