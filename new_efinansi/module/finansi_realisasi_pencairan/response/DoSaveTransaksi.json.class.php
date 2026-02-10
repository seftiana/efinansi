<?php
/**
* ================= doc ====================
* FILENAME     : DoSaveTransaksi.json.class.php
* @package     : DoSaveTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-02
* @Modified    : 2015-04-02
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_realisasi_pencairan/business/ProcessRealisasiPencairan.php';

class DoSaveTransaksi extends JsonResponse
{
   public function ProcessRequest()
   {
      $mObj       = new RealisasiPencairan();
      $mProcess   = new ProcessRealisasiPencairan();
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