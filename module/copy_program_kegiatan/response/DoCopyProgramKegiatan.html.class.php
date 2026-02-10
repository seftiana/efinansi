<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
'module/copy_program_kegiatan/response/ProcessCopyProgram.proc.class.php';

class DoCopyProgramKegiatan extends HtmlResponse
{
    public function TemplateModule() {}
	
	public function ProcessRequest() {
		$Obj = new ProcessCopyProgram();
		$urlRedirect = $Obj->Copy();
		$this->RedirectTo($urlRedirect);
		return NULL;
	 }
	public function ParseTemplate($data = NULL) {}
}