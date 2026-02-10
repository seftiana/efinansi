<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/jumlah_kelas_per_unit/response/ProcessJumlahKelasPerUnit.proc.class.php';

class DoDeleteJumlahKelasPerUnit extends JsonResponse
{
	
	public function ProcessRequest() 
	{
		$mObj = new ProcessJumlahKelasPerUnit;
		$urlRedirect = $mObj->Delete();
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	 }

}

?>