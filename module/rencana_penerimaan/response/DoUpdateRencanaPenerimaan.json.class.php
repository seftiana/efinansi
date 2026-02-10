<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/rencana_penerimaan/business/ProcessRencanaPenerimaan.proc.class.php';

class DoUpdateRencanaPenerimaan extends JsonResponse
{

   function ProcessRequest() {
      $mObj          = new AppRencanaPenerimaan();
      $mProcess      = new ProcessRencanaPenerimaan();
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

      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
   }
}
?>