<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/sub_account_kedua/response/ProcessSubAccountKedua.proc.class.php';

class DoDeleteSubAccountKedua extends JsonResponse {

   public function ProcessRequest(){
      $mObj          = new SubAccountKedua();
      $mProcess      = new ProcessSubAccountKedua();
      $process       = $mProcess->Delete();
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