<?php
/**
* ================= doc ====================
* FILENAME     : DoUpdateDetailBelanja.json.class.php
* @package     : DoUpdateDetailBelanja
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-01-08
* @Modified    : 2014-01-08
* @Analysts    : Dyah Fajar
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/response/DetailBelanja.proc.class.php';

class DoUpdateDetailBelanja extends JsonResponse
{
   public function ProcessRequest()
   {
      $mObj          = new DetailBelanja();
      $return        = $mObj->Update();

      $urlRedirect   = $return['url'];
      $destination   = ($return['redirect'] === false) ? 'box-subcontent-element' : 'subcontent-element';

      return array( 
         'exec' => 'GtfwAjax.replaceContentWithUrl("'.$destination.'","'.$urlRedirect.'&ascomponent=1")'
      );
      
   }
}
?>