<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rencana_penerimaan_adjust/response/ProcessAdjustment.proc.class.php';

#doc
#    classname:    DoAdjust
#    scope:        PUBLIC
#
#/doc

class DoAdjust extends HtmlResponse
{
    function TemplateModule()
    {
        # code
    }
    
    function ProcessRequest()
    {
        $obj            = new ProcessAdjustment();
        $url_redirect   = $obj->Adjust();
        
        $this->RedirectTo($url_redirect);
        
        return NULL;
    }
    
    function ParseTemplate($data = null)
    {
        # code
    }

}
?>
