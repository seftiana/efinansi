<?php
/**
* ================= doc ====================
* FILENAME     : DoUpdateTransaksi.json.class.php
* @package     : DoUpdateTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-05-19
* @Modified    : 2015-05-19
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_penerimaan_kas/business/ProcessTransaksi.php';

class DoUpdateTransaksi extends JsonResponse
{
   public function ProcessRequest()
   {
      $mProcess      = new ProcessTransaksi();
      $mObj          = new TransaksiPenerimaanKas();
      $process       = $mProcess->Update();
      $url           = $process['url'];
      $message       = $process['message'];
      $data          = $process['data'];

      $module        = $mObj->getModule($url);
      $sub_module    = $mObj->getSubmodule($url);
      $action        = $mObj->getAction($url);
      $type          = $mObj->getType($url);
      Messenger::Instance()->Send(
         $module,
         $sub_module,
         $action,
         $type,
         array(
            $data,
            $message,
            $process['style']
         ),
         Messenger::NextRequest
      );

      return array(
         'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$url.'&ascomponent=1")'
      );
   }
}
?>