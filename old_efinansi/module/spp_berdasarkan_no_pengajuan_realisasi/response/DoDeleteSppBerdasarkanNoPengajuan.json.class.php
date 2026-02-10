<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/realisasi_pencairan_2/response/ProcessSppBerdasarkanNoPengajuan.proc.class.php';

class DoDeleteSppBerdasarkanNoPengajuan extends JsonResponse 
{
	public function ProcessRequest() 
	{
		$Obj = new ProcessSppBerdasarkanNoPengajuan();      
		$urlRedirect   = $Obj->Delete();      
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');       
   }
   
}
?>