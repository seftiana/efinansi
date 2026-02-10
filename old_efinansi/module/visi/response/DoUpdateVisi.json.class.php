<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/visi/response/ProcessVisi.proc.class.php';

#doc
#    classname:    DoUpdateVisi
#    scope:        PUBLIC
#
#/doc

class DoUpdateVisi extends JsonResponse
{
    function TemplateModule()
    {
        # code...
    }
    
    function ProcessRequest()
    {
        $obj            = new ProcessVisi();
        $url_redirect   = $obj->Update();
        
        return array( 
            'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$url_redirect.'&ascomponent=1")'
        );
        
    }
    
    function ParseTemplate($data = null)
    {
        # code...
    }

}
?>
