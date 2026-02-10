<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/history_transaksi_realisasi/response/ProcessTransaksi.proc.class.php';

class DoDeleteHTRealisasiPencairan extends JsonResponse 
{
	
	public function ProcessRequest() 
	{
		$Obj = new ProcessTransaksi();
		$urlRedirect = $Obj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	}

	public function ParseTemplate($data = NULL) {}
	
	
}

?>