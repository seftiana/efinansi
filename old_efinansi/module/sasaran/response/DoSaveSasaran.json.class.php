<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/sasaran/response/ProcessSasaran.proc.class.php';

#doc
#    classname:    DoSaveSasaran
#    scope:        PUBLIC
#
#/doc

class DoSaveSasaran extends JsonResponse
{
    function TemplateModule()
    {
        # code...
    }
    
    function ProcessRequest()
    {
        $obj            = new ProcessSasaran();
        $url_redirect   = $obj->Save();
        
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
