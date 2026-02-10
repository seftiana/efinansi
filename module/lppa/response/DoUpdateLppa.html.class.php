<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/lppa/response/ProcessLppa.proc.class.php';

class DoUpdateLppa extends HtmlResponse 
{   
    public function TemplateModule() {}
   
    public function ProcessRequest() 
    {
        $Obj = new ProcessLppa();   
        $urlRedirect = $Obj->Update();     
        $this->RedirectTo($urlRedirect) ;      
        return NULL;
    }

    public function ParseTemplate($data = NULL) {}
    
}
?>