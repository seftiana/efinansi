<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/realisasi_pencairan/response/ProcessRealisasiPencairan.proc.class.php';

class DoUpdateRealisasiPencairan extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {
      $Obj = new ProcessRealisasiPencairan();
      
      $urlRedirect = $Obj->Update();
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');
   }

   function ParseTemplate($data = NULL) {
   }
}
?>
