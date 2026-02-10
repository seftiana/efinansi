<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/renstra/business/ProcessRenstra.php';

class DoSetAktifRenstra extends JsonResponse
{
   public function ProcessRequest()
   {
      $Obj           = new ProcessRenstra();
      $process       = $Obj->setActive();

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

      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$process['url'].'&ascomponent=1")');
   }
}
?>