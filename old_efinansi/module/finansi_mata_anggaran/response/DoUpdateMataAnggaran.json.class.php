<?php
/**
* ================= doc ====================
* FILENAME     : DoUpdateMataAnggaran.json.class.php
* @package     : DoUpdateMataAnggaran
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-29
* @Modified    : 2015-03-29
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_mata_anggaran/business/ProcessMataAnggaran.php';

class DoUpdateMataAnggaran extends JsonResponse
{
   public function ProcessRequest()
   {
      $mProcess      = new ProcessMataAnggaran();
      $mObj          = new MataAnggaran();

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