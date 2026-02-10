<?php
/**
* ================= doc ====================
* FILENAME     : DoDeleteJurnalPengeluaran.json.class.php
* @package     : DoDeleteJurnalPengeluaran
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-14
* @Modified    : 2015-04-14
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_jurnal_pengeluaran/business/FinansiJurnalPengeluaran.php';

class DoDeleteJurnalPengeluaran extends JsonResponse
{
   public function ProcessRequest()
   {
      $mObj       = new JurnalPengeluaran();
      $mProcess   = new FinansiJurnalPengeluaran();
      $process    = $mProcess->Delete();
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