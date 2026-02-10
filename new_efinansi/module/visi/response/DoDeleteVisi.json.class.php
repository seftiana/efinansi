<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/visi/response/ProcessVisi.proc.class.php';
#doc
#    classname:    DoDeleteVisi
#    scope:        PUBLIC
#
#/doc

class DoDeleteVisi extends JsonResponse
{
    function TemplateModule()
    {
        #code
    }
    
    function ProcessRequest()
    {
        $obj        = new ProcessVisi();
        $url_delete = $obj->Delete();
        
        return array( 
            'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$url_delete.'&ascomponent=1")'
        );
    }
}
?>
