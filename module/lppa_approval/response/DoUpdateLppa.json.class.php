<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/lppa_approval/response/ProcessLppa.proc.class.php';

class DoUpdateLppa extends JsonResponse 
{

    public function TemplateModule() {}
   
    public function ProcessRequest() 
    {
        $Obj = new ProcessLppa();   
        $urlRedirect = $Obj->Update();
        return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1");');
    }
    
    public function ParseTemplate($data = NULL) {}
}

?>