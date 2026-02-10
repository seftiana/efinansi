<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/jumlah_kelas_per_unit/response/ProcessJumlahKelasPerUnit.proc.class.php';

class DoDeleteJumlahKelasPerUnit extends HtmlResponse 
{

    public function TemplateModule() {}
    
    public function ProcessRequest() 
    {
        $mObj = new ProcessJumlahKelasPerUnit;
		$urlRedirect = $mObj->Delete();
		$this->RedirectTo($urlRedirect);
		return NULL;
	}
	
	public function ParseTemplate($data = NULL) {}
    
}

?>