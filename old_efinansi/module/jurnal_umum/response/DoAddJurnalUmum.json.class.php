<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/jurnal_umum/business/ProcessJurnal.php';

class DoAddJurnalUmum extends JsonResponse
{
   function ProcessRequest() {
      $mObj       = new JurnalUmum();
      $mProcess   = new ProcessJurnal();
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