<?php
/**
* ================= doc ====================
* FILENAME     : DoSetStatus.json.class.php
* @package     : DoSetStatus
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-12-10
* @Modified    : 2014-12-10
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_dipa/business/ProcessFinansiDipa.php';

class DoSetStatus extends JsonResponse
{
   public function ProcessRequest()
   {
      $mObj          = new ProcessFinansiDipa();
      $process       = $mObj->DoChangeStatus();
      $url_redirect  = $process['url'];
      Messenger::Instance()->Send(
         'finansi_dipa',
         'FinansiDipa',
         'view',
         'html',
         array(
            NULL,
            $process['message'],
            $process['style']
         ),
         Messenger::NextRequest
      );

      return array(
         'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$url_redirect.'&ascomponent=1")'
      );
   }
}
?>