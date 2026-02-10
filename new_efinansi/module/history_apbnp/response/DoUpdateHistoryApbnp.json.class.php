<?php

require_once GTFWConfiguration::GetValue('application','docroot').
    'module/history_apbnp/response/ProcessHistoryApbnp.proc.class.php';


class DoUpdateHistoryApbnp extends JsonResponse
{
   
   public function ProcessRequest()
   {    
        $objProcess = new ProcessHistoryApbnp;      
        $urlRedirect = $objProcess->Update();     
        return array( 
            'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")'
        );      
   }
   
}

?>