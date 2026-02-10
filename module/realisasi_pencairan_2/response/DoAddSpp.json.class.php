<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot').
'module/realisasi_pencairan_2/response/ProcessSpp.proc.class.php';

class DoAddSpp extends JsonResponse
{
   function ProcessRequest(){
      $obj           = new ProcessSpp();
      $url_redirect  = $obj->Add();
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$url_redirect.'&ascomponent=1")');
   }
}
?>