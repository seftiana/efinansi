<?php
/**
* ================= doc ====================
* FILENAME     : DoDeleteApprovalJurnal.json.class.php
* @package     : DoDeleteApprovalJurnal
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-02-23
* @Modified    : 2015-02-23
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/approval_jurnal/business/ProcessApprovalJurnal.php';

class DoDeleteApprovalJurnal extends JsonResponse
{
   function ProcessRequest() {
      $mObj          = new ApprovalJurnal();
      $mProcess      = new ProcessApprovalJurnal();
      $process       = $mProcess->UnApprove();
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