<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/sub_account_ketiga/response/ProcessSubAccountKetiga.proc.class.php';

class DoUpdateSubAccountKetiga extends JsonResponse {

   public function ProcessRequest() {
      $mObj          = new SubAccountKetiga();
      $mProcess      = new ProcessSubAccountKetiga();
      $process       = $mProcess->Update();
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