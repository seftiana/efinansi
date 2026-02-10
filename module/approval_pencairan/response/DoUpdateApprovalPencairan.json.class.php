<?php
// require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
// 'module/approval_pencairan/response/ProcessApprovalPencairan.proc.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/approval_pencairan/business/ApprovalPencairan.php';

class DoUpdateApprovalPencairan extends JsonResponse
{
   function ProcessRequest() {
      $mObj       = new AppApprovalPencairan();
      $mProcess   = new ApprovalPencairan();
      $process    = $mProcess->doApproval();
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
            $process['data'],
            $process['message'],
            $process['style']
         ),
         Messenger::NextRequest
      );
      // $approval_pencairanObj = new ProcessApprovalPencairan();
      // $urlRedirect = $approval_pencairanObj->Update();
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
   }
}
?>
