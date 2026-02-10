<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/posting/business/ProcessPosting.php';

class DoPosting extends JsonResponse
{
   function ProcessRequest() {
      $mProcess      = new ProcessPosting();
      $mObj          = new AppPosting();
      $process       = $mProcess->doPosting();
      $urlRedirect   = $process['url'];
      $module        = $mObj->getModule($urlRedirect);
      $subModule     = $mObj->getSubModule($urlRedirect);
      $action        = $mObj->getAction($urlRedirect);
      $type          = $mObj->getType($urlRedirect);

      Messenger::Instance()->Send(
         $module,
         $subModule,
         $action,
         $type,
         array(
            NULL,
            $process['message'],
            $process['style']
         ),
         Messenger::NextRequest
      );
      return array(
         'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")'
      );
   }
}
?>