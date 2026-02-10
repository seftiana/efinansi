<?php
/**
* Module : realisasi_pencairan_2
* FileInclude : ProcessSpp.proc.class.php
* Class : DoUpdateSpp
* Extends : JsonResponse
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/realisasi_pencairan_2/response/ProcessSpp.proc.class.php';

class DoUpdateSpp extends JsonResponse
{
   function ProcessRequest(){
      $obj              = new ProcessSpp();
      $url_redirect     = $obj->Update();
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$url_redirect.'&ascomponent=1")');
   }
}
?>
