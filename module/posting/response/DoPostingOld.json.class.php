<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/posting/business/ProcessPosting.php';

class DoPostingOld extends JsonResponse
{
   function ProcessRequest() {print_r($_POST);die;
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