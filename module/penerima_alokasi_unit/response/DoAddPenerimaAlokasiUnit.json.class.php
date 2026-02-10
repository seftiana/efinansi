<?php
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
			'module/penerima_alokasi_unit/response/ProcessPenerimaAlokasiUnit.proc.class.php';

class DoAddPenerimaAlokasiUnit extends JsonResponse 
{

	public function ProcessRequest() 
	{
		$obj = new ProcessPenerimaAlokasiUnit();
		$urlRedirect = $obj->Add();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.
						$urlRedirect.'&ascomponent=1")');
	 }

}
?>
