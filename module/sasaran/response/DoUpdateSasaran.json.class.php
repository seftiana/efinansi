<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/sasaran/response/ProcessSasaran.proc.class.php';

#doc
#    classname:    DoUpdateSasaran
#    scope:        PUBLIC
#
#/doc

class DoUpdateSasaran extends JsonResponse
{
    function TemplateModule()
    {
        # code...
    }
    
    function ProcessRequest()
    {
        $obj            = new ProcessSasaran();
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
