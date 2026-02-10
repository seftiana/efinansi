<?php
/**
* ================= doc ====================
* FILENAME     : DoSaveTransaksi.json.class.php
* @package     : DoSaveTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-23
* @Modified    : 2015-04-23
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_pembayaran/business/Transaksi.php';

class DoSaveTransaksi extends JsonResponse
{
   public function ProcessRequest()
   {
      $mProcess      = new Transaksi();
      $mObj          = new TransaksiPembayaran();
      $process       = $mProcess->Save();
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