<?php
/**
* ================= doc ====================
* FILENAME     : DoSaveJurnalPengeluaran.json.class.php
* @package     : DoSaveJurnalPengeluaran
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-13
* @Modified    : 2015-04-13
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_jurnal_pengeluaran/business/FinansiJurnalPengeluaran.php';

class DoSaveJurnalPengeluaran extends JsonResponse
{
   public function ProcessRequest()
   {
      $mObj       = new JurnalPengeluaran();
      $mProcess   = new FinansiJurnalPengeluaran();
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