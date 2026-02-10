<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/visi/response/ProcessVisi.proc.class.php';

#doc
#    classname:    DoSaveVisi
#    scope:        PUBLIC
#
#/doc

class DoSaveVisi extends JsonResponse
{
    function TemplateModule()
    {
        # code...
    }
    
    function ProcessRequest()
    {
        $obj            = new ProcessVisi();
        $url_redirect   = $obj->Save();
        
        return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$url_redirect.'&ascomponent=1")');
        
    }
    
    function ParseTemplate($data = null)
    {
        # code
    }
}
?>
