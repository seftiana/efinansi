<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/lppa_approval/response/ProcessLppa.proc.class.php';

class DoAddLppa extends HtmlResponse 
{   
    public function TemplateModule() {}
   
    public function ProcessRequest() 
    {
        $Obj = new ProcessLppa();   
        $urlRedirect = $Obj->Add();     
        $this->RedirectTo($urlRedirect) ;      
        return NULL;
    }

    public function ParseTemplate($data = NULL) {}
    
}
?>