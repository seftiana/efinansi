<?php

require_once GTFWConfiguration::GetValue('application','docroot').
'module/movement_anggaran_approval/response/ProcessMovementApproval.proc.class.php';

class DoUpdateMovementApproval extends HtmlResponse
{

    public function TemplateModule(){}
        
    public function ProcessRequest()
    {
        $obj        = new ProcessMovementAnggaranApproval;
        $url_redirect   = $obj->Approval();            
        $this->RedirectTo($url_redirect);
    }
        
    public function ParseTemplate($data = null){}
}

?>