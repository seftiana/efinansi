<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/jurnal_penerimaan/response/ProcJurnalPenerimaan.proc.class.php';

class DoAddJurnalPenerimaan extends JsonResponse
{
   public function ProcessRequest()
   {
      $mObj       = new JurnalPenerimaan();
      $mProcess   = new ProcJurnalPenerimaan();
      $process    = $mProcess->Save();
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