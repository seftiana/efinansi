<?php
/**
* ================= doc ====================
* FILENAME     : DoDeleteDipa.json.class.php
* @package     : DoDeleteDipa
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

class DoDeleteDipa extends JsonResponse
{
   public function ProcessRequest()
   {
      $mObj          = new ProcessFinansiDipa();
      $process       = $mObj->Delete();
      $url_redirect  = $process['url'];

      if($process['return'] === false){
         Messenger::Instance()->Send(
            'finansi_dipa',
            'AddDipa',
            'view',
            'html',
            array(
               $process['data'],
               $process['message'],
               $process['style']
            ),
            Messenger::NextRequest
         );
      }else{
         Messenger::Instance()->Send(
            'finansi_dipa',
            'FinansiDipa',
            'view',
            'html',
            array(
               $process['data'],
               $process['message'],
               $process['style']
            ),
            Messenger::NextRequest
         );
      }

      return array(
         'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$url_redirect.'&ascomponent=1")'
      );
   }
}
?>