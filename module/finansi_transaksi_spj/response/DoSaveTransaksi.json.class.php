<?php
/**
* ================= doc ====================
* FILENAME     : DoSaveTransaksi.json.class.php
* @package     : DoSaveTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-18
* @Modified    : 2015-03-18
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_spj/business/ProcessTransaksiSpj.php';

class DoSaveTransaksi extends JsonResponse
{
   public function ProcessRequest()
   {
      $mProcess      = new ProcessTransaksiSpj();
      $mObj          = new TransaksiSpj();
      $process       = $mProcess->Save();
      $urlRedirect   = $process['url'];
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