<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
'module/copy_program_kegiatan/response/ProcessCopyProgram.proc.class.php';

class DoCopyProgramKegiatan extends JsonResponse
{
	public function TemplateModule() {}
	
	public function ProcessRequest() 
	{
		$Obj = new ProcessCopyProgram();
		$urlRedirect = $Obj->Copy();
		return array( 
				'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.
				$urlRedirect.'&ascomponent=1");');
	 }
	 
	public function ParseTemplate($data = NULL) {}
}