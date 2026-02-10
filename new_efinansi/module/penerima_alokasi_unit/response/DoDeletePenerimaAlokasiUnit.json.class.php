<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
			'module/penerima_alokasi_unit/response/ProcessPenerimaAlokasiUnit.proc.class.php';

class DoDeletePenerimaAlokasiUnit extends  JsonResponse
{
	
	public function ProcessRequest() 
	{
		$obj = new ProcessPenerimaAlokasiUnit();
		$urlRedirect = $obj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.
					$urlRedirect.'&ascomponent=1")');
	 }

}
?>