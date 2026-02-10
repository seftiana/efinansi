<?php

require_once GTFWConfiguration::GetValue('application','docroot').
'module/movement_anggaran_approval/response/ProcessMovementApproval.proc.class.php';

class DoUpdateMovementApproval extends JsonResponse
{

    public function TemplateModule(){}
        
    public function ProcessRequest()
    {
        $obj        = new ProcessMovementAnggaranApproval;
        $url_redirect = $obj->Approval();        
        return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$url_redirect.'&ascomponent=1")');     
    }
        
    public function ParseTemplate($data = null){}

}
    
?>