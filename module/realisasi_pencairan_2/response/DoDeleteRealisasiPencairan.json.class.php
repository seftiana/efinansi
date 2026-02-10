<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/realisasi_pencairan_2/response/ProcessRealisasiPencairan.proc.class.php';

class DoDeleteRealisasiPencairan extends JsonResponse {

   function TemplateModule() {
   }
   
   function ProcessRequest() {

      $Obj = new ProcessRealisasiPencairan();   
      
      $urlRedirect = $Obj->Delete();
      
      return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")');       
    }

   function ParseTemplate($data = NULL) {
   }
}
?>
