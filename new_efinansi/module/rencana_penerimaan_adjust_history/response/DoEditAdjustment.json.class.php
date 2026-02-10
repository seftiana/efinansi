<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rencana_penerimaan_adjust_history/response/ProcessAdjustment.proc.class.php';

#doc
#    classname:    DoEditAdjustment
#    scope:        PUBLIC
#
#/doc

class DoEditAdjustment extends JsonResponse
{
    
    function TemplateModule()
    {
        #code
    }
    
    function ProcessRequest()
    {
        $obj            = new ProcessAdjustment();
        $url_redirect   = $obj->Update();
        return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$url_redirect.'&ascomponent=1")');
    }
    
    function ParseTemplate($data = null)
    {
        # code...
    }

}
?>
