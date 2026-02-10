<?php
/**
* ================= doc ====================
* FILENAME     : DoDeleteBp.json.class.php
* @package     : DoDeleteBp
* scope        : PUBLIC
* @Author      : noor hadi
* @Created     : 2016-06-14
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2016 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_sppu/business/FinansiSppu.php';

class DoDeleteBp extends JsonResponse
{
   public function ProcessRequest()
   {
      $mProcess   = new FinansiSppu();
      $mObj       = new Sppu();
      $process    = $mProcess->DeleteBp();

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