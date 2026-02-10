<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/misi/response/ProcessMisi.proc.class.php';
#doc
#    classname:    DoDeleteMisi
#    scope:        PUBLIC
#
#/doc

class DoDeleteMisi extends JsonResponse
{
    function TemplateModule()
    {
        # code...
    }
    
    function ProcessRequest()
    {
        $obj            = new ProcessMisi();
        $url_redirect   = $obj->Delete();
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
