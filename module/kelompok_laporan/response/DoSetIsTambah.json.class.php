<?php


require_once GTFWConfiguration::GetValue('application','docroot').
'module/kelompok_laporan/response/ProcessIsTambah.proc.class.php';

class DoSetIsTambah extends JsonResponse
{
   public function ProcessRequest()
   {
      $mObj          = new SetIsTambah();
      $mProcess      = new ProsessIsTambah();
      $process       = $mProcess->SetIsTambah();
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