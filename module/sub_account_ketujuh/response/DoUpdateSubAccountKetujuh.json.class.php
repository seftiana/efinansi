<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/sub_account_ketujuh/response/ProcessSubAccountKetujuh.proc.class.php';

class DoUpdateSubAccountKetujuh extends JsonResponse {

   public function ProcessRequest() {
      $mObj          = new SubAccountKetujuh();
      $mProcess      = new ProcessSubAccountKetujuh();
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