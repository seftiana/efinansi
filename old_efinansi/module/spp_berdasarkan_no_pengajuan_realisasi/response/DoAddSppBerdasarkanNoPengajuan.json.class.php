<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/realisasi_pencairan_2/response/ProcessSppBerdasarkanNoPengajuan.proc.class.php';


class DoAddSppBerdasarkanNoPengajuan extends JsonResponse 
{

	public function ProcessRequest() 
	{
		$Obj = new ProcessSppBerdasarkanNoPengajuan();
        $urlRedirect  = $Obj->Add();		
		return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
	} 
	   
}
?>