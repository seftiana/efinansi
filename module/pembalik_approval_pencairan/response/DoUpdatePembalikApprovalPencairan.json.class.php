<?php
// require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
// 'module/pembalik_approval_pencairan/response/ProcessPembalikApprovalPencairan.proc.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/pembalik_approval_pencairan/business/PembalikApprovalPencairan.php';

class DoUpdatePembalikApprovalPencairan extends JsonResponse {
   public function ProcessRequest() {
      $mProcess      = new PembalikApprovalPencairan();
      $mObj          = new AppPembalikApprovalPencairan();
      $process       = $mProcess->UnApprove();
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

      return array(
         'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")'
      );

   }
}
?>
