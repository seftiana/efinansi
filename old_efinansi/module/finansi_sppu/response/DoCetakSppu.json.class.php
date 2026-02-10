<?php
/**
* ================= doc ====================
* FILENAME     : DoSaveSppu.json.class.php
* @package     : DoSaveSppu
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-08
* @Modified    : 2015-04-08
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_sppu/business/Sppu.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_sppu/response/ProcessSppu.proc.class.php';

class DoCetakSppu extends JsonResponse
{
   public function ProcessRequest()
   {
      $mProcess      = new ProcessSppu();
      $mObj          = new Sppu();
      $process       = $mProcess->Add();
      $urlRedirect   = $process['url'];
      $message       = $process['message'];
      $style         = $process['style'];
      $data          = $process['data'];

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
            $data,
            $message,
            $style
         ),
         Messenger::NextRequest
      );

      return array(
         'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")'
      );
   }
}
?>