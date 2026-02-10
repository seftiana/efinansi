<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rencana_penerimaan_adjust_history/response/ProcessAdjustment.proc.class.php';

#doc
#    classname:    DoApprovalAdjustment
#    scope:        PUBLIC
#
#/doc

class DoApprovalAdjustment extends HtmlResponse
{
    function TemplateModule()
    {
        # code...
    }
    
    function ProcessRequest()
    {
        $obj            = new ProcessAdjustment();
        $url_redirect   = $obj->Approval();
        $this->RedirectTo($url_redirect);
        
        return null;
    }
    
    function ParseTemplate($data = null)
    {
        # code...
    }
}
?>
