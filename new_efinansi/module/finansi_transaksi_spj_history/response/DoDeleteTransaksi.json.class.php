<?php
/**
* ================= doc ====================
* FILENAME     : DoDeleteTransaksi.json.class.php
* @package     : DoDeleteTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-27
* @Modified    : 2015-04-27
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_spj_history/business/ProcessTransaksiSpj.php';

class DoDeleteTransaksi extends JsonResponse
{
   public function ProcessRequest()
   {
      $mProcess      = new ProcessTransaksiSpj();
      $mObj          = new HistoryTransaksiSpj();
      $process       = $mProcess->Delete();
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