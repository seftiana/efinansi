<?php
/**
 * @modified 2015-03-25
 */
require_once GTFWConfiguration::GetValue('application','docroot').
'module/approval/business/ProcessApproval.php';

class DoUpdateDetilApproval extends JsonResponse
{
   function ProcessRequest() {
      $mObj          = new AppDetilApproval();
      $mProcess      = new ProcessApproval();
      $process       = $mProcess->Appove();
      $urlRedirect   = $process['url'];
      $dest          = is_null($process['dest']) ? 'subcontent-element' : $process['dest'];
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
            $process['data'],
            $process['message'],
            $process['style']
         ),
         Messenger::NextRequest
      );

      return array(
         'exec' => 'GtfwAjax.replaceContentWithUrl("'.$dest.'","'.$urlRedirect.'&ascomponent=1")'
      );
   }
}
?>