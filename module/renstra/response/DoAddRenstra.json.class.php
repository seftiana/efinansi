<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/renstra/business/ProcessRenstra.php';

class DoAddRenstra extends JsonResponse
{
   function ProcessRequest() {
      $Obj           = new ProcessRenstra();
      $process       = $Obj->Save();

      if($process['redirect'] == false){
         Messenger::Instance()->Send(
            'renstra',
            'InputRenstra',
            'view',
            'html',
            array(
               $process['data'],
               $process['message'],
               $process['style']
            ),
            Messenger::NextRequest
         );
      }else{
         Messenger::Instance()->Send(
            'renstra',
            'Renstra',
            'view',
            'html',
            array(
               NULL,
               $process['message'],
               $process['style']
            ),
            Messenger::NextRequest
         );
      }
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$process['url'].'&ascomponent=1")');
   }
}
?>