<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/history_transaksi_keuangan_spj/response/ProcessJurnal.proc.class.php';

class DoUpdateJurnal extends JsonResponse 
{

	public function TemplateModule() {}
   
	public function ProcessRequest() 
	{
		$Obj = new ProcessJurnal();
		$urlRedirect = $Obj->Update();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
    }

	public function ParseTemplate($data = NULL) {}
	
}

?>