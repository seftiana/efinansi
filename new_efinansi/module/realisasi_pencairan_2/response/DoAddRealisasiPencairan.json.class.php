<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/realisasi_pencairan_2/response/ProcessRealisasiPencairan.proc.class.php';

class DoAddRealisasiPencairan extends JsonResponse
{
   function ProcessRequest()
   {
      $Obj           = new ProcessRealisasiPencairan();
      $urlRedirect   = $Obj->Add();
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
   }
}
?>