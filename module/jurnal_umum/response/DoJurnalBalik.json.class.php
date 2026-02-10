<?php
/**
* ================= doc ====================
* FILENAME     : DoJurnalBalik.json.class.php
* @package     : DoJurnalBalik
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-02-22
* @Modified    : 2015-02-22
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/jurnal_umum/business/ProcessJurnal.php';

class DoJurnalBalik extends JsonResponse
{
   function ProcessRequest() {
      $mObj       = new JurnalUmum();
      $mProcess   = new ProcessJurnal();
      $process    = $mProcess->JurnalBalik();
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