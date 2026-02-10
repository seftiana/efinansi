<?php
/**
* ================= doc ====================
* FILENAME     : DoSaveSppu.json.class.php
* @package     : DoSaveSppu
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-08
* @Modified    : 2015-04-08
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_sppu/business/FinansiSppu.php';

require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_sppu/business/GetCookieSppu.class.php';

class DoSaveSppu extends JsonResponse
{
   public function ProcessRequest()
   {
      $mProcess   = new FinansiSppu();
      $mObj       = new Sppu();
      $process    = $mProcess->Save();
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

      //hapus cookies 
      $cookieSPPU= new GetCookieSppu(); 
      $getCookie = json_encode($cookieSPPU->get());

      //end hapus cookies
      return array(
         'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1");
          var getCookie = '.$getCookie.';
          if(getCookie.length > 0) {
             for (var i = 0; i < getCookie.length; i++){ 
                $.removeCookie("myCBId_"+getCookie[i], +getCookie[i]);
             }
          }'
      );
   }
}
?>