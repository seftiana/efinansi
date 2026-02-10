<?php
/**
* ================= doc ====================
* FILENAME     : DoSetAktif.json.class.php
* @package     : DoSetAktif
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-02-09
* @Modified    : 2015-02-09
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/periode_tahun/business/ProcessPeriodeTahun.php';

class DoSetAktif extends JsonResponse
{
   public function ProcessRequest()
   {
      $mObj          = new PeriodeTahun();
      $mProcess      = new ProcessPeriodeTahun();
      $process       = $mProcess->SetAktif();
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