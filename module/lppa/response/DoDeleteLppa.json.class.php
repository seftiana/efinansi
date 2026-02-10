<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/lppa/response/ProcessLppa.proc.class.php';

class DoDeleteLppa extends JsonResponse 
{

    public function TemplateModule() {}
   
    public function ProcessRequest() 
    {
        $Obj = new ProcessLppa();   
        $urlRedirect = $Obj->Delete();
        return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1");');
    }
    
    public function ParseTemplate($data = NULL) {}
}

?>