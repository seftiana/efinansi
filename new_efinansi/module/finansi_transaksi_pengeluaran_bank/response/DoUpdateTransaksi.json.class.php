<?php
/**
* ================= doc ====================
* FILENAME     : DoUpdateTransaksi.json.class.php
* @package     : DoUpdateTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-14
* @Modified    : 2015-04-14
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_pengeluaran_bank/business/ProcessTransaksi.php';

class DoUpdateTransaksi extends JsonResponse
{
   public function ProcessRequest()
   {
      $mObj       = new TransaksiPengeluaranBank();
      $mProcess   = new ProcessTransaksi();
      $process    = $mProcess->Update();
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