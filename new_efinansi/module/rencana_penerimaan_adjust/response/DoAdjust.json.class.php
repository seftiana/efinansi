<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rencana_penerimaan_adjust/response/ProcessAdjustment.proc.class.php';

#doc
#    classname:    DoAdjust
#    scope:        PUBLIC
#
#/doc

class DoAdjust extends JsonResponse
{
    function TemplateModule()
    {
        # code
    }
    
    function ProcessRequest()
    {
        $obj            = new ProcessAdjustment();
        $url_redirect   = $obj->Adjust();
        return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$url_redirect.'&ascomponent=1")');
    }
    
    function ParseTemplate($data = null)
    {
        # code
    }

}
?>
